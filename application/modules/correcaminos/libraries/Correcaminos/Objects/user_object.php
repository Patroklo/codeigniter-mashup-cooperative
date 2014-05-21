<?php

    class user_object extends Correcaminos\Objects\base{
        
		private $is_admin = NULL;
        
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

			foreach($this->get_data('groups') as $group)
			{
				if($group->get_data('name') == $admin_group)
				{
					$this->is_admin = TRUE;
					return TRUE;
				}
			}

			return $this->is_admin;
			
		}


		public function in_group($check_group, $check_all = false)
		{
			if(!$this->logged_in())
			{
				return FALSE;
			}

			if (!is_array($check_group))
			{
				$check_group = array($check_group);
			}
	
			$groups_array = $this->get_data('groups');

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




    }