<?php

	// a upload model extension that let's to automatize the uploading changin some 
	// basic behaviour of the original model
	// this model let's you use it without the need of extending it manually
use Correcaminos\ORM\MemoryManager;

class Orm_upload_operations extends CI_Model {
	
	public $tableName 		= FALSE;
	public $directory		= FALSE;
	public $className 		= FALSE;
	public $rules	  		= FALSE;
	public $reference_id	= FALSE;
	public $fieldName		= FALSE;
	
	function __construct($config = array())
	{
		$this->load->library('upload');

		$this->load->library('cy_upload/Cy_uploader', $config);
		
		$this->load->model('cy_upload/Upload_engine');

		$this->load->_include_class('cy_upload/libraries/file_base');
		$this->load->_include_class('cy_upload/libraries/extended_base_object');

	}
	
	/*		protected $name_parent_object;
		protected $parent_object;
		
		// the reference of the parent object used to insert or update files
		protected $reference_id;

		
		// inherited from the parent_object
		protected $className;
		protected $rules;
		
		// the main and generic upload directory
		// should be changed		
		protected $directory	= 'files';*/
		
		
	public function engine_initialization_data()
	{
		return array('directory' 	=> $this->directory,
					 'className'	=> $this->className,
					 'fieldName'	=> $this->fieldName
					 );
	}
	
	public function save_object($object)
	{
			$state = $object->_get_state();

			if(is_null($state))
			{
				return FALSE;
			}
			
			$class_object = get_class($object);
			
			$object_data = MemoryManager::get_class_data($class_object);
			
			$object_key_pri = $object_data->get_primary_column();

			$object_table = $object_data->get_table();
			
			$config_data = $object->get_config_data();
			
			$this->directory 	= $config_data['directory'];
			$this->rules	 	= $config_data['rules'];
			$this->className 	= $config_data['className'];
			$this->reference_id = $config_data['reference_id'];
			$this->fieldName	= $object->get_field_name();

			if($state == 'UPDATE')
			{
				
				$this->update($object);
				MemoryManager::update_object($object);
			}
			elseif($state == 'INSERT')
			{
				
				$this->insert($object);
				MemoryManager::insert_object($object);
			}
			elseif($state == 'DELETE')
			{
				MemoryManager::delete_object($object);
				$this->delete($object);
			}
			

	}

	private function insert($object)
	{
		
		if(!is_numeric($this->reference_id))
		{
			throw new Exception("There is no reference id setted for this file object upload.", 1);
		}
		
		$id = $this->reference_id;
		
		$this->Upload_engine->initialize($this->engine_initialization_data());
		$insert_data = $this->Upload_engine->insert($id);
		if($insert_data !== FALSE)
		{
			foreach($insert_data as $key => $value)
			{
				$object->set_data($key, $value);
			}
		}
		return $insert_data;
	}

	private function update($object)
	{
		$id = $object->get_data('id');

		$insert_data = $this->insert($object);

		if($insert_data !== FALSE)
		{
			$this->Upload_engine->initialize($this->engine_initialization_data());
			$this->Upload_engine->delete($id);
		}
	}
	
	private function delete($object)
	{
		$id = $object->get_data('id');
		
		$this->Upload_engine->initialize($this->engine_initialization_data());
		
		$this->Upload_engine->delete($id);
		
		unset($object);
	}

	private function rules()
	{
		return $this->rules;
	}


}
