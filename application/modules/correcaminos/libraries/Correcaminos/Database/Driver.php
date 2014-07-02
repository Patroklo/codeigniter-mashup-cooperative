<?php
   
    namespace Correcaminos\Database;
    
    use Correcaminos\Warning,
        Correcaminos\ORM\MemoryManager,
		Correcaminos\Database\Result;

    class Driver{
        
        //connection parameters
            static $connection_string = NULL;
            static $hostname    = NULL;
            static $username    = NULL;
            static $password    = NULL;
            static $database    = NULL;
            static $dbdriver    = NULL;
            static $pconnect    = NULL;
            static $db_debug    = NULL;
            static $memcache_on 	= NULL;
			static $apccache_on 	= NULL;
			static $filecache_on	= NULL;
			static $inner_cache_on = NULL;
            static $cachedir    = NULL;     //esto será el cacheo de archivo; el cacheo de memoria dependerá de $_memcache_on
            static $char_set    = NULL;
            static $dbcollat    = NULL;
            static $port        = NULL;
			static $error_mode  = 'SILENT';  // WARNING || EXCEPTION
            static $benchmark   = FALSE;
            static $autoinit    = TRUE;
			static $memcache_expire = 300;
			static $post_benchmarking = FALSE;
			static $regenerate_table_file_data = FALSE;
			static $_errors_activated = FALSE;
			static $_transaction_status = TRUE;
			
			const NO_PDO = 3;

          
            
        //connection 
            static $connection = FALSE;

        	static $transaction = FALSE;

		
        // --------------------------------------------------------------------
    
        /**
         * Initialize Database Settings
         * Load the basic data of the class and tries to connect it into the database.
         * @access  static Called by the constructor
         * @param   mixed
         * @return  void
         */
        static function initialize( array $db)
        {
            
            foreach($db as $key => $data)
            {
                self::$$key = $data;
            }
            
            // If an existing connection resource is available
            // there is no need to connect and select the database
            if(self::$connection !== FALSE)
            {
                return TRUE;
            }

            if(self::$autoinit == TRUE)
            {
                // Connect to the database and set the connection ID
                self::check_connection();
            }

            return TRUE;
        }


        private static function check_connection()
        {
            if(self::$connection == FALSE)
            {
                self::$connection = self::db_connect();
				
				//if cache it's activated, then we'll launch the cache init from memorymanager
				if(self::is_cache_on() == TRUE)
				{
					MemoryManager::memcache_init();
				}
            }
        }


        static function query($sql, $parameters = FALSE, $cache = NULL, $tables = NULL)
        {
			if(is_array($sql))
			{
				extract($sql, EXTR_OVERWRITE);
			}

            if ($sql == '')
            {
                self::_exception('Query vacía.');
                return FALSE;
            }

            self::check_connection();

            //Check if the query is a write type. If it is, then we won't cache it
            $query_write = self::is_write_type($sql);	
            
            if(is_null($cache))
			{
				$cache = self::$memcache_expire;
			}
			

            


			if(self::is_cache_on() or self::$benchmark == TRUE)
			{
	            // Save the query into MemoryManager query list
	            $key_query = MemoryManager::add_query($sql, $parameters, $tables);
				
				if($query_write == FALSE)
				{
					//if we have the cache activated and the query is cached, then we get it
					if(self::is_cache_on() && MemoryManager::memcache_exists($key_query) != FALSE)
					{
						return new Result(NULL, $key_query);
					}
				}
				
				$time_start = microtime(true);
				
			}
			else
			{
				$key_query = 'noncached_query';
			}

            //=============================================
            // Run the Query
            //=============================================
            $result_data = self::_execute($sql, $parameters);


			if(self::is_cache_on() or self::$benchmark == TRUE)
			{
	            //we calculate the total time of the query.
	            $time_end =  microtime(true);
	            $total = $time_end - $time_start;
	            
	            if($query_write == TRUE && $tables != NULL)
	            {
	                //Marcamos como borrado el cache de las querys anteriores de las tablas que se han modificado
	                //con esta modificación a la base de datos
	                MemoryManager::cache_off($tables);
	            }
	
	            //let's add the benchmark into the query 
	            MemoryManager::end_query($total, $key_query);
	
	            //If benchmarking it's true for all the querys, then it will be shown each time.
	            if(self::$post_benchmarking == TRUE)
	            {
	                echo '<pre>';
	                  echo var_dump(MemoryManager::last_query(), MemoryManager::last_benchmark());
	                echo '</pre>';
	            }
			}
            

			if($query_write == TRUE)
			{
				MemoryManager::set_affected_rows($result_data->rowCount());
				return $result_data;
			}
			else
			{
				return new Result($result_data, $key_query, $cache);
			}

        }

		//relaunches the query to get the PDO object
		public static function relaunchQuery($key)
		{
		
			$query_data = MemoryManager::get_query_key($key);
			$result_data = self::_execute($query_data->get_query(), $query_data->get_params());
			MemoryManager::set_PDO_statement($key, $result_data);
			return $result_data;
		}


        /**
         * Comprueba si es una query de tipo escritura
         */
        private static function is_write_type($sql, $matcher = FALSE)
        {
            
            if($matcher == FALSE)
            {
                if ( ! preg_match('/^\s*"?(SET|INSERT|UPDATE|DELETE|REPLACE|CREATE|DROP|TRUNCATE|LOAD DATA|COPY|ALTER|GRANT|REVOKE|LOCK|UNLOCK)\s+/i', $sql))
                {
                    return FALSE;
                }
            }
            else
            {
                if ( ! preg_match('/^\s*"?('.$matcher.')\s+/i', $sql))
                {
                    return FALSE;
                }               
            }

            return TRUE;
        }


        private static function _exception($msg)
        {
            if(self::$db_debug == TRUE)
            {
                Warning::exception($msg);
            }
        }
		
		
		static function begin_transaction()
		{
			self::$connection->beginTransaction();
			self::$transaction = TRUE;
			self::$_transaction_status = TRUE;
		}
		
		static function commit_transacion()
		{
			if(self::$_transaction_status == TRUE)
			{
				self::$connection->commit();	
			}
			else
			{
				self::rollback_transaction();
			}
			
			self::$transaction = FALSE;			
		}

		static function rollback_transaction()
		{
			self::$connection->rollBack();
			self::$transaction = FALSE;
		}
		
		static function transaction_status()
		{
			return self::$_transaction_status;
		}

         /**
          *     Función que realiza la conexión con la base de datos.
          */
         private static function db_connect()
         {
            //  if config has given a direct connection string, we will use it
            //  if not, then we will make one manually with the given config
            if(self::$connection_string == NULL)
            {
                    // we make the connection string for the PDO
                    if(self::$dbdriver == NULL)
                    {
                        self::$_exception('No se ha definido un driver de BBDD válido.');
                        return FALSE;
                    }
                    else 
                    {
                        self::$connection_string = self::$dbdriver.':';
                    }
                    
                    if(self::$hostname == NULL)
                    {
                        self::$_exception('No se ha definido un hostname válido.');
                        return FALSE;
                    }
                    else 
                    {
                        self::$connection_string .= 'host='.self::$hostname.';';  
                    }
                    
                    if(self::$database == NULL)
                    {
                        self::$_exception('No se ha definido una base de datos válida.');
                        return FALSE;
                    }
                    else{
                        self::$connection_string .='dbname='.self::$database.';';
                    }
                    
                    if(self::$char_set !== NULL)
                    {
                        self::$connection_string .= 'charset='.self::$char_set.';';
                    }                   
            }

            $driverOptions = array();
            
            if(self::$pconnect == NULL or self::$pconnect == FALSE)
            {
                 $driverOptions[\PDO::ATTR_PERSISTENT] = FALSE;     
            }
            else{
                 $driverOptions[\PDO::ATTR_PERSISTENT] = TRUE;              
            }
            
			
			if(self::$error_mode == 'WARNING')
			{
				//throws the error code and warning if there is an error
            	$driverOptions[\PDO::ATTR_ERRMODE] = \PDO::ERRMODE_WARNING;
				self::$_errors_activated = TRUE;
			}
			elseif(self::$error_mode == 'EXCEPTION')
			{
				//throws the error code and an exception, 
            	$driverOptions[\PDO::ATTR_ERRMODE] = \PDO::ERRMODE_EXCEPTION;
				self::$_errors_activated = TRUE;
			}
			else
			{
				//throws only a error code; the driver will then catch it
				//in _execute and trigger an exception
	            $driverOptions[\PDO::ATTR_ERRMODE] = \PDO::ERRMODE_SILENT;
				self::$_errors_activated = FALSE;
			}

            //$driverOptions[\PDO::ATTR_EMULATE_PREPARES] = FALSE;
			$driverOptions[\PDO::MYSQL_ATTR_USE_BUFFERED_QUERY] = TRUE;
            
            //we add this beacuse in some old php servers the charset alone in the connection string wont work
            $driverOptions[\PDO::MYSQL_ATTR_INIT_COMMAND] = "SET NAMES 'utf8'";
            
            try
            {
                return new \PDO(self::$connection_string , self::$username, self::$password, $driverOptions);                   
            }
            catch(\PDOException $e)
            {
                self::_exception($e->getMessage());
            }


         }
        
        
        /**
         * Ejecutamos la query
         */
        private static function _execute($sql, $parameters = FALSE)
        {
            if ( ! self::$connection)
            {
                self::_exception('No hay ninguna conexión creada.');
            }
            
            
			if(empty($parameters))
			{
				$parameters = FALSE;
			}

            if(!is_array($parameters) and $parameters !== FALSE)
            {
                self::_exception('Los parámetros tienen que estar en un formato de array.');
            }

            $sth = self::$connection->prepare($sql, array(\PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true));
           
            //  if the sql has parameters, then we send it in the execute.
            //  nevertheless the system lets you sending plain sql, it's very
            //  important to use parameters in order to avoid sql injection
        	if($parameters !== FALSE)
            {
				$sth->execute($parameters);
				self::check_errors($sth);
            }
            else
            {
                $sth->execute();
				self::check_errors($sth);
            }

            return $sth;

        }


		private static function check_errors(&$sth)
		{
			if($sth->errorCode() !== '00000')
			{
				if(self::$transaction == TRUE)
				{
					//self::rollback_transaction();
					self::$_transaction_status = FALSE;
				}
			
				if(self::$_errors_activated == TRUE)
				{
					echo MemoryManager::last_query().'<br />';
					$error = $sth->errorInfo();
					self::_exception($error[2]);
				}
			}
		}
        
        public static function last_insert_id()
		{
			return self::$connection->lastInsertId(); 
		}



        /**
         * ========================================================================================================
         * ========================================================================================================
         *              CACHE FUNCTIONS
         * ========================================================================================================
         * ========================================================================================================
         */ 

		public static function cache_off($type = NULL)
		{
			if(is_null($type))
			{
				self::$memcache_on = FALSE;
				self::$apccache_on = FALSE;
				self::$inner_cache_on = FALSE;
			}
			else
			{
				self::$$type = FALSE;
			}
		}

		public static function cache_on($type = NULL)
		{
			if(is_null($type))
			{
				self::$memcache_on = TRUE;
				self::$apccache_on = TRUE;
				self::$inner_cache_on = TRUE;
			}
			else
			{
				self::$$type = TRUE;
			}
		}
		
		public static function is_cache_on($cache_type = 'all')
		{
			if($cache_type == 'memcache')
			{
				$cache_type = array('memcache_on', 'apccache_on');
			}
			elseif($cache_type == 'all')
			{
				$cache_type = array('memcache_on', 'apccache_on', 'inner_cache_on');
			}
			
			if(!is_array($cache_type))
			{
				$cache_type = array($cache_type);
			}
			
			$retorno = FALSE;
			
			foreach($cache_type as $c)
			{
				if(self::$$c == TRUE)
				{
					$retorno = TRUE;
				}
			}
			
			return $retorno;
		}

    }