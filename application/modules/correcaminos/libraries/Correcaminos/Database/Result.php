<?php namespace Correcaminos\Database;

use Correcaminos\ORM\MemoryManager,
	Correcaminos\Database\Driver,
	Correcaminos\Warning;

	class Result{ // implements \Iterator{
		
		
		private $_PdoStatement  		= NULL;
		private $_num_rows	  			= NULL;
		private $_position	  			= 0;
		private $_actual_value  		= NULL;
		private $_reset_pointer 		= FALSE;
		private $_results 				= array();
		private $_result_unique 		= array();
		private $_group					= array();
		private $_group_result  		= array();
		private $_key_query 			= NULL;
		private $_loaded				= FALSE;
		private $_cached				= FALSE;
		private $_query_data			= NULL;
		
		
		private $_results_key 				= NULL;
		private $_result_unique_key			= NULL;
		private $_group_key					= NULL;
		private $_group_result_key			= NULL;
		
		private $_memcache_expire			= NULL;
		
		private $_types = array('_results', '_group', '_group_result', '_result_unique');
		
		private $_generic_object = 'd_data';
		
		function __construct(\PDOStatement $statement = NULL, $key_query = NULL, $memcache_expire = NULL)
		{

			$this->_key_query = $key_query;
			//$this->_query_data = $query_data;
			$this->_memcache_expire = ((is_null($memcache_expire))?0:$memcache_expire);
			
			//loading the cache data if it exists
			if((is_null($statement) and Driver::is_cache_on() == FALSE) or (is_null($statement) and is_null($key_query)))
			{
				$this->_exception('There\'s no query in the statement parameter.');
			}
			elseif($this->_key_query !== NULL && is_null($statement) && Driver::is_cache_on() == TRUE)
			{
				$this->load_cache_data();
			}

			if($this->_loaded == FALSE)
			{
				$this->_PdoStatement = $statement;
				$this->_num_rows = $this->_PdoStatement->rowCount();	
				$this->_loaded = TRUE;
			}
		}


		private function generate_results($type, $fetch_style, $fetch_arguments, $class_load = FALSE)
		{
			$result_data = array();
			
			if($class_load == FALSE)
			{
				if(is_null($fetch_arguments))
				{
					$result_data = $this->_PdoStatement->fetchAll($fetch_style);
				}
				else
				{
					$result_data = $this->_PdoStatement->fetchAll($fetch_style, $fetch_arguments);
				}
			}
			else 
			{
				// tiny objects load much faster than big ones with PDO
				// so we will use d_data and fetch to load them into the 
				// big class

				if(is_null($fetch_arguments))
				{

					$this->_PdoStatement->setFetchMode($fetch_style, $this->_generic_object);
					while (($row = $this->_PdoStatement->fetch($fetch_style)) !== false) {
						$result_data[] = new $type($row);
					}
				}
				else 
				{
					//if there is a $fetch_arguments defined then it's a group query, so well have 
					//to use this tweak to get the grouped values right
					$rows = $this->_PdoStatement->fetchAll($fetch_style, $this->_generic_object);
					foreach($rows as $key => $row)
					{
						foreach($row as $r)
						{
							$result_data[$key][] = new $type($r);
						}
					}
				}
			}	
			
			return $result_data;
		}

		/**
		 * This method checks if already exists the data we want to retrieve.
		 * If it doesn't it tries to make a query
		 */
		private function _check_loaded_data($type, $query_type)
		{

			if(array_key_exists($type, $this->$query_type))
			{
				return TRUE;
			}
			
			if(is_null($this->_PdoStatement))
			{
				return Driver::NO_PDO;
			}
			
			return FALSE;
			
		}

		
		//more info about the fetch -> http://php.net/manual/en/pdostatement.fetch.php
		
		/**
		 * Returns an array with the data of the query. It can be iterated with minimum memory consumption
		 * as if it was a fetch iteration
		 */		 
		function result($type = 'object' , $fetch_arguments = NULL, $ctor_args = NULL)
		{
			//it will enter into the query only if results its empty or we are making another type or result (from object to array ...)
			
			$checked_loaded_data = $this->_check_loaded_data($type, '_results');

			if($checked_loaded_data == FALSE)
			{
				return $this->_result($type, $fetch_arguments, $ctor_args);
			}
			elseif($checked_loaded_data == TRUE)
			{
				return $this->_results[$type];
			}
			else
			{
				return $checked_loaded_data;
			}
		}
		
		private function _result($type, $fetch_arguments, $ctor_args)
		{

				$class_load  = FALSE;

				if($type == 'object')
				{
					$fetch_style = \PDO::FETCH_OBJ;
				}
				elseif($type == 'array')
				{
					$fetch_style = \PDO::FETCH_ASSOC;
					
				}
				else
				{
					$fetch_style = \PDO::FETCH_CLASS;		
					
					if(!class_exists($type))
					{
						MemoryManager::load_object($type);
						
						if(Driver::is_cache_on() == TRUE){  MemoryManager::set_object_memcache($this->_key_query, $type);  }
					}
					
					$class_load = TRUE;
					$fetch_arguments = NULL;
				}
							
				$this->check_pointer();
				$this->_reset_pointer = TRUE;

				$this->_results[$type] = $this->generate_results($type, $fetch_style, $fetch_arguments, $class_load);
				
				$this->make_cache();
				
				return $this->_results[$type];
		}

		//TODO añadir cache y demás mierdas
		function result_column($column_index = 0)
		{
					
			$checked_loaded_data = $this->_check_loaded_data('no', '_result_unique');
			
			if($checked_loaded_data == FALSE)
			{
				return $this->_result_unique($column_index);
			}
			elseif($checked_loaded_data == TRUE)
			{
				return $this->_result_unique['no'];
			}
			else
			{
				return $checked_loaded_data;
			}
		}
		
		private function _result_unique($column_index)
		{
			$this->check_pointer();
			$this->_reset_pointer = TRUE;
			$this->_result_unique['no'] =  $this->_PdoStatement->fetchAll(\PDO::FETCH_COLUMN, $column_index);
			return $this->_result_unique['no'];
		}
		


		public function group($type = 'object', $fetch_arguments = NULL, $ctor_args = NULL)
		{
			
			$checked_loaded_data = $this->_check_loaded_data($type, '_group');
			
			if($checked_loaded_data == FALSE)
			{
				return $this->_group($type, $fetch_arguments, $ctor_args);
			}
			elseif($checked_loaded_data == TRUE)
			{
				return $this->_group[$type];
			}
			else
			{
				return $checked_loaded_data;
			}
		}
		
		private function _group($type, $fetch_arguments, $ctor_args)
		{			
			$class_load = FALSE;
			
			if($type == 'object')
			{
				$fetch_style = \PDO::FETCH_GROUP|\PDO::FETCH_OBJ;
			}
			elseif($type == 'array')
			{
				$fetch_style = \PDO::FETCH_GROUP|\PDO::FETCH_ASSOC;
			}
			else
			{
				$fetch_style = \PDO::FETCH_CLASS|\PDO::FETCH_GROUP;
				if(!class_exists($type))
				{
					MemoryManager::load_object($type);
					if(Driver::is_cache_on() == TRUE){  MemoryManager::set_object_memcache($this->_key_query, $type);  }
				}
				$fetch_arguments = $type;
				$class_load = TRUE;
			}
		
			$this->check_pointer();
			$this->_reset_pointer = TRUE;
	

			$this->_group[$type] = $this->generate_results($type, $fetch_style, $fetch_arguments, $class_load);
			
			$this->make_cache();

			return $this->_group[$type];
		}
		

		
		public function group_result($type = 'object', $fetch_arguments = NULL, $ctor_args = NULL)
		{
			return $this->group_unique($type, $fetch_arguments, $ctor_args);
		}
		
		
		public function group_unique($type = 'object', $fetch_arguments = NULL, $ctor_args = NULL)
		{
			$checked_loaded_data = $this->_check_loaded_data($type, '_group_result');
			
			if($checked_loaded_data == FALSE)
			{
				return $this->_group_unique($type, $fetch_arguments, $ctor_args);
			}
			elseif($checked_loaded_data == TRUE)
			{
				return $this->_group_result[$type];
			}
			else
			{
				return $checked_loaded_data;
			}
		}
		
		private function _group_unique($type, $fetch_arguments, $ctor_args)
		{
			$class_load = FALSE;
			
			if($type == 'object')
			{
				$fetch_style = \PDO::FETCH_GROUP|\PDO::FETCH_OBJ;
			}
			elseif($type == 'array')
			{
				$fetch_style = \PDO::FETCH_GROUP|\PDO::FETCH_ASSOC;
			}
			else
			{
				$fetch_style = \PDO::FETCH_GROUP|\PDO::FETCH_CLASS;
				if(!class_exists($type))
				{
					MemoryManager::load_object($type);
					if(Driver::is_cache_on() == TRUE){  MemoryManager::set_object_memcache($this->_key_query, $type);  }
				}
				$class_load = TRUE;
				$fetch_arguments = $type;
			}
			
			$this->check_pointer();
			$this->_reset_pointer = TRUE;
			
			$retorno = $this->generate_results($type, $fetch_style, $fetch_arguments, $class_load);

			$this->_group_result[$type] = array_map(
													function($data) {
														if(is_array($data))
														{
															return reset($data);
														}
														else
														{
															return $data;
														}
													}
													, $retorno);
	
			$this->make_cache();

			return $this->_group_result[$type];
		}

	  
		
		/**
		 * Returns the num rows of the query
		 */
		function num_rows()
		{
			return $this->_num_rows;
		}
		
		
		/**
		 * Returns TRUE if the query has 0 resuts and FALSE if it has more
		 */
		 function is_empty()
		 {
		 	return ($this->_num_rows == 0 or is_null($this->_num_rows));
		 }

		
		/**
		 * goes directly into the row selected in $row_num
		 * due to a pdo bug, in mysql it's impossible to go directly
		 * into a row. So we'll have to iterate
		 */
		public function row($num_row = 0, $type = 'object')
		{
			
			if(!is_numeric($num_row))
			{
				$type = $num_row;
				$num_row = 0;
			}
			
			
			//if we have already loaded the data with cached query
			//PDO won't exist, so we need to do this instead
			$checked_loaded_data = $this->_check_loaded_data($type, '_results');
			
			
			if($checked_loaded_data == TRUE)
			{
				return $this->_results[$type][$num_row];
			}
			elseif($checked_loaded_data == FALSE)
			{
				return $this->_row($num_row, $type);
			}
			else
			{
				return $checked_loaded_data;
			}
		}
		
		
		private function _row($num_row, $type)
		{	
			if($type == 'object')
			{
				$data_type = \PDO::FETCH_OBJ;
			}
			elseif($type == 'array')
			{
				$data_type = \PDO::FETCH_ASSOC;
			}
			else
			{
				$data_type = \PDO::FETCH_CLASS|\PDO::FETCH_PROPS_LATE;
				if(!class_exists($type))
				{
					MemoryManager::load_object($type);
					if(Driver::is_cache_on() == TRUE){  MemoryManager::set_object_memcache($this->_key_query, $type);  }
				}
				$this->_PdoStatement->setFetchMode($data_type, $this->_generic_object);
				$fetch_arguments = $type;
			}
						
			$this->check_pointer();
			$this->_reset_pointer = TRUE;
			
			if($num_row == 0)
			{
				$return_data = $this->_PdoStatement->fetch($data_type, \PDO::FETCH_ORI_FIRST);
			}
			else
			{
				for($i = 0; $i < $num_row; $i++)
				{
					$this->_PdoStatement->fetch($data_type, \PDO::FETCH_ORI_NEXT);
				}
				$return_data = $this->_PdoStatement->fetch($data_type, \PDO::FETCH_ORI_NEXT);
			}
			
			if(isset($fetch_arguments))
			{
				$return_data = new $fetch_arguments($return_data);
			}


			return $return_data;

		}
		
		
		private function check_pointer()
		{
			if($this->_reset_pointer == TRUE)
			{
				$this->_PdoStatement->execute();
				$this->_reset_pointer = FALSE;
			}
		}


		private static function _exception($msg)
		{
			if(Driver::$db_debug == TRUE)
			{
				Warning::exception($msg);
			}
		}
		
		
		//********************************************************************
		//********************************************************************
		//			CACHE FUNCTIONS
		//********************************************************************
		//********************************************************************
		
		
		
		
		function generate_cache_data()
		{
			 return     array('_results' 				=> $this->_results,
							  '_group'					=> $this->_group,
							  '_group_result'			=> $this->_group_result,
							  '_result_unique'			=> $this->_result_unique,
							  '_num_rows'				=> $this->_num_rows,
							  //'_query_data'				=> $this->_query_data,
							  '_PdoStatement'			=> $this->_PdoStatement);
		}

		function load_cache_data()
		{
			MemoryManager::get_object_memcache($this->_key_query);
			$cache_data = MemoryManager::get_memcache($this->_key_query);

			if($cache_data != FALSE)
			{
				$this->_results 			= $cache_data['_results'];
				$this->_group	 			= $cache_data['_group'];
				$this->_group_result 		= $cache_data['_group_result'];
				$this->_result_unique		= $cache_data['_result_unique'];
				$this->_num_rows 			= $cache_data['_num_rows'];
				$this->_PdoStatement		= $cache_data['_PdoStatement'];
				//$this->_query_data			= $cache_data['_query_data'];
				$this->_cached 				= TRUE;
				$this->_loaded 				= TRUE;
				//we let the reset_pointer to true in order to reset the PDO connection next time
				//we need a query
				$this->_reset_pointer		= TRUE;
				
				//if pdoStatement is null, then we will try to launch the query again and CACHE it
				if($this->_PdoStatement === FALSE)
				{
					$this->_PdoStatement = Driver::relaunchQuery($this->_key_query);
				}
				
			}
		}
		
		private function make_cache()
		{
			if($this->_key_query !== NULL && Driver::is_cache_on('all') == TRUE)
			{
				MemoryManager::set_memcache($this->_key_query, $this->generate_cache_data(), $this->_memcache_expire);
			}
		}
	
		//resetea la query en caso de que necesitemos reusar una función como result pero con otro tipo de datos.
		public function reset_query($type = NULL)
		{

			if(is_null($type))
			{
				$this->_results 			= NULL;
				$this->_group	 			= NULL;
				$this->_group_result 		= NULL;
				$this->_result_unique		= NULL;
				$this->_num_rows			= 0;
			}
			elseif(in_array($type, $this->_types))
			{
				$this->$type = NULL;
			}
		
		}
		
}