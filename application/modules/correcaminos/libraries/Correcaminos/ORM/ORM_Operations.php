<?php namespace Correcaminos\ORM;

	use	Correcaminos\ORM\ORM_QueryBuilder,
		Correcaminos\ORM\MemoryManager,
		Correcaminos\Database\QueryBuilder;

	class ORM_Operations {
		
		
		static function save_object($object)
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
			
			$data_values = array();
			
			$column_list = $object_data->get_columns();
			
			foreach($column_list as $column)
			{
				if($column['Field'] != $object_key_pri)
				{
					$data_values[$column['Field']] = $object->get_data($column['Field']);
				}
			}

			if(!empty($data_values))
			{
				$queryString = new QueryBuilder();
				$queryString = $queryString->From($object_table)->values($data_values);
				
				if($state == 'UPDATE')
				{
					$queryString->where($object_key_pri, $object->get_data($object_key_pri))->update();
				
					MemoryManager::update_object($object);
				}
				elseif($state == 'INSERT')
				{
					$id = $queryString->insert();
					$object->set_data($object_key_pri,$id);
					
					MemoryManager::insert_object($object);
				}
				elseif($state == 'DELETE')
				{
					$queryString->delete();
					unset($object);
					
					MemoryManager::delete_object($object);
				}
			}
		}
		
		static function save_table($table, $type = NULL)
		{
			$object_list = MemoryManager::get_objects_by_table($table, $type);
			
			foreach($object_list as $object_data)
			{
				$object_data['object']->save();
			}
		}
		
	}