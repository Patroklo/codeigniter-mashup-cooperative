<?php 

use Correcaminos\Database\QueryBuilder;
	

	class CI_db_parser
	{
		
		var $queryString = NULL;
		var $insert_id = NULL;
		
		function __construct()
		{
			$this->_reset_query_string();
			
			$CI =& get_instance();
			$CI->db = $this;
		}


		private function _reset_query_string()
		{
			$this->queryString = new QueryBuilder();
		}

        /**
         * ========================================================================================================
         * ========================================================================================================
         *              SQL FUNCTIONS
         * ========================================================================================================
         * ========================================================================================================
         */ 

		//calls all the methods of the querybuilder
		public function __call($method, $args)
		{
			
			if(method_exists($this->queryString, $method))
			{
				$this->queryString = call_user_func_array(array($this->queryString,$method), $args);
				return $this;
			}

		}
		
		public function get($table = NULL, $cache = NULL)
		{
			if(!is_null($table))
			{
				$this->queryString = $this->queryString->from($table);
			}
			
			$return_data = $this->queryString->get($cache);
			$this->_reset_query_string();
			return $return_data;
		}
		
		public function get_where($table, $data, $cache = NULL)
		{
			$this->queryString = $this->queryString->where($data);
			
			return $this->get($table, $cache);
		}
		
		public function order_by($column, $type = NULL)
		{
			
			if(is_null($type))
			{
				$column = explode(',',$column);
				
				foreach($column as $c)
				{
					$data = explode(' ', $c);
					
					$this->queryString = $this->queryString->order_by($data[0], $data[1]);
				}
				
			}
			else
			{
				$this->queryString = $this->queryString->order_by($column, $type);
			}

			return $this;
		}
		
		public function affected_rows()
		{
			$CI =& get_instance();
			return $CI->correcaminos->affected_rows();
		}
		
		
		public function insert($table, $data)
		{
			$CI =& get_instance();
			$this->insert_id = $CI->correcaminos->insert($table, $data);
			return $this->insert_id;
		}
		
		public function delete($table, $where = NULL)
		{
			$CI =& get_instance();
			
			if(!is_null($where))
			{
				return $CI->correcaminos->delete($table, $where);
			}
			else
			{
				return $this->queryString->From($table)->delete();
			}
		}
		
		public function update($table, $data, $where)
		{
			$CI =& get_instance();
						
			if(!is_null($where))
			{
				return $CI->correcaminos->update($table, $data, $where);
			}
			else
			{
				return $this->queryString->From($table)->values($data)->update();
			}
		}
		
		
		public function count_all_results($table)
		{
			$query = $this->get($table);
			
			return $query->num_rows();
		}
		
		
		public function insert_id()
		{
			return $this->insert_id;
		}

		public function escape_str($string)
		{
			return $string;
		}

		public function query($sql)
		{
			$return_data =  $this->queryString->raw_query($sql);
			$this->_reset_query_string();
			return $return_data;
		}

        /**
         * ========================================================================================================
         * ========================================================================================================
         *              TRANSACTION FUNCTIONS
         * ========================================================================================================
         * ========================================================================================================
         */ 
         
         		
			public function trans_begin()
			{
				$CI =& get_instance();
				$CI->correcaminos->begin_transaction();
			}
			
			public function trans_status()
			{
				$CI =& get_instance();
				return $CI->correcaminos->transaction_status();
			}
			
			public function trans_commit()
			{
				$CI =& get_instance();
				$CI->correcaminos->commit_transaction();
			}
			
			public function trans_rollback()
			{
				$CI =& get_instance();
				$CI->correcaminos->rollback_transaction();
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
			$CI =& get_instance();
			return $CI->correcaminos->column_exists($table, $column);
		}
		
		function list_fields($table)
		{
			$CI =& get_instance();
			$column_data =  $CI->correcaminos->list_fields($table);
			

			$return_data = array_reduce($column_data, function($result, $i)
														{
															$result[] = $i->Field;
															return $result;
														}, array());
														
			return $return_data;
			
		}
				
	}
