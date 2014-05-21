<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');


class Auth_hooks_model extends CI_Model
{
	public function __construct()
	{
		parent::__construct();
	}
	
	public function post_login_successful()
	{
		$this->auth->login();
	}
	
}