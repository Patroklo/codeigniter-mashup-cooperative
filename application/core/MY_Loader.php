<?php
/**
 * @name		CodeIgniter HMVC Modules
 * @author		Jens Segers
 * @link		http://www.jenssegers.be
 * @license		MIT License Copyright (c) 2012 Jens Segers
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @author hArpanet - 23-Jun-2014
 *
 *      Widget() method added to load Widgets for Template library by Jens Segers
 *
 */

if (!defined("BASEPATH"))
    exit("No direct script access allowed");

class MY_Loader extends CI_Loader {

    /**
     * List of loaded modules
     *
     * @var array
     * @access protected
     */
    protected $_ci_modules = array();

    /**
     * List of loaded controllers
     *
     * @var array
     * @access protected
     */
    protected $_ci_controllers = array();

    /**
     * Constructor
     *
     * Add the current module to all paths permanently
     */
    public function __construct() {
        parent::__construct();

        // Get current module from the router
        $router = & $this->_ci_get_component('router');
        if ($router->module) {
            $this->add_module($router->module);
        }
    }

    /**
     * Controller Loader
     *
     * This function lets users load and hierarchical controllers to enable HMVC support
     *
     * @param	string	the uri to the controller
     * @param	array	parameters for the requested method
     * @param	boolean return the result instead of showing it
     * @return	void
     */
    public function controller($uri, $params = array(), $return = FALSE) {
        // No valid module detected, add current module to uri
        list($module) = $this->detect_module($uri);
        if (!isset($module)) {
            $router = & $this->_ci_get_component('router');
            if ($router->module) {
                $module = $router->module;
                $uri = $module . '/' . $uri;
            }
        }

        // Add module
        $this->add_module($module);

        // Execute the controller method and capture output
        $void = $this->_load_controller($uri, $params, $return);

        // Remove module
        $this->remove_module();

        return $void;
    }

    /**
     * Class Loader
     *
     * This function lets users load and instantiate classes.
     * It is designed to be called from a user's app controllers.
     *
     * @param	string	the name of the class
     * @param	mixed	the optional parameters
     * @param	string	an optional object name
     * @return	void
     */
    public function library($library = '', $params = NULL, $object_name = NULL) {
        if (is_array($library)) {
            foreach ($library as $class) {
                $this->library($class, $params);
            }
            return;
        }

        // Detect module
        if (list($module, $class) = $this->detect_module($library)) {
            // Module already loaded
            if (in_array($module, $this->_ci_modules)) {
                return parent::library($class, $params, $object_name);
            }

            // Add module
            $this->add_module($module);

            // Let parent do the heavy work
            $void = parent::library($class, $params, $object_name);

            // Remove module
            $this->remove_module();

            return $void;
        } else {
            return parent::library($library, $params, $object_name);
        }
    }

    /**
     * Model Loader
     *
     * This function lets users load and instantiate models.
     *
     * @param	string	the name of the class
     * @param	string	name for the model
     * @param	bool	database connection
     * @return	void
     */
    public function model($model, $name = '', $db_conn = FALSE) {
        if (is_array($model)) {
            foreach ($model as $babe) {
                $this->model($babe);
            }
            return;
        }

        // Detect module
        if (list($module, $class) = $this->detect_module($model)) {
            // Module already loaded
            if (in_array($module, $this->_ci_modules)) {
                return parent::model($class, $name, $db_conn);
            }

            // Add module
            $this->add_module($module);

            // Let parent do the heavy work
            $void = parent::model($class, $name, $db_conn);

            // Remove module
            $this->remove_module();

            return $void;
        } else {
            return parent::model($model, $name, $db_conn);
        }
    }

    /**
     * Load View
     *
     * This function is used to load a "view" file.  It has three parameters:
     *
     * 1. The name of the "view" file to be included.
     * 2. An associative array of data to be extracted for use in the view.
     * 3. TRUE/FALSE - whether to return the data or load it.  In
     * some cases it's advantageous to be able to return data so that
     * a developer can process it in some way.
     *
     * @param	string
     * @param	array
     * @param	bool
     * @return	void
     */
    public function view($view, $vars = array(), $return = FALSE) {
        // Detect module
        if (list($module, $class) = $this->detect_module($view)) {
            // Module already loaded
            if (in_array($module, $this->_ci_modules)) {
                return parent::view($class, $vars, $return);
            }

            // Add module
            $this->add_module($module);

            // Let parent do the heavy work
            $void = parent::view($class, $vars, $return);

            // Remove module
            $this->remove_module();

            return $void;
        } else {
            return parent::view($view, $vars, $return);
        }
    }

