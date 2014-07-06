<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

	// base class which will send a form field object depending of the __get like
	// $this->cyforms->text_field->...

	class Cyforms
	{
		function __get($form_field)
		{
			if (class_exists($form_field) && is_subclass_of($form_field, 'Cyform_field_base'))
			{
				return new $form_field();
			}
			else
			{
				throw new Exception('The '.$form_field.' form field type doesn\'t exist.', 1);
			}
		}
	}

	// base class with all the common code for all the fields
	// all fields must inherit it

	class Cyform_field_base
	{
		// base field values and html options
		protected $id;
		protected $name;
		protected $class = array();
		protected $value;
		protected $label;
		protected $placeholder;

		// initialited automatically BUT may be changed manually
		protected $wrapper;		// (boolean) Inserts the field between a HTML wrapper
		protected $wrapper_view;

		// can't initialize manually
		protected $view_options		= array();
		protected $view_path;
		protected $form_field_type	= NULL;
		protected $_ci;

		function __construct()
		{

			$this->_ci =& get_instance();

			$this->_ci->load->config('cyforms/form_initialization', TRUE);

			$data = $this->_ci->config->item('form_initialization');

			foreach ($data as $option => $value)
			{
				$this->$option = $value;
			}
		}

		/**
		 * fill the $view_options array that will send all the form data into the field method
		 *
		 * @return void
		 */

		protected function make_options()
		{
			if ($this->name === NULL)
			{
				$this->name = $this->id;
			}

			$this->view_options = get_object_vars($this);

			$this->view_options['class']	= ((empty($this->class))?'':implode(' ', $this->class));
		}


		/**
		 * check the $view_options array for initialization errors, like no form field name, id, etc...
		 *
		 * @return void
		 */

		protected function check_options()
		{
			$obligatory_options = array('id', 'name');

			foreach ($obligatory_options as $option_name)
			{
				if (!isset($this->view_options[$option_name]) || is_null($this->view_options[$option_name]))
				{
					$this->exception('The '.$option_name.' option it\'s not defined');
					return FALSE;
				}
			}

			if ($this->form_field_type === NULL)
			{
				$this->exception('The class doesn\'t have a field type defined.');
				return FALSE;
			}


			return TRUE;
		}

		/**
		 * makes the html happen. If we want to change the view for another method, it should be here.
		 *
		 * @return void
		 */
		protected function generate_html()
		{
			$field	= $this->_ci->load->view($this->view_path.$this->form_field_type.'_view', $this->view_options, TRUE);
			
			if ($this->wrapper == TRUE)
			{
				return $this->_ci->load->view($this->wrapper_view, array('field' => $field), TRUE);
			}
			
			return $field;
		}

		/**
		 * inserts in the object the options parameters given in the field initialization
		 *
		 * @return void
		 */

		public function options(array $options)
		{
			$invalid_values = array('view_options', 'view_path', 'form_field_type', '_ci');
			
			foreach ($invalid_values as $value) 
			{
				if(array_key_exists($value, $options))
				{
					unset($options[$value]);
				}
			}
		
			
			foreach ($options as $option => $value)
			{
				if (isset($this->$option) && !is_null($this->$option) and is_array($this->$option))
				{				
					if (!is_array($value))
					{
						$value = array($value);
					}

					$this->$option = array_merge($this->$option, $value);
				}
				else
				{
					$this->$option = $value;
				}
			}

			return $this;
		}

		public function generate($options = NULL)
		{
			if (!is_null($options))
			{
				$this->options($options);
			}

			$this->make_options();

			if ($this->check_options() == FALSE)
			{
				return FALSE;
			}

			return $this->generate_html();
		}

		protected function exception($message)
		{
			throw new Exception($message, 1);
		}

	}

	/**
	 * =========================================================================
	 *
	 * 	Form field classes
	 *
	 * =========================================================================
	 */


	class input_text extends Cyform_field_base
	{

		protected $form_field_type = 'input_text';

	}


	class select extends Cyform_field_base
	{

		protected $form_field_type = 'select';
		protected $option_values;

		/**
		 * checks additional options for the select field
		 *
		 * @return void
		 */

		protected function check_options()
		{

			if (is_null($this->view_options['option_values']))
			{
				$this->exception('The select field doesn\'t have any defined options.');
				return FALSE;
			}

			return parent::check_options();
		}

	}

	class checkbox extends Cyform_field_base
	{

		protected $form_field_type = 'checkbox';
		protected $checked	= FALSE;

	}

	class datepicker extends Cyform_field_base
	{
		protected $form_field_type 	= 'datepicker';

		// lo pongo en un array porque luego es más fácil de trabajar con estos
		// a la hora de usarlo en el html colo tienes que poner
		// class="'.implode(' ', $class_list).'"

		protected $class 			= array('datepicker');

	}
