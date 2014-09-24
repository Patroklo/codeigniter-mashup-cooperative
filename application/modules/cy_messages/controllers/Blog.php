<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Blog extends MY_Controller {

	public function __construct()
	{
		parent::__construct();
		
		$this->load->model('cy_messages/Cy_blog_model');
	}

	public function blog_list()
	{
		$reference_id = 0;
			
		$this->data->post_list = $this->Cy_blog_model->load_reference_stream(NULL);
		
		$this->load->view('cy_messages/blogs/post_list', $this->data);
	}
	
	
	public function load_post()
	{
		$post_name = $this->uri->segment('post_name');
		
		$reference_id = 0;

		$this->data->post = $this->Cy_blog_model->carga(array('message_url' => $post_name, 'reference_id' => $reference_id));

		if ( ! $this->Cy_blog_model->carga)
		{
			show_404();
		}

		// TODO añadir comentarios

		$this->load->view('cy_messages/blogs/post', $this->data);
	}
	
	



}