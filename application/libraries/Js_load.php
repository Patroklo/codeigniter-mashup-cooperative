<?php


if (!defined("BASEPATH"))
    exit("No direct script access allowed");

    class Js_load {
    	
		var $js_list = array(
								'header' => array(),
								'footer' => array()
							);
		
		public function add($function, $type = 'footer')
		{
			if (is_array($function))
			{
				foreach ($function as $f)
				{
					$this->add($f, $type);
				}
			}
			else
			{
				// with this call we add the file where the method is called
				// useful for debug reasons
				$caller = debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 1);
				
				$caller = reset($caller);

				$this->js_list[$type][$caller['file']] = $function;
			}
		}
		
		public function list_methods($type = 'footer')
		{
			$return_data = '';

			if ($this->has_methods($type))
			{
				$return_data.= '<script src="'.base_url().'/js/footer.js"></script>
								<script>
									
									var base_url = "'.base_url().'";
									var in_html  = "?format=html";
								
									$(\'document\').ready(function () {';
										
										foreach ($this->js_list[$type] as $j)
										{
											$return_data.= $j.';';
										}
								
				$return_data.= '	});
								</script>';
			}

			return $return_data;
		}
		
		public function has_methods($type = 'footer')
		{
			return ! empty($this->js_list[$type]);
		}
		
    }
