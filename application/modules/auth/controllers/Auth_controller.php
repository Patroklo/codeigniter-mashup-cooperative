<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Auth_controller extends MY_Controller {

	var $data;

	function __construct()
	{
		parent::__construct();
		$this->load->library('auth/Auth');
		$this->load->model('auth/Ion_auth_model');
		$this->load->library('form_validation');
		$this->load->helper('url');


		$this->form_validation->set_error_delimiters($this->config->item('error_start_delimiter', 'ion_auth'), $this->config->item('error_end_delimiter', 'ion_auth'));

		$this->lang->load('auth');
		$this->load->helper('language');
	}



	//create a new user
	function create_user()
	{
		
		$this->data['title'] = "Create User";
		
		$this->load->library('auth/ion_auth');

		$tables = $this->config->item('tables','ion_auth');

		//validate form input
		$this->form_validation->set_rules('first_name', $this->lang->line('create_user_validation_fname_label'), 'required|xss_clean');
		$this->form_validation->set_rules('last_name', $this->lang->line('create_user_validation_lname_label'), 'required|xss_clean');
		$this->form_validation->set_rules('email', $this->lang->line('create_user_validation_email_label'), 'required|valid_email|is_unique['.$tables['users'].'.email]');
		$this->form_validation->set_rules('phone', $this->lang->line('create_user_validation_phone_label'), 'required|xss_clean');
		$this->form_validation->set_rules('company', $this->lang->line('create_user_validation_company_label'), 'required|xss_clean');
		$this->form_validation->set_rules('password', $this->lang->line('create_user_validation_password_label'), 'required|min_length[' . $this->config->item('min_password_length', 'ion_auth') . ']|max_length[' . $this->config->item('max_password_length', 'ion_auth') . ']|matches[password_confirm]');
		$this->form_validation->set_rules('password_confirm', $this->lang->line('create_user_validation_password_confirm_label'), 'required');

		if ($this->form_validation->run() == true)
		{
			$username = strtolower($this->input->post('first_name')) . ' ' . strtolower($this->input->post('last_name'));
			$email    = strtolower($this->input->post('email'));
			$password = $this->input->post('password');

			$additional_data = array(
				'first_name' => $this->input->post('first_name'),
				'last_name'  => $this->input->post('last_name'),
				'company'    => $this->input->post('company'),
				'phone'      => $this->input->post('phone'),
			);
		}
		if ($this->form_validation->run() == true && $this->ion_auth->register($username, $password, $email, $additional_data))
		{
			//check to see if we are creating the user
			//redirect them back to the admin page
			$this->session->set_flashdata('message', $this->ion_auth_model->messages());
			redirect(base_url(), 'refresh');
		}
		else
		{
			//display the create user form
			//set the flash data error message if there is one
			$this->data['message'] = (validation_errors() ? validation_errors() : ($this->ion_auth_model->errors() ? $this->ion_auth_model->errors() : $this->session->flashdata('message')));

			$this->data['first_name'] = array(
				'name'  => 'first_name',
				'id'    => 'first_name',
				'type'  => 'text',
				'value' => $this->form_validation->set_value('first_name'),
			);
			$this->data['last_name'] = array(
				'name'  => 'last_name',
				'id'    => 'last_name',
				'type'  => 'text',
				'value' => $this->form_validation->set_value('last_name'),
			);
			$this->data['email'] = array(
				'name'  => 'email',
				'id'    => 'email',
				'type'  => 'text',
				'value' => $this->form_validation->set_value('email'),
			);
			$this->data['company'] = array(
				'name'  => 'company',
				'id'    => 'company',
				'type'  => 'text',
				'value' => $this->form_validation->set_value('company'),
			);
			$this->data['phone'] = array(
				'name'  => 'phone',
				'id'    => 'phone',
				'type'  => 'text',
				'value' => $this->form_validation->set_value('phone'),
			);
			$this->data['password'] = array(
				'name'  => 'password',
				'id'    => 'password',
				'type'  => 'password',
				'value' => $this->form_validation->set_value('password'),
			);
			$this->data['password_confirm'] = array(
				'name'  => 'password_confirm',
				'id'    => 'password_confirm',
				'type'  => 'password',
				'value' => $this->form_validation->set_value('password_confirm'),
			);

			$this->_render_page('auth/auth/create_user', $this->data);
		}
	}


	//log the user in
	function login()
	{
		$this->data['title'] = "Login";

		//validate form input
		$this->form_validation->set_rules('identity', 'Identity', 'required');
		$this->form_validation->set_rules('password', 'Password', 'required');

		if ($this->form_validation->run() == true)
		{
			//check to see if the user is logging in
			//check for "remember me"
			$remember = (bool) $this->input->post('remember');

			if ($this->ion_auth_model->login($this->input->post('identity'), $this->input->post('password'), $remember))
			{
				//if the login is successful
				//redirect them back to the home page
				$this->session->set_flashdata('message', $this->ion_auth_model->messages());
				redirect(base_url(), 'refresh');
			}
			else
			{
				//if the login was un-successful
				//redirect them back to the login page
				$this->session->set_flashdata('message', $this->ion_auth_model->errors());
				redirect(current_url(), 'refresh'); //use redirects instead of loading views for compatibility with MY_Controller libraries
			}
		}
		else
		{
			//the user is not logging in so display the login page
			//set the flash data error message if there is one
			$this->data['message'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('message');

			$this->data['identity'] = array('name' => 'identity',
				'id' => 'identity',
				'type' => 'text',
				'value' => $this->form_validation->set_value('identity'),
			);
			$this->data['password'] = array('name' => 'password',
				'id' => 'password',
				'type' => 'password',
			);

			$this->_render_page('auth/auth/login', $this->data);
		}
	}

	//log the user out
	function logout()
	{
		$this->data['title'] = "Logout";

		//log the user out
		$logout = $this->auth->logout();

		//redirect them to the login page
		$this->session->set_flashdata('message', $this->ion_auth_model->messages());
		redirect(base_url(), 'refresh');
	}


	//forgot password
	function forgot_password()
	{
		$this->form_validation->set_rules('email', $this->lang->line('forgot_password_validation_email_label'), 'required|valid_email');
		if ($this->form_validation->run() == false)
		{
			//setup the input
			$this->data['email'] = array('name' => 'email',
				'id' => 'email',
			);

			if ( $this->config->item('identity', 'ion_auth') == 'username' ){
				$this->data['identity_label'] = $this->lang->line('forgot_password_username_identity_label');
			}
			else
			{
				$this->data['identity_label'] = $this->lang->line('forgot_password_email_identity_label');
			}

			//set any errors and display the form
			$this->data['message'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('message');
			$this->_render_page('auth/auth/forgot_password', $this->data);
		}
		else
		{
			
			$this->load->library('auth/ion_auth');

			$identity = $this->ion_auth_model->users()->where($this->config->item('identity', 'ion_auth'), strtolower($this->input->post('email')))->row();
			
	        if (empty($identity)) 
	        {
		    	$this->ion_auth->set_message('forgot_password_email_not_found');
		    	$this->session->set_flashdata('message', $this->ion_auth->messages());
                redirect(current_url(), 'refresh');
            }

			//run the forgotten password method to email an activation code to the user
			$forgotten = $this->ion_auth->forgotten_password($identity->get_data($this->config->item('identity', 'ion_auth')));
			
			if ($forgotten)
			{
				//if there were no errors
				$this->session->set_flashdata('message', $this->ion_auth_model->messages());
				redirect(Route::named('login'), 'refresh'); //we should display a confirmation page here instead of the login page
			}
			else
			{
				$this->session->set_flashdata('message', $this->ion_auth_model->errors());
				redirect(Route::named('forgot_password'), 'refresh');
			}
		}
	}

	//reset password - final step for forgotten password
	public function reset_password()
	{
		if (!$this->uri->segment('code'))
		{
			show_404();
		}
		else
		{
			$code = $this->uri->segment('code');
		}

 		$this->load->library('auth/ion_auth');

		$user = $this->ion_auth->forgotten_password_check($code);

		if ($user)
		{
			//if the code is valid then display the password reset form

			$this->form_validation->set_rules('new', $this->lang->line('reset_password_validation_new_password_label'), 'required|min_length[' . $this->config->item('min_password_length', 'ion_auth') . ']|max_length[' . $this->config->item('max_password_length', 'ion_auth') . ']|matches[new_confirm]');
			$this->form_validation->set_rules('new_confirm', $this->lang->line('reset_password_validation_new_password_confirm_label'), 'required');

			if ($this->form_validation->run() == false)
			{
				//display the form

				//set the flash data error message if there is one
				$this->data['message'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('message');

				$this->data['min_password_length'] = $this->config->item('min_password_length', 'ion_auth');
				$this->data['new_password'] = array(
					'name' => 'new',
					'id'   => 'new',
				'type' => 'password',
					'pattern' => '^.{'.$this->data['min_password_length'].'}.*$',
				);
				$this->data['new_password_confirm'] = array(
					'name' => 'new_confirm',
					'id'   => 'new_confirm',
					'type' => 'password',
					'pattern' => '^.{'.$this->data['min_password_length'].'}.*$',
				);
				$this->data['user_id'] = array(
					'name'  => 'user_id',
					'id'    => 'user_id',
					'type'  => 'hidden',
					'value' => $user->get_data('id'),
				);
				// $this->data['csrf'] = $this->_get_csrf_nonce();
				$this->data['code'] = $code;

				//render
				$this->_render_page('auth/auth/reset_password', $this->data);
			}
			else
			{
				// do we have a valid request?
				// if ($this->_valid_csrf_nonce() === FALSE || $user->get_data('id') != $this->input->post('user_id'))
				if ($user->get_data('id') != $this->input->post('user_id'))
				{

					//something fishy might be up
					$this->ion_auth_model->clear_forgotten_password_code($code);

					show_error($this->lang->line('error_csrf'));

				}
				else
				{
					// finally change the password
					$identity = $user->get_data($this->config->item('identity', 'ion_auth'));

					$change = $this->ion_auth_model->reset_password($identity, $this->input->post('new'));

					if ($change)
					{
						//if the password was successfully changed
						$this->session->set_flashdata('message', $this->ion_auth_model->messages());
						$this->logout();
					}
					else
					{
						$this->session->set_flashdata('message', $this->ion_auth_model->errors());
						redirect(Route::named('reset_password', array($code)) , 'refresh');
					}
				}
			}
		}
		else
		{
			//if the code is invalid then send them back to the forgot password page
			$this->session->set_flashdata('message', $this->ion_auth_model->errors());
			redirect(Route::named('forgot_password'), 'refresh');
		}
	}



	//change password
	function change_password()
	{

		$this->form_validation->set_rules('old', $this->lang->line('change_password_validation_old_password_label'), 'required');
		$this->form_validation->set_rules('new', $this->lang->line('change_password_validation_new_password_label'), 'required|min_length[' . $this->config->item('min_password_length', 'ion_auth') . ']|max_length[' . $this->config->item('max_password_length', 'ion_auth') . ']|matches[new_confirm]');
		$this->form_validation->set_rules('new_confirm', $this->lang->line('change_password_validation_new_password_confirm_label'), 'required');

		if ($this->form_validation->run() == false)
		{
			//display the form
			//set the flash data error message if there is one
			$this->data['message'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('message');

			$this->data['min_password_length'] = $this->config->item('min_password_length', 'ion_auth');
			$this->data['old_password'] = array(
				'name' => 'old',
				'id'   => 'old',
				'type' => 'password',
			);
			$this->data['new_password'] = array(
				'name' => 'new',
				'id'   => 'new',
				'type' => 'password',
				'pattern' => '^.{'.$this->data['min_password_length'].'}.*$',
			);
			$this->data['new_password_confirm'] = array(
				'name' => 'new_confirm',
				'id'   => 'new_confirm',
				'type' => 'password',
				'pattern' => '^.{'.$this->data['min_password_length'].'}.*$',
			);
			$this->data['user_id'] = array(
				'name'  => 'user_id',
				'id'    => 'user_id',
				'type'  => 'hidden',
				'value' => $this->auth->get_user_id(),
			);

			//render
			$this->_render_page('auth/auth/change_password', $this->data);
		}
		else
		{
			$identity = $this->session->userdata('identity');

			$change = $this->ion_auth_model->change_password($identity, $this->input->post('old'), $this->input->post('new'));

			if ($change)
			{
				//if the password was successfully changed
				$this->session->set_flashdata('message', $this->ion_auth_model->messages());
				$this->logout();
			}
			else
			{
				$this->session->set_flashdata('message', $this->ion_auth_model->errors());
				redirect(current_url(), 'refresh');
			}
		}
	}

	//activate the user
	function activate()
	{
		$id = $this->uri->segment('id');
		$code = $this->uri->segment('code');

		if ($code !== NULL)
		{
			$activation = $this->ion_auth_model->activate($id, $code);
		}
		else if ($this->auth->is_admin())
		{
			$activation = $this->ion_auth_model->activate($id);
		}

		if ($activation)
		{
			//redirect them to the auth page
			$this->session->set_flashdata('message', $this->ion_auth_model->messages());
			redirect(base_url(), 'refresh');
		}
		else
		{
			//redirect them to the forgot password page
			$this->session->set_flashdata('message', $this->ion_auth_model->errors());
			redirect(Route::named('forgot_password'), 'refresh');
		}
	}
	

	//deactivate the user
	function deactivate($id)
	{
		$id =  $this->uri->segment('id');
		
		$this->load->library('form_validation');
		$this->form_validation->set_rules('confirm', $this->lang->line('deactivate_validation_confirm_label'), 'required');
		$this->form_validation->set_rules('id', $this->lang->line('deactivate_validation_user_id_label'), 'required|alpha_numeric');

		if ($this->form_validation->run() == FALSE)
		{
			// insert csrf check
			// $this->data['csrf'] = $this->_get_csrf_nonce();
			$this->data['user'] = $this->ion_auth_model->users()->where('id', $id)->row();

			$this->_render_page('auth/auth/deactivate_user', $this->data);
		}
		else
		{
			// do we really want to deactivate?
			if ($this->input->post('confirm') == 'yes')
			{
				// do we have a valid request?
				// if ($this->_valid_csrf_nonce() === FALSE || $id != $this->input->post('id'))
				if ($id != $this->input->post('id'))
				{
					show_error($this->lang->line('error_csrf'));
				}

				// do we have the right userlevel?
				if ($this->auth->logged_in() && $this->auth->is_admin())
				{
					$this->ion_auth_model->deactivate($id);
				}
			}

			//redirect them back to the auth page
			redirect(base_url(), 'refresh');
		}
	}


	//edit a user
	function edit_user()
	{
		$id = $this->uri->segment('id');
		
		if(is_null($id))
		{
			$id = $this->auth->get_user_id();
		}
		
		$this->data['title'] = "Edit User";

		if (!$this->auth->logged_in() || (!$this->auth->is_admin() && !($this->auth->get_user_id() == $id)))
		{
			redirect(base_url(), 'refresh');
		}

		$user = $this->ion_auth_model->users()->where('id', $id)->row();
		$groups=$this->ion_auth_model->groups()->get('array');
		$currentGroups = $this->ion_auth_model->get_users_groups($id)->result();

		//validate form input
		$this->form_validation->set_rules('first_name', $this->lang->line('edit_user_validation_fname_label'), 'required|xss_clean');
		$this->form_validation->set_rules('last_name', $this->lang->line('edit_user_validation_lname_label'), 'required|xss_clean');
		$this->form_validation->set_rules('phone', $this->lang->line('edit_user_validation_phone_label'), 'required|xss_clean');
		$this->form_validation->set_rules('company', $this->lang->line('edit_user_validation_company_label'), 'required|xss_clean');
		$this->form_validation->set_rules('groups', $this->lang->line('edit_user_validation_groups_label'), 'xss_clean');

		if (isset($_POST) && !empty($_POST))
		{
			// do we have a valid request?
			// if ($this->_valid_csrf_nonce() === FALSE || $id != $this->input->post('id'))
			if ($id != $this->input->post('id'))
			{
				show_error($this->lang->line('error_csrf'));
			}

			$data = array(
				'first_name' => $this->input->post('first_name'),
				'last_name'  => $this->input->post('last_name'),
				'company'    => $this->input->post('company'),
				'phone'      => $this->input->post('phone'),
			);

			// Only allow updating groups if user is admin
			if ($this->auth->is_admin())
			{
				//Update the groups user belongs to
				$groupData = $this->input->post('groups');

				if (isset($groupData) && !empty($groupData)) {

					$this->ion_auth_model->remove_from_group('', $id);

					foreach ($groupData as $grp) {
						$this->ion_auth_model->add_to_group($grp, $id);
					}

				}
			}

			//update the password if it was posted
			if ($this->input->post('password'))
			{
				$this->form_validation->set_rules('password', $this->lang->line('edit_user_validation_password_label'), 'required|min_length[' . $this->config->item('min_password_length', 'ion_auth') . ']|max_length[' . $this->config->item('max_password_length', 'ion_auth') . ']|matches[password_confirm]');
				$this->form_validation->set_rules('password_confirm', $this->lang->line('edit_user_validation_password_confirm_label'), 'required');

				$data['password'] = $this->input->post('password');
			}

			if ($this->form_validation->run() === TRUE)
			{
				$this->ion_auth_model->update($user->get_data('id'), $data);

				//check to see if we are creating the user
				//redirect them back to the admin page
				$this->session->set_flashdata('message', "User Saved");

				redirect('/', 'refresh');
				
			}
		}

		//display the edit user form
		// $this->data['csrf'] = $this->_get_csrf_nonce();

		//set the flash data error message if there is one
		$this->data['message'] = (validation_errors() ? validation_errors() : ($this->ion_auth_model->errors() ? $this->ion_auth_model->errors() : $this->session->flashdata('message')));

		//pass the user to the view
		$this->data['user'] = $user;
		$this->data['groups'] = $groups;
		$this->data['currentGroups'] = $currentGroups;

		$this->data['first_name'] = array(
			'name'  => 'first_name',
			'id'    => 'first_name',
			'type'  => 'text',
			'value' => $this->form_validation->set_value('first_name', $user->get_data('first_name')),
		);
		$this->data['last_name'] = array(
			'name'  => 'last_name',
			'id'    => 'last_name',
			'type'  => 'text',
			'value' => $this->form_validation->set_value('last_name', $user->get_data('last_name')),
		);
		$this->data['company'] = array(
			'name'  => 'company',
			'id'    => 'company',
			'type'  => 'text',
			'value' => $this->form_validation->set_value('company', $user->get_data('company')),
		);
		$this->data['phone'] = array(
			'name'  => 'phone',
			'id'    => 'phone',
			'type'  => 'text',
			'value' => $this->form_validation->set_value('phone', $user->get_data('phone')),
		);
		$this->data['password'] = array(
			'name' => 'password',
			'id'   => 'password',
			'type' => 'password'
		);
		$this->data['password_confirm'] = array(
			'name' => 'password_confirm',
			'id'   => 'password_confirm',
			'type' => 'password'
		);

		$this->_render_page('auth/auth/edit_user', $this->data);
	}


	// create a new group
	function create_group()
	{
		$this->data['title'] = $this->lang->line('create_group_title');

		//validate form input
		$this->form_validation->set_rules('group_name', $this->lang->line('create_group_validation_name_label'), 'required|alpha_dash|xss_clean');
		$this->form_validation->set_rules('description', $this->lang->line('create_group_validation_desc_label'), 'xss_clean');

		if ($this->form_validation->run() == TRUE)
		{
			$new_group_id = $this->ion_auth_model->create_group($this->input->post('group_name'), $this->input->post('description'));
			if($new_group_id)
			{
				// check to see if we are creating the group
				// redirect them back to the admin page
				$this->session->set_flashdata('message', $this->ion_auth_model->messages());
				redirect("auth", 'refresh');
			}
		}

		//display the create group form
		//set the flash data error message if there is one
		$this->data['message'] = (validation_errors() ? validation_errors() : ($this->ion_auth_model->errors() ? $this->ion_auth_model->errors() : $this->session->flashdata('message')));

		$this->data['group_name'] = array(
			'name'  => 'group_name',
			'id'    => 'group_name',
			'type'  => 'text',
			'value' => $this->form_validation->set_value('group_name'),
		);
		$this->data['description'] = array(
			'name'  => 'description',
			'id'    => 'description',
			'type'  => 'text',
			'value' => $this->form_validation->set_value('description'),
		);

		$this->_render_page('auth/auth/create_group', $this->data);
		
	}


	//edit a group
	function edit_group()
	{

		$id = $this->uri->segment('id');

		$this->data['title'] = $this->lang->line('edit_group_title');

		$group = $this->ion_auth_model->groups()->where('id', $id)->row();

		//validate form input
		$this->form_validation->set_rules('group_name', $this->lang->line('edit_group_validation_name_label'), 'required|alpha_dash|xss_clean');
		$this->form_validation->set_rules('group_description', $this->lang->line('edit_group_validation_desc_label'), 'xss_clean');

		if (isset($_POST) && !empty($_POST))
		{
			if ($this->form_validation->run() === TRUE)
			{
				$group_update = $this->ion_auth_model->update_group($id, $_POST['group_name'], $_POST['group_description']);

				if($group_update)
				{
					$this->session->set_flashdata('message', $this->lang->line('edit_group_saved'));
					redirect(base_url(), 'refresh');
				}
				
				$this->session->set_flashdata('message', $this->ion_auth_model->errors());
				
				
			}
		}

		//set the flash data error message if there is one
		$this->data['message'] = (validation_errors() ? validation_errors() : ($this->ion_auth_model->errors() ? $this->ion_auth_model->errors() : $this->session->flashdata('message')));

		//pass the user to the view
		$this->data['group'] = $group;

		$this->data['group_name'] = array(
			'name'  => 'group_name',
			'id'    => 'group_name',
			'type'  => 'text',
			'value' => $this->form_validation->set_value('group_name', $group->get_data('name')),
		);
		$this->data['group_description'] = array(
			'name'  => 'group_description',
			'id'    => 'group_description',
			'type'  => 'text',
			'value' => $this->form_validation->set_value('group_description', $group->get_data('description')),
		);

		$this->_render_page('auth/auth/edit_group', $this->data);
	}


	function permission_list()
	{
		
		$data->groups = $this->ion_auth_model->groups()->get();
		
		$data->users = $this->ion_auth_model->users()->get();
		
		$data->permissions = $this->ion_auth_model->permissions()->get();
		
		$this->_render_page('auth/auth/permission/permission_list', $data);
		
	}
	
	function load_permissions()
	{
		
		$permission_type = $this->uri->segment('permission_type');
		$id 			 = $this->uri->segment('id');
	
		$this->load->model('auth/form_models/Assign_permissions_model');
		
		
		$this->data['object_data'] = $this->Assign_permissions_model->load_permission($permission_type, $id);
			
		if($this->data['object_data'] == FALSE)
		{
			show_404();
		}
		
		$this->Assign_permissions_model->valid();

		$this->_render_page('auth/auth/permission/load_permission');
		
		
	}
	
	function add_permission()
	{
		$this->load->model('auth/form_models/Permissions_model');
		
		if($this->Permissions_model->valid())
		{
			redirect('permission_list', 'refresh');
		}
		
		$this->_render_page('auth/auth/permission/permission_data');
	}
	
	function edit_permission()
	{
		$permission_id = $this->uri->segment('id');
		
		$permission_object = $this->ion_auth_model->permissions()->where('id', $permission_id)->row();
		
		if($permission_object == FALSE)
		{
			show_404();
		}
		
		$this->load->model('auth/form_models/Permissions_model');
		
		
		$this->Permissions_model->carga('permission_object', $permission_object);
		
		$this->Permissions_model->valid();
		
		$this->_render_page('auth/auth/permission/permission_data');
	}


		/**************************************************************
		 * 
		 * 
		 * 
		 * 		SIN PASAR AL NUEVO SISTEMA
		 * 
		 * 
		 * 
		 * 
		 * ***************************************************************/



	//redirect if needed, otherwise display the user list
	function index()
	{

		if (!$this->ion_auth->logged_in())
		{
			//redirect them to the login page
			redirect('auth/login', 'refresh');
		}
		elseif (!$this->ion_auth->is_admin()) //remove this elseif if you want to enable this for non-admins
		{
			//redirect them to the home page because they must be an administrator to view this
			return show_error('You must be an administrator to view this page.');
		}
		else
		{
			//set the flash data error message if there is one
			$this->data['message'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('message');

			//list the users
			$this->data['users'] = $this->ion_auth->users()->result();
			foreach ($this->data['users'] as $k => $user)
			{
				$this->data['users'][$k]->groups = $this->ion_auth->get_users_groups($user->id)->result();
			}

			$this->_render_page('auth/index', $this->data);
		}
	}

	function _render_page($view, $data=null, $render=false)
	{

		$this->viewdata = (empty($data)) ? $this->data: $data;

		$view_html = $this->load->view($view, $this->viewdata, $render);

		if (!$render) return $view_html;
	}

}
