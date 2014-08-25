<?php

    class permission_object extends Correcaminos\Objects\base{
        
		private $is_admin = NULL;
		private $group_names;
        
        static function _classData()
        {
            return array('tableName' => 'permissions',
	                     'joins' => array(),
		  				/* 'files' => array( 'user_photo_object' 	=> array('directory' => 'foto_usuario',
																	 	 'className' => 'Prueba',
																		 'rules'	 =>  array(
																			                     'field'   => 'userfile',
																			                     'label'   => 'userfile',
																			                     'rules'   => 'required'
																			                  )
            
																		)),  */                                                                                                                                                                                                                                                                  
                         'primary_column' => 'id');
        }

}