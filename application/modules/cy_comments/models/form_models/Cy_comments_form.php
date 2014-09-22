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

						)
					),
					array('id' => 'reference_id',
						'options' => array(
							'type' => 'Hidden',
							'rules' => 'required',
							'object_type' => 'message_object',
							'fieldName' => 'reference_id',
							'value' => $this->Cy_comments_model->reference_id,
						)),
					array('id' => 'message_type',
						'options' => array(
							'type' => 'Hidden',
							'rules' => 'required',
							'object_type' => 'message_object',
							'fieldName' => 'message_type',
							'value' => $this->Cy_comments_model->message_type,
						)),
					array('id' => 'inner_id',
						'options' => array(
							'type' => 'Hidden',
							'object_type' => 'message_object',
							'fieldName' => 'inner_id',
							'value' => $this->Cy_comments_model->inner_id,
						))
				)
			);
		}
		else
		{

			$options = array('field_type' => 'Bootstrap',
				'objects' => 'message_object',
				'fields' => array(
					array('id' => 'anonymous_name',
						'options' => array(
							'type' => 'Text',
							'rules' => 'required',
							'object_type' => 'message_object',
							'fieldName' => 'anonymous_name',
						)
					),
					array('id' => 'message_text',
						'options' => array(
							'type' => 'Textarea',
							'rules' => 'required',
							'object_type' => 'message_object',
							'fieldName' => 'message_text',
						)
					),
					array('id' => 'reference_id',
						'options' => array(
							'type' => 'Hidden',
							'rules' => 'required',
							'object_type' => 'message_object',
							'fieldName' => 'reference_id',
							'value' => $this->Cy_comments_model->reference_id,
						)),
					array('id' => 'message_type',
						'options' => array(
							'type' => 'Hidden',
							'rules' => 'required',
							'object_type' => 'message_object',
							'fieldName' => 'message_type',
							'value' => $this->Cy_comments_model->message_type,
						)),
					array('id' => 'inner_id',
						'options' => array(
							'type' => 'Hidden',
							'object_type' => 'message_object',
							'fieldName' => 'inner_id',
							'value' => $this->Cy_comments_model->inner_id,
						)),
					array('id' => 'recaptcha_response_field',
						'options' => array(
							'type'  => 'Recaptcha',
							'rules' => array('required', array('recaptcha_valido', function()
																{
																	$this->load->library('cy_form_generator/Recaptcha');
																	$this->recaptcha->recaptcha_check_answer();

																	if ($this->recaptcha->getIsValid() == FALSE)
																	{
																		$this->form_validation->set_message('recaptcha_valido', 'El captcha no es válido.');
																		return FALSE;
																	}

																	return TRUE;
																})
											)
						)
					)
				)
			);
		}


		parent::form_definition($options);
	}


	protected function insert($object_key)
	{
		$data = $this->input->post();
		
		if ( ! array_key_exists('inner_id', $data))
		{
			$data['inner_id'] = NULL;
		}
		
		$this->Cy_comments_model->insert($data);
	}

	protected function update($object_key)
	{
		$this->Cy_comments_model->update($this->input->post());
	}

}