<?php defined('BASEPATH') OR exit('No direct script access allowed');

// require_once APPPATH.'core/Api_Controller.php';
require_once APPPATH.'modules/rest_server/libraries/REST_Controller.php';

class Comments extends REST_Controller {

	public function __construct()
	{
		parent::__construct();

		$this->load->model('cy_comments/Cy_comments_model');
		$this->load->model('cy_comments/form_models/Cy_comments_form');

	}

	public function message_list_get()
	{
		$reference_id = $this->uri->segment('reference_id');
		$reference_id = 1;
// $this->Cy_comments_model->show_comments($reference_id)
		$this->response(array('hola' => '1', 'adios'),  '200');

	}
	
	public function new_comment()
	{
		$reference_id = $this->uri->segment('reference_id');
		$comment_type = $this->uri->segment('comment_type');
		
		echo $this->Cy_comments_model->show_comments($reference_id);

	}

}