    /**
     * Loads a config file
     *
     * @param	string
     * @param	bool
     * @param 	bool
     * @return	void
     */
    public function config($file = '', $use_sections = FALSE, $fail_gracefully = FALSE) {
        // Detect module
        if (list($module, $class) = $this->detect_module($file)) {
            // Module already loaded
            if (in_array($module, $this->_ci_modules)) {
                return parent::config($class, $use_sections, $fail_gracefully);
            }

            // Add module
            $this->add_module($module);

            // Let parent do the heavy work
            $void = parent::config($class, $use_sections, $fail_gracefully);

            // Remove module
            $this->remove_module();

            return $void;
        } else {
            parent::config($file, $use_sections, $fail_gracefully);
        }
    }

    /**
     * Load Helper
     *
     * This function loads the specified helper file.
     *
     * @param	mixed
     * @return	void
     */
    public function helper($helper = array()) {
        if (is_array($helper)) {
            foreach ($helper as $help) {
                $this->helper($help);
            }
            return;
        }

        // Detect module
        if (list($module, $class) = $this->detect_module($helper)) {
            // Module already loaded
            if (in_array($module, $this->_ci_modules)) {
                return parent::helper($class);
            }

            // Add module
            $this->add_module($module);

            // Let parent do the heavy work
            $void = parent::helper($class);

            // Remove module
            $this->remove_module();

            return $void;
        } else {
            return parent::helper($helper);
        }
    }

    /**
     * Loads a language file
     *
     * @param	array
     * @param	string
     * @return	void
     */
    public function language($file = array(), $lang = '') {
        if (is_array($file)) {
            foreach ($file as $langfile) {
                $this->language($langfile, $lang);
            }
            return;
        }

        // Detect module
        if (list($module, $class) = $this->detect_module($file)) {
            // Module already loaded
            if (in_array($module, $this->_ci_modules)) {
                return parent::language($class, $lang);
            }

            // Add module
            $this->add_module($module);

            // Let parent do the heavy work
            $void = parent::language($class, $lang);

            // Remove module
            $this->remove_module();

            return $void;
        } else {
            return parent::language($file, $lang);
        }
    }

    /**
     * Load Widget
     *
     * This function provides support to Jens Segers Template Library for loading
     * widget controllers within modules (place in module/widgets folder).
     * @author  hArpanet - 23-Jun-2014
     *
     * @param   string $widget  Must contain Module name if widget within a module
     *                          (eg. test/nav  where module name is 'test')
     * @return  array|false
     */
    public function widget($widget) {

        // Detect module
        if (list($module, $widget) = $this->detect_module($widget)) {
            // Module already loaded
            if (in_array($module, $this->_ci_modules)) {
                return array($module, $widget);
            }

            // Add module
            $this->add_module($module);

            // Look again now we've added new module path
            $void = $this->widget($module.'/'.$widget);

            // Remove module if widget not found within it
            if (!$void) {
                $this->remove_module();
            }

            return $void;

        } else {
            // widget not found in module
            return FALSE;
        }
    }

    /**
     * Add Module
     *
     * Allow resources to be loaded from this module path
     *
     * @param	string
     * @param 	boolean
     */
    public function add_module($module, $view_cascade = TRUE) {
        if ($path = $this->find_module($module)) {
            // Mark module as loaded
            array_unshift($this->_ci_modules, $module);

            // Add package path
            parent::add_package_path($path, $view_cascade);
        }
    }

    /**
     * Remove Module
     *
     * Remove a module from the allowed module paths
     *
     * @param	type
     * @param 	bool
     */
    public function remove_module($module = '', $remove_config = TRUE) {
        if ($module == '') {
            // Mark module as not loaded
            array_shift($this->_ci_modules);

            // Remove package path
            parent::remove_package_path('', $remove_config);
        } else if (($key = array_search($module, $this->_ci_modules)) !== FALSE) {
            if ($path = $this->find_module($module)) {
                // Mark module as not loaded
                unset($this->_ci_modules[$key]);

                // Remove package path
                parent::remove_package_path($path, $remove_config);
            }
        }
    }

