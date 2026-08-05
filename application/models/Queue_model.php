<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Queue_model
 *
 * Database operations for the job_queue table.
 * Used by Payroll_api (to create jobs) and Queue_worker (to process them).
 */
class Queue_model extends CI_Model
{
    private $table = 'job_queue';

    public function __construct()
    {
        parent::__construct();
    }

    // ------------------------------------------------------------------
    //  CREATE
    // ------------------------------------------------------------------

    /**
     * Insert a new job into the queue.
     *
     * @param  string $job_id   UUID v4
     * @param  string $type     Report type (e.g. 'pending_overtime')
     * @param  array  $payload  All input params needed to reproduce the report
     * @param  int    $priority 1-10 (1 = highest)
     * @return bool
     */
    public function create_job($job_id, $type, $payload, $priority = 5, $company_id = 0)
    {

        return $this->db->insert($this->table, array(
            'job_id'     => $job_id,
            'type'       => $type,
            'payload'    => json_encode($payload),
            'status'     => 'pending',
            'priority'   => $priority,
            'created_at' => date('Y-m-d H:i:s'),
            'company_id' => $company_id
        ));
    }

    // ------------------------------------------------------------------
    //  READ
    // ------------------------------------------------------------------

    /**
     * Fetch a job by its public UUID.
     *
     * @param  string $job_id
     * @return object|null
     */
    public function get_by_job_id($job_id)
    {
        return $this->db->where('job_id', $job_id)->get($this->table)->row();
    }

    /**
     * Claim the next pending job (oldest first, respecting priority).
     * Uses an atomic UPDATE + SELECT to avoid race conditions if
     * multiple workers ever run simultaneously.
     *
     * @return object|null  The claimed job row, or null if none available.
     */
    public function claim_next()
    {
        // 1) Atomically mark one pending row as 'processing'
        $sql = "UPDATE `{$this->table}`
                SET    `status`     = 'processing',
                       `started_at` = NOW(),
                       `attempts`   = `attempts` + 1
                WHERE  `status` = 'pending'
                  AND  `attempts` < `max_attempts`
                ORDER BY `priority` ASC, `created_at` ASC
                LIMIT 1";
        $this->db->query($sql);

        if ($this->db->affected_rows() === 0) {
            return null;
        }

        // 2) Retrieve the row we just claimed
        return $this->db
            ->where('status', 'processing')
            ->order_by('started_at', 'DESC')
            ->limit(1)
            ->get($this->table)
            ->row();
    }

    // ------------------------------------------------------------------
    //  UPDATE
    // ------------------------------------------------------------------

    /**
     * Update progress data during job processing (for real-time frontend updates).
     * Progress is stored as JSON to allow flexible step tracking.
     *
     * @param  int    $id       Primary key
     * @param  array  $progress Progress data (step, total_steps, message, timestamp, etc.)
     * @return bool
     */
    public function update_progress($id, $progress)
    {
        // Ensure progress has a timestamp
        if (!isset($progress['timestamp'])) {
            $progress['timestamp'] = date('Y-m-d H:i:s');
        }

        return $this->db->where('id', $id)->update($this->table, array(
            'progress'   => json_encode($progress),
            'updated_at' => date('Y-m-d H:i:s')
        ));
    }

    /**
     * Mark a job as completed and store its result.
     *
     * @param  int    $id      Primary key
     * @param  array  $result  The report data array
     * @return bool
     */
    public function mark_completed($id, $result)
    {
        return $this->db->where('id', $id)->update($this->table, array(
            'status'       => 'completed',
            'result'       => json_encode($result),
            'completed_at' => date('Y-m-d H:i:s'),
            'progress'     => json_encode(array('status' => 'completed', 'timestamp' => date('Y-m-d H:i:s')))
        ));
    }

    /**
     * Mark a job as failed with an error message.
     *
     * @param  int    $id
     * @param  string $error
     * @return bool
     */
    public function mark_failed($id, $error)
    {
        return $this->db->where('id', $id)->update($this->table, array(
            'status'       => 'failed',
            'error'        => $error,
            'completed_at' => date('Y-m-d H:i:s')
        ));
    }

