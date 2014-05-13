<?php

    class provincia_object extends Correcaminos\Objects\base{
        
        
        static function _classData()
        {
            return array('tableName' => 'provincia',
                         'joins' => array('poblaciones'		=> array('loading_type'			=>	'eager',
                         											 'type'					=>	'OneToMany',
                         											 'target'				=> 'poblacion_object',
                         											 'columnName'			=> 'idprovincia',
                         											 'referencedColumnName'	=> 'idprovincia'
						 											)
						 ),                                                                                                                                                                                                                                                                                                                  
                         'primary_column' => 'idprovincia');
        }

    }