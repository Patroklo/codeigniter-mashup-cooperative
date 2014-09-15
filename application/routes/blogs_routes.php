<?php


	Route::filter('logged', function()
	{
		$CI =& get_instance();
		$CI->load->library('auth/Auth');
	
		if (!$CI->auth->logged_in())
		{
			show_404();
		}
	});
	
	Route::filter('object_exists', function($uri, $model_url, $model_name, $id)
	{
		$CI =& get_instance();
		$CI->load->model($model_url);
		
		if ($CI->{$model_name}->carga($id) == FALSE)
		{
			show_404();
		}
		
	});


	Route::pattern('id', '(:num)');
	Route::pattern('post_name', '(:any)');

	Route::prefix('admin', function(){
		Route::any('blog',					    	'cy_messages/admin/Blog/index', 		        	array('before' => 'logged', 
																									  			'as' => 'admin/blog/list'));

		Route::any('blog/new',					    'cy_messages/admin/Blog/new_post', 		        	array('before' => 'logged', 	
																									  			'as' => 'admin/blog/new_post'));

		Route::any('blog/edit/{id}',				'cy_messages/admin/Blog/edit_post/$1',				array('before' => array('logged', 'object_exists[cy_messages/Cy_blog_model:Cy_blog_model:{id}]'),
																									  			'as' => 'admin/blog/edit'));
		
		Route::any('blog/delete/{id}',				'cy_messages/admin/Blog/delete_post/$1',			array('before' => array('logged', 'object_exists[cy_messages/Cy_blog_model:Cy_blog_model:{id}]'),
																							  		  			'as' => 'admin/blog/edit'));
	});
	
	Route::get('blog',								'cy_messages/Blog/blog_list');
	
	Route::get('blog/{post_name}',					'cy_messages/Blog/load_post/$1');
	
