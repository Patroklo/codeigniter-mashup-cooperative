<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

    /**
     *  Stores the auth user object and the usual methods dealing with it
     * 
     * 
     */

	class Auth
	{
		private $_logged_user = NULL;
		
		public function __construct()
		{
			
			$this->load->config('auth/ion_auth', TRUE);
			$this->lang->load('ion_auth', '', FALSE, TRUE, '', 'auth');
			$this->load->helper('cookie');
			$this->load->helper('language');
			$this->load->helper('url');
	
			// Load the session, CI2 as a library, CI3 uses it as a driver
			if (substr(CI_VERSION, 0, 1) == '2')
			{
				$this->load->library('session');
			}
			else
			{
				$this->load->driver('session');
			}
	
			$this->load->model('auth/ion_auth_model');

			// auto-login the user if they are remembered
			// only enters if the session it's dead and there are
			// cookies that hold the user data
			
			if (!$this->logged_in() && get_cookie($this->config->item('identity_cookie_name', 'ion_auth')) && get_cookie($this->config->item('remember_cookie_name', 'ion_auth')))
			{
				$this->ion_auth_model->login_remembered_user();
			}
	
			$this->ion_auth_model->trigger_events('library_constructor');
			
			$this->_load_auth();
	
			$this->load->model('auth/auth_hooks_model');
			$this->ion_auth_model->set_hook('post_login_successful', 'load_user_data', $this->auth_hooks_model, 'post_login_successful', array());
		}

		/**
		 * __get
		 *
		 * Allows models to access CI's loaded classes using the same
		 * syntax as controllers.
		 *
		 * @param	string
		 * @access private
		 */
		function __get($key)
		{
			$CI =& get_instance();
			return $CI->$key;
		}
		
		
		private function _check_banned()
		{
			if($this->logged_in())
			{
				if($this->get_auth()->get_data('banned') == 1)
				{
					$this->ion_auth->logout();
				}
			}
		}
		
		private function _load_auth()
		{
			if(is_null($this->_logged_user))
			{
				if($this->logged_in())
				{
					$this->_logged_user = $this->correcaminos->beep('user_object')->where('id', $this->get_user_id())->row();

					$this->_check_banned();
				}
				else
				{
					$this->_logged_user = NULL;
				}
			}
		}

		
		public function get_auth()
		{
			if(is_null($this->_logged_user) and $this->logged_in())
			{
				$this->_load_auth();
			}

			return $this->_logged_user;
		}


		public function login()
		{
			$this->_load_auth();
			$this->_check_banned();
		}
		
		
		/**
		 * ION AUTH LIBRARY FUNCTIONS FOR THE ORM LOGGED USER
		 */

		public function get_user_id()
		{
			$user_id = $this->session->userdata('user_id');
			if (!empty($user_id))
			{
				return $user_id;
			}
			return null;
		}



		public function is_admin()
		{
			if(!$this->logged_in())
			{
				return FALSE;
			}
			
			return $this->get_auth()->is_admin();

		}

		public function in_group($check_group, $check_all = false)
		{
			if(!$this->logged_in())
			{
				return FALSE;
			}

			return $this->get_auth()->in_group($check_group, $check_all);
		}


		public function has_permission($permission, $type = 'reading')
		{
			if(!$this->logged_in())
			{
				return FALSE;
			}
			
			return $this->get_auth()->has_permission($permission, $type);
		}

		/**
		 * logout
		 *
		 * @return void
		 * @author Mathew
		 **/
		public function logout()
		{
			$this->ion_auth_model->trigger_events('logout');
	
			$identity = $this->config->item('identity', 'ion_auth');
			$this->session->unset_userdata( array($identity => '', 'id' => '', 'user_id' => '') );
	
			//delete the remember me cookies if they exist
			if (get_cookie($this->config->item('identity_cookie_name', 'ion_auth')))
			{
				delete_cookie($this->config->item('identity_cookie_name', 'ion_auth'));
			}
			if (get_cookie($this->config->item('remember_cookie_name', 'ion_auth')))
			{
				delete_cookie($this->config->item('remember_cookie_name', 'ion_auth'));
			}
	
			//Destroy the session
			$this->session->sess_destroy();
	
			//Recreate the session
			if (substr(CI_VERSION, 0, 1) == '2')
			{
				$this->session->sess_create();
			}
			else
			{
				$this->session->sess_regenerate(TRUE);
			}
	
			$this->_logged_user = NULL;
	
			$this->ion_auth_model->set_message('logout_successful');
			return TRUE;
		}
	
		/**
		 * logged_in
		 *
		 * @return bool
		 * @author Mathew
		 **/
		public function logged_in()
		{
			$this->ion_auth_model->trigger_events('logged_in');
	
			return (bool) $this->session->userdata('identity');
		}

	}