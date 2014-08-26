<?php

	require_once APPPATH.'modules/cy_form_generator/models/Cy_correcaminos_form_model.php';

	class Permissions_model extends Cy_correcaminos_form_model {
		
		
		 function form_definition($options = NULL)
		 {
		 	// all the data defined as in the previous comments
		 	
		 	$options = array('field_type'	=> 'Bootstrap',
							 'objects'		=> 'permission_object',
							 'fields'		=> array(
							 							array('id'		=> 'name',
															  'options'	=> array(
															  					 'type'		=> 'Text',
																				 'rules'	=> 'required',
																				 'object_type'	=> 'permission_object',
																				 'fieldName'	=> 'name',
																				 'label'		=> 'Nombre de permiso',
																				 )
														)
												)
														
							);
		 	
			
			parent::form_definition($options);
		 }
		
		
	}
