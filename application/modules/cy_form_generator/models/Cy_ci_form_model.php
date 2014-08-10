<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

	
	require_once APPPATH.'modules/cy_form_generator/models/Cy_base_form_model.php';
	
	class Cy_ci_form_model extends Cy_base_form_model
	{
		// Database type => Codeigniter
		
		/*
		 * Data creation format
		 * 		
		 * 		[field_type]	=> (optional) (string) field form type definition for /libraries/Form_field.php
		 * 		[tables] 		=> (array)
	 	 * 								[name]				=> (string) the table name
		 * 								[primary_key]	=> (optional) (string) 
	 	 * 								[alias] 			=> (optional) (string) neccesary when using two or more times the same table;
		 * 																			if not defined, will use name as alias
		 * 		[fields]		=> (array) of Field format
		 * 
		 */
		 
		/* 
		 * Field format:
		 * 
		 * 		id (unique) (string) (field name and id for the html labels)
		 * 		options (array)
		 * 					[type] 					=> string (field form type, like text, textarea, checkbox...)
		 * 					[rules]					=> string or array
		 * 														string => (rules for codeigniter's form_validation)
		 * 														array => array('insert' => (string), 'update' => (string))
		 * 					[table_alias]			=> (optional) string (object of the field, if not setted, then won't be used in database related methods)
		 * 														  (it's the name or alias (in case there is an alias defined) of the object)
		 * 					[fieldName]				=> (optional) string (field name in the table) if not set, will try to use the id as fieldName
		 * 					[value]					=> (optional) mixed (field data will be set in the form)
		 * 					[additional_parameters] => (optional) additional parameters for the html form
		 * 
		 *
		 * 	Updload Field Format
		 * 
		 * 		id (unique) (string) (field name and id for the html labels)
		 * 		options (array)
		 * 					[type] 					=> string (field form type, like text, textarea, checkbox...)
		 * 					[rules]					=> string or array
		 * 														string => (rules for codeigniter's form_validation)
		 * 														array => array('insert' => (string), 'update' => (string))
		 * 					[value]					=> (optional) mixed (field data will be set in the form)
		 * 					[additional_parameters] => (optional) additional parameters for the html form
		 * 
		 */
		  
		 protected $tables;
		 
		/**
		 * sets the form options and fields for the model
		 *
		 * @return void
		 * @author  Patroklo
		 */
	
	
		 
		function set_options($options)
		{
	
			// we set the object names in the array
			// objects won't be setted until the method carga it's called or the $_post it's read
			// then the object will be made empty
			
			if(array_key_exists('tables', $options))
			{
				if(!is_array($options['tables']))
				{
					$options['tables'] = array($options['tables']);
				}
				
				foreach($options['tables'] as $table_data)
				{
					$this->create_table($table_data);
				}
			}
			
			$new_options = array();
			
			
			// change the fields sub array in order to be comprensible for
			// the parent
			
			if(array_key_exists('fields', $options))
			{
				foreach($options['fields'] as &$field_data)
				{
					if(!array_key_exists('fieldName', $field_data['options']))
					{
						$field_data['options']['fieldName'] = $field_data['id'];
					}
					
					$field_data['options']['name'] = $field_data['id'];

					$new_options[$field_data['id']] =  $field_data['options'];
				}
				
				$options['fields'] = $new_options;
				
			}
			
			parent::set_options($options);
			
		}
	
		/**
		 * creates an object in the $this->tables variable
		 *
		 * @return void
		 * @author  Patroklo
		 */
	
		private function create_table($table_data)
		{
				if(is_array($table_data))
				{
					if(!array_key_exists('primary_key', $table_data))
					{
						$table_data['primary_key'] = 'id';
					}
					
					if(!array_key_exists('alias', $table_data))
					{
						$table_data['alias'] = $table_data['name'];
					}

					$table_data['data']				= NULL;
					$table_data['state']			= 'INSERT';
					
				}
				else
				{
					$tableName = $table_data;
					
					$table_data = array();
					
					$table_data['name']				= $tableName;
					$table_data['alias']			= $tableName;
					$table_data['primary_key'] 		= 'id';
					$table_data['data']				= NULL;
					$table_data['state']			= 'INSERT';
				}
				
				$table_data['fields'] = array();
				
				$this->tables[$table_data['alias']] = $table_data;
		}
		

		protected function update($table_key)
		{
			$table_fields = $this->tables[$table_key]['fields'];
			
			// check every $_post data for object values
			// if there are, it adds the new value to them
			foreach($table_fields as $field_key)
			{
				if(array_key_exists($field_key, $this->sanitized_data))
				{
					$field_value = $this->sanitized_data[$field_key];
					
					$field_options 	= $this->fields[$field_key]->get_options();
					$field_name		= $field_options['fieldName'];

					$this->tables[$table_key]['data'][$field_name] = $field_value;
					
				}
			}

			$table = $this->tables[$table_key];

			$data = $table['data'];
			
			$this->db->where($table['primary_key'], $data[$table['primary_key']]);
			
			unset($data[$table['primary_key']]);
			
			$this->db->update($table['name'], $data); 

		}

		protected function insert($table_key)
		{
			$table_fields = $this->tables[$table_key]['fields'];
			
			// check every $_post data for object values
			// if there are, it adds the new value to them
			foreach($table_fields as $field_key)
			{
				if(array_key_exists($field_key, $this->sanitized_data))
				{
					$field_value = $this->sanitized_data[$field_key];
					
					$field_options 	= $this->fields[$field_key]->get_options();
					$field_name		= $field_options['fieldName'];

					$this->tables[$table_key]['data'][$field_name] = $field_value;
					
				}
			}

			$table = $this->tables[$table_key];

			$data = $table['data'];

			unset($data[$table['primary_key']]);

			$id = $this->db->insert($table['name'], $data); 
			
			$this->tables[$table_key]['data'] = $table['data'];
			$this->tables[$table_key]['data'][$table['primary_key']] = $id;
		}



		/**
		 * adds or edits the data of a form field
		 *
		 * @return null
		 * @author Patroklo
		 */
		function set($field_id, $options = NULL) 
		{
			
			if(is_array($options) && array_key_exists('table_alias', $options))
			{
				$this->tables[$options['table_alias']]['fields'][] = $field_id;
			}
			
			return parent::set($field_id, $options);
		}

		
		/**
		 * In this version of the class the method checks every object to see if it's in insert
		 * or update state, then it calls the insert or update method with the object
		 * 
		 * @return void
		 * @author  Patroklo
		 */
		 
		function save($data = NULL)
		{
			
			parent::save($data);
			
			// now calls update or insert depending of each object
	
			foreach($this->tables as $key => $table)
			{
	
				if($table['state'] == 'INSERT')
				{
					$this->insert($key);
				}
				// it will enter here if the object it's in update or delete state
				else
				{
					$this->update($key);
				}
			}
			
		}
		
		function fill_table($table_alias, $row = NULL)
		{
			if($row === NULL and count($this->tables) > 1)
			{
				throw new Exception("You can't call a fill_table method without a filter.", 1);
			}
			
			if(!array_key_exists($table_alias, $this->tables))
			{
				throw new Exception("The table ".$table_alias." it's not defined in the form.", 1);
			}
			
			if(!is_object($row))
			{
				throw new Exception("The data must be in stdClass format.", 1);
			}
			
			$this->tables[$table_alias]['data'] = (array) $row;
			$this->tables[$table_alias]['state'] = 'UPDATE';
			
			$this->_load_table_fields($table_alias);
			
			if($this->tables[$table_alias]['data'] !== NULL and $this->is_loaded() == FALSE)
			{
				$this->set_loaded($this->tables[$table_alias]['data'] != NULL);
			}	

		}
	
	
		function load_table($table_alias, $filter = NULL)
		{
			if($filter === NULL and count($this->tables) > 1)
			{
				throw new Exception("You can't define a carga method without a filter.", 1);
			}
			elseif($filter === NULL and count($this->tables) == 1)
			{
				$filter 		= $table_alias;
				$table_alias 	= key($this->tables);
			}
			
			if(!array_key_exists($table_alias, $this->tables))
			{
				throw new Exception("The object ".$table_alias." it's not defined in the form.", 1);
			}
			
			
			if(is_numeric($filter))
			{
				$filter = array('id' => $filter);
			}
			
			$query = $this->db->get_where($this->tables[$table_alias]['name'], $filter, 1);
			if($query->num_rows() == 0)
			{
				throw new Exception("The object ".$table_alias." could not be loaded.", 1);
			}

			$row = (array) $query->row(); 
			
			$this->tables[$table_alias]['data'] = $row;
			$this->tables[$table_alias]['state'] = 'UPDATE';
			
			$this->_load_table_fields($table_alias);
			
			if($this->tables[$table_alias]['data'] !== NULL)
			{
				$this->set_loaded($this->tables[$table_alias]['data'] != NULL);
			}
			
		}
		
		private function _load_table_fields($table_alias)
		{	
			// we give the fields a value
			foreach($this->fields as &$field)
			{
				$field_options = $field->get_options();
				
				if(array_key_exists('table_alias', $field_options) && $field_options['table_alias'] == $table_alias)
				{
					$table_name 		= $field_options['table_alias'];
					$field_name 		= $field_options['fieldName'];
		
					if(!empty($this->tables[$table_name]['data']))
					{
						$field_value = $this->tables[$table_name]['data'][$field_name];
						$field->set_value($field_value);
					}
				}
			}
		}



		function field_set_value($field_id, $value = NULL)
		{
			// empty for this class
		} 
	}