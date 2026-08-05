<?php
defined('BASEPATH') or exit('No direct script access allowed');

use Aws\S3\S3Client;
use Aws\Exception\AwsException;

class Clocking_job_cron extends CI_Controller
{
    private $should_run = true;
    private $max_memory = 134217728; // 128MB default
    private $sleep_time_no_jobs = 5;  // Seconds to sleep when no jobs
    private $sleep_time_after_batch = 1; // Seconds after processing a batch
    private $is_debug = false; // Debug flag to control console output

    public function __construct()
    {
        parent::__construct();
        $this->load->helper('url');
        $this->load->helper('general_helper');

        // Allow CLI or specific debug tokens
        if (!is_cli() && $this->input->get('debug') !== '1' && $this->input->get('code') !== 'clocking_fast_2026') {
            show_error('Not allowed', 403);
        }

        // Set debug mode from query param or CLI flag
        $this->is_debug = ($this->input->get('debug') == '1' || $this->input->get('code') === 'clocking_fast_2026');
    }

    public function index()
    {
        // Log at the start of process
        $start_message = "Clocking job processor started at " . date('Y-m-d H:i:s');
        error_log($start_message);

        if ($this->is_debug || is_cli()) {
            echo "\n" . str_repeat("=", 60) . "\n";
            echo $start_message . "\n";
            echo "Debug mode: " . ($this->is_debug ? "ON" : "OFF") . "\n";
            echo str_repeat("=", 60) . "\n\n";
        }

        $this->process_continuous();
    }

    /**
     * New method for Supervisor - Runs continuously
     */
    public function process_continuous()
    {
        // Unlimited execution time for background processing
        set_time_limit(0);

        // Catch termination signals for graceful shutdown
        if (function_exists('pcntl_signal')) {
            pcntl_signal(SIGTERM, [$this, 'shutdown']);
            pcntl_signal(SIGINT, [$this, 'shutdown']);
        }

        $cycle_count = 0;
        $start_time = time();
        $last_log_time = time();
        $jobs_processed_total = 0;
        $no_job_count = 0;

        // Log start only once
        error_log("Clocking Job Processor entering main loop");

        if ($this->is_debug || is_cli()) {
            echo "Processor is now active and waiting for jobs...\n";
            // echo "Press Ctrl+C to stop\n\n";
        }

        while ($this->should_run) {
            try {
                // Process one batch
                $processed = $this->process_batch(50);
                $jobs_processed_total += $processed;

                if ($processed === 0) {
                    $no_job_count++;

                    // Only log every 60 cycles (about 5 minutes) when idle
                    if ($no_job_count % 60 === 0) {
                        $idle_message = "Idle: No pending jobs for " . ($no_job_count * 5) . " seconds. Total processed: {$jobs_processed_total}";
                        error_log($idle_message);

                        if ($this->is_debug) {
                            echo date('Y-m-d H:i:s') . " - " . $idle_message . "\n";
                        }
                    }

                    sleep($this->sleep_time_no_jobs);
                } else {
                    $no_job_count = 0;
                    // Log only when jobs are processed
                    $process_message = "Processed {$processed} jobs. Total: {$jobs_processed_total}";
                    error_log($process_message);

                    if ($this->is_debug) {
                        echo date('Y-m-d H:i:s') . " - " . $process_message . "\n";
                    }

                    usleep(100000);
                }

                // Log summary every hour
                if (time() - $last_log_time >= 3600) {
                    $hourly_message = "Hourly Summary - Processed: {$jobs_processed_total}, Memory: " . memory_get_usage(true) . " bytes";
                    error_log($hourly_message);

                    if ($this->is_debug) {
                        echo "\n" . str_repeat("-", 60) . "\n";
                        echo $hourly_message . "\n";
                        echo str_repeat("-", 60) . "\n\n";
                    }

                    $last_log_time = time();
                }

                // Self-restart logic
                $cycle_count++;
                if ($cycle_count >= 500 || (time() - $start_time) > 3600) {
                    $restart_message = "Scheduled restart after " . ($cycle_count >= 500 ? "500 cycles" : "1 hour");
                    error_log($restart_message);

                    if ($this->is_debug) {
                        echo $restart_message . "\n";
                    }

                    $this->restart_self();
                }

                if (function_exists('pcntl_signal_dispatch')) {
                    pcntl_signal_dispatch();
                }
            } catch (Exception $e) {
                // Only log errors
                $error_message = "ERROR in main loop: " . $e->getMessage();
                error_log($error_message);

                if ($this->is_debug) {
                    echo $error_message . "\n";
                }

                sleep(10);
            }
        }

        $stop_message = "Clocking Job Processor stopped. Total processed: {$jobs_processed_total}";
        error_log($stop_message);

        if ($this->is_debug) {
            echo "\n" . $stop_message . "\n";
        }
    }

