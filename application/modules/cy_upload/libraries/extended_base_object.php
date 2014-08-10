<?php

use Correcaminos\ORM\MemoryManager,
	Correcaminos\Warning,
	Correcaminos\ORM\ORM_Operations;

class extended_base_object extends Correcaminos\Objects\base{
	
	protected $field_object = array();
	
	
	public function _get_loaded_file($file_fieldName)
	{
		if(!$this->_object_loaded())
		{
			return FALSE;
		}
		
		if(!array_key_exists($file_fieldName, $this->field_object))
		{
			return FALSE;
		}
		
		return $this->field_object[$file_fieldName];
	}
	
	public function get_file($file_fieldName, $id = NULL)
	{

		if(!$this->_object_loaded())
		{
			return FALSE;
		}
		
		if(!array_key_exists($file_fieldName, $this->field_object) || ($id !== NULL and ($this->field_object[$file_fieldName]->get_data('id') == $id)))
		{
			$this->__get_file($file_fieldName, $id);
		}
		
		return $this->_get_loaded_file($file_fieldName);
		
	}
	
	private function __get_file($file_fieldName, $id = NULL)
	{
		
		$CI = &get_instance();
		
		// loading the files for this object
		$class_object = get_class($this);
			
		$object_data = MemoryManager::get_class_data($class_object);

		$files = $object_data->get_files();

		if(!array_key_exists($file_fieldName, $files))
		{
			throw new Exception("El objeto tipo field ".$file_fieldName." no está declarado en el objeto de tipo ".get_class($this), 1);
		}

		if(array_key_exists('className', $files[$file_fieldName]))
		{
			$className = $files[$file_fieldName]['className'];
		}
		else
		{
			$className = get_class($this);
		}
		
		$uploadClass = $CI->Upload_engine->get_classData($className);
		
		$query = beep($file_fieldName)->where('innerid', $this->get_data($object_data->get_primary_column()))->where('classid', $uploadClass['id']);
		
		if($id != NULL)
		{
			$query = $query->where('id', $id);
		}
		
		$object_field = $query->get_one();

		if($object_field != FALSE)
		{
			$object_field->set_parent($this);
			
			$this->field_object[$file_fieldName] = $object_field;
		}
	}
	
	
	public function get_files($file_fieldName)
	{
		$CI = &get_instance();

		if(!$this->_object_loaded())
		{
			return FALSE;
		}
		
		// loading the files for this object
		$class_object = get_class($this);
			
		$object_data = MemoryManager::get_class_data($class_object);

		$files = $object_data->get_files();

		if(array_key_exists('className', $files[$file_fieldName]))
		{
			$className = $files[$file_fieldName]['className'];
		}
		else
		{
			$className = get_class($this);
		}
		
		$uploadClass = $CI->Upload_engine->get_classData($className);
		
		$query = beep($file_fieldName)->where('innerid', $this->get_data($object_data->get_primary_column()))->where('classid', $uploadClass['id']);

		return $query->get();

	}
	
	
}