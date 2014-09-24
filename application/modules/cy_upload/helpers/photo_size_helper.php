<?php

		/**
		 * opciones
		 * 
		 * 		resize
		 * 		crop
		 * 		resize_crop
		 * 		resize_smaller
		 * 		crop_smaller
		 * 		resize_crop_smaller
		 * 
		 */

	function photo_size_list($type = FALSE)
	{
	
		if ($type == FALSE)
		{
			return FALSE;
		}
		
		$size_list = array('avatar' => array(
												'big' 		=> array('width' => 100,  'height' => 100, 'action' => 'resize_crop_smaller'			),
												'little' 	=> array('width' => 50,   'height' => 50,  'action' => 'resize_crop_smaller'	),
												),
							);
			
		
		if(array_key_exists($type, $size_list))
		{
			return $size_list[$type];
		}
		
		return FALSE;
	}
