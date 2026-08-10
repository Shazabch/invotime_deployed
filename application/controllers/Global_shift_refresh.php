<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Global Shift Refresh
 * One-click refresh of shifts and paired clockings for all employees
 * affected by the timezone bug.
 * URL: /global_shift_refresh
 */
class Global_shift_refresh extends CI_Controller
{
    private $bug_date = '2026-08-05';
    private $backup_table = null;

    public function __construct()
    {
        parent::__construct();
        if (empty($this->backup_table)) {
            $this->backup_table = $this->_detect_backup_table();
        }
    }

    private function _detect_backup_table()
    {
        $q = $this->db->query("SHOW TABLES LIKE 'clockings_news_fix_backup_%'");
        if ($q && $q->num_rows() > 0) {
            $row = array_values($q->row_array());
            return $row[0];
        }
        return 'clockings_news_fix_backup_20260805';
    }

    private function _table_exists($table)
    {
        $q = $this->db->query("SHOW TABLES LIKE '" . $this->db->escape_str($table) . "'");
        return ($q && $q->num_rows() > 0);
    }

    public function index()
    {
        $data['bug_date']      = $this->bug_date;
        $data['backup_table']  = $this->backup_table;
        $data['table_exists']  = $this->_table_exists($this->backup_table);
        $data['summary']       = $this->_get_summary();
        $data['employees']     = $this->_get_affected_employees();
        $data['companies']     = $this->_get_company_breakdown();
        $this->load->view('global_shift_refresh', $data);
    }

    public function process()
    {
        set_time_limit(0);
        ini_set('memory_limit', '512M');
        ob_implicit_flush(true);
        if (ob_get_level() > 0) {
            ob_end_flush();
        }

        $company_id = $this->input->get('company_id') ? (int) $this->input->get('company_id') : null;
        $limit      = $this->input->get('limit') ? (int) $this->input->get('limit') : 0;
        $offset     = $this->input->get('offset') ? (int) $this->input->get('offset') : 0;

        $bt = $this->backup_table;

        echo '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Processing</title>';
        echo '<style>body{font-family:monospace;font-size:13px;padding:20px;line-height:1.6}';
        echo '.ok{color:green}.err{color:red}.warn{color:orange}</style></head><body>';
        echo '<h3>Global Shift Refresh Started</h3>';
        echo '<p>Bug date: <strong>' . html_escape($this->bug_date) . '</strong></p>';
        echo '<p>Backup table: <code>' . html_escape($bt) . '</code></p>';
        echo '<hr>';

        if (!$this->_table_exists($bt)) {
            echo '<p class="err">Backup table <code>' . html_escape($bt) . '</code> not found!</p>';
            echo '<p>Run in phpMyAdmin: <code>SHOW TABLES LIKE \'%backup%\';</code></p>';
            echo '</body></html>';
            return;
        }

        $employees = $this->_get_affected_employees($company_id, $limit, $offset);
        $total = count($employees);

        if ($total === 0) {
            echo '<p class="warn">No affected employees found to process.</p>';
            echo '</body></html>';
            return;
        }

        echo '<p>Employees to process: <strong>' . $total . '</strong></p>';
        if ($company_id) {
            echo '<p>Filtered by company_id: <strong>' . $company_id . '</strong></p>';
        }
        if ($limit > 0) {
            echo '<p>Chunk: offset=' . $offset . ', limit=' . $limit . '</p>';
        }
        echo '<hr>';

        $processed = 0;
        $failed = 0;
        $errors = array();

        foreach ($employees as $emp) {
            $emp_id  = (int) $emp['employee_id'];
            $comp_id = (int) $emp['company_id'];
            $name    = isset($emp['employee_name']) ? $emp['employee_name'] : 'Emp ' . $emp_id;

            try {
                $this->_refresh_employee($emp_id);
                $processed++;
                echo '<span class="ok">[OK]</span> [' . $processed . '/' . $total . '] Emp ' . $emp_id . ' - ' . html_escape($name) . ' (Company ' . $comp_id . ')<br>';
            } catch (Exception $e) {
                $failed++;
                $msg = '[FAIL] [' . $processed . '/' . $total . '] Emp ' . $emp_id . ' - ' . html_escape($name) . ' (Company ' . $comp_id . ') ERROR: ' . $e->getMessage();
                echo '<span class="err">' . html_escape($msg) . '</span><br>';
                $errors[] = $msg;
            }
            flush();
            usleep(10000);
        }

        echo '<hr><h4>Summary</h4>';
        echo '<ul><li>Processed: <strong>' . $processed . '</strong></li>';
        echo '<li>Failed: <strong>' . $failed . '</strong></li></ul>';

        echo '<h4>Verification</h4>';
        $this->_run_verification();

        if (!empty($errors)) {
            echo '<h4 class="err">Errors</h4><pre>' . html_escape(implode("\n", $errors)) . '</pre>';
        }

        echo '<hr><p><a href="' . site_url('global_shift_refresh') . '">&larr; Back to dashboard</a></p>';
        echo '</body></html>';
    }

