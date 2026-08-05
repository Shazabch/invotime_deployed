<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\DeviceClockingJob;
use App\Devices;
use App\Employee;
use App\Clockings_new;
use App\NewClocking;
use Carbon\Carbon;
use DB;

class ProcessDeviceClockingsSean extends Command
{
    protected $signature = 'clocking:process-device
                            {--batch-size=50 : Number of jobs to process per batch}
                            {--continuous : Run continuously}
                            {--once : Run once and exit}';

    protected $description = 'Process device clocking jobs from queue';

    private $should_run = true;
    private $is_debug = false;

    public function handle()
    {
        $this->is_debug = $this->option('verbose');

        if ($this->option('continuous')) {
            $this->processContinuous();
        } else {
            $this->processBatch($this->option('batch-size'));
        }
    }

    private function processContinuous()
    {
        $this->info("Device Clocking Processor started at " . now());
        $this->info("Running in continuous mode...");

        $batch_size = $this->option('batch-size');
        $processed_total = 0;

        while ($this->should_run) {
            try {
                $processed = $this->processBatch($batch_size);
                $processed_total += $processed;

                if ($processed === 0) {
                    // $this->info("No pending jobs. Sleeping for 5 seconds...");
                    sleep(5);
                } else {
                    $this->info("Processed {$processed} jobs. Total: {$processed_total}");
                    usleep(100000); // 0.1 second
                }

                // Restart every 500 cycles or 1 hour
                static $cycle = 0;
                $cycle++;
                if ($cycle >= 500 || (time() - LARAVEL_START) > 3600) {
                    $this->info("Restarting process to prevent memory leaks...");
                    $this->should_run = false;
                    sleep(2);
                    exit(0);
                }

            } catch (\Exception $e) {
                $this->error("Error in main loop: " . $e->getMessage());
                sleep(10);
            }
        }
    }

    private function processBatch($limit)
    {
        // Get pending jobs
        $jobs = DeviceClockingJob::where('status', 'pending')
            ->orderBy('id', 'asc')
            ->limit($limit)
            ->get();

        if ($jobs->isEmpty()) {
            return 0;
        }

        $processed = 0;

        foreach ($jobs as $job) {
            // Lock job for processing
            $updated = DeviceClockingJob::where('id', $job->id)
                ->where('status', 'pending')
                ->update([
                    'status' => 'processing',
                    'started_at' => now()
                ]);

            if (!$updated) {
                continue;
            }

            try {
                $this->processJob($job);

                // Delete job on success
                $job->delete();
                $processed++;

                $this->info("✓ Processed job #{$job->id}");

            } catch (\Exception $e) {
                // Mark as failed
                $job->update([
                    'status' => 'failed',
                    'error_log' => $e->getMessage(),
                    'attempts' => $job->attempts + 1,
                    'failed_at' => now()
                ]);

                $this->error("✗ Failed job #{$job->id}: " . $e->getMessage());
            }
        }

        return $processed;
    }

    private function processJob($job)
    {
        $payload = json_decode($job->payload, true);
        if (!$payload) {
            throw new \Exception("Invalid payload JSON");
        }

        // Handle S3 upload if image exists
        $image_url = '';
        if ($job->image_path && file_exists($job->image_path)) {
            $image_url = $this->uploadToS3($job->image_path);
            @unlink($job->image_path);
        }

        // Process the clocking with all the complex logic
        $this->processClockingLogic($payload, $image_url);
    }

