<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Admindashboard extends CI_Controller
{
    private $admin_id; // null = global view, int = filtered by admin

    public function __construct()
    {
        parent::__construct();
        $this->load->database();

        // ===== CORS HEADERS =====
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
        header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
        header("Access-Control-Allow-Credentials: false");
        header("Content-Type: application/json; charset=UTF-8");

        // Handle preflight OPTIONS request
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(200);
            exit;
        }

        $this->output->set_content_type('application/json');

        // === Get admin_id from GET parameter (OPTIONAL) ===
        $admin_id = $this->input->get('admin_id');

        if ($admin_id !== null && $admin_id !== '') {
            if (!is_numeric($admin_id) || $admin_id <= 0) {
                $this->json([
                    'success' => false,
                    'message' => 'Invalid admin_id. It must be a positive integer.'
                ], 400);
                exit;
            }
            $this->admin_id = (int) $admin_id;
        } else {
            $this->admin_id = null; // Global view
        }
    }

    private function json($data, $status_code = 200)
    {
        $this->output
            ->set_content_type('application/json')
            ->set_status_header($status_code)
            ->set_output(json_encode($data));
    }

    private function calculateDaysLeft($startDate, $contractMonths, $lastRenewalDate = null)
    {
        $baseDate = $lastRenewalDate ?: $startDate;
        if (!$baseDate || !$contractMonths) return null;

        $date = new DateTime($baseDate);
        $date->modify("+$contractMonths months");
        // Subtract 1 month
        $date->modify("-1 month");
        // Set to last day of the month
        $date->modify('last day of this month');

        $now = new DateTime();
        $interval = $now->diff($date);
        $days = $interval->days;
        if ($interval->invert) {
            $days = -$days;
        }
        return $days;
    }

    public function index()
    {
        $is_filtered = $this->admin_id !== null;

        // === Total Companies ===
        if ($is_filtered) {
            $this->db->where('admin_id', $this->admin_id);
        }
        $total_companies = $this->db->count_all_results('companies');

        // === Total Resellers ===
        $this->db->reset_query();
        if ($is_filtered) {
            $this->db->where('admin_id', $this->admin_id);
        }
        $resellers = $this->db->count_all_results('resellers');

        // Fetch companies for contract calculations
        $this->db->reset_query();
        $this->db->select('id, start_date, contract_months, last_renewal_date');
        $this->db->from('companies');
        if ($is_filtered) {
            $this->db->where('admin_id', $this->admin_id);
        }
        $this->db->where('start_date IS NOT NULL', null, false);
        $this->db->where('contract_months IS NOT NULL', null, false);

        $this->db->where('contract_months >', 0);
        $query = $this->db->get();
        $companies = $query ? $query->result() : [];


        // Calculate contract stats
        $expiringSoon = 0;
        $expired = 0;
        $active = 0;
        foreach ($companies as $company) {
            $days = $this->calculateDaysLeft($company->start_date, $company->contract_months, $company->last_renewal_date);
            if ($days < 0) {
                $expired++;
            } elseif ($days <= 30) {
                $expiringSoon++;
                $active++; // Also count expiring soon in active
            } else {
                $active++;
            }
        }

        // Sum recurring_dealer (companies only)
        $this->db->reset_query();
        $this->db->select_sum('recurring_dealer');
        $this->db->from('companies');
        if ($is_filtered) {
            $this->db->where('admin_id', $this->admin_id);
        }
        $sum_query = $this->db->get();
        $recurring_dealer = 0.0;
        if ($sum_query) {
            $row = $sum_query->row();
            if ($row && isset($row->recurring_dealer)) {
                $recurring_dealer = (float) $row->recurring_dealer;
            }
        }

        // Final response
        $this->json([
            'success' => true,
            'filtered_by_admin_id' => $this->admin_id, // null if global
            'stats' => [
                'total_companies' => (int) $total_companies,
                'resellers'       => (int) $resellers,
                'recurring_rm' => $recurring_dealer,
                'expiringSoon'    => (int) $expiringSoon,
                'expired'         => (int) $expired,
                'active'          => (int) $active
            ]
        ]);
    }


    public function breakdown()
    {
        $reseller_id = $this->input->get('reseller_id');
        $admin_id    = $this->admin_id; // from constructor
        
        // Pagination params for scrollable charts
        $limit = $this->input->get('limit') ? (int)$this->input->get('limit') : null;
        $offset = $this->input->get('offset') ? (int)$this->input->get('offset') : 0;

        $dbErrors = [];

        // ==============================
        // Base WHERE conditions
        // ==============================
        $baseWhere = [];

        if ($admin_id !== null) {
            $baseWhere['c.admin_id'] = (int) $admin_id;
        }

        if (!empty($reseller_id) && is_numeric($reseller_id)) {
            $baseWhere['c.reseller_id'] = (int) $reseller_id;
        }

        // ==============================
        // 1️⃣ Clients count per dealer
        // ==============================
        $this->db->reset_query();
        $this->db->select('c.reseller_id AS dealer_id, r.name AS dealer_name, COUNT(*) AS client_count');
        $this->db->from('companies c');
        $this->db->join('resellers r', 'r.id = c.reseller_id', 'left');

        if (!empty($baseWhere)) {
            $this->db->where($baseWhere);
        }

        $this->db->group_by('c.reseller_id, r.name');
        $this->db->order_by('client_count', 'DESC');
        
        // Count total dealers before pagination
        $count_query = clone $this->db;
        $total_dealers_count = $count_query->count_all_results();
        
        // Apply pagination if limit provided
        if ($limit !== null) {
            $this->db->limit($limit, $offset);
        }
        
        $queryResult = $this->db->get();
        if ($queryResult === false) {
            $dbErrors[] = $this->db->error();
            $byClientCount = [];
        } else {
            $byClientCount = $queryResult->result_array();
        }

        // Handle NULL dealer names
        foreach ($byClientCount as &$row) {
            $row['dealer_id'] = $row['dealer_id'];
            $row['dealer_name'] = $row['dealer_name'] ?: 'Unknown';
        }
        unset($row);

        // ==============================
        // 2️⃣ Recurring total per dealer
        // ==============================
        $this->db->reset_query();
        $this->db->select('c.reseller_id AS dealer_id, r.name AS dealer_name, SUM(c.recurring_dealer) AS recurring_total');
        $this->db->from('companies c');
        $this->db->join('resellers r', 'r.id = c.reseller_id', 'left');

        if (!empty($baseWhere)) {
            $this->db->where($baseWhere);
        }

        $this->db->group_by('c.reseller_id, r.name');
        $this->db->order_by('recurring_total', 'DESC');
        
        // Apply pagination if limit provided
        if ($limit !== null) {
            $this->db->limit($limit, $offset);
        }
        
        $queryResult = $this->db->get();
        if ($queryResult === false) {
            $dbErrors[] = $this->db->error();
            $byRecurring = [];
        } else {
            $byRecurring = $queryResult->result_array();
        }

        // Handle NULL dealer names
        foreach ($byRecurring as &$row) {
            $row['dealer_id'] = $row['dealer_id'];
            $row['dealer_name'] = $row['dealer_name'] ?: 'Unknown';
        }
        unset($row);

        // ==============================
        // 3️⃣ Active vs Expired per dealer
        // ==============================
        $this->db->reset_query();
        $this->db->select('
        c.reseller_id AS dealer_id,
        r.name AS dealer_name,
        c.start_date,
        c.contract_months,
        c.last_renewal_date
    ');
        $this->db->from('companies c');
        $this->db->join('resellers r', 'r.id = c.reseller_id', 'left');
        $this->db->where('start_date IS NOT NULL', null, false);
        $this->db->where('start_date !=', '0000-00-00');
        $this->db->where('contract_months IS NOT NULL', null, false);
        $this->db->where('contract_months >', 0);

        if (!empty($baseWhere)) {
            $this->db->where($baseWhere);
        }

        $query = $this->db->get();
        if ($query === false) {
            $dbErrors[] = $this->db->error();
            $companies = [];
        } else {
            $companies = $query->result();
        }

        $statusMap = [];

        foreach ($companies as $c) {

            // ---- SAFE DATE HANDLING ----
            try {
                $baseDate = (!empty($c->last_renewal_date) && $c->last_renewal_date !== '0000-00-00')
                    ? $c->last_renewal_date
                    : $c->start_date;

                if (!$baseDate) {
                    continue;
                }

                $expiry = new DateTime($baseDate);
                $expiry->modify("+{$c->contract_months} months");
                $expiry->modify("-1 month");
                $expiry->modify('last day of this month');

                $now  = new DateTime();
                $diff = $now->diff($expiry);
                $daysLeft = $diff->invert ? -$diff->days : $diff->days;
            } catch (Exception $e) {
                continue; // NEVER break API
            }

            $dealerId = $c->dealer_id ?: 0;
            $dealerName = $c->dealer_name ?: 'Unknown';

            if (!isset($statusMap[$dealerId])) {
                $statusMap[$dealerId] = [
                    'dealer_id'     => $dealerId,
                    'dealer_name'   => $dealerName,
                    'active_count'  => 0,
                    'expired_count' => 0,
                ];
            }

            if ($daysLeft < 0) {
                $statusMap[$dealerId]['expired_count']++;
            } else {
                $statusMap[$dealerId]['active_count']++;
            }
        }

        $byStatus = array_values($statusMap);
        
        // Sort byStatus by dealer name for consistency
        usort($byStatus, function($a, $b) {
            return strcmp($a['dealer_name'], $b['dealer_name']);
        });
        
        // Apply pagination to byStatus if limit provided
        if ($limit !== null) {
            $byStatus = array_slice($byStatus, $offset, $limit);
        }

        // ==============================
        // Final JSON response
        // ==============================
        $response = [
            'success' => true,
            'filtered_by_admin_id'    => $admin_id,
            'filtered_by_reseller_id' => $reseller_id ?: null,
            'pagination' => [
                'total_dealers' => (int)$total_dealers_count,
                'limit' => $limit,
                'offset' => $offset,
                'has_more' => $limit !== null && ($offset + count($byClientCount)) < $total_dealers_count
            ],
            'data' => [
                'byClientCount' => $byClientCount,
                'byRecurring'   => $byRecurring,
                'byStatus'      => $byStatus,
            ]
        ];

        if (!empty($dbErrors)) {
            $response['db_errors'] = $dbErrors;
        }

        $this->json($response);
    }
}
