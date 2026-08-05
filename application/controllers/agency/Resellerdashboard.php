<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Resellerdashboard extends CI_Controller
{
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

    public function index($reseller_id)
    {
        // Validate reseller_id
        if (!is_numeric($reseller_id) || $reseller_id <= 0) {
            return $this->json([
                'success' => false,
                'message' => 'Invalid reseller ID'
            ], 400);
        }

        // Total companies for this reseller
        $total_companies = $this->db->where('reseller_id', $reseller_id)->count_all_results('companies');

        // Fetch companies for contract calculations
        $this->db->reset_query();
        $this->db->select('id, start_date, contract_months, last_renewal_date, recurring_dealer');
        $this->db->from('companies');
        $this->db->where('reseller_id', $reseller_id);
        $this->db->where('start_date IS NOT NULL');
        $this->db->where('contract_months IS NOT NULL');
        $this->db->where('contract_months >', 0);
        $companies = $this->db->get()->result();

        // Calculate contract stats and sum recurring_dealer
        $expiringSoon = 0;
        $expired = 0;
        $active = 0;
        $recurring_dealer = 0;
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
            $recurring_dealer += (float) $company->recurring_dealer;
        }

        // Return JSON
        $this->json([
            'success' => true,
            'stats'   => [
                'total_companies' => $total_companies,
                'recurring_rm' => $recurring_dealer,
                'expiringSoon'    => $expiringSoon,
                'expired'         => $expired,
                'active'          => $active
            ]
        ]);
    }

}