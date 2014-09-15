<?php

	$CI =& get_instance();
	
	$CI->load->_include_class('modules/cy_messages/models/Cy_messages_model');

	class Cy_blog_model extends Cy_messages_model
	{
		var $message_type       = 'blog';

		// permission names for the permission library of auth
		
		var $write_permission   = 'blog_post_permission';


		function insert($data)
		{
			// reference_id will be usually zero because all users
			// will use the same blog
			$data['reference_id'] = 0;
			
			if (array_key_exists('parent_id', $data))
			{
				unset($data['parent_id']);
			}

			return parent::insert($data);
		}

		function update($data)
		{
			if ($this->carga === FALSE)
			{
				return FALSE;
			}

			if (array_key_exists('parent_id', $data))
			{
				unset($data['parent_id']);
			}

			return parent::update($data);
		}

			/**
			 * loads a stream of a message and all it's answers
			 * using a limit and offset for pagination
			 *
			 * @return array of messages
			 * @author  Joseba J
			 */
			function admin_reference_stream($reference_id, $limit = 15, $offset = 0)
			{

				if (!$this->global_read_permission())
				{
					return FALSE;
				}
				
				$data_original 					= array();
				$data_original['reference_id']	= $reference_id;
				$data_original['parent_id']		= 0;
				$data_original['message_type']  = $this->message_type;

				
				$query		  = $this->get_query(TRUE)->where($data_original)->
													offset($offset);
				if($limit != FALSE)
				{
					$query	  = $query->limit($limit);
				}
				
				$this->stream = $query->get();
				
				return $this->stream;
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
			return $this->auth->has_permission($this->write_permission);
		}

		function global_update_permission()
		{
			return $this->auth->has_permission($this->write_permission);
		}

		function global_delete_permission()
		{
			return $this->auth->has_permission($this->write_permission);
		}


		protected  function get_query($admin = FALSE)
		{
			if ($admin == TRUE)
			{
				return beep($this->object)->where('deleted', 0)->order_by('creation_date', 'ASC');
			}
			else 
			{
				return beep($this->object)->where('deleted', 0)->where('creation_date <=', date('Y-m-d H:i:s'))->order_by('creation_date', 'ASC');
			}
		}
		
		// debe poderse poner posts de cara al futuro (fecha futura)
		// solo se mostrarán los blogs de fecha now <
		

	}