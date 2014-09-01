<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');


require_once APPPATH.'modules/cy_form_generator/models/Cy_base_form_model.php';

class Cy_correcaminos_form_model extends Cy_base_form_model
{
	

	
	// Database type => Correcaminos
	
	/*
	 * Data creation format
	 * 		
	 * 		[field_type]	=> (optional) (string) field form type definition for /libraries/Form_field.php
	 * 		[objects] 		=> (string) (loaded or not (setted as insert or update))
	 * 						   (optional) (array)
	 * 										[name]	=> (string) the object class name
	 * 										[alias] => (string) neccesary when using two or more objects of the same class name
	 * 															in order to be able to tell which is which
	 * 		[fields]		=> (array) of Field format
	 * 
	 */
	 
	/* 
	 * Field format:
	 * 
	 * 		id (unique) (string) (field name and id for the html labels)
	 * 		options (array)
	 * 					[type] 					=> string (field form type, like text, textarea, checkbox...)
	 * 					[rules]					=> string or array
	 * 														string => (rules for codeigniter's form_validation)
	 * 														array => array('insert' => (string), 'update' => (string))
	 * 					[object_type]			=> (optional) string (object of the field, if not setted, then won't be used in database related methods)
	 * 														  (it's the name or alias (in case there is an alias defined) of the object)
	 * 					[fieldName]				=> (optional) string (field name in the table) if not set, will try to use the id as fieldName
	 * 					[value]					=> (optional) mixed (field data will be set in the form)
	 * 					[additional_parameters] => (optional) additional parameters for the html form
	 * 
	 */
	 
	 /*
	  * (only for file objects)
	  * File field format:
	  * 
	  * 	id (unique) (string) (if fieldName it's not defined, will be used to define file field name defined in the _classData method)
	  * 	options (array)
	  * 			[type]						=> (string) file type must be upload
	  * 			[upload]					=> (boolean) (optional) TRUE|FALSE (true in this case, duh!)
	  * 			[rules]						=> string or array
	  * 														string => (rules for codeigniter's form_validation)
	  * 														array => array('insert' => (string), 'update' => (string))
	  * 			[object_type]				=> (string) object that holds the file reference
	  * 													(it's the name or alias (in case there is an alias defined) of the object
	  * 			[fieldName]					=> (optional) (string) field name defined in the _classData method
	  * 			[additional_parameters] 	=> (optional) additional parameters for the html form
	  *
	  */

	 
	 protected $objects;
	 
	/**
	 * sets the form options and fields for the model
	 *
	 * @return void
	 * @author  Patroklo
	 */

	 
	 function reset_data()
	 {
			$this->active_field_type 	= NULL;
			$this->loaded 				= FALSE;
			$this->fields				= array();
			$this->post_data			= NULL;
			$this->form					= NULL;
			$this->rules				= NULL;
			$this->sanitized_data		= NULL;
			$this->objects				= NULL;
			$this->error				= FALSE;
	 }

	 
	function set_options($options)
	{
		$this->reset_data();
		
		// we set the object names in the array
		// objects won't be setted until the method carga it's called or the $_post it's read
		// then the object will be made empty
		
		if(array_key_exists('objects', $options))
		{
			if(!is_array($options['objects']))
			{
				$options['objects'] = array($options['objects']);
			}
			
			foreach($options['objects'] as $object)
			{
				$this->create_object($object);
			}
		}
		
		$new_options = array();
		

		// change the fields sub array in order to be comprensible for
		// the parent
		
		if(array_key_exists('fields', $options))
		{
			foreach($options['fields'] as &$field_data)
			{
				if(array_key_exists('object_type', $field_data['options']))
				{
					if(!array_key_exists($field_data['options']['object_type'], $this->objects))
					{
						throw new Exception("The object ".$field_data['options']['object_type']." it's not defined in the object list.", 1);
					}
				}
				
				if(!array_key_exists('fieldName', $field_data['options']))
				{
					$field_data['options']['fieldName'] = $field_data['id'];
				}
				
				if(!array_key_exists('name', $field_data['options']))
				{
					$field_data['options']['name'] = $field_data['id'];
				}

				if(array_key_exists('upload', $field_data['options']) && $field_data['options']['upload'] == TRUE)
				{
					$this->set_upload($field_data);
				}
				
				$new_options[$field_data['id']] =  $field_data['options'];
			}
			
			$options['fields'] = $new_options;
			
		}
		
		parent::set_options($options);
		
	}

	/**
	 * creates an object in the $this->objects variable
	 *
	 * @return void
	 * @author  Patroklo
	 */

