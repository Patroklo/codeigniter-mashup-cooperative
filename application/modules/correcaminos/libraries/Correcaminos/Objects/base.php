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
			
			protected $__object_loaded;
            
            function __construct($data = NULL, $_new_object = FALSE)
            {
            	

                $this->_data = $data;
				
				$this->__object_loaded = TRUE;
				
				if($_new_object == TRUE)
				{
					$this->_check_insert();

					// we insert a blank empty object of the table
					// in order to be able to use get_data and set_data
					if(is_null($this->_data))
					{
						$this->__object_loaded = FALSE;
						
						$classData = $this->_classData();
						
						$columns = MemoryManager::get_table_data($classData['tableName']);

						$this->_data = new \stdClass();
						
						foreach($columns as $column)
						{
							$fieldName = $column['Field'];
							$this->_data->$fieldName = NULL;
						}
					}
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
			 return array('tableName' => (string) 'poblacion',
                         	'joins' => array(	'provincia'		=> array('loading_type'			=>	(enum) 'eager|lazy',
	                         											 'type'					=>	(enum) 'OneToOne|OneToMany|ManyToMany|OneToText',
	                         											 'target'				=>  (string) (object name) 'provincia_object',
	                         											 'columnName'			=>  (string) (column name in object) 'idprovincia',
	                         											 'referencedColumnName'	=>  (string) (column name in target object)'idprovincia'
			  															 'order_column'			=>  (optional) (string) (column name in target object)
							 											)
			  									'prueba'	=> array('loading_type'							=>	(enum) 'eager|lazy',
	                         											 'type'								=>	'ManyToMany', (enum) 'OneToOne|OneToMany|ManyToMany|OneToText',
	                         											 'target'							=> (string) (object name) 'provincia_object',
	                         											 'columnName'						=> (string) (column name in object) 'idprovincia',
	                         											 'referencedColumnName'				=> (string) (column name in target object)'idprovincia'
			  															 'intermediateTable'				=> (optional) (string) (table used in many to many) 'tabla',
			  															 'intermediateColumnName' 			=> 'derp',
			   															 'intermediatereferencedColumnName' => 'derp_derp',
			  															 'order_column'						=> (optional) (string) (column name in target object)order_column
							 										)
						 					),
			  				 'files' => array( 'foto' 	=> array('directory' => (string) (object_name) 'foto_usuario',
			   												 	 'className' => (optional) (string) //if not defined, will use the object's name),
			  													 'rules'	 => (optional) (string, rules from CI)
			  													)                                                                                                                                                                                                                                                                              
                         'primary_column' => 'idpoblacion');
			  */
             
             
            function _object_loaded()
			{
				return $this->__object_loaded;
			}
             
            static function _classData()
            {
                return FALSE;
            }
            
            function get_data($field)
            {
				if(property_exists($this->_data, $field))
				{
					return $this->_data->$field;
				}
				else
				{
					$classData = $this->_classData();
					
					if(array_key_exists('joins', $classData) && array_key_exists($field, $classData['joins']))
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
						else
						{
							$this->set_data($field, array());
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
			
			protected function _check_update()
			{
				if(is_null($this->_state))
				{
					$this->_state = 'UPDATE';

					MemoryManager::add_update_object($this);
				}
			}
			
			protected function _check_insert()
			{
				$this->_state = 'INSERT';

				MemoryManager::add_insert_object($this);
			}
			
			protected function _check_delete()
			{
				$this->_state = 'DELETE';

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
      
}