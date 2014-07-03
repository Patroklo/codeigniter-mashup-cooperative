<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Derp extends CI_Controller {

	public function index()
	{

		$this->load->library('cyforms/cyforms');

		echo $this->cyforms->field('derp', array(
			'id'	=> 'element_id',
			'name'	=> 'element_name',
			'value'	=> 'Aloha'
		));

		echo 'Testing.';

	}

}

/* End of file derp.php */
/* Location: ./application/controllers/derp.php */