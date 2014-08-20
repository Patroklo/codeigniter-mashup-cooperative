<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Welcome extends MY_Controller {


	public function index()
	{
		$this->load->controller('Derp');
		
		
		
		$this->load->library('cyforms/Cyforms');
		echo $this->cyforms->input_text->options(array(
			'id'			=> 'id_campo',
			'label'			=> 'Campo de prueba',
			'placeholder'	=> 'Un placeholder',
			'name'			=> 'name_campo',
			'value'			=> 'derpy'
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

		echo $this->cyforms->select->generate(array(
			'id'		=> 'le_dropdown',
			'name'		=> 'le_name5',
			'label'		=> 'Un dropdown',
			'value'		=> 3,
			'disabled'	=> TRUE,
			'options'	=> array(
				'1'	=> 'Opción',
				'3'	=> 'Otra opción',
				'5'	=> 'Tercera'
			)
		));

		$this->load->helper('cyforms/cyforms');

		echo generate_field('input_text', array(
			'id'				=> 'element_id',
			'label'				=> 'Campo con data attributes',
			'name'				=> 'element_name',
			'value'				=> 'Aloha',
			'autofocus'			=> TRUE,
			'placeholder'		=> 'Placeholder...',
			'help'				=> 'Un texto de ayuda sobre este campo en concreto.',
			'error'				=> 'El campo dududuá es obligatorio.',
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

		/* TEST */

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

	public function prueba_forms()
	{
		

		
		$this->load->library('correcaminos/correcaminos');
		$this->load->helper('correcaminos/correcaminos');
		
		$this->load->model('cy_upload/Orm_upload_operations');
		$this->load->model('Prueba_formulario_model');

		if($this->uri->segment('id'))
		{
			$user = $this->correcaminos->beep('user_object')->where('id',$this->uri->segment('id'))->get_one();
			
			if(empty($user))
			{
				show_404();
			}

			$this->Prueba_formulario_model->carga('user_object', $user);
	 	}


		$this->Prueba_formulario_model->valid();
			
		$this->load->view('myform');
	}

	 public function password_check($str)
	 {
	 	
	 	if($this->input->post('password') != $str)
		{
			$this->form_validation->set_message('password_check', 'la contraseña debe ser puto igual en ambos campos');
			return FALSE;
		}
		else 
		{
			 return TRUE;
		}
	 }


}


/* End of file welcome.php */
/* Location: ./application/controllers/Welcome.php */