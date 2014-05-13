<?php

    class department_object extends Correcaminos\Objects\base{

		
        static function _classData()
        {
            return array('tableName' => 'departments',
                         'primary_column' => 'dept_no');
        }
    }