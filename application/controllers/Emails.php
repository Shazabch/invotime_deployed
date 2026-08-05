<?php
class Emails extends CI_Controller {

	function __construct()
	{
		parent::__construct();
	}
	public function sendEmails(){
		$this->load->library('email');
		$config = array();
		$config['protocol'] = 'smtp';
		$config['smtp_host'] = 'smtp.zoho.com';
		$config['smtp_user'] = 'sean@invocore.com.my';
		$config['smtp_pass'] = 'seanO!eweb3939';
		$config['smtp_port'] = 465;
		$config['smtp_crypto'] = 'ssl';
		$config['mailtype'] = 'html';
		$this->email->initialize($config);
		$this->email->set_newline("\r\n");
		$query = $this->db->select('*')->from('slip_emails')->where('email <>', '')->where_in('state',array('pending','error'))->get();
		$emails = $query->result();
		foreach ($emails as $email) {

			$this->email->from('sean@invocore.com.my', "Invotime");
			$this->email->to($email->email);
			$this->email->subject($email->subject);
			$this->email->message($email->message);
        //Send mail
			if($this->email->send())
				$this->db->where('id',$email->id)->update('slip_emails',array('state'=>'sent'));
			else
				$this->db->where('id',$email->id)->update('slip_emails',array('state'=>'error'));
		}
	}

}