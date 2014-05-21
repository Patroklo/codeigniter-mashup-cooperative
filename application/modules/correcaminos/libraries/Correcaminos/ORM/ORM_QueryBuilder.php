<?php namespace Correcaminos\ORM;

    use Correcaminos\Warning,
        Correcaminos\ORM\ORM_Driver,
        Correcaminos\Database\QueryBuilder,
        Correcaminos\ORM\MemoryManager,
        Correcaminos\ORM\Result,
		Correcaminos\Parser,
		Correcaminos\QueryBox,
		Correcaminos\ORM\ORM_Relation_Manager,
		Correcaminos\ORM\ORM_Operations;

    class ORM_QueryBuilder{
        
        private $_main_table = NULL;
        
        private $_queryBuilder = NULL;
        
        private $_columns;
		
		private $_joins;
        
        private $_primary_column;
        
        private $_table;
        
        private $_class;
        
        private $_select_list = array();
        
        private $_result_data = NULL;
		
		private $_eager_loading = TRUE;
		
		private $_query_join_name = NULL;

		//where data stored for join purpouses for eager loading data
		private $_filters = array();

        public function From($className)
        {
            $classData = MemoryManager::get_class_data($className);

            //cargamos los datos del objeto, su tabla, su clase, sus columnas
            $this->_table   = $classData->get_table();
            $this->_class   = $className;
            $this->_columns = $classData->get_columns();
            $this->_primary_column = $classData->get_primary_column();
			$this->_joins 	= $classData->get_joins();
            
            $this->_queryBuilder = new QueryBuilder();
            $this->_queryBuilder = $this->_queryBuilder->from($this->_table);
            
            return $this;
        }

        /**
         * ========================================================================================================
         * ========================================================================================================
         *              QUERYBUILDER FUNCTIONS
         * ========================================================================================================
         * ========================================================================================================
         */ 

        function where($column, $value = FALSE, $parse = TRUE)
        {
        	$this->_queryBuilder = $this->_queryBuilder->where($column, $value, $parse);
            return $this;  
			
			// TODO borrar?
            // if($value == FALSE)
			// {
				// if(is_array($column))
				// {
					// foreach($column as $key => $c)
					// {
						// if(is_numeric($key))
						// {
							// $this->_exception("The column name can't be a number.");
						// }
						// //$this->_queryBuilder = $this->_queryBuilder->where($key, $c);
						// $this->where($key, $c);
					// }
				// }
				// elseif(is_string($column))
				// {
				 	// $list = Parser::_separate_columns_where($column, 'AND');
// 
                    // foreach($list as &$item)
                    // {
                        // if(strpos($item['column'], '(') !== FALSE)
                        // {
                            // $this->_queryBuilder = $this->open_bracket($item['type']);
                            // $item['column'] = str_replace('(', '', $item['column']);
                        // }
//                         
                        // if(strpos($item['value'], ')') !== FALSE)
                        // {
                            // $close_bracket = TRUE;
                            // $item['value'] = str_replace(')', '', $item['value']);
                        // }
                        // else
                        // {
                            // $close_bracket = FALSE;
                        // }
// 
                        // //$this->_queryBuilder = $this->_queryBuilder->where($item['column'], $item['value'], $item['type']);
                        // $this->where($item['column'], $item['value'], $item['type']);
//     
                        // if($close_bracket == TRUE)
                        // {
                            // $this->_queryBuilder = $this->close_bracket();
                        // }
//                         
                    // }
				// }
			// }
			// else
			// {
				// //comprobamos si la columna es normal, entonces se llamará a where, o si se trata
				// //de una columna de relación OneToOne o alguna de este tipo. En ese caso se hará
				// //un join y se pondrá el where para esa tabla nueva.
				// //de paso comprobamos si la columna existe
				// $parsed_column = Parser::_track_alias_column($column);
// 				
// 
				// $this->_queryBuilder = $this->_queryBuilder->where($column, $value, $type);
			// }
			// return $this;
        }
        
        
		//no permitimos enviar sentencias sql complejas en el orm.
        function or_where($column, $value = FALSE)
        {
            //$column = $this->get_column($column);
            $this->_queryBuilder = $this->_queryBuilder->or_where($column, $value);
            return $this;               
        }

        function where_in($column, $in)
        {
            //$column = $this->get_column($column);

            $this->_queryBuilder = $this->_queryBuilder->where_in($column, $in);
            return $this;               
        }       
        
        
        function or_where_in($column, $in)
        {
            //$column = $this->get_column($column);
            $this->_queryBuilder = $this->_queryBuilder->or_where_in($column, $in);
            return $this;               
        }
        
        function where_not_in($column, $in)
        {
            //$column = $this->get_column($column);
            $this->_queryBuilder = $this->_queryBuilder->where_not_in($column, $in);
            return $this;                   
        }       

        function or_where_not_in($column, $in)
        {
            //$column = $this->get_column($column);
            $this->_queryBuilder = $this->_queryBuilder->or_where_not_in($column, $in);
            return $this;                   
        }       

         function open_bracket($type = 'AND')
         {
            $this->_queryBuilder = $this->_queryBuilder->open_bracket($type);
            return $this;               
         }
        
        function close_bracket()
        {
            $this->_queryBuilder = $this->_queryBuilder->close_bracket();
            return $this;               
        }
        
        function having($key, $value)
        {
            //$key = $this->get_column($key);
            $this->_queryBuilder = $this->_queryBuilder->having($key, $value);
            return $this;   
        }

        function or_having($key, $value)
        {
            //$key = $this->get_column($key);
            $this->_queryBuilder = $this->_queryBuilder->or_having($key, $value);
            return $this;   
        }

         function group_by($cols)
         {
            $this->_queryBuilder = $this->_queryBuilder->group_by($cols);
            return $this;               
         }
         
        function like($column, $value, $side = 'both')
        {
            //$column = $this->get_column($column);
            $this->_queryBuilder = $this->_queryBuilder->like($column, $value, $side);
            return $this;           
        }

        function or_like($column, $value, $side = 'both')
        {
            //$column = $this->get_column($column);
            $this->_queryBuilder = $this->_queryBuilder->or_like($column, $value, $side);
            return $this;   
        }
        
        function not_like($column, $value, $side = 'both')
        {
            //$column = $this->get_column($column);
            $this->_queryBuilder = $this->_queryBuilder->not_like($column, $value, $side);
            return $this;   
        }
        
        function or_not_like($column, $value, $side = 'both')
        {
            //$column = $this->get_column($column);
            $this->_queryBuilder = $this->_queryBuilder->or_not_like($column, $value, $side);
            return $this;   
        }

        function order_by($cols, $order = '')
        {
            $this->_queryBuilder = $this->_queryBuilder->order_by($cols, $order);
            return $this;               
        }

         function limit($limit)
         {
            $this->_queryBuilder = $this->_queryBuilder->limit($limit);
            return $this;           
         }

         function offset($offset)
         {
            $this->_queryBuilder = $this->_queryBuilder->offset($offset);
            return $this;           
         }
		 
		 function _join($table, $join_clause, $type = '')
		 {
		 	 $this->_queryBuilder = $this->_queryBuilder->join($table, $join_clause, $type = '');
			 return $this;
		 }


        /**
         * ========================================================================================================
         * ========================================================================================================
         *              EXECUTION FUNCTIONS
         * ========================================================================================================
         * ========================================================================================================
         */ 
         

        private function list_columns()
        {

            foreach($this->_columns as $key => $column)
            {
                $this->_select_list[$column['Field']] = $this->_table.'.'.$column['Field'].' as '.$column['Field'];
            }
			
            return $this->_select_list;
            
        }
		
		public function _add_columns($columns)
		{
			if(is_string($columns))
			{
				$columns = explode(',', $columns);
			}
			
			
			
			foreach($columns as $c)
			{
				array_unshift($this->_select_list, $c);
			}
		}
		
		
		//este es un tipo de join especial que se crea con el campo OneToText; implica las relaciones 1 a 1 y añade un campo más al select
		//como por ejemplo la relación entre un usuario y su DNI, que añadiría al select el número de DNI sin tener que hacer por ello dos
		//query distintas
		function add_joins()
		{
			if(is_null($this->_joins))
			{
				return FALSE;
			}
            foreach($this->_joins as $key => $column)
            {
            	if($column['type'] == 'OneToText')
				{

					$classData = MemoryManager::get_class_data($column['target']);
					$table       = $classData->get_table();
					$columns     = $classData->get_columns();
					$primary_col = $classData->get_primary_column();

					$join_clause = $this->_table.'.'.$column['name'].' = '.$table.'.'.((array_key_exists('referencedColumnName', $column))?$column['referencedColumnName']:$primary_col);
					$this->_queryBuilder = $this->_queryBuilder->join($table, $join_clause);
					$this->_queryBuilder = $this->_queryBuilder->select($table.'.'.$column['readedColumnName'].((array_key_exists('renameColumnName', $column))?' as '.$column['renameColumnName']:''));
				}
			}
					
		}
       
         private function eager_loading()
         {
         	//si no se permite el eager loading, ignorará esta función.
			if($this->_eager_loading == FALSE)
			{
				return FALSE;
			}

			if(is_null($this->_joins))
			{
				return FALSE;
			}
			
			$id_list = array();
			ini_set('error_reporting', E_ALL);
			include_once 'ORM_Relation_Manager.php';
			

            foreach($this->_joins as $key => $column)
            {

            	if(array_key_exists('loading_type', $column) and $column['loading_type'] == 'eager')
				{
					$column['origin_table']	= $this->_table;

					$relation = new ORM_Relation_Manager($column);
					
					if(!array_key_exists($column['columnName'], $id_list))
					{
						foreach($this->_result_data as &$r)
						{
							$id_list[$column['columnName']][] = $r->get_data($column['columnName']);
						}
					}

					
					$relation->add_index($id_list[$column['columnName']]);

					$join_data = $relation->get_data();
					
					foreach($this->_result_data as &$r)
					{
						if(array_key_exists($r->get_data($column['columnName']), $join_data))
						{
							$r->set_data($key, $join_data[$r->get_data($column['columnName'])], FALSE);
						}
					}
				}
            }

         }
         
        function get()
        {

			$query = $this->_pre_processing();
			
			$this->_result_data = $query->result($this->_class);
            //process the data to add it into the memory            
            $this->_post_processing();

            $result = $this->_result_data;

           	$this->_whipe_out();

            return $result;
        }

        function get_grouped($group_by_column)
        {
        	
			$this->_add_columns($group_by_column);

			$query = $this->_pre_processing();
			
			$this->_result_data = $query->group($this->_class);
            //process the data to add it into the memory            
            $this->_post_processing();

            $result = $this->_result_data;

           	$this->_whipe_out();

            return $result;
        }
		
		function row()
		{
			$result_data = $this->limit(1)->get();
			
			return reset($result_data);
		}
		
		private function _whipe_out()
		{
			$this->_select_list = NULL;
            unset($this->_result_data);
			$this->_result_data = NULL;
            unset($this->_queryBuilder);
            $this->_queryBuilder = NULL;
		}

		private function _pre_processing()
		{
			if($this->_eager_loading == TRUE)
			{
				$this->_save_query_join();
			}
			

            $this->_queryBuilder = $this->_queryBuilder->select($this->list_columns());

			$this->add_joins();
			
			return $this->_queryBuilder->get();
	
		}
		
		private function _post_processing()
		{
		 	//TODO añadir las querys eager loading para generar los objetos
            // rollo (objeto)->where('campo', 'dato')
            //TODO si es un campo lazy loading debería tener un valor especifico para
            //identificarlo y cargarlo dinámicamente.
            
            $this->eager_loading();
			
		}

        private function _exception($msg)
        {
        	Warning::exception($msg);
        }
		
        /**
         * ========================================================================================================
         * ========================================================================================================
         *              SEUDO PRIVATE FUNCTIONS
         * ========================================================================================================
         * ========================================================================================================
         */ 
         
         function _eager_loading($eager)
		 {
		 	if(gettype($eager) != 'boolean')
			{
				throw new Exception("El valor pasado a _eager_loading debe ser un boolean.", 1);
				
			}
			
		 	$this->_eager_loading = $eager;
		 }
    	
        /**
         * ========================================================================================================
         * ========================================================================================================
         *              PRIVATE FUNCTIONS
         * ========================================================================================================
         * ========================================================================================================
         */
         
         public function _set_query($queryBuilder)
		 {
		 	$this->_queryBuilder = $queryBuilder;
		 }
         
         //saves the query for eager loading joins
         private function _save_query_join()
		 {
		 	$this->_query_join_name = md5(time().rand());
			
			while(QueryBox::filter_exists($this->_query_join_name))
			{
				$this->_query_join_name = md5(time().rand());
			}
			
		 	QueryBox::set_filter($this->_queryBuilder, $this->_query_join_name);
		 } 
		 
		 private function _get_query_join()
		 {
		 	$query = QueryBox::get_filter($this->_query_join_name);
		 }
         
         function get_columns()
		 {
		 	$return_data = array();
			
			foreach($this->_columns as $c)
			{
				$return_data[$c['Field']] = $c;
			}

		 	return $return_data;
		 }
		 
		 function get_table()
		 {
		 	return $this->_table;
		 }
    
        /**
         * ========================================================================================================
         * ========================================================================================================
         *              PRIVATE FUNCTIONS
         * ========================================================================================================
         * ========================================================================================================
         */
	
		function save()
		{
			ORM_Operations::save_table($this->_table);
		}
	
	
    }