<?php
defined('BASEPATH') OR exit('No direct script access allowed');


class Reminder extends CI_Controller
{
    public function companies()
    {
        $sql = "
        SELECT c.id, c.name, c.email, c.status,
        r.id AS reseller_id, r.name AS reseller_name, r.email AS reseller_email,
        c.first_reminder_sent_at,
        c.second_reminder_sent_at,
        c.final_reminder_sent_at,
        IF(c.status = 'terminated', NULL,
            DATEDIFF(
                LAST_DAY(DATE_ADD(
                    IF(c.last_renewal_date IS NULL OR YEAR(c.last_renewal_date) = 0, 
                       c.start_date, 
                       c.last_renewal_date),
                    INTERVAL c.contract_months - 1 MONTH
                )),
                CURDATE()
            )
        ) AS days_left
        FROM companies c
        LEFT JOIN resellers r ON r.id = c.reseller_id
        WHERE c.status != 'terminated'
        AND YEAR(c.start_date) > 0
        ";

        $query = $this->db->query($sql);
        
        if (!$query) {
            echo json_encode(['success' => false, 'error' => $this->db->error()]);
            return;
        }
        
        $companies = $query->result();

        $data = [
            'first' => [],
            'second' => [],
            'final' => []
        ];

        foreach ($companies as $c) {
            if ($c->days_left <= 31 && $c->days_left > 10 && empty($c->first_reminder_sent_at)) {
                $data['first'][] = $c;
            }
            elseif ($c->days_left <= 10 && $c->days_left > 0 && empty($c->second_reminder_sent_at)) {
                $data['second'][] = $c;
            }
            elseif ($c->days_left <= 0 && empty($c->final_reminder_sent_at)) {
                $data['final'][] = $c;
            }
        }

        echo json_encode(['success' => true, 'data' => $data]);
    }

    public function markSent()
    {
        $input = json_decode(file_get_contents('php://input'), true);

        $column = $input['type'] . '_reminder_sent_at';

        $this->db->where('id', $input['company_id'])
                 ->update('companies', [$column => date('Y-m-d H:i:s')]);

        echo json_encode(['success' => true]);
    }
}
