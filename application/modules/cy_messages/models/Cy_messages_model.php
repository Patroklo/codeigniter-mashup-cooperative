<?php

	class Cy_messages_model extends CI_model
	{
	
		// TODO permitir hacer mensajes que no se muestren públicamente, que sean bocetos?
	
		public $tableName	    = 'messages';
	
		public $object 		    = 'message_object';

		public $user_object     = 'user_object';
		
		public $carga 		    = FALSE;

		public $message_type    = 'no_type';
		
		public $stream		    = array();
	
		function __construct() 
		{
		}
	
		/**
		 * ========================================================================================================
		 * ========================================================================================================
		 *              CRUD CRUD CRUD CRUD
		 * ========================================================================================================
		 * ========================================================================================================
		 */
		 
		 
		 	/**
			  * loads a single message with permissions
			  *
			  * @return message data
			  * @author  Joseba J
			  */
			
			function carga($data, $object = TRUE) 
			{
				if ( ! is_array($data))
				{
					if (!$this->msg_read_permission($data))
					{
						return FALSE;
					}
					
					$data = array('id' => $data);
				}
				else
				{
					if (!$this->global_read_permission())
					{
						return FALSE;
					}
				}
				
				$data['deleted'] = 0;
				
				$query = beep_from($this -> tableName) -> where($data) -> limit(1) -> get();
				
				if ($query -> num_rows() > 0) {
					$query -> result();
					
					if ($object == FALSE) {
						$this -> carga = $query -> row();
					} 
					elseif($object === TRUE)
					{
						$this -> carga = $query -> row(0, $this -> object);

						$this->carga = $this->check_anonymous($this->carga);
					}
					else {
						$this -> carga = $query -> row(0, $object);
					}
				} else {
					$this -> carga = FALSE;
				}
		
				return $this -> carga;
			}
			
			function descarga()
			{
				$this->carga = FALSE;
			}
	
			/**
			 * Inserts new message
			 *
			 * @return inserted id
			 * @author Joseba J
			 */
		
			function insert($data) 
			{
				if ( ! $this->global_insert_permission())
				{
					return FALSE;
				}

				$this->load->helper('url');

				$data['message_type']   = $this->message_type;
				$data['ip']				= $this->input->ip_address();
				
				if ( ! array_key_exists('creacion_date', $data))
				{
					$data['creation_date']	= date("Y-m-d H:i:s");
				}

				$data['user_id']		= ($this->auth->logged_in() ? $this->auth->get_user_id() : 0);

				$new_id = beep_from($this->tableName)->values($data)->insert();

				$data = array();
				$data['message_url']    = url_title($data['message_title'].'_'.$new_id, '_', TRUE);

				beep_from($this->tableName)->values($data)->where('id', $new_id)->update();

				return $this->carga($new_id);
			}
			
			function update($data)
			{
				if ($this->carga === FALSE)
				{
					return FALSE;
				}
				
				if (!$this->msg_update_permission($this->carga->get_data('id')))
				{
					return FALSE;
				}

				$data['edited'] 		= 1;
				$data['edition_date']	= date("Y-m-d H:i:s");
				$data['edition_ip']		= $this->input->ip_address();
				$data['url']            = url_title($data['message_title'].'_'.$this->carga->get_data('id'), '_', TRUE);
				
				foreach ($data as $key => $d)
				{
					$this->carga->set_data($key, $d);
				}
				
				$this->carga->save();
			}
			
			function delete()
			{
				if ($this->carga === FALSE)
				{
					return FALSE;
				}
				
				if (!$this->msg_delete_permission($this->carga->get_data('id')))
				{
					return FALSE;
				}
				$this->correcaminos->begin_transaction();
				{
					beep_from($this->tableName)->where('id', $this->carga->get_data('id'))->values(array('deleted' => 1))->update();
					
					beep_from($this->tableName)->where('parent_id', $this->carga->get_data('id'))->values(array('deleted' => 1))->update();
				}
				$this->correcaminos->commit_transaction();
				
				
				$this->descarga();
			}

			
			/**
			 * loads a stream of a message and all it's answers
			 * using a limit and offset for pagination
			 *
			 * @return array of messages
			 * @author  Joseba J
			 */
			function load_message_stream($message_id, $limit = 15, $offset = 0)
			{

				if ( ! $this->msg_read_permission($message_id))
				{
					return FALSE;
				}
				
				$data_original 				    = array();
				$data_original['id'] 		    = $message_id;
				$data_original['message_type']  = $this->message_type;
				$data_original['parent_id']	    = 0;
				
				$data_answers 				    = array();
				$data_answers['parent_id']	    = $message_id;
				$data_original['message_type']  = $this->message_type;
				
				$query		  = $this->get_query()->where($data_original)->
													 or_where($data_answers)->
													 offset($offset);
				if($limit != FALSE)
				{
					$query	  = $query->limit($limit);
				}
				
				$this->stream = $this->check_anonymous($query->get());
				
				return $this->stream;
			}


			
			/**
			 * loads a stream of messages without it's answers
			 *
			 * @return array of messages
			 * @author  Joseba J
			 */
			function load_reference_stream($reference_id, $limit = 15, $offset = 0)
			{
				if (!$this->global_read_permission())
				{
					return FALSE;
				}
				
				$data_original 					= array();
				$data_original['reference_id']	= $reference_id;
				$data_original['parent_id']		= 0;
				$data_original['message_type']  = $this->message_type;

				
				$query		  = $this->get_query()->where($data_original)->
													offset($offset);
				if($limit != FALSE)
				{
					$query	  = $query->limit($limit);
				}
				
				$this->stream = $query->get();
				
				return $this->stream;
			}

			protected function get_query()
			{
				return beep($this->object)->where('deleted', 0)->order_by('id', 'ASC');
		
			}


			public function change_message_type($message_type)
			{
				$this->message_type = $message_type;
			}

			/**
			 * checks if any of the loaded messages is anonymous and adds a new user
			 * object in it with the anonymous data
			 *
			 * @return boolean
			 * @author  Joseba J
			 */

			protected function check_anonymous($message_list)
			{
				$this->load->model('auth/Ion_auth_model');

				if ( ! is_array($message_list))
				{
					if ($message_list->get_data('user_id') == 0)
					{
						$anonymous_user = new $this->Ion_auth_model->anonymous_object(
														array(
																'id'         => 0,
																'first_name' => $message_list->get_data('anonymous_name'),
																'last_name ' => ''
															)
														);
						$message_list->set_data('author', $anonymous_user);
					}
					
					return $message_list;
				}

				foreach ($message_list as &$message)
				{
					$message = $this->check_anonymous($message);
				}

				return $message_list;
			}

		/**
		 * ========================================================================================================
		 * ========================================================================================================
		 *              SECURITY METHODS
		 * ========================================================================================================
		 * ========================================================================================================
		 */
		 
			/**
			 * gives a global permission about accessing to a specific or 
			 * set of messages
			 *
			 * @return boolean
			 * @author  Joseba J
			 */
			function global_read_permission()
			{
				return TRUE;
			}
		
		
			function global_insert_permission()
			{
				return TRUE;
			}

			function global_update_permission()
			{
				return TRUE;
			}

			function global_delete_permission()
			{
				return TRUE;
			}
			
			function msg_read_permission($message_id)
			{
				if ($this->global_read_permission())
				{
					return TRUE;
				}
				
				return $this->carga->can_read();
			}

			function msg_insert_permission($message_id)
			{
				if ($this->global_insert_permission())
				{
					return TRUE;
				}
			}

			function msg_update_permission()
			{
				if ($this->carga === FALSE)
				{
					return FALSE;
				}
				
				if ($this->global_update_permission())
				{
					return TRUE;
				}
				
				return $this->carga->can_update();
			}
		
			function msg_delete_permission()
			{
				if ($this->carga === FALSE)
				{
					return FALSE;
				}
				
				if ($this->global_delete_permission())
				{
					return TRUE;
				}
				
				return  $this->carga->can_delete();
			}

	}
