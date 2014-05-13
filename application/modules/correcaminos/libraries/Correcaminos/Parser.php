<?php namespace Correcaminos;


	class Parser{

		//number of the param used
		private static $_param_pointer  = 0;
		
		
		public function reset_param_pointer()
		{
			self::$_param_pointer = 0;
		}
        
        public static function fetch_param_pointer()
        {
            self::$_param_pointer += 1;
            return ':'.self::$_param_pointer;
        }

        /**
         * Tests whether the string has an SQL operator
         *
         * @access  private
         * @param   string
         * @return  bool
         */
        static function _has_operator($str)
        {
            $str = trim($str);
            if ( ! preg_match("/(<|>|!|=|is null|is not null)/i", $str))
            {
                return FALSE;
            }
    
            return TRUE;
        }
		
		static function _clean_operators($str)
		{
			$return['column'] = preg_replace("/(<|>|!|=|is null|is not null)/i", '', $str);
			$return['column'] = trim($return['column']);
			$return['operator'] = trim(str_replace($return['column'], '', $str));

			return $return;
		}

		

		/**
         * we consider the first chars of the string as the join column and the
         * rest as additional conditions
         */
        public static function _separate_columns_join($data)
        {
              $resp = array();
             
              do
              {
                  preg_match('/(.+)( and | ! | && | not | \|\| | or | xor )(.+)/i', $data, $match);
                if(!empty($match))
                {
                    array_unshift($resp, $match[2], $match[3]);
                    $data = $match[1];
                }
                else
                {
                    array_unshift($resp, $data);
                    $data = NULL;
                }
            
              }while( $data != NULL);
             
              

                  $ent = '';
                  foreach($resp as $r)
                  {
                      if( preg_match("/(<|>|!|=|is null|is not null)/i", $r))
                    {
                           
                              preg_match("/(.+)(<|>|!|=|is null|is not null)(.+)/i", $r, $match);
                             
                            if (strpos($match[1], ".") !== FALSE)
                            {
                                $match[1] = explode('.',trim($match[1]));
                                $match[1] = '`'.$match[1][0].'`.`'.$match[1][1].'`';
                            }                            

                              $ent.= trim($match[1]).' '.trim($match[2]).' '.trim($match[3]);
                    }
                    else
                    {
                        $ent.=' '.trim($r).' ';
                    }
                  }
                  
                  return $ent;

             
        }


        /**
         * we consider the first chars of the string as the join column and the
         * rest as additional conditions
	     */
	        public static function _separate_columns_where($data, $type)
	        {
	              $arrayP[] = array('type' => $type);
	              $resp = array();
	             
	              do
	              {
	                preg_match('/(.+)( and | ! | && | not | \|\| | or | xor )(.+)/i', $data, $match);
	                if(!empty($match))
	                {
	                    array_unshift($resp, $match[2], $match[3]);
	                    $data = $match[1];
	                }
	                else
	                {
	                    array_unshift($resp, $data);
	                    $data = NULL;
	                }
	            
	              }while( $data != NULL);
	                  foreach($resp as $r)
	                  {
	                    if( preg_match("/(<|>|!|=|is null|is not null)/i", $r))
	                    {
	                           
	                              preg_match("/(.+)(<|>|!|=|is null|is not null)(.+)/i", $r, $match);
	
	                              $arrayP[count($arrayP) - 1]['column'] = $match[1].$match[2];
	                              $arrayP[count($arrayP) - 1]['value'] = $match[3];
	                              
	                    }
	                    else
	                    {
	                            $arrayP[] = array('type' => $r);
	                    }
	                  }
	
	                  return $arrayP;
	 
	        }


        /**
         * Checks the alias in the columns to let them escape it
         */       
	        public static function _track_alias_column($data, $pointers = 1)
	        {
				$data = str_replace('`', '', $data);
	            //generate the new pointers
	            if($pointers > 1)
	            {
	                for($_i = 0; $_i < $pointers; $_i++)
	                {
	                     $private_pointer[] = self::fetch_param_pointer();
	                }   
	
	            }
	            else 
	            {
	                     $private_pointer = self::fetch_param_pointer();
	            }
	
	           
	            
	            //separate the column and the operator
	            if(self::_has_operator($data))
	            {
	                preg_match('/([\w\.\s]+)([\W\s]+)/', $data, $match);
	                
	                $data = trim($match[1]);
	                $operator = ' '.trim($match[2]).' ';
	                
	            }
	            else
	            {
	                $operator = '';
	            }
	
	            // if a table alias is used we can recognize it by a space
	            if (strpos($data, " ") !== FALSE)
	            {
	
	                // if the alias is written with the AS keyword, remove it
	                preg_match('/\s+AS\s+[\w\.\s]+/i', $data,$k);
	                // Grab the alias   
	                if(empty($k))
	                {
	                    preg_match('/\s+[\w\.\s]+/i', $data,$k);
	                    $alias = preg_replace('/\s+AS\s+/i', '', $k);   
	
	                    $alias  = trim($alias[0]);
	                    
	                    $column = preg_replace('/\s+[\w\.\s]+/i', '', $data);                    
	                }
	                else 
	                {
	                    $alias = preg_replace('/\s+AS\s+/i', '', $k);   
	
	                    $alias  = trim($alias[0]);
	                    
	                    $column = preg_replace('/\s+AS\s+[\w\.\s]+/i', '', $data); 
	                }
	
	               /* // if the alias is written with the AS keyword, remove it
	                $data = preg_replace('/\s+AS\s+/i', ' ', $data);
	
	                // Grab the alias
	                $alias  = trim(strrchr($data, " "));
	                //$column = trim(str_replace($alias, ' ', $data)); 
	                //$column = trim(preg_replace('/AS\s+'.$alias.'/i', ' ', $data));
	                  */ 
	                  if(preg_match('/\'+[\w]+\'/i', $column) == 0)
	                  {
	                        if (strpos($column, ".") !== FALSE)
	                        {
	                            $column = explode('.',$column);
	                            $column = '`'.$column[0].'`.'.(($column[1] == '*')?'*':'`'.$column[1].'`');
	                        }
	                        else {
	                            $column = '`'.$column.'`';
	                        }
	                        
	                        $ret['column']          = $column.' as `'.$alias.'`'.$operator;
	                        $ret['alias']           = '`'.$alias.'`';
	                        $ret['parameter']       = $private_pointer;
	                  }
	                  else
	                  {
	                        $ret['column']          = $column.' as `'.$alias.'`'.$operator;
	                        $ret['alias']           = '`'.$alias.'`';
	                        $ret['parameter']       = $private_pointer;
	                  }
	                
	            }
	            else{
	
	                    if (strpos($data, ".") !== FALSE)
	                    {
	                        $data = explode('.',$data);
	                        $data = '`'.$data[0].'`.'.(($data[1] == '*')?'*':'`'.$data[1].'`');
	                    }
	                    else {
	                        $data = '`'.$data.'`';
	                    }
	                                        
	                    $ret['column']          = $data.$operator;
	                    $ret['alias']           = '';
	                    $ret['parameter']       = $private_pointer;
	            }
	
	            return $ret;
	        }

			//usado para separar las tablas de formaciones del tipo Nombretabla.Nombrecolumna
			//cuando se quiere comprobar si la tabla y columna corresponden a sus dueños.
			public static function _strip_column_table($data)
			{
				if (strpos($data, ".") !== FALSE)
	            {
	                $data = explode('.',$data);
					
					return array('table' => $data[0], 'column' => $data[1]);
	            }
	            else {
	                return array('table' => NULL, 'column' => $data);
	            }
			}




	}