    /**
     * Re-queue a failed/stuck job for retry (set status back to pending).
     *
     * @param  int $id
     * @return bool
     */
    public function requeue($id)
    {
        return $this->db->where('id', $id)->update($this->table, array(
            'status'     => 'pending',
            'started_at' => null,
            'error'      => null
        ));
    }

    /**
     * Cancel a job by marking it as failed with "Cancelled by user" message.
     *
     * @param  int $id
     * @return bool
     */
    public function cancel_job($id)
    {
        return $this->db->where('id', $id)->update($this->table, array(
            'status'       => 'failed',
            'error'        => 'Cancelled by user',
            'completed_at' => date('Y-m-d H:i:s')
        ));
    }

    /**
     * Delete a job completely from the queue.
     *
     * @param  int $id
     * @return bool
     */
    public function delete_job($id)
    {
        return $this->db->where('id', $id)->delete($this->table);
    }

    // ------------------------------------------------------------------
    //  MAINTENANCE
    // ------------------------------------------------------------------

    /**
     * Reset jobs stuck in 'processing' for more than $minutes minutes.
     * Useful as a cron safety net.
     *
     * @param  int $minutes
     * @return int Number of rows reset
     */
    public function reset_stuck_jobs($minutes = 30)
    {
        $stale_threshold = date('Y-m-d H:i:s', strtotime("-{$minutes} minutes"));
        $future_threshold = date('Y-m-d H:i:s', strtotime('+5 minutes'));

        $this->db->where('status', 'processing')
                  ->group_start()
                      ->where('started_at <', $stale_threshold)
                      ->or_where('started_at >', $future_threshold)
                  ->group_end()
                  ->where('attempts <', 3)  // only if retries remain
                  ->update($this->table, array(
                      'status'     => 'pending',
                      'started_at' => null
                  ));
        return $this->db->affected_rows();
    }

    /**
     * Purge completed/failed jobs older than $days days.
     *
     * @param  int $days
     * @return int Number of rows deleted
     */
    public function purge_old_jobs($days = 7)
    {
        $this->db->where_in('status', array('completed', 'failed'))
                  ->where('created_at <', date('Y-m-d H:i:s', strtotime("-{$days} days")))
                  ->delete($this->table);
        return $this->db->affected_rows();
    }

    /**
     * Get queue statistics (for monitoring dashboard / health endpoint).
     *
     * @return array
     */
    public function get_stats()
    {
        $rows = $this->db->select('status, COUNT(*) as cnt')
                         ->group_by('status')
                         ->get($this->table)
                         ->result();
        $stats = array('pending' => 0, 'processing' => 0, 'completed' => 0, 'failed' => 0);
        foreach ($rows as $r) {
            $stats[$r->status] = (int)$r->cnt;
        }
        return $stats;
    }

    /**
     * Get recent jobs, optionally filtered by type prefix.
     *
     * @param int         $limit
     * @param string|null $type_prefix
     * @return array
     */
    public function get_recent_jobs($limit = 20, $type_prefix = null)
    {
        $limit = max(1, min(200, (int)$limit));
        $user = get_user();
        if (is_array($user) && !empty($user['company_id'])) {
             $company_id = (int)$user['company_id'];
        }else{
            $company_id = 0;
        }


        $select = 'id, job_id, type, status, attempts, payload, result, error, created_at, started_at, completed_at';
        if ($this->db->field_exists('max_attempts', $this->table)) {
            $select .= ', max_attempts';
        }
        if ($this->db->field_exists('company_id', $this->table)) {
            $select .= ', company_id';
        }
        if ($this->db->field_exists('clocking_remarks_required', $this->table)) {
            $select .= ', clocking_remarks_required';
        }

        $this->db->select($select)
            ->from($this->table)
            ->where('company_id', $company_id)
            ->order_by('created_at', 'DESC')
            ->limit($limit);

        if ($type_prefix) {
            $this->db->like('type', $type_prefix, 'after');
        }

        return $this->db->get()->result();
    }
}