	private function create_object($object_data)
	{
			if(is_array($object_data))
			{
				$object_name = $object_data['name'];
				$object_alias= $object_data['alias'];
			}
			else
			{
				$object_name = $object_data;
				$object_alias= $object_data;
			}
			
			$this->objects[$object_alias] = array('name'	=> $object_name,
												  'data'	=> NULL,
												  'fields'	=> array());

			if(is_array($object_data) && array_key_exists('upload_parent', $object_data))
			{
				$this->objects[$object_alias]['upload_parent'] = $object_data['upload_parent'];
			}
			else 
			{
				$this->objects[$object_alias]['upload_parent'] = NULL;
			}
	}


	/**
	 * checks if the object passed by parameters it's a file upload object
	 *
	 * @return BOOLEAN
	 * @author  Patroklo
	 */
	function is_file_object($object) 
	{
		return !is_null($object['upload_parent']);
	}
	
	
	/**
	 * called from set_options, checks the upload fields data and makes a new 
	 * object to hold it in case it's a field object child of another object
	 * like => user -> user_photo (the field will be declared in user but here
	 * 							   we will change that and add it in user_photo object)
	 *
	 * @return void
	 * @author  
	 */
	
	protected function set_upload(&$field_data)
	{
		$object_name 	= $field_data['options']['object_type'];
		//$field_object 	= $this->objects[$object_name]['data']->_get_loaded_file($field_data['options']['fieldName']);
		
		// get the parent field FILE definition in order to get the data description and so on
		$objectClassData 	= $this->correcaminos->get_class_data($object_name)->get_files();
		

		// if it doesn't exist the file field or the field in the objectClassData, it may be a independent upload
		// without its parent object in the form. That implies that the object should be loaded with a valid parent or 
		// reference_id in the save state or will throw an exception
		
		if(is_array($objectClassData) && array_key_exists($field_data['options']['fieldName'], $objectClassData))
		{
			$fileClassData 		= $objectClassData[$field_data['options']['fieldName']];
			
			// if rules are not setted, will use the parent object rules
			
			if(!array_key_exists('rules',$field_data['options']))
			{
				$field_data['options']['rules'] = $fileClassData['rules'];
			}
		
			// creates a new object, the upload object with the data defined in the field
			
			$new_object_type = $object_name.'_'.$objectClassData[$field_data['options']['fieldName']]['className'];

			$this->create_object(array( 'name' 			=> $field_data['options']['fieldName'],
										'alias'			=> $new_object_type,
										'upload_parent'	=> $object_name
								));
			

			$this->_check_object_loaded_file($new_object_type);

			$field_data['options']['object_type'] = $new_object_type;	
		}
	}

	protected function _check_object_loaded_file($object_alias)
	{
		$file_object = $this->objects[$object_alias];
		$object_name = $file_object['upload_parent'];
		
		if($this->objects[$object_name]['data'] !== NULL)
		{
			$loaded_file = $this->objects[$object_name]['data']->_get_loaded_file($file_object['name']);
			
			if($loaded_file != FALSE)
			{
				$this->objects[$object_alias]['data'] = $loaded_file;
			}
		}
	}
	
	
	protected function _save_object($object)
	{
		if($this->is_file_object($object))
		{
			// check if there is a reference if already defined in the upload object,
			// if not, try to set one
			$config_data = $object['data']->get_config_data();
			
			if(is_null($config_data['reference_id']))
			{
				$object['data']->set_parent($this->objects[$object['upload_parent']]['data']);
			}
			
		}
		
		$object['data']->save();
	}
	
	protected function update($object_key)
	{
		$object = $this->objects[$object_key];

		$object_fields = $object['fields'];
	
		// checks all object fields in order to apply the changes into the object
				
		foreach ($object_fields as $field_name)
		{
			if(array_key_exists($field_name, $this->sanitized_data))
			{
				$field_options 		= $this->fields[$field_name]->get_options();
				
				if(!array_key_exists('upload', $field_options) || $field_options['upload'] == FALSE)
				{
					$field_value		= $this->sanitized_data[$field_name];
					$field_object_name 	= $field_options['fieldName'];
					
					$this->objects[$object_key]['data']->set_data($field_object_name, $field_value);
				}
			}
		}

		
		$this->_save_object($object);
	}
	
	
	protected function insert($object_key)
	{

		$object = $this->objects[$object_key];

		$object_fields = $object['fields'];
		
		// checks all object fields in order to apply the changes into the object
		
		foreach ($object_fields as $field_name)
		{
			if(array_key_exists($field_name, $this->sanitized_data))
			{
				$field_options 		= $this->fields[$field_name]->get_options();
				
				if(!array_key_exists('upload', $field_options) || $field_options['upload'] == FALSE)
				{
					$field_value		= $this->sanitized_data[$field_name];
					$field_object_name 	= $field_options['fieldName'];
					
					$this->objects[$object_key]['data']->set_data($field_object_name, $field_value);
				}
			}
		}
		
		$this->_save_object($object);
	}
	
	
	
