<?php

	
	class Cy_comment_admin extends Base_model
	{
		
		var $tableName = 'comment_admin';
		
		function close_comments($type, $reference_id = NULL, $inner_id = NULL)
		{
			$insert_data = array(
								 'type'			=> $type,
								 'reference_id' => $reference_id,
								 'inner_id'		=> $inner_id
								 );
								 
			$id = $this->me()->values($insert_data)->insert();
			
			return $id;
		}
		
		function open_comments($type, $reference_id = NULL, $inner_id = NULL)
		{
			$query = $this->me()->where('type', $type);
			
			if( ! is_null($reference_id))
			{
				$query = $query->where('reference_id', $reference_id);
			}
			
			if ( ! is_null($inner_id))
			{
				$query = $query->where('inner_id', $inner_id);
			}
			
			$query->delete();
			
		}
		
		function is_closed($type, $reference_id = NULL, $inner_id = NULL)
		{
			if (is_null($reference_id) and is_null($inner_id) or (is_null($inner_id)))
			{
				$query = $this->me()->where(array(
								 'type'			=> $type,
								 'reference_id' => $reference_id,
								 'inner_id'		=> $inner_id))->get();
			}
			else
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
								 'inner_id'		=> $inner_id))->get();
			}
			
			return (($query->num_rows() > 0)?TRUE:FALSE);
			
		}
		
		
	}
