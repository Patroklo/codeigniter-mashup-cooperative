<?php

use Correcaminos\ORM\MemoryManager,
	Correcaminos\Warning,
	Correcaminos\ORM\ORM_Operations;

class file_base extends Correcaminos\Objects\base{

		protected $name_parent_object;
		protected $parent_object;
		
		// the reference of the parent object used to insert or update files
		protected $reference_id;

		
		// inherited from the parent_object
		protected $className;
		protected $rules;
		
		// the main and generic upload directory
		// should be changed		
		protected $directory	= 'files';
		
        function __construct($data = NULL, $_new_object = FALSE)
        {
        	
			
			
        	// loads the basic data needed to launch the upload library
			$parent_data = $this->__get_parent_data();
			
			if(!array_key_exists(get_class($this), $parent_data['files']))
			{
				throw new Exception("The class ".get_class($this)." it's not initialized in ".$this->parent_object, 1);
			}
			else 
			{
				$file_parent_data = $parent_data['files'][get_class($this)];
			}
			
			
			
			if(array_key_exists('className', $file_parent_data))
			{
				$this->className = $file_parent_data['className'];
			}
			else
			{
				$this->className = $this->name_parent_object;
			}
			
			// TODO repasar, la carga no la hace bien, tiene que buscar en el array de files
			
			if(array_key_exists('rules', $file_parent_data))
			{
				$this->rules = array($file_parent_data['rules']);
			}
			
			if(array_key_exists('directory', $file_parent_data))
			{
				$this->directory = $file_parent_data['directory'];
			}
			
			$this->__check_basic_values();

        	parent::__construct($data, $_new_object);

        }
		
		/**
		 * Checks the basic parameters of the object to know if it's well initialized
		 *
		 * @return BOOL
		 * @author  Patroklo
		 */
		function __check_basic_values() 
		{
			$null_values = array('name_parent_object', 'className', 'rules', 'directory');
			
			foreach($null_values as $key)
			{
				if(is_null($this->$key))
				{
					throw new Exception("The file object has been initialized with incorrect data.", 1);
				}
			}
			
		}
		
		function __get_parent_data()
		{
			$name_parent_object = $this->name_parent_object;
			
			$CI =& get_instance();
			$CI->correcaminos->load_object($name_parent_object);
			
        	return $name_parent_object::_classData();
		}	
		
		// called from base object in get_file or get_files
		// called manually when needed
		// the most important thing that this method does is that
		// now the insert new file can get an id for inserting a new
		// file
		
		function set_parent(&$object)
		{
			$this->parent_object = $object;

			$parent_data = $this->__get_parent_data();

			if($object->get_data($parent_data['primary_column']))
			{
				$this->reference_id = $object->get_data($parent_data['primary_column']);
			}
			
		}
		
		
		function get_config_data()
		{
			return array('directory'	=> $this->directory,
						 'rules'		=> $this->rules,
						 'className'	=> $this->className,
						 'reference_id'	=> $this->reference_id);
		}
		
		
		function rules()
		{
			return $this->rules;
		}
		
		function get_field_name()
		{
			$field_rules = $this->rules();
	
			if(array_key_exists('field', $field_rules[0]))
			{
				$field = $field_rules[0]['field'];
			}
			else
			{
				$field = 'userfile';
			}
	
			return $field;
		}
		
		
		/**
		 * =================================
		 * Overwrited methods
		 * =================================
		 */

	 	function save()
		{
			$CI =& get_instance();

			if($this->_get_state() == NULL && $this->_data != NULL && $this->get_data('id'))
			{
				// check $_FILES for a possible update in case there is an uploaded file with this object
				if(array_key_exists($this->get_field_name(), $_FILES))
				{
					$this->_check_update();
				}
			}
			
			if(is_null($this->reference_id) and $this->_get_state() == 'UPDATE')
			{
				$this->reference_id = $this->get_data('innerid');
			}
			
			if(is_null($this->reference_id))
			{
				throw new Exception("The reference id of the object type ".get_class($this)." it's not defined.", 1);
			}
			
			$CI->load->model('cy_upload/ORM_Upload_Operations');
			$CI->Orm_upload_operations->save_object($this);
			
			$this->_state = NULL;
		}
		 
		 
		 
		 
		 
		 
		 
		 
      
}