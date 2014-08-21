<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Min {

	var $CI;

	function __construct()
	{
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


		if ( ! empty($this->CI->config->item('cyassets.js.header')))
		{
			$js['header']	= $this->CI->minify->combine_files($this->CI->config->item('cyassets.js.header'));
			$this->CI->minify->save_file($js['header'], 'js/header.js');
		}

		if ( ! empty($this->CI->config->item('cyassets.js.footer')))
		{
			$js['footer']	= $this->CI->minify->combine_files($this->CI->config->item('cyassets.js.footer'));
			$this->CI->minify->save_file($js['footer'], 'js/footer.js');
		}

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