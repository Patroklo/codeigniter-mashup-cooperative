<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * CodeIgniter
 *
 * An open source application development framework for PHP 5.1.6 or newer
 *
 * @package		CodeIgniter
 * @author		ExpressionEngine Dev Team
 * @copyright	Copyright (c) 2008 - 2011, EllisLab, Inc.
 * @license		http://codeigniter.com/user_guide/license.html
 * @link		http://codeigniter.com
 * @since		Version 1.0
 * @filesource
 */

// ------------------------------------------------------------------------

/**
 * File Uploading Class
 *
 * @package		CodeIgniter
 * @subpackage	Libraries
 * @category	Uploads
 * @author		ExpressionEngine Dev Team
 * @link		http://codeigniter.com/user_guide/libraries/file_uploading.html
 */
class Cy_uploader extends CI_Upload {

	// --------------------------------------------------------------------
	
	public $base_directory = 'uploads';
    public $upload_directory = 'uploads/procesar';
	
	public $uploads_done = array();
	
	public function __construct($config = array())
	{
        //introducimos manualmente el upload path si no existe

        $this->upload_directory = FCPATH.$this->upload_directory;

        if(!array_key_exists('upload_path', $config))
        {
        	if(!is_dir($this->base_directory))
	        {
	            mkdir($this->base_directory);
	        }
			
            if(is_dir($this->upload_directory) == FALSE)
            {
                mkdir($this->upload_directory);
            }
            $config['upload_path'] = $this->upload_directory;
        }

		$config['allowed_types'] = '*';
	
        return parent::__construct($config);
	}
	


    public function get_directory($name = 'comun', $date = TRUE)
    {
        $real_inner_directory = realpath($this->base_directory);
        
        if(!is_dir($this->base_directory))
        {
            mkdir($this->base_directory);
        }
        
        $real_inner_directory = $real_inner_directory.'/'.$name;
        $inner_directory = $this->base_directory.'/'.$name;
        
        if(!is_dir($real_inner_directory))
        {
            mkdir($real_inner_directory);
        }
        
        if($date == TRUE)
        {
            $real_inner_directory = $real_inner_directory.'/'.date('Y');
            $inner_directory = $inner_directory.'/'.date('Y');
            
            if(!is_dir($real_inner_directory))
            {
                mkdir($real_inner_directory);
            }      
            
            $real_inner_directory = $real_inner_directory.'/'.date('m');
            $inner_directory = $inner_directory.'/'.date('m');
            if(!is_dir($real_inner_directory))
            {
                mkdir($real_inner_directory);
            }            
            
            $real_inner_directory = $real_inner_directory.'/'.date('d');
            $inner_directory = $inner_directory.'/'.date('d');
            if(!is_dir($real_inner_directory))
            {
                mkdir($real_inner_directory);
            }             
        }
        
        return $inner_directory.'/';
        
    }


	public function do_upload($field = 'userfile')
	{
		if(array_key_exists($field, $this->uploads_done))
		{
			return TRUE;
		}
		
		$return_data = parent::do_upload($field);
		
		if($return_data == FALSE)
		{
			return FALSE;
		}
		else{
			$this->uploads_done[$field] = TRUE;
			return $return_data;
		}
		
	}
	// --------------------------------------------------------------------

}
// END Upload Class

/* End of file Upload.php */
/* Location: ./system/libraries/Upload.php */
