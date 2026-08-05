<?php
defined('BASEPATH') or exit('No direct script access allowed');

use Aws\S3\S3Client;
use Aws\Exception\AwsException;

class Employees extends CI_Controller
{
    public function __construct()
    {

        parent::__construct();
        $this->load->helper('url');
        $this->load->model('MyModel');
        $this->load->library('upload');

        $this->load->library('session');
        header("Access-Control-Allow-Origin: *");
        header('Access-Control-Allow-Headers: Content-Type, Content-Range, Content-Disposition, Content-Description');
    }

    public function index()
    {
        echo "running";
    }
    public function update_face_url()
    {
        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            echo json_encode(['status' => false, 'message' => 'Only POST method allowed']);
            return;
        }

        // Get input data
        $input = json_decode(file_get_contents('php://input'), true);

        // Validate
        if (empty($input['employee_code']) || empty($input['face_image_url'])) {
            echo json_encode(['status' => false, 'message' => 'Missing required fields']);
            return;
        }

        // Find employee
        $employee = $this->db->select('id')
            ->from('employees')
            ->where('id', $input['employee_code'])
            ->limit(1)
            ->get()
            ->row();

        if (!$employee) {
            echo json_encode(['status' => false, 'message' => 'Employee not found']);
            return;
        }

        $employee_id = $employee->id;
        $face_image_url = $input['face_image_url'];

        // Define image types: 'live_face','static_face','palm','fingerprint'
        $image_type = !empty($input['image_type']) ? $input['image_type'] : 'live_face';

        // Define which image types should update the employees table (face images only)
        $face_image_types = ['live_face', 'static_face'];

        // Check if this is a face image type
        $is_face_image = in_array($image_type, $face_image_types);

        // First, try to update existing record for this employee and image type
        $update_data = [
            'face_image_url' => $face_image_url,
            'image_type' => $image_type,
            'device_sn' => !empty($input['device_sn']) ? $input['device_sn'] : NULL,
            'uploaded_at' => !empty($input['upload_time']) ? $input['upload_time'] : date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $this->db->where('employee_id', $employee_id);
        $this->db->where('image_type', $image_type);
        $this->db->update('employee_face_images', $update_data);
        $affected_rows = $this->db->affected_rows();

        if ($affected_rows > 0) {
            // Record was updated, get the ID
            $record = $this->db->select('id')
                ->from('employee_face_images')
                ->where('employee_id', $employee_id)
                ->where('image_type', $image_type)
                ->limit(1)
                ->get()
                ->row();
            $record_id = $record->id;
            $action = 'updated';
            $is_new = false;
        } else {
            // No record was updated, so insert new one
            $face_data = array_merge($update_data, [
                'employee_id' => $employee_id,
                'created_at' => date('Y-m-d H:i:s')
            ]);

            $this->db->insert('employee_face_images', $face_data);
            $record_id = $this->db->insert_id();
            $action = 'created';
            $is_new = true;
        }

        // Update employees table ONLY for face image types (live_face, static_face)
        if ($is_face_image) {
            $this->db->where('id', $employee_id)
                ->update('employees', [
                    'face_image_url' => $face_image_url,
                    'face_image_updated_at' => date('Y-m-d H:i:s')
                ]);
        }

        // Prepare response message based on image type
        $image_type_display = str_replace('_', ' ', $image_type);
        $image_type_display = ucfirst($image_type_display);

        $message = $image_type_display . ' URL ' . $action . ' successfully';

        // Add clarification for non-face images
        if (!$is_face_image) {
            $message .= ' (not linked to employee profile)';
        }

        echo json_encode([
            'status' => true,
            'message' => $message,
            'data' => [
                'record_id' => $record_id,
                'employee_id' => $employee_id,
                'face_image_url' => $face_image_url,
                'image_type' => $image_type,
                'is_new' => $is_new,
                'action' => $action,
                'employee_table_updated' => $is_face_image
            ]
        ]);
    }

    /**
     * Get face image URL for employee
     * GET endpoint: /ApiController/get_face_url/{employee_code}
     */
    public function get_face_url($employee_code = null)
    {
        $response = [
            'status' => false,
            'message' => '',
            'data' => null
        ];

        if (!$employee_code) {
            $employee_code = $this->input->get('employee_code');
        }

        if (!$employee_code) {
            $response['message'] = 'employee_code parameter is required';
            return $this->output
                ->set_content_type('application/json')
                ->set_status_header(200)
                ->set_output(json_encode($response));
        }

        // Get employee
        $employee = $this->db->select('id, first_name, last_name')
            ->from('employees')
            ->where('id', $employee_code)
            ->or_where('special_id', $employee_code)
            ->or_where('qr_barcode', $employee_code)
            ->where('deleted_at', null)
            ->limit(1)
            ->get()
            ->row();

        if (!$employee) {
            $response['message'] = 'Employee not found';
            return $this->output
                ->set_content_type('application/json')
                ->set_status_header(200)
                ->set_output(json_encode($response));
        }

        // Get face images
        $faceImages = $this->db->select('face_image_url, image_type, uploaded_at')
            ->from('employee_face_images')
            ->where('employee_id', $employee->id)
            ->order_by('uploaded_at', 'DESC')
            ->get()
            ->result();

        $response['status'] = true;
        $response['message'] = count($faceImages) > 0 ? 'Face images found' : 'No face images found';
        $response['data'] = [
            'employee_id' => $employee->id,
            'employee_name' => $employee->first_name . ' ' . $employee->last_name,
            'face_images' => $faceImages
        ];

        return $this->output
            ->set_content_type('application/json')
            ->set_status_header(200)
            ->set_output(json_encode($response));
    }
}
