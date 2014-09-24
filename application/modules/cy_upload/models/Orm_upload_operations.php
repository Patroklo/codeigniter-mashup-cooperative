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


	public function copy_image($object, $resize_data_arr)
	{
		$image_file = $object->get_data('file');
		
		$class_object = get_class($object);

		$object_data = MemoryManager::get_class_data($class_object);

		$object_table = $object_data->get_table();
		
		// make basic data, will be the same in every copy
		
		$insert_values = array(
								'classid' 		=> $object->get_data('classid'),
								'innerid' 		=> $object->get_data('innerid'),
								'upload_date' 	=> date("Y-m-d H:i:s"),
								'dir'			=> $object->get_data('dir'),
								'format'		=> $object->get_data('format'),
								'exif'			=> $object->get_data('exif'),
								);



		foreach ($resize_data_arr as $name => $resize_data)
		{

			// insert the new row
			$new_copy_id = $this->correcaminos->beep_from($object_table)->values($insert_values)->insert();

			$new_file_name = $insert_values['dir'].$new_copy_id.$insert_values['format'];
	
			// copy it and modify it's size if you have to
			if (is_array($resize_data))
			{
				$this->Upload_engine->copy_image($object->get_data('file'), $new_file_name, $resize_data);
			}
			else
			{
				copy($object->get_data('file'), $new_file_name);
			}
			
			// update data for the copy
			$update_values = array(
								   'file'		=> $new_file_name,
								   'file_size'	=> filesize($new_file_name),
								   'filename'	=> $new_copy_id,
									);
			
			$this->correcaminos->beep_from($object_table)->where('id', $new_copy_id)->values($update_values)->update();
			

			// add the copy data into the original file
			$copies = $object->get_data('copies');
			
			if ($copies != NULL or $copies != '')
			{
				$copies = json_decode($copies, TRUE);
			}
			else
			{
				$copies = array();
			}
			
			$copies[$name] = $new_copy_id;
			
			$object->set_data('copies', json_encode($copies));
		}

		$object->save(FALSE);
		
	}


}
