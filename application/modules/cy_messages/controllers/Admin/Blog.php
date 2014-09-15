<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Blog extends MY_Controller {

	public function __construct()
	{
		parent::__construct();
		
		$this->load->model('cy_messages/Cy_blog_model');
	}

	public function index()
	{
		$this->data->post_list = $this->Cy_blog_model->admin_reference_stream(0);
		
		$this->load->view('cy_messages/blogs/post_list', $this->data);
		
	}

	public function new_post()
	{
		$this->load->model('cy_messages/form_models/Cy_blog_form');

		if ($this->Cy_blog_form->valid())
		{
			redirect(Route::named('blog/list'));
		}

		$this->load->view('cy_messages/blogs/new_post_view');
	}

	public function edit_post()
	{
		$this->load->model('cy_messages/form_models/Cy_blog_form');

		$this->Cy_blog_form->carga($this->Cy_blog_model->carga);

		if ($this->Cy_blog_form->valid())
		{
			redirect(Route::named('blog/list'));
		}

		$this->load->view('cy_messages/blogs/new_post_view');
	}

	public function delete_post()
	{
		$this->Cy_blog_model->delete();
		
		redirect(base_url());
	}


}