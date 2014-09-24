<?php
$CI =& get_instance();

$CI->load->_include_class('models/Base_model');
	
	class Cy_comment_admin extends Base_model
	{
		
		var $tableName = 'comment_admin';
		
		function close_comments($type, $reference_id = NULL, $inner_id = NULL)
		{
			if ( is_null($reference_id) and is_null($inner_id))
			{
				$this->me()->where('type', $type)->delete();
			}
			elseif ( ! is_null($reference_id) and is_null($inner_id))
			{
				$this->me()->where('type', $type)->where('reference_id', $reference_id)->delete();
			}
			else
			{
				$this->me()->where('type', $type)->where('reference_id', $reference_id)->where('inner_id', $inner_id)->delete();
			}

			$insert_data = array(
								 'type'			=> $type,
								 'reference_id' => $reference_id,
								 'inner_id'		=> $inner_id,
				                 'permission'   => 0
								 );
								 
			$id = $this->me()->values($insert_data)->insert();
			
			return $id;
		}
		
		function open_comments($type, $reference_id = NULL, $inner_id = NULL)
		{
			// if it's a global type open, we delete all closed comments
			if ( is_null($reference_id) and is_null($inner_id))
			{
				$this->me()->where('type', $type)->delete();
				return;
			}
			elseif ( ! is_null($reference_id) and is_null($inner_id))
			{
				$this->me()->where('type', $type)->where('reference_id', $reference_id)->delete();

				$insert_data = array(
					'type'          => $type,
					'reference_id'  => $reference_id,
					'inner_id'      => NULL,
					'permission'    => 1
				);

				$this->me()->values($insert_data)->insert();

				return;
			}
			else
			{
				// if all data of the parameters it's not null, we will post an open post there

				$this->me()->where('type', $type)->where('reference_id', $reference_id)->where('inner_id', $inner_id)->delete();

				$insert_data = array(
					'type'          => $type,
					'reference_id'  => $reference_id,
					'inner_id'      => $inner_id,
					'permission'    => 1
				);

				$this->me()->values($insert_data)->insert();
				return;
			}
		}
		
		function is_closed($type, $reference_id = NULL, $inner_id = NULL)
		{
			$query = $this->me()->where(array(
					'type'			=> $type,
					'reference_id' => NULL,
					'inner_id'		=> NULL))->
			or_where(array(
					'type'			=> $type,
					'reference_id' => $reference_id,
					'inner_id'		=> NULL))->
			or_where(array(
					'type'			=> $type,
					'reference_id' => $reference_id,
					'inner_id'		=> $inner_id))->
			order_by('type', 'ASC')->
			order_by('reference_id', 'ASC')->
			order_by('inner_id','ASC')->
			get();

			if ($query->num_rows() == 0)
			{
				return FALSE;
			}

			$closed = FALSE;

			$types = array('type', 'reference_id', 'inner_id');
			
			$permission_list = $query->result();
			
			$p = end($permission_list);
			
			// foreach ($query->result() as $p)
			// {
			foreach ($types as $t)
			{
				if ( ! is_null($p->{$t}) and $p->permission == 1)
				{
					$closed = FALSE;
				}
				elseif ( ! is_null($p->{$t}) and $p->permission == 0)
				{
					$closed = TRUE;
				}
			}
			// }

			return $closed;


/*			if ( is_null($reference_id) and is_null($inner_id))
			{
				$query = $this->me()->where(array(
								'type'			=> $type,
								'reference_id'  => $reference_id,
								'inner_id'		=> $inner_id,
							    'permission'    => 1))->get();
			}
			elseif (is_null($inner_id))
			{
				$query = $this->me()->where(array(
								 'type'			=> $type,
								 'reference_id' => $reference_id,
								 'inner_id'		=> $inner_id))->get();
			}
			else
			{*/
				$query = $this->me()->where(array(
								 'type'			=> $type,
								 'reference_id' => NULL,
								 'inner_id'		=> NULL))->
								 or_where(array(
								 'type'			=> $type,
								 'reference_id' => $reference_id,
								 'inner_id'		=> NULL))->
								 or_where(array(
								 'type'			=> $type,
								 'reference_id' => $reference_id,
								 'inner_id'		=> $inner_id))->get();
			//}


			
			return (($query->num_rows() > 0)?TRUE:FALSE);
			
		}
		
		
	}