    /**
     * Renamed original process() method to process_batch()
     * Returns number of jobs processed
     */
  public function process_batch($limit = 50)
{
    if (!$this->db->table_exists('clocking_jobs')) {
        $error_msg = "ERROR: Table clocking_jobs does not exist.\n";
        error_log($error_msg);

        if ($this->is_debug) {
            echo $error_msg;
        }

        return 0;
    }

    // Get pending jobs
    $jobs = $this->db->where('status', 'pending')
        ->limit((int)$limit)
        ->order_by('id', 'ASC')
        ->get('clocking_jobs')
        ->result();

    if (empty($jobs)) {
        return 0;
    }

    $processed_count = 0;

    foreach ($jobs as $job) {
        // Check memory usage
        $memory_usage = memory_get_usage(true);
        if ($memory_usage > $this->max_memory) {
            $memory_msg = "Memory limit approaching ({$memory_usage} bytes). Stopping to restart...\n";
            error_log($memory_msg);

            if ($this->is_debug) {
                echo $memory_msg;
            }

            $this->should_run = false;
            return $processed_count;
        }

        // Lock job for processing
        $this->db->where('id', $job->id)
            ->where('status', 'pending')
            ->update('clocking_jobs', [
                'status' => 'processing',
                'started_at' => date('Y-m-d H:i:s')
            ]);

        // Check if update was successful
        if ($this->db->affected_rows() === 0) {
            continue;
        }

        try {
            $payload = json_decode($job->payload, true);
            if (!$payload) {
                throw new Exception("Invalid payload JSON");
            }

            // 1. Process S3 Upload
            $image_url = '';
            if (!empty($job->image_path) && file_exists(FCPATH . $job->image_path)) {
                $image_url = $this->upload_to_s3($job->image_path);
                @unlink(FCPATH . $job->image_path);
            }

            // 2. Perform Reverse Geocoding
            $address = isset($payload['address']) ? trim((string)$payload['address']) : '';
            if ($address === '' && !empty($payload['lat']) && !empty($payload['lon'])) {
                $address = $this->getAddress($payload['lat'], $payload['lon']);
            }

            // 3. shift_days Lookup
            $shift_id = $this->get_shift_id($payload);

            // 4. Insert into clockings_news
            $insert_data = array(
                'employee_id'     => $payload['user_id'],
                'latlon'          => $payload['latlon'] ?? null,
                'type'            => $payload['action'] ?? null,
                'datetime'        => $payload['datetime'] ?? null,
                'device_id'       => $payload['device_id'] ?? null,
                'mode'            => $payload['scan_type'] ?? null,
                'weather'         => null,
                'shift_id'        => $shift_id,
                'scan_distance'   => $payload['scan_distance'] ?? null,
                'temprature'      => $payload['temprature'] ?? null,
                'clocking_remark' => $payload['clocking_remark'] ?? null,
                'sync_mode'       => $payload['sync_mode'] ?? null,
                'address'         => $address,
                'selfie'          => $image_url
            );

            $this->db->insert('clockings_news', $insert_data);

            if ($this->db->affected_rows() === 0) {
                throw new Exception("Failed to insert into clockings_news");
            }

            // 5. Heavy recalculation function
            if (function_exists('update_new_clockings')) {
                update_new_clockings($payload['user_id'], $payload['datetime']);
            }

            // 6. Delete job upon success
            $this->db->where('id', $job->id)->delete('clocking_jobs');
            $processed_count++;

            // ALWAYS show success in console (not just debug)
            echo "✓ Processed job #{$job->id} (User: {$payload['user_id']})\n";

            // Also log to file
            error_log("Processed job ID: {$job->id} for user: {$payload['user_id']}");

        } catch (Exception $e) {
            // Mark as failed
            $this->db->where('id', $job->id)->update('clocking_jobs', [
                'status' => 'failed',
                'error_log' => $e->getMessage(),
                'attempts' => ($job->attempts ?? 0) + 1,
                'failed_at' => date('Y-m-d H:i:s')
            ]);

            // ALWAYS show errors in console
            echo "✗ FAILED Job #{$job->id}: " . $e->getMessage() . "\n";

            // Also log to file
            error_log("FAILED Job ID: {$job->id} - Error: " . $e->getMessage());
        }
    }

    return $processed_count;
}

