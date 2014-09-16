<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

	class Min
	{
	
		var $CI;
	
		function __construct()
		{
			if (get_instance() === NULL)
			{
				new CI_Controller();
			}	
			
			//parent::__construct();
			$this->CI	=& get_instance();
			
			
			
		}
	
		public function index()
		{
	
			$this->CI->load->config('cyassets/assets');
	
			include 'less/lessc.inc.php';
	
			try
			{
				foreach ($this->CI->config->item('cyassets.less') as $origin => $destiny)
				{
				    lessc::ccompile($origin, $destiny);
				}
			}
			catch (exception $ex)
			{
			    exit('lessc fatal error:<br />'.$ex->getMessage());
			}
	
	
			$this->CI->load->driver('cyassets/Minify');
	
			$css	= $this->CI->minify->combine_files($this->CI->config->item('cyassets.css'));
	
			$this->CI->minify->save_file($css, 'css/styles.css');
	
			$js = '';
			if ( ! empty($this->CI->config->item('cyassets.js.header')))
			{
				$archives = array_merge($this->CI->config->item('cyassets.js.header'), $this->get_module_files('js', 'header.js'));
				$js	= $this->CI->minify->combine_files($archives);
				$this->CI->minify->save_file($js, 'js/header.js');
			}
			$js = '';
			if ( ! empty($this->CI->config->item('cyassets.js.footer')))
			{
				$archives = array_merge($this->CI->config->item('cyassets.js.footer'), $this->get_module_files('js', 'footer.js'));
				$js	= $this->CI->minify->combine_files($archives);
				$this->CI->minify->save_file($js, 'js/footer.js');
			}
	
		}
		
		private function get_module_files($format = 'js', $type = 'header.js')
		{
			$path = APPPATH.'modules';
			
			$return_array = array();
			
			foreach (new DirectoryIterator($path) as $file)
			{
			    if($file->isDot()) continue;
			
			    if($file->isDir())
			    {
				    if (is_dir($path.'/'.$file->getFilename().'/js'))
					{
						if (file_exists($path.'/'.$file->getFilename().'/js/'.$type))
						{
							$return_array[] = $path.'/'.$file->getFilename().'/js/'.$type;
						}
					}
			    }
			}
			
			return $return_array;
			
		}
	
	
		/*public function generar()
		{
			//$this->CI->CI->load->driver('session');
	
			if ($_SERVER['QUERY_STRING'] == 'css' OR $_SERVER['QUERY_STRING'] == 'all')
			{
	
	
				$css['style']	= $this->CI->CI->minify->combine_files($config['style.css']);
	
				$this->CI->minify->save_file($css['style'], 'css/style.css');
	
			}
	
			if ($_SERVER['QUERY_STRING'] == 'js' OR $_SERVER['QUERY_STRING'] == 'all')
			{
	
				$this->CI->load->driver('minify');
	
				$config['footer.js']		= array(
					//'js/vendor/jquery-1.10.2.min.js',
					//'js/vendor/bootstrap.js',
					'js/source/scripts.js'
				);
	
				$js['footer']	= $this->CI->minify->combine_files($config['footer.js']);
				$this->CI->minify->save_file($js['footer'], 'js/footer.js');
	
			}
	
		}*/
	
	}

/* End of file min.php */
/* Location: ./application/controllers/min.php */