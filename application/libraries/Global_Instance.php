<?php

	class Global_Instance {
		
		private static $instance;
		
		private static $loaded = FALSE;
		
		static function &get_instance()
		{
			if(is_null(self::$instance))
			{
				self::$instance = new stdClass();
			}

			return self::$instance;
		}
		
		static function get_loaded()
		{
			return self::$loaded;
		}
		
		static function set_loaded($state = TRUE)
		{
			self::$loaded = $state;
		}
	}