    /* ================================================================ */

    private function _get_summary()
    {
        $bt = $this->backup_table;
        if (!$this->_table_exists($bt)) {
            return array('clockings' => 0, 'employees' => 0, 'companies' => 0);
        }

        $total_clockings = 0;
        $q = $this->db->query("SELECT COUNT(*) AS n FROM `" . $this->db->escape_str($bt) . "`");
        if ($q && $q->num_rows() > 0) {
            $total_clockings = (int) $q->row()->n;
        }

        $total_employees = 0;
        $q = $this->db->query("SELECT COUNT(DISTINCT employee_id) AS n FROM `" . $this->db->escape_str($bt) . "`");
        if ($q && $q->num_rows() > 0) {
            $total_employees = (int) $q->row()->n;
        }

        $total_companies = 0;
        $q = $this->db->query("SELECT COUNT(DISTINCT company_id) AS n FROM `" . $this->db->escape_str($bt) . "`");
        if ($q && $q->num_rows() > 0) {
            $total_companies = (int) $q->row()->n;
        }

        return array('clockings' => $total_clockings, 'employees' => $total_employees, 'companies' => $total_companies);
    }

    private function _get_company_breakdown()
    {
        $bt = $this->backup_table;
        if (!$this->_table_exists($bt)) {
            return array();
        }

        $q = $this->db->query("SELECT b.company_id, COALESCE(co.name, 'Unknown') AS company_name, COUNT(DISTINCT b.employee_id) AS employee_count, COUNT(*) AS clocking_count FROM `" . $this->db->escape_str($bt) . "` b LEFT JOIN companies co ON co.id = b.company_id GROUP BY b.company_id ORDER BY employee_count DESC");

        if (!$q) {
            return array();
        }
        return $q->result_array();
    }

    private function _get_affected_employees($company_id = null, $limit = 0, $offset = 0)
    {
        $bt = $this->backup_table;
        if (!$this->_table_exists($bt)) {
            return array();
        }

        // Read directly from backup table only — no joins that can fail on missing columns
        $sql = "SELECT DISTINCT employee_id, company_id FROM `" . $this->db->escape_str($bt) . "` WHERE 1=1";

        if ($company_id) {
            $sql .= " AND company_id = " . (int) $company_id;
        }

        $sql .= " ORDER BY company_id, employee_id";

        if ($limit > 0) {
            $sql .= " LIMIT " . (int) $limit . " OFFSET " . (int) $offset;
        }

        $q = $this->db->query($sql);
        if (!$q) {
            log_message('error', 'Global_shift_refresh query failed: ' . $this->db->last_query());
            return array();
        }

        $rows = $q->result_array();

        // Try to fetch names in a separate, safe query
        $emp_ids = array();
        foreach ($rows as $r) {
            $emp_ids[] = (int) $r['employee_id'];
        }

        $names = array();
        if (!empty($emp_ids)) {
            // Detect which name column exists
            $name_col = 'name';
            $cols = $this->db->query("SHOW COLUMNS FROM employees LIKE 'name'")->result_array();
            if (empty($cols)) {
                $cols = $this->db->query("SHOW COLUMNS FROM employees LIKE 'full_name'")->result_array();
                if (!empty($cols)) {
                    $name_col = 'full_name';
                } else {
                    $cols = $this->db->query("SHOW COLUMNS FROM employees LIKE 'first_name'")->result_array();
                    if (!empty($cols)) {
                        $name_col = "CONCAT(first_name, ' ', last_name)";
                    } else {
                        $name_col = 'id'; // fallback
                    }
                }
            }

            $id_list = implode(',', $emp_ids);
            $nq = $this->db->query("SELECT id, " . $name_col . " AS emp_name FROM employees WHERE id IN (" . $id_list . ")");
            if ($nq) {
                foreach ($nq->result_array() as $nr) {
                    $names[(int) $nr['id']] = $nr['emp_name'];
                }
            }
        }

        // Attach names
        foreach ($rows as &$r) {
            $eid = (int) $r['employee_id'];
            $r['employee_name'] = isset($names[$eid]) ? $names[$eid] : 'Emp ' . $eid;
        }
        unset($r);

        return $rows;
    }

