<?php

	Route::filter('not_logged', function()
	{
		$CI =& get_instance();
		$CI->load->library('auth/Auth');
		
		if ($CI->auth->logged_in())
		{
			show_404();
		}
	});
	
	Route::filter('is_admin', function(){
			
		$CI =& get_instance();
		
		if (!$CI->auth->is_admin())
		{
			show_404();
		}
	});
	
	Route::filter('logged', function()
	{
		$CI =& get_instance();
		$CI->load->library('auth/Auth');
		
		if (!$CI->auth->logged_in())
		{
			show_404();
		}
	});
	
	Route::filter('activate_user', function(){
		
		$CI =& get_instance();
		$CI->load->library('auth/Auth');
		
		if ($CI->auth->logged_in() and !$CI->auth->is_admin())
		{
			show_404();
		}	
	});
	
	Route::pattern('id',        '(:num)');
	

	Route::pattern('permission_type', '(^(group|user)$)');


	Route::any('register',					'auth/Auth_controller/create_user', 		array('before' => 'not_logged',	'as' => 'register'));
	Route::any('login', 					'auth/Auth_controller/login', 				array('before' => 'not_logged', 'as' => 'login'));
	Route::any('logout',					'auth/Auth_controller/logout', 				array('before' => 'logged', 	'as' => 'logout'));
	Route::any('forgot_password',			'auth/Auth_controller/forgot_password', 	array('before' => 'not_logged', 'as' => 'forgot_password'));
	Route::any('forgot_password/{code}',	'auth/Auth_controller/reset_password/$1',	array('before' => 'not_logged',	'as' => 'reset_password'));
	Route::any('change_password',			'auth/Auth_controller/change_password',		array('before' => 'logged', 	'as' => 'change_password'));
	Route::any('activate/{id}/{code?}', 	'auth/Auth_controller/activate/$1/$2', 		array('before' => 'activate_user', 'as' => 'activate_user'));
	Route::any('deactivate/{id}', 			'auth/Auth_controller/deactivate/$1', 		array('before' => 'is_admin', 'as' => 'deactivate_user'));
	Route::any('edit_user/{id?}',			'auth/Auth_controller/edit_user/$1', 		array('before' => 'logged', 'as' => 'edit_user'));
	Route::any('create_group',				'auth/Auth_controller/create_group',		array('before' => 'is_admin', 'as' => 'create_group'));
	Route::any('edit_group/{id}',			'auth/Auth_controller/edit_group/$1',		array('before' => 'is_admin', 'as' => 'edit_group'));
	Route::any('permissions',				'auth/Auth_controller/permission_list',		array('before' => 'is_admin', 'as' => 'permission_list'));
	Route::any('permissions/{permission_type}/{id}',
											'auth/Auth_controller/load_permissions/$1/$2',		
																						array('before' => 'is_admin', 'as' => 'load_permission'));
	Route::any('add_permission',			'auth/Auth_controller/add_permission',		array('before' => 'is_admin', 'as' => 'add_permission'));
	Route::any('edit_permission/{id}',		'auth/Auth_controller/edit_permission/$1',
																						array('before' => 'is_admin', 'as' => 'edit_permission'));
	
	
	
	
	
	
	
	