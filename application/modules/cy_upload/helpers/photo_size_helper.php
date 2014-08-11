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
		
		$size_list = array('imagenes_galeria' => array(
														'xl' 		=> array('width' => 1500, 'height' => 900, 'action' => 'resize_smaller'			),
														'lg' 		=> array('width' => 1000, 'height' => 750, 'action' => 'resize_smaller'			),
														'md' 		=> array('width' => 500,  'height' => 500, 'action' => 'resize_smaller'			),
														'sm' 		=> array('width' => 250,  'height' => 250, 'action' => 'resize_smaller'			),
														'sm_crop' 	=> array('width' => 250,  'height' => 250, 'action' => 'resize_crop_smaller'	),
														'xs' 		=> array('width' => 100,  'height' => 100, 'action' => 'resize_smaller'			),
														'xs_crop' 	=> array('width' => 100,  'height' => 100, 'action' => 'resize_crop_smaller'	),
														),
							'mobile_icons' => array(
														'60x60'		=> array('width' => 60, 'height' => 60, 'action' => 'resize_crop_smaller'		),
														'76x76'		=> array('width' => 76, 'height' => 76, 'action' => 'resize_crop_smaller'		),
														'120x120'	=> array('width' => 120, 'height' => 120, 'action' => 'resize_crop_smaller'		),
													)
							);
			
		
		if(array_key_exists($type, $size_list))
		{
			return $size_list[$type];
		}
		
		return FALSE;
	}
