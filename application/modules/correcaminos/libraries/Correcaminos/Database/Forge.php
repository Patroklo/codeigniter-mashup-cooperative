<?php namespace Correcaminos\Database;
    
    use Correcaminos\Warning,
    	Correcaminos\Database\QueryBuilder,
        Correcaminos\Database\Result,
        Correcaminos\Database\Forge;
		
	class Forge{
		
		
		private $_main_table = NULL;
		
		function __construct($tableName)
		{
			$this->_main_table = $tableName;
		}
		
		
		 function column_exists($column)
         {
            $sql = 'SHOW COLUMNS FROM '.$this->_main_table.' LIKE :1';

            $values = array(':1' => $column);
           
			$query = new QueryBuilder();
			
			$result = $query->raw_query($sql, $values);
		   
            if($result->num_rows() > 0)
            {
                return TRUE;
            }
            else
            {
                return FALSE;
            }    
         }
		 
		 function columns()
		 {
		 	$sql = 'SHOW COLUMNS FROM '.$this->_main_table;
			
			$values = array();
			
			$query = new QueryBuilder();
			
			$return_data = $query->raw_query($sql);
			
			return $return_data->result();

		 }
		
		
	}