<?php

require_once APPPATH . 'modules/cy_form_generator/models/Cy_correcaminos_form_model.php';

class Cy_comments_form extends Cy_correcaminos_form_model
{
	function __construct($options = NULL)
	{

		$this->load->model('cy_comments/Cy_comments_model');

		parent::__construct();
	}


	function form_definition($options = NULL)
	{
		// all the data defined as in the previous comments
		if ($this->auth->logged_in())
		{
			$options = array('field_type' => 'Bootstrap',
				'objects' => 'message_object',
				'fields' => array(
					array('id' => 'message_text',
						'options' => array(
							'type' => 'Textarea',
							'rules' => 'required',
							'object_type' => 'message_object',
							'fieldName' => 'message_text',
							'label' => 'Body',
						)
					),
				)
			);
		}
		else
		{
			$options = array('field_type' => 'Bootstrap',
				'objects' => 'message_object',
				'fields' => array(
					array('id' => 'message_text',
						'options' => array(
							'type' => 'Textarea',
							'rules' => 'required',
							'object_type' => 'message_object',
							'fieldName' => 'message_text',
							'label' => 'Body',
						)
					),
				)
			);
		}



		parent::form_definition($options);
	}


	protected function insert($object_key)
	{
		$this->Cy_blog_model->insert($this->sanitized_data);
	}

	protected function update($object_key)
	{
		$this->Cy_blog_model->update($this->sanitized_data);
	}

}