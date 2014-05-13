<?php namespace Correcaminos\ORM;

    use Correcaminos\Database\QueryBuilder,
    	Correcaminos\Database\Driver,
		Correcaminos\Warning;
    
	
	class ORM_JSON_Parser {
		
		static $tableData = array();
		static $tableName;
	
	
		public static function get_columns($tableName)
		{
			self::$tableName = $tableName;
			
			if(!self::loaded($tableName))
			{
				self::load_table(self::$tableName);
			}
			
			$col_list = array();
			
			foreach(self::$tableData[self::$tableName] as $row)
			{
				$col_list[] = $row;
			}
			
			return $col_list;
		}
	
		//creates the json data table file 
		private static function create_table_file($tableName)
		{
			$queryBuilder = new QueryBuilder();
			
			if(!self::check_table_exists($tableName))
			{
				return FALSE;
			}
			
			$query = $queryBuilder->raw_query('DESCRIBE '. $tableName);
			
			$table_data = $query->result();
			
			$json_data = json_encode($table_data);
			
			self::make_file($tableName, $json_data);
			
			return TRUE;

		}
		
		
		//checks if the table has it's data loaded
		static function loaded()
		{
			if(array_key_exists(self::$tableName, self::$tableData))
			{
				return TRUE;
			}
			return FALSE;
		}


		private static function make_file($tableName, $json_data)
		{
			file_put_contents(self::make_file_path($tableName), $json_data);
		}

		//loads the table data from json file.
		//if it doesn't exist the file, it creates it
		//if table doesn't exist, it throws an exception
		
		//if the regenerate table data it's on, it will make the file every time
		private static function load_table()
		{
			$file_path = self::make_file_path(self::$tableName);
			
			if(!file_exists($file_path) or Driver::$regenerate_table_file_data == TRUE)
			{
				$return = self::create_table_file(self::$tableName);
				
				if($return == FALSE)
				{
					self::_exception('ORM_JSON ERROR: The table '.self::$tableName.' doesn\'t exist in the database.');
				}
			}
			
			$json_data = file_get_contents($file_path);

			self::$tableData[self::$tableName] = json_decode($json_data, TRUE);

			return TRUE;
		}
			
			
		private static function make_file_path()
		{
			return CC_OBJECT_DEFINITION_PATH.'data_table_'.self::$tableName.'.php';
		}

		private static function check_table_exists()
		{
			$queryBuilder = new QueryBuilder();
			$query = $queryBuilder->raw_query('SHOW TABLES LIKE :1', array(':1' => self::$tableName));
			
			if($query->num_rows() == 0)
			{
				return FALSE;
			}
			
			return TRUE;

		}

	    private static function _exception($msg)
	    {
	            Warning::exception($msg);
	    }		

		
	}