    /**
     * Optimized shift_id lookup
     */
    private function get_shift_id($payload)
    {
        if (empty($payload['datetime'])) {
            return 0;
        }

        $target_date = date("Y-m-d", strtotime($payload['datetime']));

        $query = "SELECT shift_id FROM shift_days
                  WHERE date = ?
                  AND FIND_IN_SET(?, employees)
                  LIMIT 1";

        $result = $this->db->query($query, [$target_date, (int)$payload['user_id']])->result();

        return !empty($result) ? (int)$result[0]->shift_id : 0;
    }

    /**
     * Extract S3 upload logic to separate method
     */
    private function upload_to_s3($image_path)
    {
        $bucket     = env('AWS_BUCKET', 'invotime');
        $region     = env('AWS_DEFAULT_REGION', 'ap-southeast-1');
        $accessKey  = env('AWS_ACCESS_KEY_ID', '');
        $secretKey  = env('AWS_SECRET_ACCESS_KEY', '');

        $s3Key      = 'clocking_images/' . basename($image_path);

        $s3 = new S3Client([
            'version'     => 'latest',
            'region'      => $region,
            'credentials' => [
                'key'    => $accessKey,
                'secret' => $secretKey,
            ],
        ]);

        $result = $s3->putObject([
            'Bucket'      => $bucket,
            'Key'         => $s3Key,
            'SourceFile'  => FCPATH . $image_path,
            'ContentType' => mime_content_type(FCPATH . $image_path),
        ]);

        return $result['ObjectURL'];
    }

    private function getAddress($lat, $lon)
    {
        $url = "https://nominatim.openstreetmap.org/reverse.php?lat={$lat}&lon={$lon}&zoom=18&format=jsonv2";

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERAGENT, 'invotime/1.0');
        curl_setopt($ch, CURLOPT_HTTPHEADER, ["Accept-Language: en"]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            curl_close($ch);
            return null;
        }

        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($http_code !== 200 || !$response) return null;

        $data = json_decode($response, true);
        return $data['display_name'] ?? null;
    }

    /**
     * Graceful shutdown handler
     */
    public function shutdown($signal = null)
    {
        $msg = "Received shutdown signal. Completing current batch...";
        error_log($msg);

        if ($this->is_debug) {
            echo "\n" . $msg . "\n";
        }

        $this->should_run = false;
    }

    /**
     * Restart the process to prevent memory leaks
     */
    private function restart_self()
    {
        if (is_cli()) {
            $msg = "Restarting process...";
            error_log($msg);

            if ($this->is_debug) {
                echo $msg . "\n";
            }

            $this->should_run = false;
            sleep(2);
            exit(0);
        }
    }

    /**
     * One-time batch processing (for cron jobs)
     */
    public function run_once($limit = 100)
    {
        $msg = "Running one-time batch processing with limit: {$limit}";
        error_log($msg);

        if ($this->is_debug || is_cli()) {
            echo $msg . "\n";
        }

        $processed = $this->process_batch($limit);

        $completion_msg = "Completed. Processed {$processed} jobs.";
        error_log($completion_msg);

        if ($this->is_debug || is_cli()) {
            echo $completion_msg . "\n";
        }
    }
}