	/**
	 * sets a field in the field list, and adds it into the
	 * array of fields of it's parent object
	 *
	 * @return null
	 * @author  Patroklo
	 */
	function set($field_id, $options = NULL) 
	{
		
		if(is_array($options) && array_key_exists('object_type', $options))
		{
			$this->objects[$options['object_type']]['fields'][] = $field_id;
		}
		
		return parent::set($field_id, $options);
	}
	

	
	/**
	 * In this version of the class the method checks every object to see if it's in insert
	 * or update state, then it calls the insert or update method with the object
	 * 
	 * @return void
	 * @author  Patroklo
	 */
	 
	function save($data = NULL)
	{
		
		parent::save($data);
		
		// check each object if it's initialized. If it's not, then
		// create a new empty object for it.

		foreach($this->objects as $key => &$object)
		{
			if($object['data'] === NULL)
			{
				// creates a new object with insert state
				$object['data'] = $this->correcaminos->new_object($object['name']);
			}
		}
		
		unset($object);

		
		// now calls update or insert depending of each object

		foreach($this->objects as $key => $object)
		{

			if($object['data']->_get_state() == 'INSERT')
			{
				$this->insert($key);
			}
			// it will enter here if the object it's in update or delete state
			else
			{
				$this->update($key);
			}
		}
		
	}


	function carga($object_alias, $filter = NULL)
	{
		if($filter === NULL and count($this->objects) > 1)
		{
			throw new Exception("You can't define a carga method without a filter.", 1);
		}
		elseif($filter === NULL and count($this->objects) == 1)
		{
			$filter 		= $object_alias;
			$object_alias 	= key($this->objects);
		}
		
		if(!array_key_exists($object_alias, $this->objects))
		{
			throw new Exception("The object ".$object_alias." it's not defined in the form.", 1);
		}
		
		if(is_object($filter))
		{
			$this->objects[$object_alias]['data'] = $filter;
		}
		else
		{
			if(is_numeric($filter))
			{
				$filter = array('id' => $filter);
			}
			
			$this->objects[$object_alias]['data'] = beep($this->objects[$object_alias]['name'])->where($filter)->get_one();
			
			
			if($this->objects[$object_alias]['data'] == FALSE)
			{
				throw new Exception("The object ".$object_alias." could not be loaded.", 1);
			}

		}
		
		// check if it's an object with a file that might be uploaded
		
		foreach($this->objects as $key => $object)
		{
			if($this->is_file_object($object))
			{
				$this->_check_object_loaded_file($key);
			}
		}

		// check if it's a loaded object in order to give the fields a value
		
		if($this->objects[$object_alias]['data']->_object_loaded())
		{
			$field_list = $this->objects[$object_alias]['fields'];

			foreach($field_list as $field_name)
			{
				$field = $this->fields[$field_name];
				
				$field_options = $field->get_options();
				
				if(array_key_exists('object_type', $field_options) && $field_options['object_type'] == $object_alias)
				{
					$object_name 		= $field_options['object_type'];
					$field_object_name 	= $field_options['fieldName'];
		
					if(!empty($this->objects[$object_name]['data']) && !$this->is_file_object($this->objects[$object_name]))
					{
						$object_value = $this->objects[$object_name]['data']->get_data($field_object_name);
						$field->set_value($object_value);
					}
				}

			}

		}
		
		
		// sets if the form is loaded
		
		if($this->objects[$object_alias]['data'] !== NULL and $this->is_loaded() == FALSE)
		{
			$this->set_loaded($this->objects[$object_alias]['data']->_object_loaded());
		}

		
	}
	
	function get_object($object_alias)
	{
		if(!array_key_exists($object_alias, $this->objects))
		{
			return FALSE;
		}
		
		$return_object = $this->objects[$object_alias]['data'];
		
		if(is_null($return_object))
		{
			$return_object = FALSE;
		}
		
		return $return_object;
		
	}

	function field_set_value($field_id, $value = NULL)
	{
		// empty for this class
	}

	public function move_file($field_name, $name, $destination)
	{}
	
	public function move_image($field_name, $name, $destination, $width=80, $height=80, $ratio=TRUE)
	{}

	 
/*	 function add_rule($field_id)
	 {

		$field = $this->fields[$field_id];

		$field_options = $field->get_options();

		if(array_key_exists('object_type', $field_options))
		{
			$object_name 		= $field_options['object_type'];
			
			if($this->objects[$object_name]['data'] !== NULL)
			{
				$this->set_loaded($this->objects[$object_name]['data']->_object_loaded());
			}
		}
		else
		{
			$this->set_loaded(FALSE);
		}


		parent::add_rule($field_id);
	 }
*/	 
	 
	 
	 
	
}