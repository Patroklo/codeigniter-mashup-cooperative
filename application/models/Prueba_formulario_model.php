<?php

	require_once APPPATH.'modules/cy_form_generator/models/Cy_correcaminos_form_model.php';

	class Prueba_formulario_model extends Cy_correcaminos_form_model {
		
	/*
	 * Data creation format
	 * 		
	 * 		[field_type]	=> (optional) (string) field form type definition for /libraries/Form_field.php
	 * 		[objects] 		=> (string) (loaded or not (setted as insert or update))
	 * 						   (optional) (array)
	 * 										[name]	=> (string) the object class name
	 * 										[alias] => (string) neccesary when using two or more objects of the same class name
	 * 															in order to be able to tell which is which
	 * 		[fields]		=> (array) of Field format
	 * 
	 */
	 
	/* 
	 * Field format:
	 * 
	 * 		id (string) (field name and id for the html labels)
	 * 		options (array)
	 * 					[type] 					=> string (field form type, like text, textarea, checkbox...)
	 * 					[rules]					=> string (rules for codeigniter's form_validation)
	 * 					[object_type]			=> (optional) string (object of the field, if not setted, then won't be used in database related methods)
	 * 														  (it's the name or alias (in case there is an alias defined) of the object)
	 * 					[fieldName]				=> (optional) string (field name in the table) if not set, will try to use the id as fieldname
	 * 					[value]					=> (optional) mixed (field data will be set in the form)
	 * 					[additional_parameters] => (optional) additional parameters for the html form
	 * 
	 */
	 
	 /*
	  * (only for file objects)
	  * File field format:
	  * 
	  * 	id (string) (if fieldName it's not defined, will be used to define file field name defined in the _classData method)
	  * 	options (array)
	  * 			[type]						=> (string) file type must be upload
	  * 			[upload]					=> (boolean) (optional) TRUE|FALSE (true in this case, duh!)
	  * 			[rules]						=> (string) (optional) (rules for codeigniter's form_validation)
	  * 			[object_type]				=> (string) object that holds the file reference
	  * 													(it's the name or alias (in case there is an alias defined) of the object
	  * 			[fieldName]					=> (optional) (string) field name defined in the _classData method
	  * 			[additional_parameters] 	=> (optional) additional parameters for the html form
	  *
	  */
		 function form_definition($options = NULL)
		 {
		 	// all the data defined as in the previous comments
		 	
		 	$options = array('field_type'	=> 'Bootstrap',
							 'objects'		=> 'user_object',
							 'fields'		=> array(
							 							array('id'		=> 'username',
															  'options'	=> array(
															  					 'type'		=> 'Text',
																				 'rules'	=> 'required',
																				 'object_type'	=> 'user_object',
																				 'fieldName'	=> 'username',
																				 'label'		=> 'Nombre de usuario',
																				 )
														),
														array('id'		=> 'password',
															  'options'	=> array(
															  					 'type'		=> 'Password',
																				 'rules'	=> array('insert' => 'required|md5',
																				 					 'update' => 'md5'),
																				 'object_type'	=> 'user_object',
																				 'fieldName'	=> 'password',
																				 'label'		=> 'Contraseña',
															  					)
															  ),
														array('id'		=> 'password_2',
															  'options'	=> array(
															  					 'type'		=> 'Password',
																				 'rules'	=>  array(
																				 					 
																				 					 array( 'password_mistmatch',
																				 					 		function(){

																				 					 			if($this->input->post('password') == $this->input->post('password_2'))
																												{
																													return TRUE;
																												}
																												$this->form_validation->set_message('password_mistmatch', 'Error Message');
																												return FALSE;
																				 					 		})
                                                                                                    ),
																				 'label'		=> 'Repite la contraseña',
															  					)
															  ),
														/*array('id'		=> 'userfile',
															  'options' => array(
															  					 'type'			=> 'Upload',
															  					 'rules'		=> 'required',
															  					 'object_type'	=> 'user_object',
															  					 'fieldName'	=> 'user_photo_object',
															  					 'upload'		=> TRUE
															  					 )
															 )	*/
							 						)
							);
		 	
			
			parent::form_definition($options);
		 }

        function derp()
        {
            
                echo '<pre>';
                  echo var_dump('entra a derp');
                echo '</pre>';
        }
			
		protected function update($object_key)
		{
			if($object_key == 'user_object')
			{
				if($this->sanitized_data['password'] == '')
				{
					unset($this->sanitized_data['password']);
				}
			}
			
			parent::update($object_key);
		}
		
	}
