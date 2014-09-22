<?php defined('BASEPATH') OR exit('No direct script access allowed');

// require_once APPPATH.'core/Api_Controller.php';
require_once APPPATH.'modules/rest_server/libraries/REST_Controller.php';

class Comments extends REST_Controller {

	public function __construct()
	{
		parent::__construct();

		$this->load->model('cy_comments/Cy_comments_model');
	}

	public function list_get()
	{
		$reference_id = $this->get('reference_id');
		$inner_id = (($this->get('inner_id'))?$this->get('inner_id'):NULL);

		$this->response($this->Cy_comments_model->show_comments($reference_id, $inner_id),  '200');

	}

	public function new_post()
	{
		$this->load->model('cy_comments/form_models/Cy_comments_form');

		if ($this->Cy_comments_form->valid())
		{
			if ($this->Cy_comments_model->carga == FALSE)
			{
				$this->response('Error inesperado.',  '400');
			}

			$this->response($this->Cy_comments_model->show_comment(),  '200');
		}
		else
		{
			$this->response($this->Cy_comments_model->show_errors(),  '400');
		}
	}

	public function edit_get()
	{

		$this->Cy_comments_model->carga($this->uri->segment('id'));

		if ($this->Cy_comments_model->carga == FALSE)
		{
			$this->response('Error inesperado.',  '400');
		}

		$this->response($this->Cy_comments_model->show_edit_comment(), 200);
	}

	public function edit_post()
	{
		$this->Cy_comments_model->carga($this->uri->segment('id'));

		if ($this->Cy_comments_model->carga == FALSE)
		{
			$this->response('Error inesperado.',  '400');
		}

		$this->load->model('cy_comments/form_models/Cy_comments_form');

		$this->Cy_comments_form->carga($this->Cy_comments_model->carga);

		if ($this->Cy_comments_form->valid())
		{

			if ($this->Cy_comments_model->carga == FALSE)
			{
				$this->response('Error inesperado.',  '400');
			}

			$this->response($this->Cy_comments_model->show_comment(),  '200');
		}
		else
		{
			$this->response($this->Cy_comments_model->show_errors(),  '400');
		}
	}

	public function delete_delete()
	{
		$this->Cy_comments_model->carga($this->uri->segment('id'));

		if ($this->Cy_comments_model->delete() == FALSE)
		{
			$this->response('Error inesperado.',  '400');
		}

		$this->response('OK',  '200');

	}

}