<?php

	class Log{
		
		public function write($text, $filename = FALSE)
		{
			$dir = 'log/';

			if(is_dir($dir) == FALSE)
			{
				mkdir($dir);
			}

			$route=$_SERVER['DOCUMENT_ROOT']."/".$dir;

			if(is_dir($route) == FALSE)
			{
				mkdir($route);
			}
			
			if($filename == FALSE)
			{
				$filename = 'logs';
			}
			
			if(strpos($filename, '.') == FALSE)
			{
				$filename.= '.txt';
			}
			
			$route=$route.$filename;	

			$text= date("Y-m-d H:i:s").' '.str_replace('"',"'",$text)."\n";

			file_put_contents($route, $text, FILE_APPEND | LOCK_EX);
		}
		
	}