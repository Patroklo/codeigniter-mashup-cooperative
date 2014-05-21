<?php namespace Correcaminos\ORM;

    use Correcaminos\Warning,
    	Correcaminos\ORM\ORM_QueryBuilder,
		Correcaminos\Parser;

    class ORM_Facade_QueryBuilder{
        
		private $ORM_QueryBuilder;
     
		//where data stored for join purpouses for eager loading data
		private $_filters = array();

        public function From($className)
        {
            $this->ORM_QueryBuilder = new ORM_QueryBuilder();
			$this->ORM_QueryBuilder->From($className);
            return $this;
        }

        /**
         * ========================================================================================================
         * ========================================================================================================
         *              QUERYBUILDER FUNCTIONS
         * ========================================================================================================
         * ========================================================================================================
         */ 

        
        function where($column, $value = FALSE)
        {
        	$column = $this->get_column($column);
        	$this->ORM_QueryBuilder->where($column, $value);
            return $this;           
        }
        
        
        function or_where($column, $value = FALSE)
        {
        	$column = $this->get_column($column);
        	$this->ORM_QueryBuilder->or_where($column, $value);
            return $this;               
        }

        function where_in($column, $in)
        {
        	$column = $this->get_column($column);
        	$this->ORM_QueryBuilder->where_in($column, $in);
            return $this;               
        }       
        
        
        function or_where_in($column, $in)
        {
        	$column = $this->get_column($column);
        	$this->ORM_QueryBuilder->or_where_in($column, $in);
            return $this;               
        }
        
        function where_not_in($column, $in)
        {
        	$column = $this->get_column($column);
            $this->ORM_QueryBuilder->where_not_in($column, $in);
            return $this;                   
        }       

        function or_where_not_in($column, $in)
        {
        	$column = $this->get_column($column);
            $this->ORM_QueryBuilder->or_where_not_in($column, $in);
            return $this;                   
        }       

         function open_bracket($type = 'AND')
         {
         	$column = $this->get_column($column);
            $this->ORM_QueryBuilder->open_bracket($type);
            return $this;               
         }
        
        function close_bracket()
        {
        	$column = $this->get_column($column);
            $this->ORM_QueryBuilder->close_bracket();
            return $this;               
        }
        
        function having($key, $value)
        {
        	$key = $this->get_column($key);
            $this->ORM_QueryBuilder->having($key, $value);
            return $this;   
        }

        function or_having($key, $value)
        {
        	$key = $this->get_column($key);
            $this->ORM_QueryBuilder->or_having($key, $value);
            return $this;   
        }

         function group_by($cols)
         {
         	$column = $this->get_column($column);
            $this->ORM_QueryBuilder->group_by($cols);
            return $this;               
         }
         
        function like($column, $value, $side = 'both')
        {
        	$column = $this->get_column($column);
            $this->ORM_QueryBuilder->like($column, $value, $side);
            return $this;           
        }

        function or_like($column, $value, $side = 'both')
        {
        	$column = $this->get_column($column);
            $this->ORM_QueryBuilder->or_like($column, $value, $side);
            return $this;   
        }
        
        function not_like($column, $value, $side = 'both')
        {
        	$column = $this->get_column($column);
            $this->ORM_QueryBuilder->not_like($column, $value, $side);
            return $this;   
        }
        
        function or_not_like($column, $value, $side = 'both')
        {
        	$column = $this->get_column($column);
            $this->ORM_QueryBuilder->or_not_like($column, $value, $side);
            return $this;   
        }

        function order_by($cols, $order = '')
        {
            $this->ORM_QueryBuilder->order_by($cols, $order);
            return $this;               
        }

         function limit($limit)
         {
            $this->ORM_QueryBuilder->limit($limit);
            return $this;           
         }

         function offset($offset)
         {
            $this->ORM_QueryBuilder->offset($offset);
            return $this;           
         }
		 
		 // function join($class, $join_clause = FALSE, $type = '')
		 // { 		 	
		 	// $this->ORM_QueryBuilder->join($class, $join_clause, $type);
            // return $this;
		 // }

		/**
		 * Parsea las columnas para comprobar que existen en la tabla y así aumentamos la capacidad de filtrado de las columnas
		 * añadiéndoles la tabla
		 */
        private function get_column($column)
        {
            if(is_array($column))
            {
                foreach($column as $key => $c)
                {
                    $new_key = $this->get_column($key);
                    
                    $return_data[$new_key] = $c;
                }
                
                return $return_data;
            }
            else
            {
                if(Parser::_has_operator($column))
                {
                    $parsed_data = Parser::_clean_operators($column);
                    $column = $parsed_data['column'];
                    $operator = $parsed_data['operator'];
                }
                else
                {
                    $operator = '=';
                }
    
    
                $col_data = Parser::_strip_column_table($column);
                
    
                $_columns = $this->ORM_QueryBuilder->get_columns();
                
                $_table = $this->ORM_QueryBuilder->get_table();
                
                if($col_data['table'] == NULL)
                {
                        if(array_key_exists($col_data['column'], $_columns))
                        {
                            return $_table.'.'.$_columns[$col_data['column']]['Field'].$operator;
                        }
                }
                else
                {
                    if($_table == $col_data['table'] && (array_key_exists($col_data['column'], $_columns)))
                    {
                         return $_table.'.'.$_columns[$col_data['column']]['Field'].$operator; 
                    }
                    elseif(array_key_exists($col_data['table'], $_join_tables))
                    {
                        return $col_data['table'].'.'.$col_data['column'].$operator;
                    }
                }
    
                self::_exception("The ".$column.' field doesn\'t exist.');
            }
        }

        private function _exception($msg)
        {
        	Warning::exception($msg);
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
           return $this->ORM_QueryBuilder->get($cache);
        }
		
		function save()
		{
			$this->ORM_QueryBuilder->save();
		}
		
		function row()
		{
			return $this->ORM_QueryBuilder->row();
		}
    
    }