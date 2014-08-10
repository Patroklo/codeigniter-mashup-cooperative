<?php

	class Form_field {
		
		protected $id;
		protected $name;
		protected $rules;
		protected $help_text;
		protected $value;
		protected $options = array();
		
		protected $callbacks;
		
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
					if(!array($callback_type))
					{
						$callback_type = array($callback_type);
						$key = 'before';
					}
					
					foreach($callback_type as $callback)
					{
						$this->callbacks[$key] = $callback;
					}
				}
				
				unset($options['callbacks']);
			}
			
			$object_vars = get_object_vars($this);
			
			foreach($options as $key => $value)
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
		
		function get_options()
		{
			return $this->options;
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
			if(!is_array($this->rules))
			{
				return $this->rules;
			}
			
			if($loaded == NULL)
			{
				$loaded = FALSE;
			}
			
			if($loaded == TRUE)
			{
				return $this->rules['update'];
			}
			
			return $this->rules['insert'];
		}
		
		function set_error($error)
		{
			if(!empty($error))
			{
				$this->error = $error;
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
			$this->execute_callback_list($this->callbacks['after']);
			return $return_data;
		}
		
		function get_error()
		{
			return $this->error;
		}
		
		function execute_callback_list($callbacks)
		{
			if(is_array($callbacks))
			{
				foreach($callbacks as $callback)
				{
					$this->execute_callback($callback);
				}
			}
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
				$data = call_user_func($callback);
				
				if(is_array($data))
				{
					$this->set_options($data);
				}
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
		function __construct($options)
		{
			parent::__construct($options);
			$CI =& get_instance();
			
			$CI->load->helper('cy_forms/form');
		}
		
		function get_options($extra_data = array())
		{
			$return_data 			= $extra_data + $this->options;
			$return_data['id'] 		= $this->id;
			$return_data['label'] 	= $this->name;
			$return_data['help'] 	= $this->help_text;
			$return_data['value']	= set_value($return_data['id'], $this->value);
			
			return $return_data;
		}
	}
	

	/**
	 * FIELD CLASSES
	 * 
	 */
	 
	 class Bootstrap_form {
	 	
		
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
			return form_open();
		}
		
		function submit_button()
		{
			return form_submit('mysubmit', $this->submit_text);
		}
		
		function end_form()
		{
			return form_close();
		}
		
	 }
	
	
	class Text_Bootstrap_field extends Form_Bootstrap 
	{
		function get_field($extra_data = array())
		{
			$extra_data =  $extra_data + array('type' => 'text');
			
			return summon_input($this->get_options($extra_data));
		}
	}
	
	class Textarea_Bootstrap_field extends Form_Bootstrap
	{
		function get_field($extra_data = array())
		{
			$extra_data = $extra_data + array('wysiwyg' => TRUE);
			
			return summon_textarea($this->get_options($extra_data));
		}
	}