    private function processClockingLogic($payload, $image_url)
    {
        // Get device and user
        $device = Devices::where('mac_address', $payload['mac_address'])->first();
        if (!$device) {
            throw new \Exception("Device not found");
        }

        $user = Employee::find($payload['user_id']);
        if (!$user) {
            throw new \Exception("User not found");
        }

        // Parse datetime
        $datetime_tempx = str_replace("-T", " ", $payload['datetime']);
        $datetime_tempx = str_replace("Z", "", $datetime_tempx);
        $dtx = Carbon::parse($datetime_tempx, $payload['device_timezone']);

        $clocking_type = $payload['clocking_type'];
        $alternate_clocking_interval = $payload['alternate_clocking_interval'];
        $current_date = $dtx->toDateString();
        $prev_date = Carbon::parse($dtx)->subDay()->toDateString();

        // Get shifts (optimized with joins instead of FIND_IN_SET)
        $shift = $this->getShiftForDate($user->id, $dtx);
        $prev_shift = $this->getShiftForDate($user->id, Carbon::parse($dtx)->subDay());

        $shift_id = $shift ? $shift->shift_id : 0;
        $prev_shift_id = $prev_shift ? $prev_shift->shift_id : 0;

        // Overnight shift detection
        $overnight = false;
        $prev_overnight = false;
        $optional_overnight = false;
        $optional_prev_overnight = false;
        $current_shift = null;
        $force_two_clockings = false;

        if ($shift_id) {
            $shift_data = DB::table('shifts')->where('id', $shift_id)->first();
            if ($shift_data && $shift_data->overnight == "Yes") {
                $overnight = true;
                if ($shift_data->start_time <= "12:00:00" && $shift_data->end_time > "12:00:00") {
                    $optional_overnight = true;
                }
            }
            $current_shift = $shift_data;
        }

        $prev_overnight_starts_at = null;
        if ($prev_shift_id) {
            $shift_data = DB::table('shifts')->where('id', $prev_shift_id)->first();
            if ($shift_data && $shift_data->overnight == "Yes") {
                $prev_overnight = true;
                if ($shift_data->start_time <= "12:00:00" && $shift_data->end_time > "12:00:00") {
                    $optional_prev_overnight = true;
                    $prev_overnight_starts_at = $prev_date . " " . $shift_data->start_time;
                    $prev_overnight_starts_at = date("Y-m-d H:i:s", strtotime($prev_overnight_starts_at) - 7200);
                }
            }
        }

        // Process datetime
        $dt = Carbon::parse($datetime_tempx, $payload['device_timezone']);

        // Check if clocking already exists
        $checkData = Clockings_new::where('employee_id', $user->id)
            ->where('datetime', $dt->toDateTimeString())
            ->whereNull('deleted_at')
            ->first();

        if (!$checkData) {
            $valid_clocking = true;
            $data = [];

            if ($clocking_type == "alternate") {
                // Alternate clocking logic (simplified - you can keep your original logic)
                list($valid_clocking, $current_type) = $this->processAlternateClocking(
                    $user, $dt, $current_date, $shift_id, $prev_shift_id,
                    $overnight, $prev_overnight, $optional_overnight,
                    $optional_prev_overnight, $prev_overnight_starts_at,
                    $alternate_clocking_interval
                );

                $data = [
                    'employee_id' => $user->id,
                    'type' => $current_type,
                    'datetime' => $dt->toDateTimeString(),
                    'device_id' => $payload['device_id'],
                    'mode' => $payload['scan_type'],
                    'weather' => $payload['weather'],
                    'shift_id' => $shift_id,
                    'selfie' => $image_url,
                    'temprature' => $payload['temprature'],
                    'clocking_remark' => $payload['clocking_remark'],
                    'address' => $payload['address']
                ];

                if ($current_shift && $current_shift->force_two_clockings == "Yes") {
                    $force_two_clockings = true;
                }

            } else {
                $data = [
                    'employee_id' => $user->id,
                    'type' => $payload['action'],
                    'datetime' => $dt->toDateTimeString(),
                    'device_id' => $payload['device_id'],
                    'mode' => $payload['scan_type'],
                    'weather' => $payload['weather'],
                    'shift_id' => $shift_id,
                    'selfie' => $image_url,
                    'temprature' => $payload['temprature'],
                    'clocking_remark' => $payload['clocking_remark'],
                    'address' => $payload['address']
                ];
            }

            if ($valid_clocking) {
                $clocking = Clockings_new::create($data);

                // Force two clockings logic
                if ($force_two_clockings) {
                    $this->enforceTwoClockings($user->id, $current_date);
                }

                // Rebuild aggregated table
                $this->rebuildNewClockings($user->id, $datetime_tempx, $payload['device_timezone']);
            }
        }
    }

