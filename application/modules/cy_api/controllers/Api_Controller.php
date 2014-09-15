<?php

	class Api_Controller extends CI_Controller {
		
		private $__filter_params;
		private $__active_route;

		/**
		 * What is gonna happen in output?
		 *
		 * @var object
		 */
		protected $response             = null;

		/**
		 * Stores DB, keys, key level, etc
		 *
		 * @var object
		 */
		protected $rest                 = null;

		/**
		 * List all supported methods, the first will be the default format
		 *
		 * @var array
		 */
		protected $_supported_formats   = array(
			'xml'           => 'application/xml',
			'json'          => 'application/json',
			'jsonp'         => 'application/javascript',
			'serialized'    => 'application/vnd.php.serialized',
			'php'           => 'text/plain',
			'html'          => 'text/html',
			'csv'           => 'application/csv'
		);

		function __construct()
		{
			// if we are calling this api controller as an url we will need
			// a controller loading for get_instance() initialization
			if (get_instance() === NULL)
			{
				new CI_Controller();
			}

			// This library is bundled with REST_Controller 2.5+, but will eventually be part of CodeIgniter itself
			$this->load->library('cy_api/Format');

			// init objects
			$this->response     = new stdClass();
			$this->rest         = new stdClass();

			// let's learn about the request
			$this->request      = new stdClass();

			// Is it over SSL?
			$this->request->ssl     = $this->_detect_ssl();

			// How is this request being made? POST, DELETE, GET, PUT?
			$this->request->method  = $this->_detect_method();


		}

		public function __get($key)
		{

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



		/**
		 * ==========================================================================================================================================
		 * ==========================================================================================================================================
		 *          RESTFUL METHODS         RESTFUL METHODS         RESTFUL METHODS     RESTFUL METHODS
		 * ==========================================================================================================================================
		 * ==========================================================================================================================================
		 */

		/**
		 * Detect method
		 *
		 * Detect which HTTP method is being used
		 *
		 * @return string
		 */
		protected function _detect_method()
		{
			$method = strtolower($this->input->server('REQUEST_METHOD'));

			if ($this->config->item('enable_emulate_request')) {
				if ($this->input->post('_method')) {
					$method = strtolower($this->input->post('_method'));
				} elseif ($this->input->server('HTTP_X_HTTP_METHOD_OVERRIDE')) {
					$method = strtolower($this->input->server('HTTP_X_HTTP_METHOD_OVERRIDE'));
				}
			}

			if (in_array($method, $this->allowed_http_methods) && method_exists($this, '_parse_' . $method)) {
				return $method;
			}

			return 'get';
		}




		/*
		 * Detect SSL use
		 *
		 * Detect whether SSL is being used or not
		 */
		protected function _detect_ssl()
		{
			return (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == "on");
		}
		
		
	}