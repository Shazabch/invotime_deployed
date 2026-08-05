<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\DeviceClockingJob;
use App\Devices;
use App\Employee;
use App\Company;
use App\Clockings_new;
use App\NewClocking;
use Carbon\Carbon;
use DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ProcessDeviceClockings extends Command
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
                    sleep(5);
                } else {
                    $this->info("Processed {$processed} jobs. Total: {$processed_total}");
                    usleep(100000);
                }

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
        $jobs = DeviceClockingJob::where('status', 'pending')
            ->orderBy('id', 'asc')
            ->limit($limit)
            ->get();

        if ($jobs->isEmpty()) {
            return 0;
        }

        $processed = 0;

        foreach ($jobs as $job) {
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
                $job->delete();
                $processed++;
                $this->info("✓ Processed job #{$job->id}");
            } catch (\Exception $e) {
                // ← ADD THESE LINES (clean up temp image on any failure)
                if ($job->image_path && file_exists($job->image_path)) {
                    @unlink($job->image_path);
                }
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

        $image_url = '';
        if ($job->image_path && file_exists($job->image_path)) {
            $image_url = $this->uploadToS3($job->image_path);
            // ← FIX: Only delete local file if S3 upload succeeded
            if ($image_url) {
                @unlink($job->image_path);
            }
        }

        $this->processClockingLogic($payload, $image_url);
    }

    private function processClockingLogic($payload, $image_url)
    {
        $device = Devices::where('mac_address', $payload['mac_address'])->first();
        if (!$device) {
            throw new \Exception("Device not found");
        }

        $user = Employee::find($payload['user_id']);
        if (!$user) {
            throw new \Exception("User not found");
        }

        $datetime_tempx = str_replace("-T", " ", $payload['datetime']);
        $datetime_tempx = str_replace("Z", "", $datetime_tempx);
        $dtx = Carbon::parse($datetime_tempx, $payload['device_timezone']);

        $clocking_type = $payload['clocking_type'];
        $alternate_clocking_interval = $payload['alternate_clocking_interval'];
        $current_date = $dtx->toDateString();
        $prev_date = Carbon::parse($dtx)->subDay()->toDateString();

        $shift = $this->getShiftForDate($user->id, $dtx);
        $prev_shift = $this->getShiftForDate($user->id, Carbon::parse($dtx)->subDay());

        $shift_id = $shift ? $shift->shift_id : 0;
        $prev_shift_id = $prev_shift ? $prev_shift->shift_id : 0;

        $overnight = false;
        $prev_overnight = false;
        $optional_overnight = false;
        $optional_prev_overnight = false;
        $current_shift = null;
        $force_two_clockings = false;
        $prev_shift_def = null;
        $interval_minutes = 0;

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
            $shift_data = $prev_shift_def = DB::table('shifts')->where('id', $prev_shift_id)->first();
            if ($shift_data && $shift_data->overnight == "Yes") {
                $prev_overnight = true;
                if ($shift_data->start_time <= "12:00:00" && $shift_data->end_time > "12:00:00") {
                    $optional_prev_overnight = true;
                    $prev_overnight_starts_at = $prev_date . " " . $shift_data->start_time;
                    $prev_overnight_starts_at = date("Y-m-d H:i:s", strtotime($prev_overnight_starts_at) - 7200);
                }
            }
        }
        // --- PRESHIFT DETECTION ---
        // Clock-in at 23:00–00:00 belongs to the next day's shift if is_preshift = true
        $next_date = Carbon::parse($dtx)->addDay()->toDateString();
        $next_shift = $this->getShiftForDate($user->id, Carbon::parse($dtx)->addDay());
        $next_shift_id = $next_shift ? $next_shift->shift_id : 0;

        $is_preshift_clocking = false;
        $next_shift_def = null;

        if ($next_shift_id) {
            $next_shift_def = DB::table('shifts')->where('id', $next_shift_id)->first();
            $preshiftbuffer = $next_shift_def->preshift_buffer_minutes ?? 180;
            if (
                $next_shift_def
                && !empty($next_shift_def->is_preshift)
                && strtolower(trim($next_shift_def->is_preshift ?? '')) == 'yes'
                && !empty($preshiftbuffer)
            ) {
                // Preshift window = true midnight minus buffer minutes
                // e.g. buffer=60 means clocking from 23:00:00 onward belongs to next day's shift
                $next_midnight = Carbon::parse($next_date)->startOfDay();
                $preshift_start = $next_midnight->copy()->subMinutes((int)$preshiftbuffer);
                $preshift_start = $preshift_start->toDateTimeString();
                if ($dtx->gte($preshift_start)) {
                    $is_preshift_clocking = true;
                }
            }
        }
        // --- REVERSE PRESHIFT CHECK ---
        // If TODAY's shift is itself a preshift shift, yesterday's late-night clock-in
        // (in the buffer window) may be the "in" half waiting to be paired with today's "out"
        $today_is_preshift_shift = false;
        $preshift_lookback_start = null;

        if ($shift_id && $current_shift) {
            $today_preshiftbuffer = $current_shift->preshift_buffer_minutes ?? 180;
            if (
                strtolower(trim($current_shift->is_preshift ?? '')) == 'yes'
                && !empty($today_preshiftbuffer)
            ) {
                $today_is_preshift_shift = true;
                $today_midnight = Carbon::parse($current_date)->startOfDay();
                $preshift_lookback_start = $today_midnight->copy()->subMinutes((int)$today_preshiftbuffer);
            }
        }
        // --- END REVERSE PRESHIFT CHECK ---
        // --- END PRESHIFT DETECTION ---


        // Cut-off time: shift-level > company-level > default 07:00
        $effective_cutoff_time = null;
        if (!empty($current_shift) && !empty($current_shift->cut_off_time)) {
            $effective_cutoff_time = $current_shift->cut_off_time;
        } else if (!empty($prev_shift_def) && !empty($prev_shift_def->cut_off_time)) {
            $effective_cutoff_time = $prev_shift_def->cut_off_time;
        }

        if (empty($effective_cutoff_time)) {
            $company = Company::find($device->company_id);
            $effective_cutoff_time = $company->cut_off_time ?? '07:00:00';
        }

        if ($effective_cutoff_time) {
            $t = explode(":", $effective_cutoff_time);
            $interval_minutes = intval($t[0]) * 60 + intval($t[1]);
        }

        $dt = Carbon::parse($datetime_tempx, $payload['device_timezone']);

        $checkData = Clockings_new::where('employee_id', $user->id)
            ->where('datetime', $dt->toDateTimeString())
            ->whereNull('deleted_at')
            ->first();

        if (!$checkData) {
            $valid_clocking = true;
            $data = [];

            if ($clocking_type == "alternate") {
                list($valid_clocking, $current_type, $resolved_shift_id) = $this->processAlternateClocking(
                    $user,
                    $dt,
                    $current_date,
                    $shift_id,
                    $prev_shift_id,
                    $overnight,
                    $prev_overnight,
                    $optional_overnight,
                    $optional_prev_overnight,
                    $prev_overnight_starts_at,
                    $alternate_clocking_interval,
                    $current_shift,
                    $interval_minutes,
                    $is_preshift_clocking,
                    $next_shift_id,
                    $next_date,
                    $today_is_preshift_shift,
                    $preshift_lookback_start,
                    $effective_cutoff_time
                );

                $data = [
                    'employee_id' => $user->id,
                    'type' => $current_type,
                    'datetime' => $dt->toDateTimeString(),
                    'device_id' => $payload['device_id'],
                    'mode' => $payload['scan_type'],
                    'weather' => $payload['weather'],
                    // 'shift_id' => $shift_id,
                    'shift_id' => $resolved_shift_id,
                    'selfie' => $image_url,
                    'temprature' => $payload['temprature'],
                    'clocking_remark' => $payload['clocking_remark'],
                    'address' => $payload['address'],
                    'latlon'  => $payload['latlon'] ?? null,
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
                    'address' => $payload['address'],
                    'latlon'  => $payload['latlon'] ?? null,
                ];
            }

            if ($valid_clocking) {
                $clocking = Clockings_new::create($data);

                if ($force_two_clockings) {
                    $this->enforceTwoClockings($user->id, $current_date, $prev_overnight, $prev_shift_def, $interval_minutes, $overnight, $current_shift, $effective_cutoff_time);
                }

                $this->rebuildNewClockings($user->id, $datetime_tempx, $payload['device_timezone']);
            }
        }
    }

    private function getShiftForDate($employee_id, $date)
    {
        $needle = ',' . $employee_id . ',';

        foreach (
            DB::table('shift_days')
                ->where('date', $date->toDateString())
                ->orderBy('id')
                ->cursor()
            as $shift
        ) {
            if (strpos(',' . ($shift->employees ?? '') . ',', $needle) !== false) {
                Log::info('Shift found for employee', [
                    'employee_id' => $employee_id,
                    'date' => $date->toDateString(),
                    'shift_id' => $shift->shift_id,
                    'shift_day' => $shift->date,
                ]);
                return $shift;
            }
        }

        Log::warning('Shift not found for employee', [
            'employee_id' => $employee_id,
            'date' => $date->toDateString(),
        ]);

        return null;
    }

    private function processAlternateClocking(
        $user,
        $dt,
        $current_date,
        $shift_id,
        $prev_shift_id,
        $overnight,
        $prev_overnight,
        $optional_overnight,
        $optional_prev_overnight,
        $prev_overnight_starts_at,
        $alternate_clocking_interval,
        $current_shift = null,
        $interval_minutes = 0,
        $is_preshift_clocking = false,
        $next_shift_id = 0,
        $next_date = null,
        $today_is_preshift_shift = false,
        $preshift_lookback_start = null,
        $effective_cutoff_time = null
    ) {
        $valid_clocking = true;
        $current_type = "in";
        $last_clocking = null;
        $hour = (int)$dt->format('H');
        // Get effective cutoff time early for use in multiple places
        $cutoff_hour = (int)explode(':', $effective_cutoff_time)[0];
        // Determine if current time is BEFORE cutoff
        $is_before_cutoff = false;
        if ($optional_prev_overnight) {
            $is_before_cutoff = ($hour < 6);
        } else {
            $is_before_cutoff = ($hour < $cutoff_hour);
        }

        // Helper: cut-off aware date query
        $getLastClockingByDate = function ($date) use ($user, $interval_minutes, $overnight, $prev_overnight) {
            if ($interval_minutes > 0 && ($overnight || $prev_overnight)) {
                return Clockings_new::where('employee_id', $user->id)
                    ->whereRaw('DATE(DATE_ADD(datetime, INTERVAL ? MINUTE)) = ?', [$interval_minutes, $date])
                    ->whereNull('deleted_at')
                    ->orderBy('datetime', 'desc')
                    ->first();
            }
            return Clockings_new::where('employee_id', $user->id)
                ->whereDate('datetime', $date)
                ->whereNull('deleted_at')
                ->orderBy('datetime', 'desc')
                ->first();
        };
        // Normal day shift (no overnight involved) — resolve immediately via fallback
        $is_normal_day = (($shift_id != 0 && !$overnight) || ($shift_id == 0 && !$prev_overnight)) && !$is_preshift_clocking;
        if ($is_normal_day) {
            $last_clocking = null;

            // Check if yesterday had an open preshift clock-in waiting to be paired
            if ($today_is_preshift_shift && $preshift_lookback_start) {
                $prev_date = Carbon::parse($current_date)->subDay()->toDateString();
                $lookback_end = $current_date . ' 00:00:00';

                $last_clocking = Clockings_new::where('employee_id', $user->id)
                    ->where('shift_id', $shift_id)
                    ->where('datetime', '>=', $preshift_lookback_start->toDateTimeString())
                    ->where('datetime', '<', $lookback_end)
                    ->whereNull('deleted_at')
                    ->orderBy('datetime', 'desc')
                    ->first();
            }

            // Fall back to same-day lookup if no preshift match found
            if (!$last_clocking) {
                $last_clocking = $getLastClockingByDate($current_date);
            }

            if ($last_clocking) {
                $minutes_diff = Carbon::parse($last_clocking->datetime)->diffInMinutes($dt);
                if ($minutes_diff < $alternate_clocking_interval) {
                    $valid_clocking = false;
                }
                $current_type = $last_clocking->type == "out" ? "in" : "out";
            }
            return [$valid_clocking, $current_type, $shift_id];
        }
        // BRANCH PRESHIFT: clock-in at 23:00–00:00 belongs to next day's shift (is_preshift = true)
        if ($is_preshift_clocking && $next_shift_id && $next_date) {
            // Look for any clockings already recorded under the next day's shift window
            // Window: today 23:00:00 → next_date end of day
            $preshift_window_start = $current_date . ' 23:00:00';
            $preshift_window_end   = $next_date . ' 23:59:59';

            $last_clocking = Clockings_new::where('employee_id', $user->id)
                ->where('datetime', '>=', $preshift_window_start)
                ->where('datetime', '<=', $preshift_window_end)
                ->whereNull('deleted_at')
                ->orderBy('datetime', 'desc')
                ->first();

            if ($last_clocking) {
                $minutes_diff = Carbon::parse($last_clocking->datetime)->diffInMinutes($dt);
                if ($minutes_diff < $alternate_clocking_interval) {
                    $valid_clocking = false;
                }
                $current_type = $last_clocking->type == "out" ? "in" : "out";
            }

            // This clocking belongs to the next day's shift
            return [$valid_clocking, $current_type, $next_shift_id];
        }
        $belongs_to_prev_overnight_shift = false;

        // BRANCH B: Morning completion of overnight shift
        if (
            $prev_overnight
            && (($hour < 12 && !$optional_prev_overnight) || ($hour < 6 && $optional_prev_overnight))
        ) {
            if (
                $current_shift
                && isset($current_shift->same_day_overnight)
                && $current_shift->same_day_overnight == 'next'
            ) {
                $prev_date = Carbon::parse($current_date)->subDay()->toDateString();
                $overnight_start = $prev_date . ' ' . $current_shift->start_time;
                $overnight_end = $current_date . ' ' . $current_shift->end_time;

                $last_clocking = Clockings_new::where('employee_id', $user->id)
                    ->where('datetime', '>=', $overnight_start)
                    ->where('datetime', '<', $overnight_end)
                    ->whereNull('deleted_at')
                    ->orderBy('datetime', 'desc')
                    ->first();
                if ($last_clocking) {
                    $belongs_to_prev_overnight_shift = true;
                }
            } else if ($optional_prev_overnight && $prev_overnight_starts_at) {
                $last_clocking = Clockings_new::where('employee_id', $user->id)
                    ->where('datetime', '>', $prev_overnight_starts_at)
                    ->whereNull('deleted_at')
                    ->orderBy('datetime', 'desc')
                    ->first();
                if ($last_clocking) {
                    $belongs_to_prev_overnight_shift = true;
                }
            }
        }

        // Overnight fix for early morning
        if (
            !$last_clocking
            && $overnight
            && !$optional_overnight
            && $current_shift
            && strcmp($dt->toTimeString(), $current_shift->end_time) < 0
        ) {
            $prev_date = Carbon::parse($current_date)->subDay()->toDateString();
            $overnight_start = $prev_date . ' ' . $current_shift->start_time;
            $overnight_end = $current_date . ' ' . $current_shift->end_time;

            $last_clocking = Clockings_new::where('employee_id', $user->id)
                ->where('datetime', '>=', $overnight_start)
                ->where('datetime', '<', $overnight_end)
                ->whereNull('deleted_at')
                ->orderBy('datetime', 'desc')
                ->first();
            if ($last_clocking) {
                $belongs_to_prev_overnight_shift = true;
            }
        }

        // Fallback: today (cut-off aware)
        if (!$last_clocking) {
            $last_clocking = $getLastClockingByDate($current_date);
        }


        // Fallback: previous day (cut-off aware)
        if (!$last_clocking && $prev_overnight && $is_before_cutoff) {
            $prev_date = Carbon::parse($current_date)->subDay()->toDateString();
            $last_clocking = $getLastClockingByDate($prev_date);
        }

        if ($last_clocking) {
            $last_time = Carbon::parse($last_clocking->datetime);
            $current_time = $dt;
            $minutes_diff = $last_time->diffInMinutes($current_time);

            if ($minutes_diff < $alternate_clocking_interval) {
                $valid_clocking = false;
            }

            $current_type = $last_clocking->type == "out" ? "in" : "out";
        }

        // Determine which shift_id this clocking belongs to
        $resolved_shift_id = $shift_id;
        if (
            $prev_overnight
            && (($hour < 12 && !$optional_prev_overnight) || ($hour < 6 && $optional_prev_overnight))
        ) {
            if ($belongs_to_prev_overnight_shift) {
                $resolved_shift_id = $prev_shift_id;
            }
        }

        return [$valid_clocking, $current_type, $resolved_shift_id];
    }

    private function enforceTwoClockings($employee_id, $current_date, $prev_overnight = false, $prev_shift = null, $interval_minutes = 0, $overnight = false, $current_shift = null, $cutoff_time = '07:00:00')
    {
        $query = Clockings_new::where('employee_id', $employee_id)
            ->whereNull('deleted_at')
            ->orderBy('datetime', 'asc');


        if ($prev_overnight && $prev_shift) {
            // Only treat yesterday's shift as still "open" into today if it doesn't
            // already have an "out" recorded on its own day. If it already closed
            // out normally (e.g. 07:40 in -> 20:00 out, both on 6 July), nothing
            // crossed midnight - today is a fresh shift and shouldn't pull
            // yesterday's already-completed clockings into its window.
            $prev_date = Carbon::parse($current_date)->subDay()->toDateString();

            $prev_day_already_closed = Clockings_new::where('employee_id', $employee_id)
                ->whereNull('deleted_at')
                ->whereDate('datetime', $prev_date)
                ->where('type', 'out')
                ->exists();

            if ($prev_day_already_closed) {
                $query->whereDate('datetime', $current_date);
            } else {
                $start = $prev_date . ' ' . $prev_shift->start_time;
                // $end = $current_date . ' ' . $prev_shift->end_time;
                $end = $current_date . ' ' . $cutoff_time;
                $query->whereBetween('datetime', [$start, $end]);
            }
        } elseif ($overnight && $current_shift) {
            // Today's own shift is overnight - the "in" happens today, the "out"
            // (including any OT past midnight) happens tomorrow.
            $next_date = Carbon::parse($current_date)->addDay()->toDateString();
            $start = $current_date . ' ' . $current_shift->start_time;
            $end = $next_date . ' ' . $current_shift->end_time;
            $query->whereBetween('datetime', [$start, $end]);
        } else {
            // Plain, non-overnight day: today's calendar date, nothing more.
            $query->whereDate('datetime', $current_date);
        }

        $today_clockings = $query->get();
        Log::info("Clockings before enforce", [
            'employee' => $employee_id,
            'current_date' => $current_date,
            'count' => $today_clockings->count(),
            'clockings' => $today_clockings->map(function ($c) {
                return [
                    'id' => $c->id,
                    'datetime' => $c->datetime,
                    'type' => $c->type,
                ];
            })->toArray()
        ]);
        if ($today_clockings->isNotEmpty()) {

            $first = $today_clockings->first();
            if ($first->type != "in") {
                $first->type = "in";
                $first->save();
                Log::info("Updated first clocking ID {$first->id} to type 'in'");
            }

            if ($today_clockings->count() > 1) {
                $last = $today_clockings->last();
                if ($last->type != "out") {
                    $last->type = "out";
                    $last->save();
                    Log::info("Updated last clocking ID {$last->id} to type 'out'");
                }
            } else {
            }

            foreach ($today_clockings as $key => $clocking) {

                if ($key > 0 && $key < $today_clockings->count() - 1) {
                    // $clocking->delete();
                    $clocking->deleted_at = now();
                    $clocking->save();

                    Log::info("Deleted clocking ID {$clocking->id} count {$today_clockings->count()} to enforce two clockings per day for employee {$employee_id} on {$current_date}");
                }
            }
        } else {
        }
    }

    private function rebuildNewClockings($employee_id, $datetime, $timezone)
    {
        $dt = Carbon::parse($datetime, $timezone);
        $start_time = $dt->copy()->subDay()->startOfDay();
        $end_time = $dt->copy()->addDay()->endOfDay();

        NewClocking::where('employee_id', $employee_id)
            ->whereBetween('clock_in', [$start_time, $end_time])
            ->delete();

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
        try {
            if (!file_exists($image_path)) {
                throw new \Exception("Image file not found: {$image_path}");
            }

            $filename = basename($image_path);
            $datePath = date('Y/m');
            $s3Key = "clocking_images/{$datePath}/{$filename}";

            $mimeType = mime_content_type($image_path) ?: 'image/jpeg';

            $uploaded = Storage::disk('s3')->put(
                $s3Key,
                file_get_contents($image_path),
                [
                    'visibility' => 'public',
                    'ContentType' => $mimeType,
                ]
            );

            if (!$uploaded) {
                throw new \Exception("Storage::put returned false for key: {$s3Key}");
            }

            $url = Storage::disk('s3')->url($s3Key);

            if ($this->is_debug) {
                $this->info("S3 upload success: {$url}");
            }

            return $url;
        } catch (\Exception $e) {
            $msg = "S3 upload failed for {$image_path}: " . $e->getMessage();
            $this->error($msg);
            Log::error($msg);
            return '';
        }
    }
}
