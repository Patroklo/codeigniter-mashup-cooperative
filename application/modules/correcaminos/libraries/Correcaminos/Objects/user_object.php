<?php

    class user_object extends Correcaminos\Objects\base{
        
		private $is_admin = NULL;
		private $group_names;
        
		private $permissions;
		
        static function _classData()
        {
            return array('tableName' => 'users',
	                     'joins' => array('groups'	=> array('loading_type'			=> 'lazy',
	             											 'type'					=> 'OneToMany',
	             											 'target'				=> 'group_object',
	             											 'columnName'			=> 'id',
	             											 'referencedColumnName'	=> 'id',
	             											 'intermediateTable'	=> 'users_groups',
	  														 'intermediateColumnName' => 'user_id',
	   														 'intermediatereferencedColumnName' => 'group_id'
				 											)
						 ),
		  				/* 'files' => array( 'user_photo_object' 	=> array('directory' => 'foto_usuario',
																	 	 'className' => 'Prueba',
																		 'rules'	 =>  array(
																			                     'field'   => 'userfile',
																			                     'label'   => 'userfile',
																			                     'rules'   => 'required'
																			                  )
            
																		)),  */                                                                                                                                                                                                                                                                  
                         'primary_column' => 'id');
        }


		function get_name()
		{
			return $this->_data->username;
		}
		
	
		function is_admin()
		{
			
			if(!is_null($this->is_admin))
			{
				return $this->is_admin;
			}
			
			$CI =& get_instance();

			$admin_group = $CI->config->item('admin_group', 'ion_auth');
			
			$this->is_admin = FALSE;
			
			$groups = $this->__get_group_names();
			
			if(in_array($admin_group, $groups))
			{
				$this->is_admin = TRUE;
			}

			return $this->is_admin;
			
		}

		private function __get_group_names()
		{
			
			if(is_null($this->group_names))
			{
				$this->group_names = array();
				
				foreach($this->get_data('groups') as $group)
				{
					$this->group_names[$group->get_data('id')] = $group->get_data('name');
				}
			}
			
			return $this->group_names;
		}

		public function in_group($check_group, $check_all = false)
		{

			if (!is_array($check_group))
			{
				$check_group = array($check_group);
			}
	
			$groups_array = $this->__get_group_names();

			foreach ($check_group as $key => $value)
			{
				$groups = (is_string($value)) ? $groups_array : array_keys($groups_array);
	
				/**
				 * if !all (default), in_array
				 * if all, !in_array
				 */
				if (in_array($value, $groups) xor $check_all)
				{
					/**
					 * if !all (default), true
					 * if all, false
					 */
					return !$check_all;
				}
			}
	
			/**
			 * if !all (default), false
			 * if all, true
			 */
			return $check_all;
		}
		
		public function has_permission($permission, $type = 'reading')
		{
			
			if($this->is_admin())
			{
				return TRUE;
			}
			
			$select_values = array('no permission' 	=> 0,
								   'reading'		=> 1,
								   'writting'		=> 2);
								   
			$permission_key = $select_values[$type];
			
			if(is_null($this->permissions))
			{
				$CI =& get_instance();
				
				$groups = $this->__get_group_names();
				
				$group_ids = array_keys($groups);
				
				$permission_list = $CI->ion_auth_model->get_user_permissions($this->get_data('id'), $group_ids);
				
				foreach($permission_list as $permission_data)
				{
					$this->permissions[$permission_data->name] = $permission_data->permission_level;
				}
			}
			
			if(array_key_exists($permission, $this->permissions))
			{
				if ($this->permissions[$permission] >= $permission_key)
				{
					return TRUE;
				}
			}
			
			return FALSE;

		}




    }