<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ClockingsArchive extends Command
{
    protected $signature = 'clockings:archive
                            {action : forward | reverse | verify | optimize | drop}
                            {--year=2024 : The year of data to archive or revert}
                            {--chunk=100000 : Number of rows per batch}
                            {--continuous : Keep running until all batches are done}
                            {--force : Skip confirmation prompt for drop action}';

    protected $description = 'Safely archive or revert clockings_news data by year in configurable batch sizes';

    private $should_run = true;

    // ─────────────────────────────────────────────────────────────────
    public function handle()
    {
        $action = $this->argument('action');
        $year   = (int) $this->option('year');
        $chunk  = (int) $this->option('chunk');

        // ── Validate action ──────────────────────────────────────────
        if (! in_array($action, ['forward', 'reverse', 'verify', 'optimize', 'drop'])) {
            $this->error('Invalid action. Use: forward | reverse | verify | optimize | drop');
            $this->line('');
            $this->line('Examples:');
            $this->line('  php artisan clockings:archive forward --year=2024 --chunk=50000');
            $this->line('  php artisan clockings:archive reverse --year=2024 --chunk=50000');
            $this->line('  php artisan clockings:archive verify  --year=2024');
            return 1;
        }

        // ── Validate year ────────────────────────────────────────────
        if ($year < 2000 || $year > (int) now()->year) {
            $this->error("Invalid --year={$year}. Must be between 2000 and " . now()->year . '.');
            return 1;
        }

        // ── Validate chunk ───────────────────────────────────────────
        if ($chunk < 1000 || $chunk > 1000000) {
            $this->error("Invalid --chunk={$chunk}. Must be between 1,000 and 1,000,000.");
            return 1;
        }

        $archiveTable = 'clockings_news_' . $year;
        $dateFrom     = "{$year}-01-01 00:00:00";
        $dateTo       = ($year + 1) . "-01-01 00:00:00";

        $this->line('');
        $this->line('┌──────────────────────────────────────────┐');
        $this->line("│  Action : <info>{$action}</info>");
        $this->line("│  Year   : <info>{$year}</info>");
        $this->line("│  Chunk  : <info>" . number_format($chunk) . " rows/batch</info>");
        $this->line("│  Table  : <info>{$archiveTable}</info>");
        $this->line('└──────────────────────────────────────────┘');
        $this->line('');

        // ── Route to action ──────────────────────────────────────────
        if ($action === 'forward')  return $this->runForward($archiveTable, $dateFrom, $dateTo, $chunk);
        if ($action === 'reverse')  return $this->runReverse($archiveTable, $chunk);
        if ($action === 'verify')   return $this->runVerify($archiveTable, $dateFrom, $dateTo);
        if ($action === 'optimize') return $this->runOptimize();
        if ($action === 'drop')     return $this->runDrop($archiveTable);
    }

    // ─────────────────────────────────────────────
    // FORWARD: Move year data → archive table
    // ─────────────────────────────────────────────
    private function runForward($archiveTable, $dateFrom, $dateTo, $chunk)
    {
        $this->ensureArchiveTable($archiveTable);

        if ($this->option('continuous')) {
            return $this->runContinuous('forward', $archiveTable, $dateFrom, $dateTo, $chunk);
        }

        return $this->processForwardBatch($archiveTable, $dateFrom, $dateTo, $chunk);
    }

    // ─────────────────────────────────────────────
    // REVERSE: Move archive data → main table
    // ─────────────────────────────────────────────
    private function runReverse($archiveTable, $chunk)
    {
        if (! $this->archiveTableExists($archiveTable)) {
            $this->error("Archive table does not exist: {$archiveTable}");
            return 1;
        }

        if ($this->option('continuous')) {
            return $this->runContinuous('reverse', $archiveTable, null, null, $chunk);
        }

        return $this->processReverseBatch($archiveTable, $chunk);
    }

    // ─────────────────────────────────────────────
    // CONTINUOUS: Keep looping until done (mirrors ProcessDeviceClockings)
    // ─────────────────────────────────────────────
    private function runContinuous($direction, $archiveTable, $dateFrom, $dateTo, $chunk)
    {
        $this->info("Running in continuous mode... (Ctrl+C to stop)");

        $totalProcessed = 0;
        static $cycle   = 0;

        while ($this->should_run) {
            try {
                $remaining = ($direction === 'forward')
                    ? $this->processForwardBatch($archiveTable, $dateFrom, $dateTo, $chunk)
                    : $this->processReverseBatch($archiveTable, $chunk);

                // processForwardBatch/processReverseBatch return FAILURE when remaining = 0
                if ($remaining === 0) {
                    $this->info("✅ All batches complete. Total processed: " . number_format($totalProcessed));
                    $this->should_run = false;
                    break;
                }

                $totalProcessed += $chunk;
                usleep(100000); // 0.1s between batches to reduce DB pressure

                // Restart every 500 cycles to prevent memory leaks (same as ProcessDeviceClockings)
                $cycle++;
                if ($cycle >= 500 || (time() - LARAVEL_START) > 3600) {
                    $this->info("Restarting process to prevent memory leaks...");
                    $this->should_run = false;
                    sleep(2);
                    exit(0);
                }

            } catch (\Exception $e) {
                $this->error("Error in continuous loop: " . $e->getMessage());
                sleep(10); // Wait before retrying on error
            }
        }

        return 0;
    }

    // ─────────────────────────────────────────────
    // BATCH: Forward (main → archive)
    // Returns SUCCESS when done, FAILURE while still has rows
    // ─────────────────────────────────────────────
    private function processForwardBatch($archiveTable, $dateFrom, $dateTo, $chunk)
    {
        try {
            DB::beginTransaction();

            // Insert batch into archive (INSERT IGNORE = safe on re-run)
            $inserted = DB::affectingStatement("
                INSERT IGNORE INTO {$archiveTable}
                SELECT * FROM clockings_news
                WHERE datetime >= ?
                  AND datetime <  ?
                ORDER BY id
                LIMIT {$chunk}
            ", [$dateFrom, $dateTo]);

            // Delete exactly the rows inserted (matched by ID)
            $deleted = DB::affectingStatement("
                DELETE FROM clockings_news
                WHERE id IN (
                    SELECT id FROM (
                        SELECT id FROM clockings_news
                        WHERE datetime >= ?
                          AND datetime <  ?
                        ORDER BY id
                        LIMIT {$chunk}
                    ) AS batch
                )
            ", [$dateFrom, $dateTo]);

            DB::commit();

            // Check remaining
            $remaining = DB::table('clockings_news')
                ->where('datetime', '>=', $dateFrom)
                ->where('datetime', '<',  $dateTo)
                ->count();

            $this->info(sprintf(
                '[FORWARD] Inserted: %s | Deleted: %s | Remaining: %s',
                number_format($inserted),
                number_format($deleted),
                number_format($remaining)
            ));

            if ($remaining === 0) {
                $this->line("<info>[FORWARD] ✅ All data archived into {$archiveTable}!</info>");
                $this->line('<comment>Next steps: php artisan clockings:archive verify --year=... then optimize</comment>');
                return 0;
            }

            $this->line('<comment>[FORWARD] ⏳ Re-run to process next batch.</comment>');
            return 1; // Signal continuous loop to keep going

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('[FORWARD] ❌ ' . $e->getMessage());
            return 1;
        }
    }

    // ─────────────────────────────────────────────
    // BATCH: Reverse (archive → main)
    // ─────────────────────────────────────────────
    private function processReverseBatch($archiveTable, $chunk)
    {
        try {
            DB::beginTransaction();

            // Insert batch back into main (INSERT IGNORE = safe on re-run)
            $inserted = DB::affectingStatement("
                INSERT IGNORE INTO clockings_news
                SELECT * FROM {$archiveTable}
                ORDER BY id
                LIMIT {$chunk}
            ");

            // Delete exactly the rows inserted (matched by ID)
            $deleted = DB::affectingStatement("
                DELETE FROM {$archiveTable}
                WHERE id IN (
                    SELECT id FROM (
                        SELECT id FROM {$archiveTable}
                        ORDER BY id
                        LIMIT {$chunk}
                    ) AS batch
                )
            ");

            DB::commit();

            // Check remaining
            $remaining = DB::table($archiveTable)->count();

            $this->info(sprintf(
                '[REVERSE] Inserted: %s | Deleted: %s | Remaining in archive: %s',
                number_format($inserted),
                number_format($deleted),
                number_format($remaining)
            ));

            if ($remaining === 0) {
                $this->line("<info>[REVERSE] ✅ All data reverted to clockings_news!</info>");
                $this->line('<comment>Next steps: php artisan clockings:archive verify --year=... then optimize then drop</comment>');
                return 0;
            }

            $this->line('<comment>[REVERSE] ⏳ Re-run to process next batch.</comment>');
            return 1; // Signal continuous loop to keep going

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('[REVERSE] ❌ ' . $e->getMessage());
            return 1;
        }
    }

    // ─────────────────────────────────────────────
    // VERIFY: Cross-check row counts
    // ─────────────────────────────────────────────
    private function runVerify($archiveTable, $dateFrom, $dateTo)
    {
        $this->info('[VERIFY] Checking row counts...');
        $this->line('');

        $mainTotal = DB::table('clockings_news')->count();

        $mainYear  = DB::table('clockings_news')
            ->where('datetime', '>=', $dateFrom)
            ->where('datetime', '<',  $dateTo)
            ->count();

        $archiveTotal = $this->archiveTableExists($archiveTable)
            ? DB::table($archiveTable)->count()
            : 'N/A (table missing)';

        $this->line('┌─────────────────────────────────────────┐');
        $this->line('│         VERIFICATION REPORT             │');
        $this->line('├─────────────────────────────────────────┤');
        $this->line("│ Main table total rows : <info>" . number_format($mainTotal) . "</info>");
        $this->line("│ Main table year rows  : <info>" . number_format($mainYear)  . "</info>");
        $this->line("│ Archive table rows    : <info>" . (is_int($archiveTotal) ? number_format($archiveTotal) : $archiveTotal) . "</info>");
        $this->line('└─────────────────────────────────────────┘');

        return 0;
    }

    // ─────────────────────────────────────────────
    // OPTIMIZE: Rebuild indexes + refresh stats
    // ─────────────────────────────────────────────
    private function runOptimize()
    {
        $this->warn('[OPTIMIZE] ⚠️  This may lock the table briefly. Best run during off-peak hours.');

        try {
            DB::statement('OPTIMIZE TABLE clockings_news');
            DB::statement('ANALYZE TABLE clockings_news');
            $this->info('[OPTIMIZE] ✅ Table optimized and analyzed successfully.');
            return 0;
        } catch (\Exception $e) {
            $this->error('[OPTIMIZE] ❌ ' . $e->getMessage());
            return 1;
        }
    }

    // ─────────────────────────────────────────────
    // DROP: Remove archive table (only if empty)
    // ─────────────────────────────────────────────
    private function runDrop($archiveTable)
    {
        if (! $this->archiveTableExists($archiveTable)) {
            $this->error("Archive table does not exist: {$archiveTable}");
            return 1;
        }

        $count = DB::table($archiveTable)->count();
        if ($count > 0) {
            $this->error("[DROP] ❌ Archive table still has " . number_format($count) . " rows. Revert all rows first.");
            return 1;
        }

        // Skip prompt if --force passed (useful in cron/scripts)
        if (! $this->option('force')) {
            if (! $this->confirm("⚠️  Are you sure you want to DROP {$archiveTable}? This is irreversible!")) {
                $this->line('[DROP] Aborted.');
                return 0;
            }
        }

        try {
            DB::statement("DROP TABLE IF EXISTS {$archiveTable}");
            $this->info("[DROP] ✅ {$archiveTable} dropped successfully.");
            return 0;
        } catch (\Exception $e) {
            $this->error('[DROP] ❌ ' . $e->getMessage());
            return 1;
        }
    }

    // ─────────────────────────────────────────────
    // HELPERS
    // ─────────────────────────────────────────────
    private function ensureArchiveTable($archiveTable)
    {
        if (! $this->archiveTableExists($archiveTable)) {
            DB::statement("CREATE TABLE IF NOT EXISTS {$archiveTable} LIKE clockings_news");
            $this->line("<comment>[INFO] Created archive table: {$archiveTable}</comment>");
        }
    }

    private function archiveTableExists($archiveTable)
    {
        return DB::getSchemaBuilder()->hasTable($archiveTable);
    }
}