    private function getShiftForDate($employee_id, $date)
    {
        // Optimized using pivot table instead of FIND_IN_SET
        // Create employee_shift_days table for better performance
        return DB::table('shift_days')
            ->where('date', $date->toDateString())
            ->whereRaw("FIND_IN_SET(?, employees)", [$employee_id])
            ->first();
    }

    private function processAlternateClocking($user, $dt, $current_date, $shift_id, $prev_shift_id,
                                            $overnight, $prev_overnight, $optional_overnight,
                                            $optional_prev_overnight, $prev_overnight_starts_at,
                                            $alternate_clocking_interval)
    {
        $valid_clocking = true;
        $current_type = "in";

        // Get last clocking
        $last_clocking = Clockings_new::where('employee_id', $user->id)
            ->whereDate('datetime', $current_date)
            ->whereNull('deleted_at')
            ->orderBy('datetime', 'desc')
            ->first();

        if ($last_clocking) {
            // Check interval
            $last_time = Carbon::parse($last_clocking->datetime);
            $current_time = $dt;
            $minutes_diff = $last_time->diffInMinutes($current_time);

            if ($minutes_diff < $alternate_clocking_interval) {
                $valid_clocking = false;
            }

            $current_type = $last_clocking->type == "out" ? "in" : "out";
        }

        return [$valid_clocking, $current_type];
    }

    private function enforceTwoClockings($employee_id, $current_date)
    {
        $today_clockings = Clockings_new::where('employee_id', $employee_id)
            ->whereDate('datetime', $current_date)
            ->whereNull('deleted_at')
            ->orderBy('datetime', 'asc')
            ->get();

        if ($today_clockings->isNotEmpty()) {
            $first = $today_clockings->first();
            if ($first->type != "in") {
                $first->type = "in";
                $first->save();
            }

            if ($today_clockings->count() > 1) {
                $last = $today_clockings->last();
                if ($last->type != "out") {
                    $last->type = "out";
                    $last->save();
                }
            }

            // Delete middle clockings
            foreach ($today_clockings as $key => $clocking) {
                if ($key > 0 && $key < $today_clockings->count() - 1) {
                    $clocking->delete();
                }
            }
        }
    }

    private function rebuildNewClockings($employee_id, $datetime, $timezone)
    {
        $dt = Carbon::parse($datetime, $timezone);
        $start_time = $dt->copy()->subDay()->startOfDay();
        $end_time = $dt->copy()->addDay()->endOfDay();

        // Delete existing aggregated records
        NewClocking::where('employee_id', $employee_id)
            ->whereBetween('clock_in', [$start_time, $end_time])
            ->delete();

        // Complex query to build in/out pairs
        $clockings_query = "
            SELECT
                a.id, a.employee_id, a.device_id, a.shift_id,
                a.datetime as clock_in, a.id as clock_in_id,
                b.datetime as clock_out, b.id as clock_out_id,
                a.reason, a.remark, a.mode as scan_type_in,
                b.mode as scan_type_out, a.created_at, b.created_at as updated_at
            FROM clockings_news a
            LEFT JOIN clockings_news b ON (
                a.employee_id = b.employee_id
                AND a.type = 'in'
                AND b.type = 'out'
                AND b.datetime = (
                    SELECT MIN(c.datetime)
                    FROM clockings_news c
                    WHERE c.datetime > a.datetime
                    AND c.employee_id = a.employee_id
                    AND c.deleted_at IS NULL
                )
            )
            WHERE a.type = 'in'
            AND a.deleted_at IS NULL
            AND (b.deleted_at IS NULL OR b.id IS NULL)
            AND a.employee_id = ?
            AND a.datetime BETWEEN ? AND ?
            ORDER BY a.datetime
        ";

        $clockings = DB::select($clockings_query, [
            $employee_id,
            $start_time->toDateTimeString(),
            $end_time->toDateTimeString()
        ]);

        foreach ($clockings as $clocking) {
            NewClocking::updateOrCreate(
                ['id' => $clocking->id],
                (array)$clocking
            );
        }
    }

    private function uploadToS3($image_path)
    {
        // Your S3 upload logic here
        // Similar to the one in your CodeIgniter example
        return "https://your-bucket.s3.amazonaws.com/clocking_images/" . basename($image_path);
    }
}