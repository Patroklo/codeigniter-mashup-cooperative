<?php
/**
 * CodeIgniter
 *
 * An open source application development framework for PHP 5.2.4 or newer
 *
 * NOTICE OF LICENSE
 *
 * Licensed under the Academic Free License version 3.0
 *
 * This source file is subject to the Academic Free License (AFL 3.0) that is
 * bundled with this package in the files license_afl.txt / license_afl.rst.
 * It is also available through the world wide web at this URL:
 * http://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to obtain it
 * through the world wide web, please send an email to
 * licensing@ellislab.com so we can send you a copy immediately.
 *
 * @package		CodeIgniter
 * @author		EllisLab Dev Team
 * @copyright	Copyright (c) 2008 - 2014, EllisLab, Inc. (http://ellislab.com/)
 * @license		http://opensource.org/licenses/AFL-3.0 Academic Free License (AFL 3.0)
 * @link		http://codeigniter.com
 * @since		Version 1.0
 * @filesource
 */
defined('BASEPATH') OR exit('No direct script access allowed');

/*
| -------------------------------------------------------------------------
| URI ROUTING
| -------------------------------------------------------------------------
| This file lets you re-map URI requests to specific controller functions.
|
| Typically there is a one-to-one relationship between a URL string
| and its corresponding controller class/method. The segments in a
| URL normally follow this pattern:
|
|	example.com/class/method/id/
|
| In some instances, however, you may want to remap this relationship
| so that a different class/function is called than the one
| corresponding to the URL.
|
| Please see the user guide for complete details:
|
|	http://codeigniter.com/user_guide/general/routing.html
|
| -------------------------------------------------------------------------
| RESERVED ROUTES
| -------------------------------------------------------------------------
|
| There are three reserved routes:
|
|	$route['default_controller'] = 'welcome';
|
| This route indicates which controller class should be loaded if the
| URI contains no data. In the above example, the "welcome" class
| would be loaded.
|
|	$route['404_override'] = 'errors/page_missing';
|
| This route will tell the Router which controller/method to use if those
| provided in the URL cannot be matched to a valid route.
|
|	$route['translate_uri_dashes'] = FALSE;
|
| This is not exactly a route, but allows you to automatically route
| controller and method names that contain dashes. '-' isn't a valid
| class or method name character, so it requires translation.
| When you set this option to TRUE, it will replace ALL dashes in the
| controller and method URI segments.
|
| Examples:	my-controller/index	-> my_controller/index
|		my-controller/my-method	-> my_controller/my_method
*/
	
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
	
	
	Route::filter('derp', function(){
	});
	
	Route::pattern('id',        '(:num)');
	


	Route::any('welcome', 		'Welcome/index');
	Route::any('prueba', 		'Welcome/prueba_forms');
	Route::any('prueba/{id}', 	'Welcome/prueba_forms/$1', 				array('before' => 'prueba[1:{id}]'))->where('id', '(:num)');
	
	Route::any('Derp/index',	'',										array('before' => 'derp'));
	
	
	Route::any('register',					'auth/Auth_controller/create_user', 		array('before' => 'not_logged'));
	Route::any('login', 					'auth/Auth_controller/login', 				array('before' => 'not_logged'));
	Route::any('logout',					'auth/Auth_controller/logout', 				array('before' => 'logged'));
	Route::any('forgot_password',			'auth/Auth_controller/forgot_password', 	array('before' => 'not_logged'));
	Route::any('forgot_password/{code}',	'auth/Auth_controller/reset_password/$1');
	Route::any('change_password',			'auth/Auth_controller/change_password',		array('before' => 'logged'));
	Route::any('activate/{id}/{code?}', 	'auth/Auth_controller/activate/$1/$2', 		array('before' => 'activate_user'));
	Route::any('deactivate/{id}', 			'auth/Auth_controller/deactivate/$1', 		array('before' => 'is_admin'));
	Route::any('edit_user/{id?}',			'auth/Auth_controller/edit_user/$1', 		array('before' => 'logged'));
	Route::any('create_group',				'auth/Auth_controller/create_group',		array('before' => 'is_admin'));
	Route::any('edit_group/{id}',			'auth/Auth_controller/edit_group/$1',		array('before' => 'is_admin'));
	
	$route = Route::map();


	$route['default_controller'] = 'welcome';
	$route['404_override'] = '';
	$route['translate_uri_dashes'] = FALSE;

/* End of file routes.php */
/* Location: ./application/config/routes.php */