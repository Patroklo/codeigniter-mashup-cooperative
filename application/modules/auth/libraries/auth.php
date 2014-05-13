<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

    /**
     *  Stores the auth user object and the usual methods dealing with it
     * 
     * 
     */

	class Original_ion_auth
	{
	    private $_logged_user_data = NULL;
	    
	    public function __construct()
	    {
	        if($this->ion_auth->logged_in())
	        {
	        	$this->_logged_user_data = $this->ion_auth_model->user()->row();
	        	$this->_user_logged = TRUE;
			}
			else 
			{
				
			}
	    }
	    
	}