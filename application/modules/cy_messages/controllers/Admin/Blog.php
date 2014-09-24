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
			redirect(Route::named('admin/blog/list'));
		}

		$this->load->view('cy_messages/blogs/new_post_view');
	}

	public function edit_post()
	{
		$this->load->model('cy_messages/form_models/Cy_blog_form');

		$this->Cy_blog_form->carga($this->Cy_blog_model->carga);

		if ($this->Cy_blog_form->valid())
		{
			redirect(Route::named('admin/blog/list'));
		}

		$this->load->view('cy_messages/blogs/new_post_view');
	}

	public function delete_post()
	{
		$this->Cy_blog_model->delete();

		redirect(Route::named('admin/blog/list'));
	}


	/**
	 * 	Callbacks
	 */
	
	public function message_url_check($str)
	{
		$query = $this->Cy_blog_model->me()->where('message_url', $str);
		
		if($this->Cy_blog_model->carga != FALSE)
		{
			$query = $query->where('id != ', $this->carga->get_data('id'));
		}

		$query = $query->get();
		
		if ($query->num_rows() > 0)
		{
			$this->form_validation->set_message('message_url_check', 'Ya existe una entrada de blog con la url dada.');
			return FALSE;
		}
		
		return TRUE;
	}

}