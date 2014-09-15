<?php

require_once APPPATH . 'modules/cy_form_generator/models/Cy_correcaminos_form_model.php';

class Cy_blog_form extends Cy_correcaminos_form_model
{
	function __construct($options = NULL)
	{

		$this->load->model('cy_messages/Cy_blog_model');

		parent::__construct();
	}


	function form_definition($options = NULL)
	{
		// all the data defined as in the previous comments

		$options = array('field_type' => 'Bootstrap',
						'objects' => 'message_object',
									'fields' => array(
													array('id' => 'message_title',
														'options' => array(
															'type' => 'Text',
															'rules' => 'required',
															'object_type' => 'message_object',
															'fieldName' => 'message_title',
															'label' => 'Post title',
														)
													),
													array('id' => 'message_text',
														'options' => array(
															'type' => 'Textarea',
															'rules' => 'required',
															'object_type' => 'message_object',
															'fieldName' => 'message_text',
															'label' => 'Body',
														)
													),
													array('id' => 'creation_date',
														'options' => array(
															'type' => 'Datepicker',
															'rules' => 'required|valid_date[d-m-Y]',
															'object_type' => 'message_object',
															'fieldName' => 'creation_date',
															'label' => 'Publication date',
															'value' => date('d-m-Y')
														)
													),
									)
		);


		parent::form_definition($options);
	}


	protected function insert($object_key)
	{
		$this->check_creation_date_value();
		
		$this->Cy_blog_model->insert($this->sanitized_data);
	}

	protected function update($object_key)
	{
		$this->check_creation_date_value();
		
		$this->Cy_blog_model->update($this->sanitized_data);
	}

	function validateDate($date, $format = 'Y-m-d H:i:s')
	{
	    $d = DateTime::createFromFormat($format, $date);
	    return $d && $d->format($format) == $date;
	}

	protected function check_creation_date_value()
	{
		if (array_key_exists('creation_date', $this->sanitized_data))
		{
			if ( ! $this->validateDate($this->sanitized_data['creation_date'], 'd-m-Y H:i'))
			{
				$this->sanitized_data['creation_date'].= ' 00:00:00';
			}
			else
			{
				$this->sanitized_data['creation_date'].= ':00';
			}
		}

		$this->sanitized_data['creation_date'] = date("Y-m-d H:i:s", strtotime($this->sanitized_data['creation_date']));

	}

}