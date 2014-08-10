<?php

    if ( ! function_exists('lang_get_data'))
    {
        function lang_get_data($id, $field)
        {
            $CI =& get_instance();
            $CI->load->model('multilanguage/multilanguage_model');
            return $CI->multilanguage_model->get_data($id, $field);
        }
    }

    if ( ! function_exists('lang_get_multidata'))
    {
        function lang_get_multidata($fields)
        {
            $CI =& get_instance();
            $CI->load->model('multilanguage/multilanguage_model');
            return $CI->multilanguage_model->get_multidata($fields);
        }
    }

    if ( ! function_exists('lang_set_data'))
    {
        function lang_set_data($id, $field, $data)
        {
            $CI =& get_instance();
            $CI->load->model('multilanguage/multilanguage_model');
            return $CI->multilanguage_model->set_data($id, $field, $data);
        }
    }	

    if ( ! function_exists('lang_set_multidata'))
    {
        function lang_set_multidata($data)
        {
            $CI =& get_instance();
            $CI->load->model('multilanguage/multilanguage_model');
            return $CI->multilanguage_model->set_multidata($data);
        }
    }
	
    if ( ! function_exists('language_list'))
    {
        function language_list($data)
        {
            $CI =& get_instance();
            $CI->load->model('multilanguage/multilanguage_model');
            return $CI->multilanguage_model->language_list();
        }
    }