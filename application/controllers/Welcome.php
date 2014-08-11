<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Welcome extends CI_Controller {

	public function index()
	{

		$this->load->library('cyforms/Cyforms');
		echo $this->cyforms->input_text->options(array(
			'id'			=> 'id_campo',
			'label'			=> 'Campo de prueba',
			'placeholder'	=> 'Un placeholder',
			'name'			=> 'name_campo'
		))->generate();

		echo $this->cyforms->datepicker->generate(array(
			'id'			=> 'le_checkbox2',
			'name'			=> 'le_name2',
			'label'			=> 'Otro campo de prueba',
			'wrapper'		=> FALSE
		));

		echo $this->cyforms->checkbox->generate(array(
			'id'		=> 'le_checkbox4',
			'name'		=> 'le_name4',
			'label'		=> 'Un checkbox',
			'checked'	=> TRUE
		));

<<<<<<< HEAD
		echo $this->cyforms->dropdown->generate(array(
=======
		echo $this->cyforms->select->generate(array(
>>>>>>> 213f5840962819c7cfab0f6cb1d95cf5c58234c3
			'id'		=> 'le_dropdown',
			'name'		=> 'le_name5',
			'label'		=> 'Un dropdown',
			'value'		=> 3,
<<<<<<< HEAD
=======
			'disabled'	=> TRUE,
>>>>>>> 213f5840962819c7cfab0f6cb1d95cf5c58234c3
			'options'	=> array(
				'1'	=> 'Opción',
				'3'	=> 'Otra opción',
				'5'	=> 'Tercera'
			)
		));

<<<<<<< HEAD
		echo $this->cyforms->input_text->generate(array(
=======
		$this->load->helper('cyforms/cyforms');

		echo generate_field('input_text', array(
>>>>>>> 213f5840962819c7cfab0f6cb1d95cf5c58234c3
			'id'				=> 'element_id',
			'label'				=> 'Campo con data attributes',
			'name'				=> 'element_name',
			'value'				=> 'Aloha',
			'autofocus'			=> TRUE,
			'placeholder'		=> 'Placeholder...',
<<<<<<< HEAD
=======
			'help'				=> 'Un texto de ayuda sobre este campo en concreto.',
			'error'				=> 'El campo dududuá es obligatorio.',
>>>>>>> 213f5840962819c7cfab0f6cb1d95cf5c58234c3
			'data_attributes'	=> array(
				'prueba'	=> 'wat',
				'test'		=> '200'
			),
			'extra'				=> 'extra="una string extra"'
		));

		echo $this->cyforms->radio->generate(array(
			'id'		=> 'le_radio',
			'name'		=> 'le_name',
			'label'		=> 'Un radio menú',
			'value'		=> 3,
			'disabled'	=> TRUE,
			'options'	=> array(
				array(
					'value'	=> 1,
					'label'	=> 'Opción'
				),
				array(
					'value'	=> 2,
					'label'	=> 'Otra opción'
				),
				array(
					'value'		=> 3,
					'label'		=> 'Tercera',
					'disabled'	=> TRUE
				),
			)
		));

		/*echo $this->cyforms->select->generate(array(
			'id'			=> 'le_checkbox',
			'name'			=> 'le_name',
			'option_values'	=> array(
				'derp'	=> 'Derp',
				'3'		=> 'Atún',
				'42'	=> 'Duh'
			)
		));*/

	}

}


/* End of file welcome.php */
/* Location: ./application/controllers/Welcome.php */