    private function _refresh_employee($employee_id)
    {
        $first_day = $this->bug_date;
        $last_day  = $this->bug_date;

        $period = new DatePeriod(
            new DateTime($first_day),
            new DateInterval('P1D'),
            (new DateTime($last_day))->add(new DateInterval('P1D'))
        );

        foreach ($period as $date) {
            $current_date = $date->format('Y-m-d');
            $shift_id = '';
            $shift = null;

            $assigned_shift = $this->db->query(
                "SELECT * FROM shift_days WHERE date = '" . $this->db->escape_str($current_date) . "' AND FIND_IN_SET(" . (int) $employee_id . ", employees)"
            )->row();

            if ($assigned_shift) {
                $shift_id = $assigned_shift->shift_id;
            }

            if ($shift_id) {
                $shift = $this->db->query(
                    "SELECT id, name, color, code, overnight FROM shifts WHERE id = " . (int) $shift_id
                )->row();
            }

            $clocking_ids_to_update = get_clocking_ids_to_update($current_date, $employee_id, $shift);

            if (!empty($clocking_ids_to_update)) {
                $this->db->query(
                    "UPDATE clockings_news SET shift_id = " . ($shift_id ? (int) $shift_id : 'NULL') . " WHERE id IN (" . $clocking_ids_to_update . ")"
                );
            }
        }

        $datetime = $first_day . " 00:00:00";
        $last_datetime = $last_day . " 23:59:59";
        update_new_clockings($employee_id, $datetime, $last_datetime);
    }

    private function _run_verification()
    {
        $bd = $this->bug_date;
        $bt = $this->backup_table;

        $remaining = 0;
        $q = $this->db->query("SELECT COUNT(*) AS n FROM clockings_news WHERE datetime >= '" . $bd . " 11:00:00' AND datetime <= '" . $bd . " 15:00:00' AND created_at >= '" . $bd . " 00:00:00' AND created_at < DATE_ADD('" . $bd . "', INTERVAL 1 DAY) AND ABS(TIMESTAMPDIFF(MINUTE, created_at, datetime)) < 60");
        if ($q && $q->num_rows() > 0) {
            $remaining = (int) $q->row()->n;
        }
        echo '<p>Bugged rows remaining in clockings_news: <strong>' . $remaining . '</strong> ';
        echo ($remaining == 0 ? '<span class="ok">OK</span>' : '<span class="err">NEEDS ATTENTION</span>') . '</p>';

        $paired = 0;
        if ($this->_table_exists($bt)) {
            $q = $this->db->query("SELECT COUNT(*) AS n FROM new_clockings nc WHERE nc.clock_in_id IN (SELECT id FROM `" . $this->db->escape_str($bt) . "`)");
            if ($q && $q->num_rows() > 0) {
                $paired = (int) $q->row()->n;
            }
        }

        $backup_count = 0;
        if ($this->_table_exists($bt)) {
            $q = $this->db->query("SELECT COUNT(*) AS n FROM `" . $this->db->escape_str($bt) . "`");
            if ($q && $q->num_rows() > 0) {
                $backup_count = (int) $q->row()->n;
            }
        }
        echo '<p>new_clockings entries for affected clockings: <strong>' . $paired . '</strong> / <strong>' . $backup_count . '</strong></p>';

        $samples = $this->db->query("SELECT nc.clock_in, nc.clock_out, nc.shift_id, s.name AS shift_name FROM new_clockings nc LEFT JOIN shifts s ON s.id = nc.shift_id WHERE nc.clock_in_id IN (SELECT id FROM `" . $this->db->escape_str($bt) . "`) LIMIT 5");

        if ($samples && $samples->num_rows() > 0) {
            echo '<p>Sample paired clockings after fix:</p>';
            echo '<table border="1" cellpadding="5" style="border-collapse:collapse;font-size:12px"><tr><th>Clock In</th><th>Clock Out</th><th>Shift</th></tr>';
            foreach ($samples->result_array() as $s) {
                echo '<tr>';
                echo '<td>' . html_escape($s['clock_in']) . '</td>';
                echo '<td>' . html_escape($s['clock_out'] ? $s['clock_out'] : '-') . '</td>';
                echo '<td>' . html_escape($s['shift_name'] ? $s['shift_name'] : 'No shift') . ' (ID: ' . (int) $s['shift_id'] . ')</td>';
                echo '</tr>';
            }
            echo '</table>';
        }
    }
}