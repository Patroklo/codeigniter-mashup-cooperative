<?php

	class AJAX_Controller extends CI_Controller {
		
		private $__filter_params;
		private $__active_route;
		
		public $data;
		
		static $entra;
		
		function __construct()
		{
			$this->data = new stdClass();
		}

		public function __get($key)
		{
			// Debugging note:
			//	If you're here because you're getting an error message
			//	saying 'Undefined Property: system/core/Model.php', it's
			//	most likely a typo in your model code.
			return get_instance()->$key;
		}
		

		public function _remap($method, $parameters = array())
		{
			// the active route it's in this controller the Class and the
			// called method. KEEP THAT IN MIND FOR ROUTING PURPOSES
			$this->__active_route  = get_class($this).'/'.$method;
			$this->__filter_params = $parameters;

			$this->call_filters('before');
			
			empty($parameters) ? $this->$method() : call_user_func_array(array($this, $method), $parameters);
			
			if($method != 'call_filters')
			{
				$this->call_filters('after');
			}
		}
		
		
		private function call_filters($type)
		{

			$loaded_route = $this->__active_route;

			$filter_list = Route::get_filters($loaded_route, $type);

			foreach($filter_list as $filter_data)
			{
				$param_list = $this->__filter_params;
				
				$callback 	= $filter_data['filter'];
				$params		= $filter_data['parameters'];
				
				// check if callback has parameters
				if(!is_null($params))
				{
					// separate the multiple parameters in case there are defined
					$params = explode(':', $params);
					
					// search for uris defined as parameters, they will be marked as {(.*)}
					foreach($params as &$p)
					{
						if (preg_match('/\{(.*)\}/', $p, $match_p))
						{
							$p = $this->uri->segment($match_p[1]);
						}
					}

					$param_list = array_merge($param_list, $params);
				}

				call_user_func_array($callback, $param_list);
			}
		}
		
		
	}