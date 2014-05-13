<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
 

if ( ! function_exists('Beep_from'))
{
    function beep_from($table)
    {
        $CI =& get_instance();
        return $CI->correcaminos->beep_from($table);
    }
}

if ( ! function_exists('load_object'))
{
    function load_object($class)
    {
        $CI =& get_instance();
        return $CI->correcaminos->load_object($class);
    }
}


if ( ! function_exists('Beep'))
{
    function beep($class)
    {
        $CI =& get_instance();
        return $CI->correcaminos->beep($class);
    }
}


if ( ! function_exists('Raw_query'))
{
    function Raw_query($query, $parameters = FALSE, $cache = NULL)
    {
        $CI =& get_instance();
        return $CI->correcaminos->raw_query($query, $parameters, $cache);
    }
}

	function get_global($name)
	{
	    $CI =& get_instance();
		return $CI->correcaminos->get_global($name);
	}
	
	function set_global($query, $name = NULL)
	{
        $CI =& get_instance();
        return $CI->correcaminos->set_global($query, $name);
	}
	
	function delete_global($name)
	{
	    $CI =& get_instance();
		return $CI->correcaminos->delete_global($name);		
	}

	function last_query()
	{
	    $CI =& get_instance();
		return $CI->correcaminos->last_query();		
	}
	function begin_transaction()
	{
	 	$CI =& get_instance();
		return $CI->correcaminos->begin_transaction();
	}
	 
	function commit_transaction()
	{
	 	$CI =& get_instance();
		return $CI->correcaminos->commit_transaction();
	}

    function rollback_transaction()
	{
	 	$CI =& get_instance();
		return $CI->correcaminos->rollback_transaction();
	}
	
	function get_class_data($className)
	{
		$CI =& get_instance();
		return $CI->correcaminos->get_class_data($className);
	}
