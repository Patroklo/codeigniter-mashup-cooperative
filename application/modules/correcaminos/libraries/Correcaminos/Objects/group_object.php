<?php

    class group_object extends Correcaminos\Objects\base{
        
        
        static function _classData()
        {
            return array('tableName' => 'groups',
                         'joins' => array(),                                                                                                                                                                                                                                                                                                                  
                         'primary_column' => 'id');
        }

		function get_name()
		{
			return $this->_data->name;
		}


    }