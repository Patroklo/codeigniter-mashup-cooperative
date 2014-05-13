<?php

    class poblacion_object extends Correcaminos\Objects\base{
        
        private $tableName = 'poblacion'; 
		
        static function _classData()
        {
            return array('tableName' => 'poblacion',
                         'joins' => array(	'provincia'		=> array('loading_type'			=> 'eager',
                         											 'type'					=> 'OneToOne',
                         											 'target'				=> 'provincia_object',
                         											 'columnName'			=> 'idprovincia',
                         											 'referencedColumnName'	=> 'idprovincia'
						 											)
						 ),                                                                                                                                                                                                                                                                                                
                         'primary_column' => 'idpoblacion');
        }
    }