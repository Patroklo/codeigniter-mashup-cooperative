<?php

    class comment_object extends Correcaminos\Objects\base{
		
        static function _classData()
        {
            return array('tableName' => 'comments',
	                     'joins' => array('author'	=> array('loading_type'			=> 'eager',
	             											 'type'					=> 'OneToOne',
	             											 'target'				=> 'user_object',
	             											 'columnName'			=> 'user_id',
	             											 'referencedColumnName'	=> 'id'
				 											)
						 ),
                         'primary_column' => 'id');
        }
		
		
		function can_read()
		{
			return TRUE;
		}
		
		function can_update()
		{
			$CI =& get_instance();
			
			if($CI->auth->get_user_id() == $this->get_data('user_id'))
			{
				return TRUE;
			}
			
			return FALSE;
		}
		
		function can_delete()
		{
			$CI =& get_instance();
			
			if($CI->auth->get_user_id() == $this->get_data('user_id'))
			{
				return TRUE;
			}
			elseif($CI->auth->is_admin() == TRUE)
			{
				return TRUE;
			}
			
			return FALSE;
		}


    }