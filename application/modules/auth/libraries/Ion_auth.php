<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once 'Original_ion_auth.php';

	class Ion_auth extends Original_ion_auth
	{
		
		
		private $_logged_user = NULL;
	
		public function __construct()
		{
			parent::__construct();
			$this->load->model('auth/auth_hooks_model');
			$this->ion_auth_model->set_hook('logged_in', 'check_active_user', $this->auth_hooks_model, 'logged_in', array());
			
			$this->get_auth();
			
		}
		
		public function get_auth()
		{
			if(is_null($this->_logged_user))
			{
				if($this->logged_in())
				{
					$this->_logged_user = $this->ion_auth_model->user()->row();
				}
				else
				{
					$this->_logged_user = FALSE;
				}
			}

			return $this->_logged_user;
		}
		/**
		 * in_group
		 *
		 * @param mixed group(s) to check
		 * @param bool user id
		 * @param bool check if all groups is present, or any of the groups
		 *
		 * 		added orm functionality. If user it's logged in and we want to know if it's in certain group, we won't need to 
		 * 		load it's groups again.
		 * 
		 * @return bool
		 * @author Joseba
		 **/
		public function in_group($check_group, $id=false, $check_all = false)
		{
			if(($id === FALSE and $this->logged_in()) or ($id == $this->get_user_id()))
			{
				$groups = $this->get_auth()->get_data('groups');
				foreach($groups as $group)
				{
					$this->_cache_user_in_group[$id][$group->get_data('id')] = $group->get_data('name');
				}
			}
			
			return parent::in_group($check_group, $id, $check_all);
		}

	}