    /**
     * Controller loader
     *
     * This function is used to load and instantiate controllers
     *
     * @param	string
     * @param	array
     * @param	boolean
     * @return	object
     */
    private function _load_controller($uri = '', $params = array(), $return = FALSE) {
        $router = & $this->_ci_get_component('router');

        // Back up current router values (before loading new controller)
        $backup = array();
        foreach (array('directory', 'class', 'method', 'module') as $prop) {
            $backup[$prop] = $router->{$prop};
        }

        // Locate the controller
        $segments = $router->locate(explode('/', $uri));
        $class = isset($segments[0]) ? $segments[0] : FALSE;
        $method = isset($segments[1]) ? $segments[1] : "index";

        // Controller not found
        if (!$class) {
            return;
        }

        if (!array_key_exists(strtolower($class), $this->_ci_controllers)) {
            // Determine filepath
            $filepath = APPPATH . 'controllers/' . $router->fetch_directory() . $class . '.php';

            // Load the controller file
            if (file_exists($filepath)) {
            	
				$this->_include_class('core/AJAX_Controller.php');
                include_once ($filepath);
            }

            // Controller class not found, show 404
            if (!class_exists($class)) {
                show_404("{$class}/{$method}");
            }
			
			// Controller class MUST BE EXTENDED FROM AJAX_Controller
			if(get_parent_class($class) != 'AJAX_Controller')
			{
				throw new Exception("The controller ".$class." must extend AJAX_Controller.", 1);
			}

            // Create a controller object
            $this->_ci_controllers[strtolower($class)] = new $class();
        }

        $controller = $this->_ci_controllers[strtolower($class)];

        // Method does not exists
        if (!method_exists($controller, $method)) {
            show_404("{$class}/{$method}");
        }

		// _remap must be called, if it exists, to mantain integrity 
		// with standard Codeigniter controller calls.
		if(method_exists($controller, '_remap'))
		{
			if(!is_array($params))
			{
				$params = array($params);
			}
			
			$params = array($method, $params);
			$method = '_remap';
		}

        // Restore router state
        foreach ($backup as $prop => $value) {
            $router->{$prop} = $value;
        }

        // Capture output and return
 
        ob_start();
        $result = call_user_func_array(array(&$controller, $method), $params);

        // Return the buffered output
        if ($return === TRUE) {
            $buffer = ob_get_contents();
            @ob_end_clean();
            return $buffer;
        }

        // Close buffer and flush output to screen
        ob_end_flush();

        // Return controller return value
        return $result;
    }

    /**
     * Detects the module from a string. Returns the module name and class if found.
     *
     * @param	string
     * @return	array|boolean
     */
    private function detect_module($class) {
        $class = str_replace('.php', '', trim($class, '/'));
        if (($first_slash = strpos($class, '/')) !== FALSE) {
            $module = substr($class, 0, $first_slash);
            $class = substr($class, $first_slash + 1);

            // Check if module exists
            if ($this->find_module($module)) {
                return array($module, $class);
            }
        }

        return FALSE;
    }

    /**
     * Searches a given module name. Returns the path if found, FALSE otherwise
     *
     * @param string $module
     * @return string|boolean
     */
    private function find_module($module) {
        $config = & $this->_ci_get_component('config');

        // Check all locations for this module
        foreach ($config->item('modules_locations') as $location) {
            $path = $location . rtrim($module, '/') . '/';
            if (is_dir($path)) {
                return $path;
            }
        }

        return FALSE;
    }
	
	
	
	
	
	
	
	/**
	 * ADDON PATROKLO PARA CARGAR OBJETOS
	 */

	/**
	 * CI Autoloader
	 *
	 * Loads component listed in the config/autoload.php file.
	 *
	 * @used-by	CI_Loader::initialize()
	 * @return	void
	 */
	  
