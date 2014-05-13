<?php namespace Correcaminos\Objects;

use Correcaminos\ORM\MemoryManager,
	Correcaminos\Warning,
	Correcaminos\ORM\ORM_Operations;

class base{
            
            private $_private_class_data = NULL;
            //private $_private_class;
            private $_joinList;
            
			protected $_data = NULL;
			
			private $_state = NULL;
            
            function __construct(&$data = NULL, $_new_object = FALSE)
            {
                $this->_data = $data;
				
				if($_new_object == TRUE)
				{
					$this->_check_insert();
				}
            }
            
            /**
             * returns the column with primary index
             */
            function get_main()
            {
                
            }
            
            /**
             * returns the columns if isset and false if the class must be lexed to get them.
             */
             
             /**
			  * example:
			 return array('tableName' => 'poblacion',
                         	'joins' => array(	'provincia'		=> array('loading_type'			=>	'eager|lazy',
	                         											 'type'					=>	'OneToOne',
	                         											 'target'				=> 'provincia_object',
	                         											 'columnName'			=> 'idprovincia',
	                         											 'referencedColumnName'	=> 'idprovincia'
							 											)
			  									'prueba'	=> array('loading_type'			=>	'eager|lazy',
	                         											 'type'					=>	'ManyToMany',
	                         											 'target'				=> 'provincia_object',
	                         											 'columnName'			=> 'idprovincia',
	                         											 'referencedColumnName'	=> 'idprovincia',
			  															 'intermediateTable'	=> 'tabla',
			  															 'intermediateColumnName' => 'derp',
			   															 'intermediatereferencedColumnName' => 'derp_derp'
							 										)
						 ),                                                                                                                                                                                                                                                                                                
                         'primary_column' => 'idpoblacion');
			  */
             
             
            static function _classData()
            {
                return FALSE;
            }
            
            function get_data($field)
            {
            	
				if(isset($this->_data->$field))
				{
					return $this->_data->$field;
				}
				else
				{
					$classData = $this->_classData();
					
					if(array_key_exists($field, $classData['joins']))
					{

						include_once CC_ROM_DEFINITION_PATH.'ORM_Relation_Manager.php';
						
						$column = $classData['joins'][$field];
						
						$column['origin_table']	= $classData['tableName'];

						$relation = new \Correcaminos\ORM\ORM_Relation_Manager($column);
						
						$relation->add_index($this->get_data($classData['joins'][$field]['columnName']));
	                      
                        $join_data = $relation->get_data();
    
                        if(array_key_exists($this->get_data($classData['joins'][$field]['columnName']), $join_data))
                        {
                            $this->set_data($field, $join_data[$this->get_data($classData['joins'][$field]['columnName'])], FALSE);
                        }
						
						return $this->_data->$field;
					}
					else
					{
						$this->_exception('The '.$field.' attribute doesn\'t exist in this object.');
					}
				}
            }
            
            function set_data($key, $data, $_change_state = TRUE)
            {
                $this->_data->$key = $data;
				
				if($_change_state == TRUE)
				{
					$this->_check_update();
				}
            }
			
			function delete()
			{
				$this->_check_delete();
			}
			
			function save()
			{
				ORM_Operations::save_object($this);

				$this->_state = NULL;
			}

            
			function _get_state()
			{
				return $this->_state;
			}
			
			private function _check_update()
			{
				if(is_null($this->_state))
				{
					$this->_state = 'UPDATE';

					MemoryManager::add_update_object($this);
				}
			}
			
			private function _check_insert()
			{
				$this->_state == 'INSERT';

				MemoryManager::add_insert_object($this);
			}
			
			private function _check_delete()
			{
				$this->_state == 'DELETE';

				MemoryManager::add_delete_object($this);
			}
			
                        
            function _get_real_data($field)
            {
                return $this->_data->$field;
            }
            

            private function _check_private_class()
            {
               if($this->_private_class_data == NULL)
               {
                   $classData = MemoryManager::get_class_data(get_class($this));
                   $this->_private_class_data = $classData;
               }
            }
	   		
	   		
	   		private static function _exception($msg)
			{
				{
					Warning::exception($msg);
				}
			}         
               // function _insert_join($colName, $joinList)
            // {
                // $this->_joinList[$colName] = $joinList;
            // }
// 			
// 			
			// function fill_object($data)
			// {
				// foreach($data as $key => $d)
				// {
					// $this->$key = $d;
				// }
			// }         
}