<?php namespace Correcaminos;

	class QueryBox
	{
		
		private static $_filterBox = array();
		
		static function get_filter($name)
		{
			if(!array_key_exists($name, self::$_filterBox))
			{
				return FALSE;
			}
			return self::deep_clone(self::$_filterBox[$name]);
		}
		
		static function set_filter($query, $name = NULL)
		{
			if(is_null($name))
			{
				$name = $query->_get_table();
			}
			self::$_filterBox[$name] = self::deep_clone($query);
		}
		
		static function delete_filter($name)
		{
			if(array_key_exists($name, self::$_filterBox))
			{
				unset(self::$_filterBox[$name]);
			}
		}
		
		static function deep_clone($object)
		{
			return unserialize(serialize($object));
		}
		
		static function filter_exists($name)
		{
			if(!array_key_exists($name, self::$_filterBox))
			{
				return FALSE;
			}
			
			return TRUE;
		}

	}