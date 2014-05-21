<?php
(defined('BASEPATH')) or exit('No direct script access allowed');

/* load the HMVC_Loader class */
require APPPATH . 'third_party/HMVC/Loader.php';

	class MY_Loader extends HMVC_Loader {

	    public function __construct()
		{
			$this->_ci_ob_level = ob_get_level();
			$this->_ci_classes = is_loaded();
	
			log_message('debug', 'Loader Class Initialized');
			
	        // Get current module from the router
	        $router = & $this->_ci_get_component('router');
	        if ($router->module) {
	            $this->add_module($router->module);
	        }
			
			
		}
		
	}