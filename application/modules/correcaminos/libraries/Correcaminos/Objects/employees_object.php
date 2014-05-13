<?php

    class employees_object extends Correcaminos\Objects\base{

		
        static function _classData()
        {
            return array('tableName' => 'employees',
                         'joins' => array(	'departments'	=> array('loading_type'			=> 'eager',
                         											 'type'					=> 'ManyToMany',
                         											 'target'				=> 'department_object',
                         											 'columnName'			=> 'emp_no',
                         											 'referencedColumnName'	=> 'dept_no',
                         											 'intermediateTable'	=> 'dept_emp',
			  														 'intermediateColumnName' => 'emp_no',
			   														 'intermediatereferencedColumnName' => 'dept_no'
						 											)
						 ),
                         'primary_column' => 'emp_no');
        }
    }