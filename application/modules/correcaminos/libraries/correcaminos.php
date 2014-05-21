<?php
use Correcaminos\Database\Driver,
    Correcaminos\Warning,
    Correcaminos\Database\QueryBuilder,
    Correcaminos\ORM\MemoryManager,
    Correcaminos\ORM\ORM_QueryBuilder,
    Correcaminos\ORM\ORM_Facade_QueryBuilder,
    Correcaminos\Objects\base,
    Correcaminos\Database\Forge,
	Correcaminos\QueryBox,
	Correcaminos\ORM\ORM_JSON_Parser;

	class Correcaminos{

		private $ORM_INIT = FALSE;


		public function __construct()
		{
					
			//path where the object directory it's stored.
			define("CC_OBJECT_PATH", APPPATH.'modules/correcaminos/libraries/Correcaminos/Objects/');
			define("CC_OBJECT_DEFINITION_PATH", CC_OBJECT_PATH.'data_objects/');
			define("CC_ROM_DEFINITION_PATH", APPPATH.'modules/correcaminos/libraries/Correcaminos/ORM/');

			require_once 'Correcaminos/Database/Driver.php';
			require_once 'Correcaminos/Database/Result.php';
			require_once 'Correcaminos/Database/QueryBuilder.php';
			require_once 'Correcaminos/ORM/MemoryManager.php';
			require_once 'Correcaminos/Warning.php';
			require_once 'Correcaminos/Objects/base.php';
			require_once 'Correcaminos/Parser.php';
			require_once 'Correcaminos/QueryBox.php';
			require_once 'Correcaminos/Cache/Lite.php';

			
			require_once CC_OBJECT_DEFINITION_PATH.'d_data.php';


			$db_conn['hostname'] 		= 'localhost';
			$db_conn['username'] 		= '';
			$db_conn['password'] 		= '';
			$db_conn['database']		= '';
			$db_conn['dbdriver'] 		= 'mysql';
            $db_conn['pconnect'] 		= FALSE;
            $db_conn['db_debug'] 		= TRUE;
			
			//caches *memcache, *apc *ramcache (this is only for the running script life) 
            $db_conn['memcache_on'] 	= FALSE;
			$db_conn['apccache_on']		= FALSE;
			$db_conn['inner_cache_on']	= TRUE;
			$db_conn['filecache_on'] 	= FALSE;
			$db_conn['memcache_expire'] = 300;
			
			$db_conn['regenerate_table_file_data'] = TRUE;
			
			
            $db_conn['cachedir'] 		= '';
            $db_conn['char_set'] 		= 'utf8';
            $db_conn['dbcollat'] 		= 'utf8_general_ci';
            $db_conn['benchmark'] 		= FALSE;
			$db_conn['post_benchmarking'] = FALSE;
            $db_conn['autoinit']		= TRUE;
            $db_conn['error_mode'] 		= '';
			


			MemoryManager::init_memory_manager();
			Driver::initialize($db_conn);   
		}

    
    
    /**
     * TODO:
     *  1 - Querybuilder
     *  2 - Sistema de cacheo
     *  3 - Sistema de retorno de datos
     *  4 - Sistema de autocarga de forma lazy o rapidly de las subquerys de las clases del ORM
     *  5 - Sistema de hacer querys de forma transaccional
     */
    

        private function _exception($msg)
        {
                Warning::exception($msg);
        }
        
        
        /**
         * ========================================================================================================================
         *      LLAMADAS CON LAS QUE SE INICIARÁ EL QUERY BUILDER // CALLS WITH WHICH WE WILL START THE QUERY BUILDER
         * ========================================================================================================================
         */
 
        public function load_object($class)
        {
            MemoryManager::load_object($class);
        }
        
		/*
		 *  Active Record Query
		 */
		
        public function beep_from($table)
        {
            $queryBuilder = new QueryBuilder();
            return $queryBuilder->From($table);
        }
		
		/*
		 *   ORM Query
		 */
		 		
		public function beep($class)
		{
			if($this->ORM_INIT != TRUE)
			{
				require_once 'Correcaminos/ORM/ORM_QueryBuilder.php';      
				require_once 'Correcaminos/ORM/ORM_Facade_QueryBuilder.php';
				require_once 'Correcaminos/ORM/ORM_Operations.php';
				$this->ORM_INIT = TRUE;
			}

			$queryBuilder = new ORM_Facade_QueryBuilder();
			return $queryBuilder->From($class);
		}
		
		
		public function raw_query($query, $parameters = FALSE, $cache = NULL)
		{
			$queryBuilder = new QueryBuilder();
            return $queryBuilder->raw_query($query, $parameters, $cache);
		}

		public function affected_rows()
		{
			return MemoryManager::get_affected_rows();
		}
		
		/**
         * ========================================================================================================
         * ========================================================================================================
         *             SINTACTIC SUGAR FUNCTIONS
         * ========================================================================================================
         * ========================================================================================================
         */ 
		
		public function insert($table, $data)
		{
			return beep_from($table)->values($data)->insert();
		}
		
		public function delete($table, $where)
		{
			return beep_from($table)->where($where)->delete();
		}
		
		//update query sintactic sugar
		public function update($table, $data, $where)
		{
			return beep_from($table)->values($data)->where($where)->update();
		}
		
		/**
         * ========================================================================================================
         * ========================================================================================================
         *              GLOBAL FILTER FUNCTIONS
         * ========================================================================================================
         * ========================================================================================================
         */ 
         
		 		
		public function set_global($query, $name = NULL)
		{
			QueryBox::set_filter($query, $name);
		}

		public function get_global($name)
		{
			return QueryBox::get_filter($name);
		}
		
		public function delete_global($name)
		{
			QueryBox::delete_filter($name);
		}
       
		/**
         * ========================================================================================================
         * ========================================================================================================
         *              BENCHMARKING FUNCTIONS
         * ========================================================================================================
         * ========================================================================================================
         */ 
 		
		public function last_query()
		{
			return MemoryManager::last_query();
		}
		
		public function benchmark()
		{
			return MemoryManager::benchmark();
		}

		public function last_benchmark()
		{
			return MemoryManager::last_benchmark();
		}

        /**
         * ========================================================================================================
         * ========================================================================================================
         *              TRANSACTION FUNCTIONS
         * ========================================================================================================
         * ========================================================================================================
         */ 

		 function begin_transaction()
		 {
		 	Driver::begin_transaction();
		 }
		 
		 function commit_transaction()
		 {
		 	Driver::commit_transacion();
		 }

		 function rollback_transaction()
		 {
		 	Driver::rollback_transaction();
		 }
		 
		 function transaction_status()
		 {
		 	return Driver::transaction_status();
		 }

        /**
         * ========================================================================================================
         * ========================================================================================================
         *              OBJECT FUNCTIONS
         * ========================================================================================================
         * ========================================================================================================
         */ 
         
         function get_class_data($className)
		 {
		 	return MemoryManager::get_class_data($className);
		 }
		 
		 
        /**
         * ========================================================================================================
         * ========================================================================================================
         *              CACHE FUNCTIONS
         * ========================================================================================================
         * ========================================================================================================
         */ 
         
         function cache_off()
		 {
		 	Driver::cache_off();
		 }
		 
		 
		 function cache_on()
		 {
		 	Driver::cache_on();
		 }
		 
		 function delete_cache()
		 {
		 	MemoryManager::delete_memcache();
		 }

        /**
         * ========================================================================================================
         * ========================================================================================================
         *              FORGE FUNCTIONS
         * ========================================================================================================
         * ========================================================================================================
         */ 

		function column_exists($table, $column)
		{
			require_once 'Correcaminos/Database/Forge.php';
			$forge = new Forge($table);
			
			return $forge->column_exists($column);
		}
		
		function list_fields($table)
		{
			require_once 'Correcaminos/Database/Forge.php';
			$forge = new Forge($table);
			
			return $forge->columns();	
		}


	}