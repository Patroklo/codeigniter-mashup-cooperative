<?php

	$CI =& get_instance();
	
	$CI->load->_include_class('modules/cy_messages/Cy_messages_model');

	class Cy_forums_model extends Cy_messages_model
	{
	
		public $tableName			= 'forums_posts';
		
		public $tableForum 			= 'forums';
	
		public $object 				= 'message_object';
		
		public $object_forum 		= 'forum_object';
		
		public $carga 				= FALSE;
		
		public $forum				= FALSE;
		
		public $message_type    	= 'forum';
		
		public $stream				= array();
	
		function __construct() 
		{
			parent::construct();
		}
	
		/**
		 * ========================================================================================================
		 * ========================================================================================================
		 *              CRUD CRUD CRUD CRUD
		 * ========================================================================================================
		 * ========================================================================================================
		 */
		 
		 
		 	function carga_forum($data, $object = TRUE)
			{
				if (!is_array($data)) 
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
				
				$query = beep_from($this -> tableForum) -> where($data) -> limit(1) -> get();
				
				if ($query -> num_rows() > 0) {
					$query -> result();
					
					if ($object == FALSE) {
						$this -> forum = $query -> row();
					} 
					elseif($object === TRUE)
					{
						$this -> forum = $query -> row(0, $this -> object_forum);
						
						if (!$this->check_basic_permissions())
						{
							$this->descarga();
						}
					}
					else {
						$this -> forum = $query -> row(0, $object);
					}
				} else {
					$this -> forum = FALSE;
				}
		
				return $this -> forum;
			}
		 
		 
		 	/**
			  * loads a single message with permissions
			  *
			  * @return message data
			  * @author  Joseba J
			  */
			
			function carga_post($data, $object = TRUE) 
			{
				
				if (!$this->check_basic_permissions())
				{
					return FALSE;
				}
				
				return parent::carga($data, $object);
				
			}
			
			function descarga()
			{
				$this->carga = FALSE;
				$this->forum = FALSE;
			}
	
			/**
			 * Inserts new message
			 *
			 * @return inserted id
			 * @author Joseba J
			 */
		
			function insert($data) 
			{
				if (!$this->check_basic_permissions())
				{
					return FALSE;
				}
				
				$data['reference_id'] = $this->forum->get_data('id');
				
				$inserted_data = parent::insert($data);

				if (array_key_exists('parent_id', $data) && $data['parent_id'] != 0)
				{
					// add last_answer to parent_id
					$this->correcaminos->begin_transaction();
					{
						beep_from($this->tableName)->where('id', $data['parent_id'])->values(array('last_answer' => $inserted_data['id']))->update();
					}
					$this->correcaminos->commit_transaction();
				}

				return $inserted_data;
			}
			
			function update($data)
			{
				if (!$this->check_basic_permissions())
				{
					return FALSE;
				}
				
				// we don't let changes of subforum in a message from update method
				if (array_key_exists('reference_id', $data))
				{
					unset($data['reference_id']);
				}
				
				return parent::update($data);
			}
			
			function delete()
			{
				if (!$this->check_basic_permissions())
				{
					return FALSE;
				}
				
				return parent::delete();
			}

			
			/**
			 * loads a stream of a message and all it's answers
			 * using a limit and offset for pagination
			 *
			 * @return array of messages
			 * @author  Joseba J
			 */
			function load_post_stream($message_id, $limit = 15, $offset = 0)
			{
				if (!$this->check_basic_permissions())
				{
					return FALSE;
				}
							
				if (!$this->msg_read_permission($message_id))
				{
					return FALSE;
				}
				
				$data_original 					= array();
				$data_original['id'] 			= $message_id;
				$data_original['reference_id']	= $this->forum->get_data('id');
				$data_original['parent_id'] 	= 0;

				
				$data_answers 					= array();
				$data_answers['parent_id']		= $message_id;
				$data_answers['reference_id']	= $this->forum->get_data('id');
				
				$query		  = $this->get_query()->where($data_original)->
													 or_where($data_answers)->
													 offset($offset);
				if($limit != FALSE)
				{
					$query	  = $query->limit($limit);
				}
				
				$this->stream['posts'] = $query->get();
				
				return $this->stream;
			}
			
			
			
			/**
			 * loads a stream of messages without it's answers
			 * 
			 * also loads all the subforums that this forum has
			 *
			 * @return array of messages
			 * @author  Joseba J
			 */
			function load_forum_content_stream($limit = 15, $offset = 0)
			{
				if (!$this->check_basic_permissions())
				{
					return FALSE;
				}
				
				$data_original 					= array();
				$data_original['reference_id']	= $this->forum->get_data('id');
				$data_original['parent_id']		= 0;

				
				$query		  = $this->get_query()->where($data_original)->
													offset($offset)->
													order_by('stick', 'DESC');
				if($limit != FALSE)
				{
					$query	  = $query->limit($limit);
				}
				
				$this->stream['posts'] = $query->get();
				
				$data_original 				= array();
				$data_original['parent_id']	= $this->forum->get_data('id');

				$query		  = $this->get_query_forum()->where($data_original);
				
				$this->stream['forums'] = $query->get();
				
				return $this->stream;
			}

			
			/**
			 * gets all the main forums (with no parents)
			 *
			 * @return void
			 * @author  
			 */
			function load_forums() 
			{
				if (!$this->check_basic_permissions())
				{
					return FALSE;
				}
				
				$data_original 				= array();
				$data_original['parent_id']	= 0;

				
				$query		  = $this->get_query_forum()->where($data_original);

				$this->stream['forums'] = $query->get();
				
				return $this->stream;
			}

			protected function get_query()
			{
				return beep($this->object)->where('deleted', 0)->order_by('id', 'ASC');
			}
			
			private function get_query_forum()
			{
				return beep($this->object_forum)->where('deleted', 0)->order_by('order', 'ASC');
			}
			

		/**
		 * ========================================================================================================
		 * ========================================================================================================
		 *              SECURITY METHODS
		 * ========================================================================================================
		 * ========================================================================================================
		 */

		 	function global_insert_permission()
			{
				if ($this->forum === FALSE)
				{
					return FALSE;
				}
				
				if ($this->forum->get_data('stores_posts') == 0)
				{
					return FALSE;
				}
				
				return parent::global_insert_permission();
			}
			
			function msg_update_pemission()
			{
				$return_bool = parent::msg_update_pemission();
				
				if ($return_bool == FALSE)
				{
					return FALSE;
				}
				
				if($this->carga->get_data('closed') == 1)
				{
					return FALSE;
				}
				
				return TRUE;
			}
		 
		 	/**
			  * check the most basic permissions to deal with forum posts
			  * like to haver permission to access the forum.
			  *
			  * @return Boolean
			  * @author  Joseba J
			  */
		 	function check_basic_permissions()
			{
				if ($this->forum === FALSE)
				{
					return FALSE;
				}
				
				if ($this->forum->can_read()== FALSE)
				{
					return FALSE;
				}
				
				return TRUE;
			}
			
			function check_user_is_mod()
			{
				// $this->forum
				// TODO: add permission or check to know if logged user is a mod or admin
				return FALSE;
			}
			
			
		/**
		 * ========================================================================================================
		 * ========================================================================================================
		 *              ADMIN AND MOD METHODS
		 * ========================================================================================================
		 * ========================================================================================================
		 */
			
			/**
			 * moves the post to another subforum.
			 *
			 * @return boolean
			 * @author Joseba J 
			 */
			function admin_move_post($new_subforum) 
			{
				if ($this->carga === FALSE)
				{
					return FALSE;
				}
				
				// checks with the origin forum
				if ($this->check_basic_permissions() === FALSE)
				{
					return FALSE;
				}
			
				if ($this->check_user_is_mod() === FALSE)
				{
					return FALSE;
				}
				
				$this->carga_forum($new_subforum);
				
				// same checks but with the destiny forum
				if ($this->check_basic_permissions() === FALSE)
				{
					return FALSE;
				}
							
				if ($this->check_user_is_mod() === FALSE)
				{
					return FALSE;
				}
				
				// this change counts as a insert in the new forum---
				if ($this->global_insert_permission() == FALSE)
				{
					return FALSE;
				}
				
				$data = array();
				$data['reference_id'] = $new_subforum;
				
				return parent::update($data);

			}
			
			/**
			 * undocumented function
			 *
			 * @return void
			 * @author  
			 */
			function admin_close_post() 
			{
				if ($this->carga === FALSE)
				{
					return FALSE;
				}
				
				if ($this->check_user_is_mod() === FALSE)
				{
					return FALSE;
				}
				
				if ($this->check_basic_permissions() === FALSE)
				{
					return FALSE;
				}
				
				$data = array();
				$data['closed'] = 1;
				
				return parent::update($data);				
			}
	
	}