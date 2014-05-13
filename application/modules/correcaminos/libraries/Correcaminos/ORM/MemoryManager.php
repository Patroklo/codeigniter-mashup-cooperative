<?php namespace Correcaminos\ORM;


    use Correcaminos\Warning,
		Correcaminos\Cache,
		Correcaminos\Database\Driver,
		Correcaminos\ORM\ORM_JSON_Parser,
		Correcaminos\QueryBox;
    

	class CacheBench{
		
		var $query_cache;
		var $exists_text = 'memcache_exists_';
		
		function __construct()
		{
			$this->query_cache = new Cache\Cache_Lite(array('memoryCaching' => TRUE, 'onlyMemoryCaching' => TRUE));
		}
		
		public function add_query($data, $key)
		{
			$data = QueryBox::deep_clone($data);
			
			$this->_add_query($data, $key);
		}
		
		public function add_PDO($data, $key)
		{
			$this->_add_query($data, 'PDO_'.$key);
		}
		
		private function _add_query($data, $key)
		{
			$this->query_cache->save($data, $key);
		}
		
		public function get_query($key)
		{
			return $this->query_cache->get($key);
		}
		
		public function get_PDO($key)
		{
			return $this->get_query('PDO_'.$key);
		}
		
		public function add_exists_query($key)
		{
			$this->add_query(TRUE, $this->exists_text.$key);
		}
		
		public function get_exists_query($key)
		{
			return (boolean) $this->get_query($this->exists_text.$key);
		}
		
		public function delete($key = NULL)
		{
			if(is_null($key))
			{
				return $this->query_cache->clean();
			}
			else
			{
				$this->query_cache->remove($this->exists_text.$key);
				return $this->query_cache->remove($key);
			}
		}
		
		public function delete_PDO($key = NULL)
		{
			if(!is_null($key))
			{
				$this->delete('PDO_'.$key);
			}
		}

	}
	
	abstract class GenericCacheDriver {
		
		var $active = FALSE;
		var $exists_text = 'memcache_exists_';
		var $memcache_expire;
		
		
		abstract function __construct();
		
		public function add_query($data, $key, $memcache_expire, $tables = NULL)
		{
			
			$this->memcache_expire = $memcache_expire;
			
				if(is_null($tables))
				{
					$this->_add_value($key, $data);
				}
				else
				{
					if(!is_array($tables))
					{
						$tables = array($tables);
					}
			
					foreach($tables as $table)
					{
						$table_data = $this->_get_value($table);
					
						if(!is_array($table_data))
						{
							$table_data = array();
						}
						
						$table_data[$key] = $data;
	
						$this->_add_value($table, $table_data);
					}
				}
			
		}
		
		
		public function get_query($key, $table = NULL)
		{
			if(is_null($table))
			{
				return $this->_get_value($key);
			}
			else
			{
				if(is_array($table))
				{
					$table = reset($table);
				}
				
				$table_data = $this->get_queryTable($table);

				if($table_data !== FALSE && array_key_exists($key, $table_data))
				{
					return $table_data[$key];
				}
			}
		}


		public function add_object_query($data, $key)
		{
			$object_list = $this->_get_value('objects');
			$object_list[$key] = $data;
			$this->_add_value('objects', $object_list);
		}
		
		public function get_object_query($key)
		{
			$object_list = $this->_get_value('objects');
			
			if($object_list === FALSE)
			{
				return FALSE;
			}
			
			if(array_key_exists($key, $object_list))
			{
				return $object_list[$key];
			}

			return FALSE;
		}


		public function delete($key = NULL)
		{
			if(is_null($key))
			{
				return $this->_delete_all_cache();
			}
			else
			{
				
				$this->_delete_cache($this->exists_text.$key);
				return $this->_delete_cache($key);
			}
		}
				
		private function get_queryTable($table)
		{
			return $this->_get_value($table);
		}
		
		public function is_active()
		{
			return $this->active;
		}
		
		public function add_exists_query($key, $memcache_expire)
		{
			$this->add_query(TRUE, $this->exists_text.$key, $memcache_expire);
		}
		
		public function get_exists_query($key)
		{
			return (boolean) $this->get_query($this->exists_text.$key);
		}
		
		abstract protected function _add_value($key, $data);
		
		abstract protected function _get_value($key);
		
		abstract protected function _delete_all_cache();
		
		abstract protected function _delete_cache($key);
		
	}



	class MemcacheBench extends GenericCacheDriver {
		
		static $query_memcache;
		
		function __construct()
		{
			$this->active = TRUE;
			self::$query_memcache  = new \Memcache();
			self::$query_memcache->connect('localhost', 11211) or $this->active = FALSE;
		}


		protected function _add_value($key, $data)
		{
			return self::$query_memcache->set($key, $data, \MEMCACHE_COMPRESSED, $this->memcache_expire);
		}
		
		protected function _get_value($key)
		{
			return self::$query_memcache->get($key);
		}
		
		protected function _delete_all_cache()
		{
			return self::$query_memcache->flush();
		}
		
		protected function _delete_cache($key)
		{
			return self::$query_memcache->delete($key);			
		}
		
	}

	class ApcCacheBench extends GenericCacheDriver {
		
		
		function __construct()
		{
			$this->active = TRUE;
			
			if(extension_loaded('apc'))
			{
				$this->active = TRUE;
			}
			else
			{
				$this->active = FALSE;
			}
		}
		
		// APC FUNCTIONS
		
		protected function _add_value($key, $data)
		{
			return apc_store($key, $data, $this->memcache_expire);
		}
		
		protected function _get_value($key)
		{
			return apc_fetch($key);
		}
		
		protected function _delete_all_cache()
		{
			return apc_clear_cache();
		}
		
		protected function _delete_cache($key)
		{
			return apc_delete($key);
		}
		
		
	}

	// cache works via order of priority
	// 1 if inner cache is on, it will return it's values first, it's faster AND can store PDO objects
	// 2 if apc is on, it will return it's values, because it's faster than memcache
	// 3 if memcache is on, it will return it's values
	
	class SharedCacheBench {
		
		var $cacheDriver = NULL;
		var $innerCacheDriver = NULL;
		var $active = FALSE;
		
		function __construct()
		{
			
			if(Driver::is_cache_on() == FALSE)
			{
				return FALSE;
			}
			
			//inner cache it's ALLWAYS active, because we need it
			//to store the PDO DATA for the other memcache
			$this->innerCacheDriver = new CacheBench();
			
			
			if(Driver::is_cache_on('apccache_on') == TRUE)
			{
				//apcCache has predominancy over memcache
				//memcache it's sooooo slow...
				$this->cacheDriver = new ApcCacheBench();
				$this->active = $this->cacheDriver->is_active();
			}
			elseif(Driver::is_cache_on('memcache_on') == TRUE) 
			{
				$this->cacheDriver = new MemcacheBench();
				$this->active = $this->cacheDriver->is_active();
			}
			else
			{
				$this->active = FALSE;
			}
		}
		
		private function inner_on()
		{
			return Driver::is_cache_on('inner_cache_on');
		}

		
		public function add_query($data, $key, $memcache_expire, $tables = NULL)
		{
			if($this->inner_on())
			{
				//delete the PDO object because it can't be cached normally
				if(array_key_exists('_PdoStatement', $data))
				{
					$this->add_PDO($key, $data['_PdoStatement']);
					unset($data['_PdoStatement']);
				}
				
				$this->innerCacheDriver->add_query($data, $key);
			}
			
			if($this->active)
			{
				//delete the PDO object because it can't be cached normally
				if(array_key_exists('_PdoStatement', $data))
				{
					$this->add_PDO($key, $data['_PdoStatement']);
					unset($data['_PdoStatement']);
				}
				
				$this->cacheDriver->add_query($data, $key, $memcache_expire, $tables);
			}
			
			$this->add_memcache_exists($key, $memcache_expire);
			
		}
		
		public function add_PDO($key, $PDO_Statement)
		{
			$this->innerCacheDriver->add_PDO($PDO_Statement, $key);
		}
		
		
		public function get_query($key, $table = NULL)
		{
			if($this->inner_on())
			{
				$data = $this->innerCacheDriver->get_query($key);
				if($data !== FALSE)
				{
					$data['_PdoStatement'] = $this->innerCacheDriver->get_PDO($key);
					return $data;
				}
			}
			
			if($this->active)
			{
				$return_data = $this->cacheDriver->get_query($key, $table);

				if($return_data !== FALSE)
				{					
					//if inner is on and we have reached this part, we load the memcached data into memory
					if($this->inner_on())
					{
						$this->innerCacheDriver->add_query($return_data, $key);
					}
				
					$return_data['_PdoStatement'] = $this->innerCacheDriver->get_PDO($key);

					return $return_data;
				}
			}

			return FALSE;
		}
		
		public function delete($key = NULL)
		{
			
			if($this->inner_on())
			{
				$this->innerCacheDriver->delete($key);
			}
			
			if($this->active)
			{
				$this->innerCacheDriver->delete_PDO($key);

				return $this->cacheDriver->delete($key);
			}
			
			return FALSE;
		}
		
		public function memcache_exists($key)
		{
			if($this->inner_on())
			{
				$data = $this->innerCacheDriver->get_exists_query($key);
				if($data !== FALSE)
				{
					return $data;
				}
			}
			
			if($this->active)
			{
				return $this->cacheDriver->get_exists_query($key);
			}

			return FALSE;
		}
		
		public function add_memcache_exists($key, $memcache_expire)
		{
			if($this->inner_on())
			{
				$this->innerCacheDriver->add_exists_query($key, $memcache_expire);
			}
			
			if($this->active)
			{
				$this->cacheDriver->add_exists_query($key, $memcache_expire);
			}
		}
		
		
		//inner cache doesn't have add or get object because it doesn't need it
		
		public function add_object_query($data, $key)
		{
			if($this->active)
			{
				return $this->cacheDriver->add_object_query($data, $key);
			}
			
			return FALSE;
		}
		
		public function get_object_query($key)
		{
			if($this->active)
			{
				return $this->cacheDriver->get_object_query($key);
			}
			
			return FALSE;
		}

	}

	
    /**
	 * Object that contains all the data of the loaded objects in correcaminos
	 */
	class ObjectBench{
		private $class;
		private $table;
		private $columns;
		private $joins;
		private $primary_column;
		
		function __construct($className)
		{
			$objectData['class']   		  = $className;
			
			$classData = $className::_classData();
			
			$this->table   		  = $classData['tableName'];
			
			if(array_key_exists('joins', $classData))
			{
				$this->joins 		  = $classData['joins'];
			}
			
			if(array_key_exists('primary_column', $classData))
			{
				$this->primary_column = $classData['primary_column'];
			}
			else
			{
				$this->primary_column = 'id';
			}
			
			$this->load_columns();
		}
		
		private function load_columns()
		{
			$this->columns = MemoryManager::get_table_data($this->table);
		}
		
		function get_table()
		{
			return $this->table;
		}
		
		function get_columns()
		{
			return $this->columns;
		}

		function get_primary_column()
		{
			return $this->primary_column;
		}
		
		function get_joins()
		{
			return $this->joins;
		}
		
		function get_className()
		{
			return $this->class;
		}
	}
	
	
    /**
	 * Query object that will hold every query and its related data
	 */
    class QueryBench{
        
        private $query_count = 0;
        private $query;
        private $params;
        private $benchmark;
		private $data = FALSE;
        private $can_cache = TRUE;
		private $tables;
        
        function __construct($query = FALSE, $params = FALSE, $tables = NULL)
        {
            if($query !== FALSE)
            {
                $this->add_query($query);
            }
            if($params !== FALSE and $params !== NULL)
            {
                $this->add_params($params);
            }
			
			$this->tables = $tables;
			
            $this->query_count += 1;
        }
        
        public function add_query($query)
        {
            $this->query = $query;
        }
        
        public function add_params($params)
        {
            if(!is_array($params))
            {
                Warning::exception('Los parámetros deben tener formato de array.');
            }
            else
            {
                $this->params = $params;
            }
        }
		
		public function add_data($data)
		{
			$this->data = &$data;
		}
		
		public function get_data()
		{
			return $this->data;
		}
        
        public function add_benchmark($time)
        {
            $this->benchmark = $time;
        }
        
        public function get_query()
        {
            return $this->query;
        }
        
        public function get_benchmark()
        {
            return $this->benchmark;
        }
        
        public function get_params()
        {
            return $this->params;
        }
        
        // TODO retornará el cacheo de la query y 
        // esta función es llamada como si se hubiera hecho
        // una query igual y en lugar de ir a la bbdd se retorna los datos de esta
        // así que también sumará +1 al query_count
        public function get_query_cache()
        {
            $this->query_count += 1;
        }
        
        
        public function can_cache()
        {
            return $this->can_cache;
        }
        
        public function delete_cache()
        {
            $this->can_cache = FALSE;
        }
		
		public function get_tables()
		{
			return $this->tables;
		}
        
    }

	//
	class MemoryStack {
		
			var $general_stack 	= array();
			var $table_stack	= array();
			
			var $update_stack	= array();
			var $insert_stack	= array();
			var $delete_stack	= array();

			var $update_table_stack	= array();
			var $insert_table_stack	= array();
			var $delete_table_stack	= array();
			
			var $warehouse		= NULL;
			
			var $last_key 		= NULL;
		
			function __construct(&$warehouse)
			{
				$this->warehouse = $warehouse;
			}

            /**
			 * añade una query nueva a la lista de querys generadas de MemoryManager
			 */
            public function add_query($sql, $params = FALSE, $tables = NULL)
            {
            	if($params != FALSE)
				{
            		$keys = array_keys($params);	
					$sql = str_replace($keys, $params, $sql);				
				}
                $key = md5($sql);
				
				$this->last_key = $key;
                
				$this->general_stack[$key][] = new QueryBench($sql, $params, $tables);
				
				if(!is_null($tables))
				{
	                foreach($tables as $table)
					{
						
						$this->table_stack[$table][$key] = $key; 
					}	
				}


                return $key;
            }
			
			
			/**
			 * Obtiene una de las querys previamente generadas
			 */
			public function get_query_key($key)
			{
				if(array_key_exists($key,  $this->general_stack))
				{
					return  end($this->general_stack[$key]);
				}			
				return FALSE;
			}     

            
            public function end_query($benchmark, $key)
            {
            	if(array_key_exists($key,  $this->general_stack))
				{
					$query = $this->get_query_key($key);
					
					$query->add_benchmark($benchmark);
				}
            }
			
			//mira todas las querys. Si alguna tiene alguna tabla
			//de la lista que se ha pasado por parámetros, se descacheará
			//ya se al modificarse y no saber (en este estado del ORM) qué
			//se ha modificado, mejor descachear todo
			public function cache_off($tables)
			{
				if(!is_array($tables))
				{
					$tables = array($tables);
				}
				
				foreach($tables as $table)
				{
					if(array_key_exists($table, $this->table_stack))
					{
						foreach($this->table_stack[$table] as $key)
						{
							$this->warehouse->get_memcache()->delete($key);
						}
						unset($this->table_stack[$table]);
					}
				}
			}
			
			public function get_last_query()
			{
				return end($this->general_stack[$this->last_key]);
			}	
	
			private function get_object_data($object)
			{
				$return['object_id'] = spl_object_hash($object);
				$classData = $object::_classData();
				$return['table'] = $classData['tableName'];
				return $return;
			}
		
			public function add_update_object($object)
			{
				
				$object_info = $this->get_object_data($object);
				$object_id = $object_info['object_id'];
				$table = $object_info['table'];
				
				$data_object = array('id' => $object_id, 'table' => $table, 'object' => $object);
				
				$this->update_stack[$object_id] = $data_object;
				$this->update_table_stack[$table][$object_id] = $data_object;
			}
			
			public function make_update_object($object)
			{
				$this->delete_update_object($object);
			}
						
			public function delete_update_object($object)
			{
				$object_info = $this->get_object_data($object);
				$object_id = $object_info['object_id'];
				$table = $object_info['table'];

				if(array_key_exists($object_id, $this->update_stack))
				{
					unset($this->update_stack[$object_id]);
				}
				
				if(array_key_exists($table, $this->update_table_stack))
				{
					if(array_key_exists($object_id, $this->update_table_stack[$table]))
					{
						unset($this->update_table_stack[$table][$object_id]);
					}
				}
			}
			
			public function add_insert_object($object)
			{
				$object_info = $this->get_object_data($object);
				$object_id = $object_info['object_id'];
				$table = $object_info['table'];
				
				$data_object = array('id' => $object_id, 'table' => $table, 'object' => $object);
				
				$this->insert_stack[$object_id] = $data_object;
				$this->insert_table_stack[$table][$object_id] = $data_object;
			}
			
			public function make_insert_object($object)
			{
				$this->delete_insert_object($object);
			}

			public function delete_insert_object($object)
			{
				$object_info = $this->get_object_data($object);
				$object_id = $object_info['object_id'];
				$table = $object_info['table'];
				
				if(array_key_exists($object_id, $this->insert_stack))
				{
					unset($this->insert_stack[$object_id]);
				}
				
				if(array_key_exists($table, $this->insert_table_stack))
				{
					if(array_key_exists($object_id, $this->insert_table_stack[$table]))
					{
						unset($this->insert_table_stack[$table][$object_id]);
					}
				}
			}
			
			public function add_delete_object($object)
			{
				$object_info = $this->get_object_data($object);
				$object_id = $object_info['object_id'];
				$table = $object_info['table'];
				
				
				$this->delete_insert_object($object);
				$this->delete_update_object($object);
				
				$data_object = array('id' => $object_id, 'table' => $table, 'object' => $object);
				
				$this->delete_stack[$object_id] = $data_object;
				$this->delete_table_stack[$table][$object_id] = $data_object;
			}
			
			public function make_delete_object($object)
			{
				$this->delete_delete_object($object);
			}	
					
			public function delete_delete_object($object_id, $table)
			{
				$object_info = $this->get_object_data($object);
				$object_id = $object_info['object_id'];
				$table = $object_info['table'];

				if(array_key_exists($object_id, $this->delete_stack))
				{
					unset($this->delete_stack[$object_id]);
				}
				
				if(array_key_exists($table, $this->delete_table_stack))
				{
					if(array_key_exists($object_id, $this->delete_table_stack[$table]))
					{
						unset($this->delete_table_stack[$table][$object_id]);
					}
				}
			}


			public function get_table_stacks($table, $type = NULL)
			{
				
				$return = array();
				
				if(is_null($type) or $type == 'INSERT')
				{
					if(array_key_exists($table, $this->insert_table_stack))
					{
						foreach($this->insert_table_stack[$table] as $object)
						{
							$return[] = $object;
						}
					}
				}

				if(is_null($type) or $type == 'UPDATE')
				{				
					if(array_key_exists($table, $this->update_table_stack))
					{
						foreach($this->update_table_stack[$table] as $object)
						{
							$return[] = $object;
						}
					}
				}
				
				if(is_null($type) or $type == 'DELETE')
				{					
					if(array_key_exists($table, $this->delete_table_stack))
					{
						foreach($this->delete_table_stack[$table] as $object)
						{
							$return[] = $object;
						}
					}	
				}			
				
				return $return;
			}
	}




	class WareHouse {
		
		var $memcache		= NULL;
		var $query_stack	= NULL;
		
		function __construct()
		{
			$this->query_stack = new MemoryStack($this);	
		}

	    public function memcache_init()
		{
			$this->memcache = new SharedCacheBench();
		}
		
		public function get_memcache()
		{
			
			if(is_null($this->memcache))
			{
				$this->_exception('Memcache it\'s not initialized.');
			}
			
			return $this->memcache;
		}
		
		public function get_query_stack()
		{
			return $this->query_stack;
		}
		
		private function _exception($message)
		{
			 Warning::exception($message);
		}
		
	}




        /**
         * Funcionamiento de la memoria: 
         * 1 - Arrays de querys -> guarda la query realizada, los parámetros que tiene y el 
         *      tiempo total de benchmarking de la query.
         *      También tiene un enlace directo al array de datos que retorna esa query.
         * 2 - Arrays de datos -> son los objetos retornados en la BBDD. Están enlazados entre ellos si se
         *      trata de enlaces 1 a 1, 1 a n o m a n.
         */


    class MemoryManager{
    
            /**
             * Variables 
             */
            static $benchmark 		= 0;
            static $query_count 	= 0;
			
			static $warehouse 		= NULL;
			
			static $object_list		= array();
			
			static $affected_rows	= 0;

			
 			public static function init_memory_manager()
			{
				self::$warehouse = new WareHouse();
			}
           
			/**
             * ===========================================================================
             * 		MEMCACHE FUNCTIONS
             * ===========================================================================
             */ 
	           	public static function memcache_init()
				{
					self::$warehouse->memcache_init();
				}
				
				//add the object class to memcache in case it's on in order to load it before 
				//the memcache starts loading data, otherwise, memcache will fail to add it 
				//into a valid object class
				public static function set_object_memcache($key, $type)
				{
					self::$warehouse->get_memcache()->add_object_query($type, $key);
				}
				
				//loads all the objects in memcache to prevent errors at loading time
				public static function get_object_memcache($key)
				{
					$className = self::$warehouse->get_memcache()->get_object_query($key);

					if($className !== FALSE)
					{
						self::load_object($className);
					}

				}
				
	            public static function set_memcache($key, $data, $memcache_expire, $tables = NULL)
				{
					self::$warehouse->get_memcache()->add_query($data, $key, $memcache_expire, $tables);
				}
				
				public static function get_memcache($key, array $tables = NULL)
				{
					return self::$warehouse->get_memcache()->get_query($key, $tables);
				}
				
				public static function set_PDO_statement($key, $PDO_Statement)
				{
					self::$warehouse->get_memcache()->add_PDO($key, $PDO_Statement);
				}
				
				public static function delete_memcache()
				{
					self::$warehouse->get_memcache()->delete();
				}
				
				public static function memcache_exists($key)
				{
					return self::$warehouse->get_memcache()->memcache_exists($key);
				}
             
			/**
             * ===========================================================================
             * 		QUERY FUNCTIONS
             * ===========================================================================
             */
            
            /**
			 * añade una query nueva a la lista de querys generadas de MemoryManager
			 */
            public static function add_query($sql, $params = FALSE, $tables = NULL)
            {
				return self::$warehouse->get_query_stack()->add_query($sql, $params, $tables);
            }
			
			
			/**
			 * Obtiene una de las querys previamente generadas
			 */
			public static function get_query_key($key)
			{
				return self::$warehouse->get_query_stack()->get_query_key($key);
			}   

            
            public static function end_query($benchmark, $key)
            {
                self::$warehouse->get_query_stack()->end_query($benchmark, $key);
                self::$benchmark += $benchmark;
                self::$query_count += 1;
            }		


			//mira todas las querys. Si alguna tiene alguna tabla
			//de la lista que se ha pasado por parámetros, se descacheará
			//ya se al modificarse y no saber (en este estado del ORM) qué
			//se ha modificado, mejor descachear todo
			public static function cache_off($tables)
			{
				self::$warehouse->get_query_stack()->cache_off($tables);
			}


            /**
             * ===========================================================================
             * 		MISCELANEA FUNCTIONS
             * ===========================================================================
             */
            
            public static function last_query()
            {
                $ret = self::$warehouse->get_query_stack()->get_last_query();
                
                
                $params = $ret->get_params();
                
                $query = $ret->get_query();
                
                foreach($params as $key => $p)
                {
                    $query = str_replace($key, $p, $query);
                }
                
                return $query;
            }
            
            public static function last_benchmark()
            {
                 $ret = self::$warehouse->get_query_stack()->get_last_query();
                 return $ret->get_benchmark();      
            }
            
            private function _exception($msg)
            {
                    Warning::exception($msg);
            }

            public static function benchmark()
            {
                return self::$benchmark;
            }
			
			public static function set_affected_rows($num_rows)
			{
				if(is_int($num_rows))
				{
					self::$affected_rows = $num_rows;
				}
			}

			public static function get_affected_rows()
			{
				return self::$affected_rows;
			}

            /**
             * ===========================================================================
             * 		ORM FUNCTIONS
             * ===========================================================================
             */


			private static function class_exist($className)
			{
				if(array_key_exists($className, self::$object_list))
				{
					return TRUE;
				}
				else
				{
					return FALSE;
				}
			}
			
			public static function load_object($className)
			{
				if(!file_exists(CC_OBJECT_PATH.$className.'.php'))
				{
					self::_exception("The class ".$className." can't be accessed from the object directory.");
				}

				require_once CC_OBJECT_PATH.$className.'.php';
			}


             public static function get_table_data($tableName)
			 {
			 	
				require_once 'ORM_JSON_Parser.php';
				
				return ORM_JSON_Parser::get_columns($tableName);
				
			 }
             
			public static function get_class_data($className)
			{
				if(!self::class_exist($className))
				{
					self::load_object($className);
					self::$object_list[$className] = new ObjectBench($className);
				}

				return self::$object_list[$className];
			}
			
			public static function add_update_object($this)
			{
				self::$warehouse->get_query_stack()->add_update_object($this);
			}
			
			public static function add_insert_object($this)
			{
				self::$warehouse->get_query_stack()->add_insert_object($this);
			}
			
			
			public static function add_delete_object($this)
			{
				self::$warehouse->get_query_stack()->add_delete_object($this);
			}
			
			public static function update_object($this)
			{
				self::$warehouse->get_query_stack()->make_update_object($this);
			}
			
			public static function insert_object($this)
			{
				self::$warehouse->get_query_stack()->make_insert_object($this);
			}
			
			public static function delete_object($this)
			{
				self::$warehouse->get_query_stack()->make_delete_object($this);
			}
			
			public static function get_objects_by_table($table, $type)
			{
				return self::$warehouse->get_query_stack()->get_table_stacks($table, $type);
			}
			
			
    }