	protected function _ci_autoloader()
	{
		
		if (file_exists(APPPATH.'config/'.ENVIRONMENT.'/autoload.php'))
		{
			include(APPPATH.'config/'.ENVIRONMENT.'/autoload.php');
		}
		else
		{
			include(APPPATH.'config/autoload.php');
		}

		if ( ! isset($autoload))
		{
			return FALSE;
		}


		// Autoload class includes
		if (isset($autoload['class']))
		{
			foreach ($autoload['class'] as $item)
			{
				$this->_include_class($item);
			}
		}

		parent::_ci_autoloader();
	} 
	 

	/**
     * Include Class
     *
     * It only loads with an include_once a class. Useful for loading the extending classes
	 * like the base_model, etc... without adding them to the superobject
	 * 
	 * Musn't have the .php extension
     *
     * @param	string	the name of the class
     * @return	void
     */
     
    public function _include_class($className = '') 
    {
        	
        if (is_array($className)) {
            foreach ($className as $class) {
                $this->_include_class($className);
            }
            return;
        }

		$class = str_replace('.php', '', trim($className, '/'));

        // Detect module
        if (list($module, $class) = $this->detect_module($className)) {
            // Module already loaded
            if (in_array($module, $this->_ci_modules)) {
            	$this->_include_class_load($class);
            }

            // Add module
            $this->add_module($module);

            // Let parent do the heavy work
            $this->_include_class_load($class);

            // Remove module
            $this->remove_module();

        } else {
            $this->_include_class_load($className);
        }
    }
	
	private function _include_class_load($class)
	{
		$class = str_replace('.php', '', $class);
		
		// Was the path included with the class name?
		// We look for a slash to determine this
		if (($last_slash = strrpos($class, '/')) !== FALSE)
		{
			// Extract the path
			$subdir = substr($class, 0, ++$last_slash);

			// Get the filename from the path
			$class = substr($class, $last_slash);
		}
		else
		{
			$subdir = '';
		}
		
		if (!class_exists($class)) 
		{
			$subclass = APPPATH.$subdir.config_item('subclass_prefix').$class.'.php';
		

			if(file_exists($subclass))
			{
				include_once($subclass);
			}
		}
		
		if (!class_exists($class)) 
		{
			$subclass = APPPATH.$subdir.$class.'.php';
	
			if(file_exists($subclass))
			{
				include_once($subclass);
			}
		}
		
		if (!class_exists($class)) 
		{
			// Let's search for the requested library file and load it.
			foreach ($this->_ci_library_paths as $path)
			{
				$subclass = $path.$subdir.$class.'.php';
				
				if(file_exists($subclass))
				{
					include_once($subclass);
					break;
				}
			}
		}

	}
	
/*    if (is_array($library)) {
            foreach ($library as $class) {
                $this->library($class, $params);
            }
            return;
        }

        // Detect module
        if (list($module, $class) = $this->detect_module($library)) {
            // Module already loaded
            if (in_array($module, $this->_ci_modules)) {
                return parent::library($class, $params, $object_name);
            }

            // Add module
            $this->add_module($module);

            // Let parent do the heavy work
            $void = parent::library($class, $params, $object_name);

            // Remove module
            $this->remove_module();

            return $void;
        } else {
            return parent::library($library, $params, $object_name);
        }*/
        
        
	/**
	 * Driver Loader
	 *
	 * Loads a driver library.
	 *
	 * @param	string|string[]	$library	Driver name(s)
	 * @param	array		$params		Optional parameters to pass to the driver
	 * @param	string		$object_name	An optional object name to assign to
	 *
	 * @return	object|bool	Object or FALSE on failure if $library is a string
	 *				and $object_name is set. CI_Loader instance otherwise.
	 */
	public function driver($library, $params = NULL, $object_name = NULL)
	{
		if (is_array($library))
		{
			foreach ($library as $driver)
			{
				$this->driver($driver, $params);
			}

			return $this;
		}
		elseif (empty($library))
		{
			return FALSE;
		}

        // Detect module
		if (list($module, $class) = $this->detect_module($library)) {
            // Module already loaded
            if (in_array($module, $this->_ci_modules)) {
                return parent::driver($class, $params, $object_name);
            }

            // Add module
            $this->add_module($module);

            // Let parent do the heavy work
            $void = parent::driver($class, $params, $object_name);

            // Remove module
            //$this->remove_module();

            return $void;
        } else {
            return parent::driver($library, $params, $object_name);
        }
	}
}