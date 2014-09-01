<?php

	class Form_field {
		
		protected $id;
		protected $name;
		protected $rules;
		protected $label;
		protected $value;
		protected $options = array();
		
		protected $callbacks;
		protected $rules_callables;
		
		
		protected $callback_response = TRUE;
		
		protected $error = FALSE;
		
		function __construct($options = array())
		{
			$this->callbacks = array('before'  => array(), 'after' => array());
			
			$this->set_options($options);
		}
		
		/**
		 * loads the options given in the method parameters
		 * 
		 * 		[callbacks]	=> (anonymous functions) [before|after]
		 * 		[id]
		 * 		[name]
		 * 		[rules]
		 * 		[help_text]
		 * 		[value]
		 * 		[options]	=> for extra options in special fields
		 * 				[callbacks]	(array) [before/after] => callback list
		 *
		 * @return void
		 * @author  Patroklo
		 */
		
		function set_options($options)
		{
			if(array_key_exists('callbacks', $options))
			{
				foreach($options['callbacks'] as $key => $callback_type)
				{
					if(!is_array($callback_type))
					{
						$callback_type = array($callback_type);
					}
					
					foreach($callback_type as $callback)
					{
						$this->callbacks[$key][] = $callback;
					}
				}
				
				unset($options['callbacks']);
			}
			
			$object_vars = get_object_vars($this);
			
			foreach($options as $key => $value)
			{
				if($key == 'options')
				{
					foreach($value as $key2 => $value2)
					{
						if(isset($this->$key2) || array_key_exists($key2, $object_vars ))
						{
							$this->$key2 = $value2;
						}
						else
						{
							$this->options[$key2] = $value2;
						}
					}
				}
				else
				{
					if(isset($this->$key) || array_key_exists($key, $object_vars ))
					{
						$this->$key = $value;
					}
					else
					{
						$this->options[$key] = $value;
					}
				}
			}
		}
		
		function get_options()
		{
			return $this->options;
		}
		
		function get_parameter($parameter_name)
		{
			if(isset($this->{$parameter_name}))
			{
				return $this->{$parameter_name};
			}
			
			return FALSE;
		}
		
		function get_field($extra_data){}

		function get_id()
		{
			return $this->id;
		}
		
		function get_name()
		{
			return $this->name;
		}
		
		function set_value($value)
		{
			$this->value = $value;
		}
		
		function get_value()
		{
			return $this->value;
		}
		
		function get_rules($loaded = NULL)
		{
			
			$return_rule = NULL;

			if (!is_array($this->rules) or 
				(!array_key_exists('update', $this->rules) and !array_key_exists('insert', $this->rules)))
			{
				$return_rule = $this->rules;
			}
			else
			{
				if ($loaded == NULL)
				{
					$loaded = FALSE;
				}
				
				if ($loaded == TRUE)
				{
					$return_rule = $this->rules['update'];
				}
				else
				{
					$return_rule = $this->rules['insert'];
				}
			}

			// if there is a callable defined then we will
			// always send it
			if (!empty($this->rules_callables))
			{
				if (!is_array($return_rule))
				{
					$return_rule = explode('|', $return_rule);
				}
				
				$return_rule = array_merge($return_rule, $this->rules_callables);
			}

			return $return_rule;

		}
		
		function set_error($error)
		{
			if(is_string($error))
			{
				$error = array($error);
			}
			
			foreach($error as $err)
			{
				$this->error.= $err;
			}
		}
		
		/**
		 * will show the field per se, also launches the callbacks in case there is something needed
		 *
		 * @return void
		 * @author  Patroklo
		 */

		function show($extra_data = array()) 
		{
			$this->execute_callback_list($this->callbacks['before']);
			$return_data = $this->get_field($extra_data);
			return $return_data;
		}
		
		function get_error()
		{
			return $this->error;
		}
		
		function execute_callbacks($type)
		{
			if($this->callbacks != NULL)
			{
				if(array_key_exists($type, $this->callbacks))
				{
					return $this->execute_callback_list($this->callbacks[$type]);
				}
			}
		}
		
		
		function execute_callback_list($callbacks)
		{
			$return_data = FALSE;
			
			if(is_array($callbacks))
			{
				foreach($callbacks as $callback)
				{
					$r_d = $this->execute_callback($callback);
					
					if($r_d)
					{
						$return_data[] = $r_d;
					}
				}
			}
			
			return $return_data;
			
		}
		
		/**
		 * it's important to give the callbacks a way to interact with the field object
		 *
		 * @return void
		 * @author  Patroklo
		 */
		
		function execute_callback(Closure $callback)
		{
			if(is_callable($callback))
			{
				$data = call_user_func($callback, $this);
				
				if(is_array($data))
				{
					$this->set_options($data);
				}
				
				return $data;
			}
		}
		
	}



	/**
	 * FACTORY FOR FIELD GENERATORS IN THE FFF METHOD
	 */
	
	class Field_factory
	{
		static function create($options)
		{
			if(!array_key_exists('type', $options))
			{
				return FALSE;
			}
			
			$className = ucwords($options['type']).'_field';
			
			return new $className($options);
		}
		
		static function form($type, $options = array())
		{
			$className = $type.'_form';
			
			return new $className($options);
		}

	}



	class Vanilla_form 
	{
		
		protected $submit_text;
		
		function __construct($options = array())
		{
			$this->set_options($options);
		}
		
		function set_options($options)
		{

			foreach($options as $key => $value)
			{
				if(isset($this->$key) || array_key_exists($key,get_object_vars($this)))
				{
					$this->$key = $value;
				}
				else
				{
					$this->options[$key] = $value;
				}
			}
		}
	 	
		function start_form()
		{
			return form_open_multipart();
		}
		
		function submit_button()
		{
			return form_submit('mysubmit', $this->submit_text);
		}
		
		function end_form()
		{
			return form_close();
		}
		
		function show_errors($errors)
		{
			$return_html = '<p>';
				
				foreach($errors as $error)
				{
					$return_html.= $error;
				}
			$return_html.= '</p>';
			
			return $return_html;
		}
		
	}


	class Form_Vanilla extends Form_field
	{
		protected $CI;
		
		function __construct($options)
		{
			parent::__construct($options);
			$this->CI =& get_instance();
			
			$this->CI->load->helper('form');
		}
	}
	
	class Text_Vanilla_field extends Form_Vanilla
	{

		protected $special_fields = array('maxlength', 'size', 'style');
		
		function get_field($extra_data)
		{
			$data = array(
			              'name'        => $this->name,
			              'id'          => $this->id,
			              'value'       => $this->value,
			            );
			
			foreach($this->special_fields as $field)
			{
				if(array_key_exists($field, $this->options))
				{
					$data[$field] = $this->options[$field];
				}
			}		
			
			return form_input($data);
		}
	}
	
	
	class Password_Vanilla_field extends Form_Vanilla
	{
		protected $special_fields = array('maxlength', 'size', 'style');
		
		function get_field($extra_data)
		{
			$data = array(
			              'name'        => $this->name,
			              'id'          => $this->id,
			            );
			
			foreach($this->special_fields as $field)
			{
				if(array_key_exists($field, $this->options))
				{
					$data[$field] = $this->options[$field];
				}
			}		
			
			return form_password($data);
		}
		
	}


	class Hidden_Vanilla_field extends Form_Vanilla
	{
		function get_field($extra_data)
		{
			return form_hidden($this->name, $this->value);
		}

	}

	class Upload_Vanilla_field extends Form_Vanilla
	{
		protected $special_fields = array('maxlength', 'size', 'style');
		
		function get_field($extra_data)
		{
			$data = array(
			              'name'        => $this->name,
			              'id'          => $this->id,
			              'value'       => $this->value,
			            );
			
			foreach($this->special_fields as $field)
			{
				if(array_key_exists($field, $this->options))
				{
					$data[$field] = $this->options[$field];
				}
			}		
			
			return form_upload($data);
		}
	}
	
	class Textarea_Vanilla_field extends Form_Vanilla
	{
		protected $special_fields = array('maxlength', 'size', 'style');
		
		function get_field($extra_data)
		{
			$data = array(
			              'name'        => $this->name,
			              'id'          => $this->id,
			              'value'       => $this->value,
			            );
			
			foreach($this->special_fields as $field)
			{
				if(array_key_exists($field, $this->options))
				{
					$data[$field] = $this->options[$field];
				}
			}		
			
			return form_textarea($data);
		}
	}

	// faltan unos cuantos campos, pero meh

	class Form_Bootstrap extends Form_field
	{
		protected $show_error;
		
		function __construct($options)
		{
			parent::__construct($options);
			$this->_CI =& get_instance();
			
			$this->_CI->load->library('cyforms/Cyforms');
			
		}
		
		function get_options()
		{
			
			$basic_data = $this->options;
			
			if(array_key_exists('data', $basic_data))
			{
				$basic_data['options'] = $basic_data['data'];
				unset($basic_data['data']);
			}
			
			$basic_data['id'] 		= $this->id;
			$basic_data['label']	= $this->label;
			$basic_data['name']		= $this->name;
			$basic_data['value'] 	= $this->value;
			
			if(!$this->error == FALSE && $this->show_error == TRUE)
			{
				$basic_data['error'] = $this->error;
			}

			return $basic_data;
		}
		
	}
	

	/**
	 * FIELD CLASSES
	 * 
	 */
	 
	class Bootstrap_form 
	{
		
		protected $submit_text;
		
		
		function __construct($options = array())
		{
			$this->set_options($options);
		}
		
		function set_options($options)
		{

			foreach($options as $key => $value)
			{
				if(isset($this->$key) || array_key_exists($key,get_object_vars($this)))
				{
					$this->$key = $value;
				}
				else
				{
					$this->options[$key] = $value;
				}
			}
		}
	 	
		function start_form()
		{
			return form_open_multipart();
		}
		
		function submit_button()
		{
			return form_submit(array('name' => 'mysubmit', 'value' => $this->submit_text, 'class' => 'btn'));
		}
		
		function end_form()
		{
			return form_close();
		}
		
		function show_errors($errors)
		{
			$return_html = '<p>';
				
				foreach($errors as $error)
				{
					$return_html.= $error;
				}
			$return_html.= '</p>';
			
			return $return_html;
		}
		
	}

	
	
	class Text_Bootstrap_field extends Form_Bootstrap 
	{
		function get_field($extra_data = array())
		{
			return $this->_CI->cyforms->input_text->options($this->get_options())->generate();
		}
	}
	
	class Textarea_Bootstrap_field extends Form_Bootstrap 
	{
		function get_field($extra_data = array())
		{
			return $this->_CI->cyforms->textarea->options($this->get_options())->generate();
		}
	}
	
	
	class Password_Bootstrap_field extends Form_Bootstrap 
	{
		function get_field($extra_data = array())
		{
			$options = $this->get_options();
			unset($options['value']);
			
			return $this->_CI->cyforms->password->options($options)->generate();
		}
	}
	
	class Checkbox_Bootstrap_field extends Form_Bootstrap
	{
		
		function get_field($extra_data = array())
		{
			$options = $this->get_options();
			
			return $this->_CI->cyforms->checkbox->options($options)->generate();
		}
	}


	class Select_Bootstrap_field extends Form_Bootstrap
	{
		function get_field($extra_data = array())
		{
			return $this->_CI->cyforms->select->options($this->get_options())->generate();
		}
	}

	class Radio_Bootstrap_field extends Form_Bootstrap
	{
		function get_field($extra_data = array())
		{
			return $this->_CI->cyforms->radio->options($this->get_options())->generate();
		}
	}

	class Datepicker_Bootstrap_field extends Form_Bootstrap
	{
		function get_field($extra_data = array())
		{
			return $this->_CI->cyforms->datepicker->options($this->get_options())->generate();
		}
	}







