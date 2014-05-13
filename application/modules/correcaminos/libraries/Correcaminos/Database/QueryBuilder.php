<?php


    /**
     * TODO
     * 1 - Hacer active record para querys manuales normales
     * 2 - Hacer active record para orm de objetos sencillos
     * 3 - Hacer active record para orm de objetos con joins eager y lazy loading
     * 
     * TODO FUTURO
     *  1 - Añadir alias a todas las tablas?
     */

    namespace Correcaminos\Database;
    
    use Correcaminos\Warning,
        Correcaminos\Database\Result,
        Correcaminos\Database\Forge,
        Correcaminos\Parser;
    
    class where_data{
        
        public $column;
        
        public $sql_value;
        
        public $value;
        
        public $type;
        
        public $parse = TRUE;
        
        public $subquery = FALSE;
    
        function __construct(array $data)
        {
            foreach($data as $key => $value)
            {
                $this->$key = $value;
            }
        }
    
    
    }
    
    
    class where{
                    
        private $_where         = NULL;
        
        private $_sql           = NULL;
        
        private $_arr_positions = NULL;

        private $_pointer       = NULL;
        
        private $_sql_values    = NULL;
        
        function __construct()
        {
            $this->reset();
        }
        
        public function reset()
        {
            $this->_where           = NULL;
            $this->_arr_positions   = NULL;
            $this->_pointer         = NULL;
            $this->_sql             = NULL;
            $this->open_bracket();
        }
        
        /**
         * Adds one where clause into the actual level of nesting
         */
        function add(where_data $where)
        {
        	
				//checks if it's the first where parameter
				//to get rid of the AND-OR operator.
				if($this->count() == 0)
				{
					$where->type = ''; 
				}

                $this->_pointer[] = $where;

                //parse makes the where statement to pass without 
                //a sql value, it uses a plain string instead
                //it's useful for two column comparations, for example
                //si parse == TRUE es que se parsea y trae _sql_values
                if($where->parse == TRUE)
                {
                    if(is_array($where->sql_value))
                    {
                        foreach($where->sql_value as $key => $sql_val)
                        {
                            $this->_sql_values[$sql_val] = $where->value[$key];
                        }
                    }
                    else 
                    {
                         $this->_sql_values[$where->sql_value] = $where->value;
                    }                    
                }
				elseif($where->subquery == TRUE)
				{
					//se generaría un sql plano ya formado y una lista de parámetros que añadir
					//al _sql_values de este where
					$this->_sql_values = $where->_sql_values + ((is_null($this->_sql_values))?array():array());
				}

                $this->_arr_positions[count($this->_arr_positions) - 1]+= 1;
        }
        
        
        function count()
        {
            if($this->_pointer == NULL)
            {
                return 0;
            }
            return count($this->_pointer);
        }

		/**
		 * Yere mighte be giants.
		 * Ye olde magic of friendship from here 
		 */


        /**
         * Adds one  more level of nesting
         */
        public function open_bracket($type = '')
        {
            if($this->_arr_positions == NULL)
            {

                $this->_where = array('type' => $type, 'list' => array());
                
                $this->_pointer = &$this->_where['list'];
                
                $this->_arr_positions[] = 0;
                
            }
            else
            {
            	

                //iterates until the actual position of the array and adds one more
                //position
                $sub_pointer = &$this->_where['list'];

                $count = count($this->_arr_positions) - 1;



                for($i = 0; $i <= ($count); $i++)
                {

                    if($i < $count)
                    {
                        $sub_pointer = &$sub_pointer[$this->_arr_positions[$i]]['list'];
                    }
                    else
                    {
						//if there is no positions in this level, the 
						//type will be null, because it's the first 
						//filter in this bracket
						if($this->_arr_positions[$i] == 0)
						{
							$type = '';
						}

                        $sub_pointer = &$sub_pointer[$this->_arr_positions[$i]];
                    }
                    
                }
                $sub_pointer = array('type' => $type, 'list' => array());

                $this->_pointer = &$sub_pointer['list'];
                
                $this->_arr_positions[] = 0;    
            }
        }

        /**
         * Pointer goes down one level of nesting
         */
        public function close_bracket()
        {
            if($this->_arr_positions == NULL or count($this->_arr_positions) < 2)
            {
                return FALSE;
            }

                array_pop($this->_arr_positions);
                
                //iterates until the father of the actual position of the array and adds one more
                //position
                $count = count($this->_arr_positions) - 2;
                if($count < 0)
                {
                    $sub_pointer = &$this->_where;
                }
                else
                {
                    $sub_pointer = &$this->_where['list'];  
                }
                
                for($i = 0; $i <= $count; $i++)
                {
                    if($i < $count)
                    {
                        $sub_pointer = &$sub_pointer[$this->_arr_positions[$i]]['list'];
                    }
                    else
                    {
                        $sub_pointer = &$sub_pointer[$this->_arr_positions[$i]];
                    }
                }

                $this->_pointer = &$sub_pointer['list'];    

                $this->_arr_positions[count($this->_arr_positions) -1] += 1;
                
        }
 
 
		/**
		 * Yere mighte be giants.
		 * Ye olde magic of friendship to here 
		 */

        /**
         * Returns the sql and values of the where clause
         */
        public function get()
        {
            return array('sql' => $this->generate_sql(), 'sql_values' => $this->_sql_values);
        }
		
		public function get_plain_sql()
		{
			
			$sql = $this->generate_sql();
			if($this->_sql_values !== NULL)
			{
				foreach($this->_sql_values as $key => $s)
				{
					$sql = str_replace($key, $s, $sql);
				}				
			}
			//we dispose of the openning and closing brackets
			$sql = preg_replace('/(\((.*?)\))/','$2' ,$sql);
			
			if($sql == '')
			{
				return FALSE;
			}
			
			return $sql;
		}
        
        
        //the query_value data used here corresponde cuando no quiere usarse el sistema de querys normal sino 
        //que quiere que el valor de la query esté hardcodeado en lugar de en una variable de un array. Se
        //puede usar para igualar columnas en una query estilo usuario.id = seguidor.usuario
        
        public function generate_sql($position = NULL)
        {
        	
        	//get all the where data into the $position var
            if($position == NULL)
            {
                $position = &$this->_where;
            }

            $return_data = '';
			
            if(is_array($position))
            {
				//add the 'and', 'or' or whatever clauses between the previous statement and this statement
				//if it's the first value of position should be type == ''
                $return_data.= (($position['type'] !== '')?' '.$position['type'].' ':'').'(';
                
				//bucle de las posiciones
                foreach($position['list'] as $pos)
                {
					//si es un array es que esta posición tiene operandos internos.
					//si no es un array será un objeto y será un operando en sí mismo.
					//se hace una llamada 
                    if(is_array($pos))
                    {
                        $return_data.= $this->generate_sql($pos);
                    }
                    else
                    {
                    	//si es un array, será una entrada de where normal.
                    	//si type es vacío no tendrá un "and", "or".... al principio
                    	//column es el nombre de la columna de la query. Puede tener la forma tabla.columna o columna
                    	//si está definido query_value entonces será un comando del tipo sin parsear y se pondrá el texto de query_value
                    	//si no está definido se usará el sq_value, que es el identificador con una key del array de parametros
                        $return_data.= (($pos->type == '')?'':' '.$pos->type.' ').$pos->column.' '.((isset($pos->query_value))?$pos->query_value:$pos->sql_value);
                    }
                }
                
                $return_data.= ')';
            }
            else
            {
                return (($position->type == '')?'':' '.$position->type.' ').$position->column.' '.((isset($position->query_value))?$position->query_value:$position->sql_value);
            }

            return $return_data;
        }

    }
    
    
    
    
    
    class QueryBuilder{

        private  $_main_table     = NULL;
        
        private  $_join_tables    = NULL;
        
        private  $_where          = NULL;
        
        private  $_select         = NULL;
        
        private  $_group_by       = NULL;
        
        private  $_order_by       = NULL;
        
        private  $_limit          = NULL;
        
        private  $_offset         = NULL;
        
        private  $_values         = NULL;
		
		private  $_is_distinct	  = FALSE;
		
		private $_ar_aliased_tables = array();

         
        /**
         * ========================================================================================================
         * ========================================================================================================
         *              TABLE CLAUSES
         * ========================================================================================================
         * ========================================================================================================
         */
         
         function __construct()
		 {
		 	$this->_reset_params();
		 }
         
                
        /**
         * Adds the main table to the query and fetchs its alias in case there is one.
         * 
         * @param String (only can be a table name)
         * @return object
         */
        function from($table)
        {
            if(!is_string($table))
            {
                $this->_exception('La tabla debe ser un string válido.');
            }

            
            
            $table = trim($table);
            
            $this->_main_table = $this->_track_alias_table($table);
            
            
            return $this;   
        }
        
        /**
         * Adds a new table to the query to join it with another one
         */
        function join($table, $join_clause, $type = '')
        {
            
            if ($type != '')
            {
                $type = strtoupper(trim($type));
    
                if ( ! in_array($type, array('LEFT', 'RIGHT', 'OUTER', 'INNER', 'LEFT OUTER', 'RIGHT OUTER')))
                {
                    $type = '';
                }
            }
            
            $table = $this->_track_alias_table($table);
            
			if(is_array($join_clause))
			{
	            // Strip apart the condition and protect the identifiers
            	$first = reset($join_clause);
				if(is_array($first))
				{
					foreach($join_clause as $j)
					{
						$this->join($table, $j, $type);
					}
				}
				else
				{
					$join_clause = implode(' ', $join_clause);
				}
			}
			else
			{
				// Strip apart the condition and protect the identifiers
          		$join_clause= Parser::_separate_columns_join($join_clause);
			}

            
            $this->_join_tables[] = array('table' => $table, 'clause' => $join_clause, 'type' => $type);
            
            return $this;
        }
    
	    
        /**
         * ========================================================================================================
         * ========================================================================================================
         *              SUBQUERY CLAUSES
         * ========================================================================================================
         * ========================================================================================================
         */ 
        

		//hacemos la Subquery
		
		private function _subquery($column, $value, $type)
		{
			
			if(!is_object($value) || get_class($value) != 'Correcaminos\Database\QueryBuilder')
			{
				return FALSE;
			}
			else 
			{
				  $datos = $value->_make_query();
				                  
	              $this->_where->add(new where_data(array( 'column' => $column, 
						              					   'subquery' => TRUE,
						                                   'query_value' => '('.$datos['sql'].')',
						                                   'parse' => FALSE,
						                                   '_sql_values' => $datos['data'], 
						                                   'type' => $type)));
			}
			
			return TRUE;
		}
        
        
        /**
         * ========================================================================================================
         * ========================================================================================================
         *              ALIAS CLAUSES
         * ========================================================================================================
         * ========================================================================================================
         */
                
        /**
         * We initialize the class values in order to start a new query
         */
        public function _reset_params()
        {
            $this->_where         = new where();
            $this->_select        = NULL;
            $this->_join_tables   = NULL;
            $this->_group_by      = NULL;
            //Parser::reset_param_pointer();
            $this->_order_by      = NULL;
            $this->_limit         = NULL;
            $this->_offset        = NULL;
            $this->_values        = NULL;
            $this->_main_table    = NULL;
			$this->_is_distinct	  = FALSE;
        } 
        
        
        /**
         * Para ahorrar problemas con las joins, sólo llevarán alias las tablas que manualmente haya asignado
         * el usuario. El tendrá que tener cuidado de evitar que las columnas se repitan en nombres
         */
        
        private function _track_alias_table($data)
        {
            // if a table alias is used we can recognize it by a space
            if (strpos($data, " ") !== FALSE)
            {
                // if the alias is written with the AS keyword, remove it
                $data = preg_replace('/\s+AS\s+/i', ' ', $data);
    
                // Grab the alias
                $alias  = trim(strrchr($data, " "));
                $table  = trim(str_replace($alias, ' ', $data)); 
                // Store the alias, if it doesn't already exist
                if ( ! in_array('`'.$alias.'`', $this->_ar_aliased_tables))
                {
                    $table = '`'.$table.'` as `'.$alias.'`';
					$this->_ar_aliased_tables[] = '`'.$alias.'`';
                }
            }
            else{
                $table = '`'.$data.'`';
            }
            
            return $table;
            
        }
        

        /**
         * ========================================================================================================
         * ========================================================================================================
         *              WHERE CLAUSES
         * ========================================================================================================
         * ========================================================================================================
         */

       
        /**
         * Adds a where clause
         * It lets use the $column parameter to pass a string with all the where clauses
         */
        function where($column, $value = FALSE, $parse = TRUE)
        {
 
            if($value === FALSE)
            {
                
               if(is_array($column))
               {
                   foreach($column as $key => $col)
                   {
                   		if(is_numeric($key))
						{
							$this->_exception("The column name can't be a number.");
						}
                       $this->where($key, $col);
                   }
               }
               elseif(is_string($column))
               {
                    $list = Parser::_separate_columns_where($column, 'AND');
                    foreach($list as &$item)
                    {
                        if(strpos($item['column'], '(') !== FALSE)
                        {
                            $this->open_bracket($item['type']);
                            $item['column'] = str_replace('(', '', $item['column']);
                        }
                        
                        if(strpos($item['value'], ')') !== FALSE)
                        {
                            $close_bracket = TRUE;
                            $item['value'] = str_replace(')', '', $item['value']);
                        }
                        else
                        {
                            $close_bracket = FALSE;
                        }
    
                        $this->_where($item['column'], $item['value'], $item['type'], $parse);
    
                        if($close_bracket == TRUE)
                        {
                            $this->close_bracket();
                        }
                        
                    }
               }
            }
            else 
            {
                $this->_where($column, $value, 'AND', $parse);
            }

            return $this;
        }
        
        function or_where($column, $value = FALSE, $parse = TRUE)
        {
            if($value === FALSE)
            {
                $list = Parser::_separate_columns_where($column, 'OR');
                foreach($list as &$item)
                {
                    if(strpos($item['column'], '(') !== FALSE)
                    {
                        $this->open_bracket($item['type']);
                        $item['column'] = str_replace('(', '', $item['column']);
                    }
                    
                    if(strpos($item['value'], ')') !== FALSE)
                    {
                        $close_bracket = TRUE;
                        $item['value'] = str_replace(')', '', $item['value']);
                    }
                    else
                    {
                        $close_bracket = FALSE;
                    }
                    
                    $this->_where($item['column'], $item['value'], $item['type'], $parse);

                    if($close_bracket == TRUE)
                    {
                        $this->close_bracket();
                    }
                    
                }
            }
            else 
            {
                $this->_where($column, $value, 'OR', $parse);
            }
            
            return $this;           
        }
        
        private function _where($column, $value, $type, $parse = TRUE)
        {
            if(!Parser::_has_operator($column))
            {
                $column.= ' =';
            }

			$parsed_column = Parser::_track_alias_column($column);

			if($this->_subquery($parsed_column['column'], $value, $type) == TRUE)
			{
				return TRUE;
			}

            if($parse == TRUE)
            { 
                

                 $this->_where->add(new where_data(array('column' => $parsed_column['column'], 
                                                       'sql_value' => $parsed_column['parameter'], 
                                                       'value' => $value, 
                                                       'type' => $type)));               
            }
            else
            {
                 $this->_where->add(new where_data(array('column' => $column, 
                                                       'query_value' => $value,
                                                       'sql_value' => Parser::fetch_param_pointer(), 
                                                       'parse' => FALSE,
                                                       'value' => $value, 
                                                       'type' => $type)));     
            }

        }
        
    
        
        function where_in($column, $in)
        {
            if(Parser::_has_operator($column))
            {
                $this->_exception('La clausula in no puede contener operadores.');
            }
            
            $this->_where_in($column, $in, 'AND');
            return $this;               
        }
        
        function or_where_in($column, $in)
        {
            if(Parser::_has_operator($column))
            {
                $this->_exception('La clausula in no puede contener operadores.');
            }
            
            $this->_where_in($column, $in, 'OR');            
            return $this;   
        }
        
        function where_not_in($column, $in)
        {
            if(Parser::_has_operator($column))
            {
                $this->_exception('La clausula in no puede contener operadores.');
            }
            
            $this->_where_in($column, $in, 'AND', TRUE);
            return $this;               
        }
        
        function or_where_not_in($column, $in)
        {
            if(Parser::_has_operator($column))
            {
                $this->_exception('La clausula in no puede contener operadores.');
            }
            
            $this->_where_in($column, $in, 'OR', TRUE);
            return $this;               
        }
        
        private function _where_in($column, $in, $type = 'AND', $not = FALSE)
        {
            if(Parser::_has_operator($column))
            {
                $this->_exception('The column '.$column.' can\'t have an operator in an "in" clause.');
            }        
			
            $column = Parser::_track_alias_column($column, count($in));

			$column['column'] =  $column['column'].(($not == TRUE)?' not ':'').' in ';

			if($this->_subquery($column['column'], $in, $type) == TRUE)
			{
				return TRUE;
			}
 
			if(empty($in))
			{
				return FALSE;
			}
 
            if(!is_array($in))
			{
				$in = explode(',',$in); 
			} 
            else
            {
                $in = array_values($in);
            }          

            if(!is_array($column['parameter']))
            {
                $column['parameter'] = array($column['parameter']);
            }

            $this->_where->add(new where_data(array('column' => $column['column'],
                                                   'query_value' => '('.implode(',',$column['parameter']).')',
                                                   'sql_value' => $column['parameter'],
                                                   'value' => $in, 
                                                   'type' => $type)));
        }

        /**
         * Adds a new where type clause like => where (col1 = 23 and col2 = 44) AND (col3 = 'asdf'...) ...
         */

         function open_bracket($type = 'AND')
         {
            $this->_where->open_bracket($type);
            return $this;
         }
         function close_bracket()
         {
            $this->_where->close_bracket();
            return $this;   
         }



        /**
         * ========================================================================================================
         * ========================================================================================================
         *              HAVING CLAUSES
         * ========================================================================================================
         * ========================================================================================================
         */

            function having($key, $value)
            {
                $this->_having($key, $value);
                return $this;
            }

            function or_having($key, $value)
            {
                $this->_having($key, $value, 'OR');
                return $this;
            }
            
            
            private function _having($key, $value, $type = 'AND')
            {
                if(!Parser::_has_operator($key))
                {
                    $key.= ' =';
                }
 
				$column = Parser::_track_alias_column($key); 

	 			if($this->_subquery($column['column'], $value, $type) == TRUE)
				{
					return TRUE;
				}

                $this->_where->add(new where_data(array('column' => $column['column'], 
                                                       'sql_value' => $column['parameter'], 
                                                       'value' => $value, 
                                                       'type' => $type)));
            }

        /**
         * ========================================================================================================
         * ========================================================================================================
         *              GROUP BY CLAUSES
         * ========================================================================================================
         * ========================================================================================================
         */

         function group_by($cols)
         {
             if(!is_array($cols))
             {
                 $cols = explode(',', $cols);
             }
             
             
             foreach($cols as $col)
             {
                 $col = Parser::_track_alias_column(trim($col));
                 
                 $this->_group_by[] = $col['column'];
             }
             
             return $this;
         }

         
        /**
         * ========================================================================================================
         * ========================================================================================================
         *              LIKE CLAUSES
         * ========================================================================================================
         * ========================================================================================================
         */

            function like($column, $value, $side = 'both')
            {
                $this->_like($column, $value, $side, '', 'AND');
                return $this;
            }
            
            function or_like($column, $value, $side = 'both')
            {
                $this->_like($column, $value, $side, '', 'OR');
                return $this;
            }
            
            function not_like($column, $value, $side = 'both')
            {
                $this->_like($column, $value, $side, 'not', 'AND');
                return $this;
            }
            
            function or_not_like($column, $value, $side = 'both')
            {
                $this->_like($column, $value, $side, 'not', 'OR');
                return $this;
            }
            
            private function _like($column, $value, $side = 'both', $not = '' ,$type = 'AND')
            {
                if(Parser::_has_operator($column))
                {
                    $this->_exception('The '.$column.' can\'t have an operator in an "like" clause.');
                }
    
                $column = Parser::_track_alias_column($column);
				
				if($this->_subquery($column['column'], $value, $type) == TRUE)
				{
					return TRUE;
				}                
                
                $side = strtolower($side);
                
                if($side !== 'both' and $side !== 'right' and $side !== 'left' and $side !== 'none')
                {
                    $this->_exception('The '.$side.' side type can\'t be instantiated in a like clause.');
                }
    
                $this->_where->add(new where_data(array('column' => $column['column'].(($not == TRUE)?' not ':'').' like ', 
                                                   'sql_value' => $column['parameter'], 
                                                   'value' => (($side == 'both' or $side == 'left')?'%':'').$value.(($side == 'both' or $side == 'right')?'%':''), 
                                                   'type' => $type)));
            }



        /**
         * ========================================================================================================
         * ========================================================================================================
         *              ORDER BY CLAUSES
         * ========================================================================================================
         * ========================================================================================================
         */

         function order_by($cols, $order = '')
         {
             if(!is_array($cols))
             {
                 $cols = array($cols => $order );
             }
             
             
             foreach($cols as $col => $direction)
             {
                 
                 if(Parser::_has_operator($col))
                 {
                     $this->_exception('The column can\'t have an operator in a order by clause.');
                 }
                 
                 $col = Parser::_track_alias_column(trim($col));
                 
                 $direction = (in_array(strtoupper(trim($direction)), array('ASC', 'DESC'), TRUE)) ? ' '.$direction.'' : ' ASC';
                 
                 $this->_order_by[] = $col['column'].$direction;
                                 
             }
             
             return $this;
             
         }
         
        /**
         * ========================================================================================================
         * ========================================================================================================
         *              LIMIT AND OFFSET CLAUSES
         * ========================================================================================================
         * ========================================================================================================
         */
         
         function limit($limit)
         {
             if(!is_numeric($limit))
             {
                 $this->_exception('The limit must have a numeric value.');
             }
             else
             {             
                $this->_limit = (int)$limit;
             }
             return $this;
         }
                
         function offset($offset)
         {
             if(!is_numeric($offset))
             {
                 $this->_exception('The offset must have a numeric value.');
             }
             else
             {
                 $this->_offset = (int)$offset;
             }

             return $this;             
         }       
                
         
        /**
         * ========================================================================================================
         * ========================================================================================================
         *              SELECT CLAUSES
         * ========================================================================================================
         * ========================================================================================================
         */
        
        
        
        /**
         * Adds select fields to the query 
         */
         function select($fields = '*', $parse = TRUE)
         {
            if(is_array($fields))
            {
                foreach($fields as $field)
                {
                    $this->select($field, $parse);
                }
                return $this;
            }

            $fields = explode(',', $fields);

            foreach($fields as $field)
            {
                
                if($field !== '')
                {
                    if($parse == TRUE)
                    {
                        $field = Parser::_track_alias_column(trim($field));
                        $this->_select[] = $field['column'];
                    }
                    else
                    {
                        $this->_select[] = $field;
                    }
                }
            }

            return $this;
         }
		 
		 function distinct($distinct = TRUE)
		 {
		 	((is_bool($distinct))?$this->_is_distinct = $distinct:FALSE);
			
			return $this;
		 }
        
        
        private function _exception($msg)
        {
            
                Warning::exception($msg);
            
        }
        
         /**
         * ========================================================================================================
         * ========================================================================================================
         *              INSERT AND UPDATE FUNCTIONS
         * ========================================================================================================
         * ========================================================================================================
         */           
        
        public function values($column, $data = TRUE, $escape = TRUE)
        {
            if(!is_array($column))
            {
                $column = array($column => $data);
            }
            else
            {
                $escape = $data;
            }
            $insert_val = array();
            
            
            //initialize the $_values
            $this->_values = array('column'     => array(),
                                   'parameters' => array(),
                                   'values'     => array());
            
            
            foreach($column as $key => $val)
            {
                 $column = Parser::_track_alias_column($key);
                 
                 $this->_values['column'][] = $column['column'];
                 //if we don't want to escape the fields like in
                 //updates with functions or column data like value = value + 1
                 if($escape == FALSE)
                 {
                    $this->_values['parameters'][$column['column']] = $val;
                 }
                 else
                 {
                    $this->_values['parameters'][$column['column']] = $column['parameter'];
                    $this->_values['values'][$column['parameter']] = $val;
                 }
                
 
            }

            
            return $this;
        }
        
        public function get_values()
        {
            return $this->_values;
        }
        
        public function last_insert_id()
        {
            return Driver::last_insert_id();
        }
        
        /**
         * ========================================================================================================
         * ========================================================================================================
         *              EXECUTION FUNCTIONS
         * ========================================================================================================
         * ========================================================================================================
         */        
        

        function get($cache = NULL)
        {
            $sqlData = $this->_make_query();

			$result = Driver::query(array('sql' => $sqlData['sql'], 'parameters' => $sqlData['data'], 'cache' => $cache, 'tables' => $this->_get_tables()));

            return $result;
        }
		        
        function insert()
        {
            
            if($this->_values == NULL)
            {
                $this->_exception('You must define values before inserting.');
            }
            
            $columns = $this->_values['column'];
            $parameters = $this->_values['parameters'];
            $values = $this->_values['values'];
            
            $sql = 'INSERT into '.$this->_main_table.' ('.implode(',', $columns).') VALUES ('.implode(',',$parameters).')';

            Driver::query(array('sql' => $sql, 'parameters' =>  $values, 'tables' => $this->_get_tables()));
            
            $result = Driver::last_insert_id();

            
            return $result;
        }
        
        function update()
        {
            if($this->_values == NULL)
            {
                $this->_exception('You must define values before updating.');
            }
            
            if($this->_where->count() == 0)
            {
                $this->_exception('You must define a search value before updating.');            
            }
            
            foreach($this->_values['column'] as $column)
            {
                $update_params[] = $column.' = '.$this->_values['parameters'][$column];
            }
            
            $sql = 'UPDATE '.$this->_main_table;

            //joins
            $sql.= $this->sql_join();

            $sql.= ' SET '.implode(', ',$update_params);

            
            $where_data = $this->_where->get();
            
            $sql.= ' WHERE '.$where_data['sql'];
            $values = array_merge($where_data['sql_values'], $this->_values['values']);
  
            $result = Driver::query(array('sql' => $sql, 'parameters' =>  $values, 'tables' => $this->_get_tables()));

            
            return $result->rowCount();

        }


		public function update_batch($values, $field, $type = 'AND')
		{
			
            $array_zero = reset($values);
			//check if the where field exists
			if(!array_key_exists($field, $array_zero))
			{
				$this->_exception('There is no field declared in the update array.');
			}
			
			$update_params = array();
			
			foreach($values as &$val)
            {
                $differences = array_diff_key($val, $array_zero);
                if(!empty($differences))
                {
                    throw new Exception("El array de values tiene keys distintas en el update batch", 1);
                }
                
                $main_key = $val[$field];
                $arr_main_keys[] = $main_key;
                
                unset($val[$field]);
                
                foreach($val as $key => $data)
                {
                    $column = Parser::_track_alias_column($key);

                    $update_params[$column['column']][] = array('needle' => $main_key, 'data' => $data);
                }
            }
            
            //hacemos el where_in
            $this->_where_in($field, $arr_main_keys);
            
            //generamos el texto raro del update batch
			$update_text = '';
            
            $column = Parser::_track_alias_column($field);
            $field  = $column['column'];
            
            
            foreach($update_params as $column => $p)
            {
                $update_text.= $column.' = CASE ';
                
                foreach($p as $data)
                {
                    $main_key =  Parser::fetch_param_pointer();
                    $data_key =  Parser::fetch_param_pointer();
                    $update_text.= ' WHEN '.$field.' = '.$main_key.' THEN '.$data_key;
                    
                    $update_data[$main_key] = $data['needle'];
                    $update_data[$data_key] = $data['data'];
                }
                
                $update_text.= ' ELSE '.$column.' END ';
            } 

            $sql = 'UPDATE '.$this->_main_table;
            
            $sql.= $this->sql_join();
            
            $sql.= '  SET '.$update_text;
            
            $where_data = $this->_where->get();
            
            $sql.= ' WHERE '.$where_data['sql'];
            
            $update_parameters = array_merge($where_data['sql_values'], $update_data);

            $result = Driver::query(array('sql' => $sql, 'parameters' => $update_parameters, 'cache' => FALSE, 'tables' => $this->_get_tables()));


            return $result->rowCount();           

		}

		public function insert_batch($values)
		{
			$array_zero = reset($values);
			
			foreach($values as $v)
			{
				$check = array_diff_key($array_zero, $v);
				
				if(!empty($check))
				{
					$this->_exception('Los arrays de inserción no son idénticos.');
				}
				
				$new_arr = array();
				
				foreach($v as $key => $c)
				{
					$new_key =  Parser::fetch_param_pointer();
					$new_arr[$new_key] = $new_key;
					$insert_values[$new_key] = $c;
				}
				
				$key_insert_values[] = $new_arr;
			}
			
			$sql = 'INSERT INTO '.$this->_main_table;
			
			$columns = array_keys($array_zero);
			
			$sql.= ' ('.implode(',', $columns).') VALUES ';
			
			foreach($key_insert_values as $i)
			{
				$sql_inserts[] = '('.implode(',', array_keys($i)).')';	
			}

			$sql.= implode(',', $sql_inserts);

			$result = Driver::query(array('sql' => $sql, 'parameters' => $insert_values, 'tables' => $this->_get_tables()));

		} 

        
        function delete()
        {
            $sql = 'DELETE from '.$this->_main_table;
            // ----------------------------------------------------------------
            //make wheres
            if($this->_where->count() !== 0)
            {
                $where_data = $this->_where->get();
                
                $sql.= ' WHERE '.$where_data['sql'];
                $params = $where_data['sql_values'];

            }
            
            $result = Driver::query(array('sql' => $sql, 'parameters' => $params, 'tables' => $this->_get_tables()));


            
            return $result->rowCount();            
                        
            
        }
        
        private function _make_query()
        {
            $sql = 'SELECT '.(($this->_is_distinct === TRUE)?' DISTINCT ':'');
			
            $params = array();
            
            // ----------------------------------------------------------------
            //make select statement
            if($this->_select == NULL)
            {
                $this->_select[] = '*';
            }
            
            $sql.= implode(', ', $this->_select);
            
            // ----------------------------------------------------------------
            //make from statement
            $sql.= ' from '.$this->_main_table.' ';
            
            // ----------------------------------------------------------------
            //make joins
            $sql.= $this->sql_join();
            
            // ----------------------------------------------------------------
            //make wheres
            if($this->_where->count() !== 0)
            {
                $where_data = $this->_where->get();
                
                $sql.= ' WHERE '.$where_data['sql'];
                $params = $where_data['sql_values'];

            }
            
            // ----------------------------------------------------------------
            //make group by
            
            if($this->_group_by !== NULL)
            {
                $sql.= ' GROUP BY '.implode(', ', $this->_group_by);
            }
            
            // ----------------------------------------------------------------
            //make order by
            
            if($this->_order_by !== NULL)
            {
                $sql.= ' ORDER BY '.implode(', ', $this->_order_by);
            }           
            
            // ----------------------------------------------------------------
            //make limit & offset
            
            if($this->_limit !== NULL)
            {
                $sql.= ' LIMIT '.$this->_limit;
            }
            
            if($this->_offset !== NULL)
            {
                $sql.= ' OFFSET '.$this->_offset;
            }

            return array('sql' => $sql, 'data' => $params);
        
        }

		private function sql_join()
		{
			if($this->_join_tables !== NULL)
            {
            	$sql = '';
				
                foreach($this->_join_tables as $join)
                {
                    $sql.= ' '.$join['type'].' JOIN '.$join['table'].' ON '.$join['clause'];
                }
				
				return $sql;
				
            }
			
			return '';
			
		}
		
		
		public function raw_query($query, $parameters = FALSE)
		{
			return Driver::query(array('sql' => $query, 'parameters' => $parameters));

		}
		

        /**
         * ========================================================================================================
         * ========================================================================================================
         *              MISCELANEA FUNCTIONS
         * ========================================================================================================
         * ========================================================================================================
         */  

		 function forge()
		 {
		 	require_once APPPATH.'libraries/Correcaminos/Database/Forge.php';
		 	$forge = new Forge($this->_main_table);
			return $forge;
		 }
		 
		 private function _get_tables()
		 {
		 	 $tables[] = $this->_main_table;
			 
			 if(is_array($this->_join_tables))
			 {
			 	foreach($this->_join_tables as $join)
				{
					$tables[] = $join['table'];
				}
			 }

			return $tables;
			 
		 }
		 
        /**
         * ========================================================================================================
         * ========================================================================================================
         *              ORM QUERY HELP FUNCTIONS
         * ========================================================================================================
         * ========================================================================================================
         */
         
         function _get_where()
		 {
		 	 return $this->_where->get_plain_sql();

		 }
		 
		 function _get_table()
		 {
		 	return str_replace('`', '', $this->_main_table);
		 }

}