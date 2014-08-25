<?php

	require_once APPPATH.'modules/cy_form_generator/models/Cy_correcaminos_form_model.php';

	class Assign_permissions_model extends Cy_correcaminos_form_model {
		
		var $loaded_permissions = array();
		
		var $permission_list = array();
		
		var $loaded_type;
		
		var $loaded_data;
		
		function load_permission($type, $id)
		{

		/*
		 * 
		 * TODO HAY QUE AÑADIR LOS PERMISOS COMO NO CONCEDIDOS 
		 * EN EL USUARIO O GRUPO PARA QUE NO SEA UN COÑAZO TRABAJAR CON
		 * ESTE SISTEMA O SI NO, MEJOR, EL PONER EL ID DEL CAMPO COMO EL ID DEL
		 * PERMISO EN SÍ Y Y A SE ENCARGARÁ EL RECEPTOR DE LA ORDEN DE
		 * INSERCION O UPDATE DE AÑADIRLO O NO.
		 * 
		 * 
		 * 
		 * 
		 * 
		 * 
		 */

			// load all the permissions
			$this->load->model('auth/Ion_auth_model');
			
			$this->loaded_type = $type;
			
			if($type == 'group')
			{
				$query = $this->ion_auth_model->groups()->where('id', $id)->get();
				
				$column = 'group_id';
			}
			else
			{
				
				$query = $this->ion_auth_model->users()->where('id', $id)->get();
				
				$column = 'user_id';
			}
			
			if(count($query) == 0)
			{
				return FALSE;
			}
			else
			{
				$this->loaded_data = $query[0];
			}
			

			$permissions = $this->ion_auth_model->permissions()->get();
			
			foreach($permissions as $p)
			{
				$this->permission_list[$p->get_data('id')] = $p;
			}

			$permissions = beep('assigned_permission_object')->where($column, $id)->get();
			
			foreach($permissions as $p)
			{
				$this->loaded_permissions[$p->get_data('permission_id')] = $p;
			}

			$this->load_form_data();
			
			return $query[0];
		}
		
		
		
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
		 function load_form_data($options = NULL)
		 {
		 	// all the data defined as in the previous comments
		 	
		 	$select_values = array('0'	=> 'No permission',
								   '1'	=> 'Reading',
								   '2'	=> 'Writting');
		 	
		 	$array_fields = array();
			
			foreach($this->permission_list as $permission)
			{
				if(array_key_exists($permission->get_data('id'), $this->loaded_permissions))
				{
					$permission_data = $this->loaded_permissions[$permission->get_data('id')];
					$level = $permission_data->get_data('permission_level');
				}
				else
				{
					$level = 0;
				}
				
				
				$array_fields[] = 
				array(
				'id'		=> 'permiso_'.$permission->get_data('id'),
			 	'options'	=> array(
			  					 'type'			=> 'Select',
								 'rules'		=> 'required|is_natural|greater_than[-1]|less_than[3]',
								 'value'		=> $level,
								 'label'		=> $permission->get_data('name'),
								 'options'		=> array('show_error'	=> FALSE,
								 						 'options' 		=> $select_values)
								 )
				);
			}
		 	

		 	$options = array('field_type'	=> 'Bootstrap',
							 'objects'		=> 'user_object',
							 'fields'		=> $array_fields
							 );

			parent::form_definition($options);
		 }
			
		protected function insert($object_key)
		{
			
			if($this->loaded_type == 'group')
			{
				$column = 'group_id';
			}
			else
			{
				$column = 'user_id';
			}
			
			$update_array = array();
			$insert_array = array();
			
			foreach($this->input->post() as $field => $data)
			{
				if(preg_match('/^permiso_\d$/', $field))
				{
					$permission_id = str_replace('permiso_', '', $field);
					
					if(!array_key_exists($permission_id, $this->permission_list))
					{
						continue;
					}

					if(array_key_exists($permission_id, $this->loaded_permissions))
					{
						$update_array[$permission_id] = array('id' => $this->loaded_permissions[$permission_id]->get_data('id'),'permission_level' => $data);
					}
					else
					{
						$insert_array[] = array($column => $this->loaded_data->get_data('id'), 'permission_id' => $permission_id, 'permission_level' => $data);
					}
					
				}
			}

			$assigned_permissions_table =  $this->config->item('assigned_permissions_table', 'adapted_ion_auth');

			if(!empty($insert_array))
			{
				beep_from($assigned_permissions_table)->insert_batch($insert_array);
			}
			
			if(!empty($update_array))
			{
				beep_from($assigned_permissions_table)->update_batch($update_array, 'id');
			}
		
		
		}
		
	}
