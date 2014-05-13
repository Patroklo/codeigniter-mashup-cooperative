<?php namespace Correcaminos;

		class Warning {
		
			public static function exception($message) {
				try{
					throw new \Exception($message);
				} catch( \Exception $e ) {
				   echo "Correcaminos caught an exception: {$e->getMessage()}";
				   echo '<pre>';
				   	echo $e->getTraceAsString();
				   echo '</pre>';
                   die();
				}
				
			}
		
		}