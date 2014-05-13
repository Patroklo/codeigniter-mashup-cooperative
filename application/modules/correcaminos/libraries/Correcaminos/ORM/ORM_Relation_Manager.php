<?php namespace Correcaminos\ORM;

	use	Correcaminos\ORM\ORM_QueryBuilder,
		Correcaminos\ORM\MemoryManager,
		Correcaminos\Database\QueryBuilder;

	class ORM_Relation_Manager {
		
		var $relationObject = NULL;
		
		function __construct($join_data)
		{

			$referencedClassData = MemoryManager::get_class_data($join_data['target']);
			$join_data['destiny_table'] = $referencedClassData->get_table();
		 	$join_data['destinyPrimaryColumn'] = $referencedClassData->get_primary_column();

            if(array_key_exists('intermediateTable', $join_data))
            {
                if($join_data['type'] == 'OneToOne')
                {
                    $this->relationObject = new ORM_Relation_OneToOne_intermediate($join_data);
                }
                elseif($join_data['type'] == 'OneToMany')
                {
                    $this->relationObject = new ORM_Relation_OneToMany_intermediate($join_data);
                }
                elseif($join_data['type'] == 'ManyToMany')
                {
                    $this->relationObject = new ORM_Relation_ManyToMany_intermediate($join_data);
                }
            }
            else
            {
                if($join_data['type'] == 'OneToOne')
                {
                    $this->relationObject = new ORM_Relation_OneToOne($join_data);
                }
                elseif($join_data['type'] == 'OneToMany')
                {
                    $this->relationObject = new ORM_Relation_OneToMany($join_data);
                }
            }
		}
		
		public function add_QueryBuilder($queryBuilder)
		{
			$this->relationObject->add_QueryBuilder($queryBuilder);
		}
		
		function add_index($id)
		{
			$this->relationObject->add_index($id);
		}
		
		public function get_data()
		{
			return $this->relationObject->get_data();
		}
		
		
	}
	
	class ORM_Relation {
		
		protected $queryBuilder		= NULL;
		protected $ORM_QueryBuilder = NULL;
		protected $join_data		= NULL;
		protected $id				= NULL;
		protected $ready			= FALSE;
		
		
		function __construct($join_data)
		{
			$this->join_data = $join_data;
		}
		
		public function add_QueryBuilder($queryBuilder)
		{
			$this->queryBuilder = $queryBuilder;
			$this->generate_query();
			$this->ready = TRUE;
		}
		
		function add_index($id)
		{
			$this->id = $id;
			$this->generate_query();
			$this->ready = TRUE;
		}
		
		
		protected function _add_join()
		{
				$this->queryBuilder = $this->queryBuilder->join($this->join_data['destiny_table'], 
																$this->join_data['origin_table'].'.'.$this->join_data['columnName'].' = '.$this->join_data['destiny_table'].'.'.$this->join_data['referencedColumnName']);
			
		}
		
		protected function generate_query()
		{
			
			$this->ORM_QueryBuilder = new ORM_QueryBuilder();
			$this->ORM_QueryBuilder->_eager_loading(false);
			$this->ORM_QueryBuilder = $this->ORM_QueryBuilder->From($this->join_data['target']);
			
			if(!is_null($this->queryBuilder))
			{
				$this->_add_join();
				$this->ORM_QueryBuilder->_set_query($this->queryBuilder);
			}
			
			if(!is_null($this->id))
			{
				if(is_array($this->id))
				{
					$this->ORM_QueryBuilder = $this->ORM_QueryBuilder->where_in($this->join_data['destiny_table'].'.'.$this->join_data['referencedColumnName'], $this->id);
				}
				else
				{
					$this->ORM_QueryBuilder = $this->ORM_QueryBuilder->where($this->join_data['destiny_table'].'.'.$this->join_data['referencedColumnName'], $this->id);
				}
			}
		}
		
		public function get_data()
		{
			if($this->ready == FALSE)
			{
				return FALSE;
			}

			return $this->ORM_QueryBuilder->get();
		}
		
	}
	
	
	class ORM_Relation_OneToOne extends ORM_Relation {
		
		public function get_data()
		{
			
			$this->ORM_QueryBuilder = $this->ORM_QueryBuilder->limit(1);
			
			$data = parent::get_data();
			
			if($data === FALSE)
			{
				return FALSE;
			}
			
			$data = reset($data);
			
			return array($data->get_data($this->join_data['referencedColumnName']) => $data);

		}
		
	}	
	
	class ORM_Relation_OneToMany extends ORM_Relation {
		
		public function get_data()
		{
			$data = parent::get_data();
			
			if($data === FALSE)
			{
				return FALSE;
			}
			
			$return_data = array();
			
			foreach($data as $d)
			{
				$return_data[$d->get_data($this->join_data['referencedColumnName'])][] = $d;
			}

			return $return_data;
		}
		
	}
	
    
    
    
    
    class ORM_Relation_intermediate extends ORM_Relation 
    {
        protected function _add_join()
        {}  
        
        protected function generate_query()
        {
            
            $this->ORM_QueryBuilder = new ORM_QueryBuilder();
            $this->ORM_QueryBuilder->_eager_loading(false);
            $this->ORM_QueryBuilder = $this->ORM_QueryBuilder->From($this->join_data['target']);
            
            if(!is_null($this->queryBuilder))
            {
                $this->queryBuilder = $this->queryBuilder->join($this->join_data['intermediateTable'], 
                                                                $this->join_data['origin_table'].'.'.$this->join_data['columnName'].' = '.$this->join_data['intermediateTable'].'.'.$this->join_data['intermediateColumnName']);
                
                $this->queryBuilder = $this->queryBuilder->join($this->join_data['destiny_table'], 
                                                                $this->join_data['intermediateTable'].'.'.$this->join_data['intermediatereferencedColumnName'].' = '.$this->join_data['destiny_table'].'.'.$this->join_data['referencedColumnName']);

            }
            else 
            {
                $this->queryBuilder = new QueryBuilder();
                
                $this->queryBuilder = $this->queryBuilder->from($this->join_data['intermediateTable']);
                
                $this->queryBuilder = $this->queryBuilder->join($this->join_data['destiny_table'], 
                                                                $this->join_data['intermediateTable'].'.'.$this->join_data['intermediatereferencedColumnName'].' = '.$this->join_data['destiny_table'].'.'.$this->join_data['referencedColumnName']);
                
            }
            
            $this->ORM_QueryBuilder->_set_query($this->queryBuilder);
            
            if(!is_null($this->id))
            {
                if(is_array($this->id))
                {
                    $this->ORM_QueryBuilder = $this->ORM_QueryBuilder->where_in($this->join_data['intermediateTable'].'.'.$this->join_data['intermediateColumnName'], $this->id);
                }
                else
                {
                    $this->ORM_QueryBuilder = $this->ORM_QueryBuilder->where($this->join_data['intermediateTable'].'.'.$this->join_data['intermediateColumnName'], $this->id);
                }
            }
            
        }  

        public function get_data()
        {
            if($this->ready == FALSE)
            {
                return FALSE;
            }

            //if intermediate table has columns, we add it into the result object
            $intermediate_columns =  MemoryManager::get_table_data($this->join_data['intermediateTable']);
            
            $col_list = array();

            foreach($intermediate_columns as $co)
            {
                if($co['Field'] != $this->join_data['intermediatereferencedColumnName'] and 
                    $co['Field'] != $this->join_data['intermediateColumnName'] and
                    $co['Key'] != 'PRI')
                {
                    $col_list[] = $co['Field']; 
                }
            }


            if(!empty($col_list))
            {
                $this->ORM_QueryBuilder->_add_columns($col_list);
            }
            

            return $this->ORM_QueryBuilder->get_grouped($this->join_data['intermediateTable'].'.'.$this->join_data['intermediateColumnName']);
        }
    
 
    }
    
    
    
    class ORM_Relation_OneToOne_intermediate extends ORM_Relation_intermediate {
        
        public function get_data()
        {
            
            $this->ORM_QueryBuilder = $this->ORM_QueryBuilder->limit(1);
            
            $data = parent::get_data();
            
            if($data === FALSE)
            {
                return FALSE;
            }
            

            reset($data);
            $key = key($data);
            $data = reset($data); 

            return array($key => $data);

        }
        
    }   
    
    class ORM_Relation_OneToMany_intermediate extends ORM_Relation_intermediate {
        
        public function get_data()
        {

            $data = parent::get_data();

            
            if($data === FALSE)
            {
                return FALSE;
            }
            
            $return_data = array();
            
            foreach($data as $key => $d)
            {
                $return_data[$key] = $d;
            }

            return $return_data;
        }
        
    } 
    
    
	/* 'joins' => array('departments'	=> array('loading_type'			=> 'eager',
						 'type'					=> 'ManyToMany',
						 'target'				=> 'department_object',
						 'columnName'			=> 'emp_no',
						 'referencedColumnName'	=> 'dept_no',
						 'intermediateTable'	=> 'dept_emp',
						 'intermediateColumnName' => 'empt_no',
						 'intermediatereferencedColumnName' => 'dept_no'
						)*/
	class ORM_Relation_ManyToMany_intermediate extends ORM_Relation_intermediate {
			


		public function get_data()
		{
		    
            $data = parent::get_data();

			if($data === FALSE)
			{
				return FALSE;
			}
			
			$return_data = array();
			
			foreach($data as $key => $d)
			{
				$return_data[$key] = $d;
			}

			return $return_data;
		}


	}
	