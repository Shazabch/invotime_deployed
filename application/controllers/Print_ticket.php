<?php
class Print_ticket extends CI_Controller {

	function __construct()
    {
      parent::__construct();

			
			//var_dump(get_user());
    }

	public function Index()
	{

		//namespace chillerlan\QRCodeExamples;
		
		//use chillerlan\QRCode\{QRCode, QROptions};

		$qr = $this->input->get('qr');

		$ticket_transactions_row = $this->db->get_where('ticket_transactions', array('qr_code' => $qr))->row();


		$event_row = $this->db->get_where('events', array('id' => $ticket_transactions_row->event_id))->row();
		$ticket_row = $this->db->get_where('tickets', array('id' => $ticket_transactions_row->ticket_id))->row();

		// var_dump($ticket_transactions_row->qr_code);
		// var_dump($ticket_transactions_row->visitor_name);
		// var_dump($ticket_transactions_row->visitor_company);
		// var_dump($event_row->event_name_english);
		// var_dump($event_row->event_name_arabic);
		// var_dump($event_row->event_logo);
		// var_dump($event_row->event_address);
		// var_dump($event_row->event_guidelines);
		// var_dump($event_row->event_contacts);



		$partners_rows = $this->db->get_where('partners', array('event_id' => $event_row->id))->result();

		//var_dump($partners_rows);


		$this->load->library('ciqrcode');

		$params['data'] = $qr;
		$params['level'] = 'H';
		$params['size'] = 10;
		$params['savename'] = FCPATH.'/qr_images/'.$qr.'.png';

		$this->ciqrcode->generate($params);

		// echo '<img src="'.base_url().'/qr_images/'.$qr.'.png" />';

		$data = array();

		$data["ticket_transactions_row"] = $ticket_transactions_row;
		$data["ticket_row"] = $ticket_row;
		$data["event_row"] = $event_row;
		$data["partners_rows"] = $partners_rows;
		$data["qr"] = base_url().'/qr_images/'.$qr.'.png';



		$this->load->view('print_ticket',$data);



	}


}


?>