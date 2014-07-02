<?php

	class Filters extends CI_Controller{
		
		private $params;
		
		// We will load the get_instance() and apply it as if this class were 
		// a normal controller
		function __construct()
		{
			parent::__construct();

			$this->params = $this->uri->segment_array();
		}
		
		// The before controller filter callings
		public function before()
		{
			$this->call_filters('before');
		}
		
		// The after controller filter callings
		public function after()
		{
			$this->call_filters('after');
		}
		
		private function call_filters($type)
		{
			$loaded_route = $this->router->get_active_route();
			$filter_list = Route::get_filters($loaded_route, $type);
			
			foreach($filter_list as $callback)
			{
				call_user_func_array($callback, $this->params);
			}
		}
		
	}