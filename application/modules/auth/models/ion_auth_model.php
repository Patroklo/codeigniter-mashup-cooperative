<?php
	include_once 'original_ion_auth_model.php';
	
	class Ion_auth_model extends Original_ion_auth_model
	{
		

        var $query_object = NULL;
		var $user_object;
		var $group_object;
        
		public function __construct()
		{
			$this->load->config('auth/adapted_ion_auth', TRUE);
			
			$this->user_object = $this->config->item('user_object', 'adapted_ion_auth');
			$this->group_object      = $this->config->item('group_object', 'adapted_ion_auth');
			
			parent::__construct();
		}
		
            /**
             * users
             *
             * @return object Users
             * @author Ben Edmunds
             **/
            public function users($groups = NULL)
            {

                $this->query_object = beep($this->user_object);

                $this->trigger_events('users');
        
                $this->trigger_events('extra_where');
                
                $this->_execute_query();

                return $this;
            }
        
            /**
             * user
             *
             * @return object
             * @author Ben Edmunds
             **/
            public function user($id = NULL)
            {
                $this->trigger_events('user');
        
                //if no id was passed use the current users id
                $id || $id = $this->session->userdata('user_id');
        
                $this->limit(1);
                $this->where('id', $id);
        
                $this->users();
               
                return $this;
            }


            /**
             * groups
             *
             * @return object
             * @author Ben Edmunds
             **/
            public function groups()
            {
                $this->trigger_events('groups');
        
                $this->query_object = beep($this->group_object);
        
                $this->_execute_query();
        
                return $this;
            }



        
            /**
             * group
             *
             * @return object
             * @author Ben Edmunds
             **/
            public function group($id = NULL)
            {
                $this->trigger_events('group');
        
                if (!is_null($id))
                {
                    $this->where('id', $id);
                }
                $this->limit(1);

                return $this->groups();
            }





			public function row()
			{
				$this->trigger_events('row');
		
				$row = reset($this->response);
                
                $this->response = NULL;
                
                return $row;
			}
			
			
			public function result()
			{
				$this->trigger_events('result');

				$result = $this->response;
                
                $this->response = NULL;
                
                return $result;
			}


			public function row_array()
			{
				$this->trigger_events(array('row', 'row_array'));
				
				return $this->row();
			}
		
		
			public function result_array()
			{
				$this->trigger_events(array('result', 'result_array'));
		
				return $this->result();
			}
		
			public function num_rows()
			{
				$this->trigger_events(array('num_rows'));

				$count = count($this->response);
                
                $this->response = NULL;
                
                return $count;
			}

			// why I can't know if an user already logged it's not active?
			// durrrr, lil' retarded if we use active or banned as indicative
			
			public function set_session($user)
			{

				parent::set_session($user);
				
				$session_data = $this->session->all_userdata();
				
				$session_data['active'] = $user->active;
				
				$this->session->set_userdata($session_data);

				return TRUE;
			}


			
			/*********************************************************
			 *********************************************************
			 * 			ADDED FUNCTIONS
			 *********************************************************
			 *********************************************************/
	
	
            /**
             * _execute_query
             *
             * @return object
             * @author Joseba Juaniz
			 * 
			 * 	The querys from users and groups are now the same thanks to the orm correcaminos
			 *  so we'll deploy the code in the same function 
			 * 
             **/
             	
            private function _execute_query()
            {
                //run each where that was passed
                if (isset($this->_ion_where) && !empty($this->_ion_where))
                {
                    foreach ($this->_ion_where as $where)
                    {
                        $this->query_object = $this->query_object->where($where);
                    }
        
                    $this->_ion_where = array();
                }
        
                if (isset($this->_ion_like) && !empty($this->_ion_like))
                {
                    foreach ($this->_ion_like as $like)
                    {
                        $this->query_object = $this->query_object->or_like($like);
                    }
        
                    $this->_ion_like = array();
                }
        
                if (isset($this->_ion_offset))
                {
                    $this->query_object = $this->query_object->offset($this->_ion_offset);
                    
                    $this->_ion_offset = NULL;
                }
                
                if (isset($this->_ion_limit))
                {
                    $this->query_object = $this->query_object->limit($this->_ion_limit);
        
                    $this->_ion_limit  = NULL;
                }
        
                //set the order
                if (isset($this->_ion_order_by) && isset($this->_ion_order))
                {
                    $this->query_object = $this->query_object->order_by($this->_ion_order_by, $this->_ion_order);
        
                    $this->_ion_order    = NULL;
                    $this->_ion_order_by = NULL;
                }
        
                $this->response = $this->query_object->get();
              
                $this->query_object = NULL;
            }
	
	}