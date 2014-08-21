<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Derp extends AJAX_Controller {


	public function index()
	{
		echo json_encode(array('hola' => 'adios'));
	}

}

/* End of file derp.php */
/* Location: ./application/controllers/derp.php */