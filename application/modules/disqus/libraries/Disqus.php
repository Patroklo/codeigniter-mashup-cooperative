<?php

	class Disqus
	{
		
		public $carga;
		
		private $CI;
		
		function __construct()
		{
			$this->CI = &get_instance();
			
			$this->CI->load->library('disqus/disqusapi/disqusapi');
			
			$this->CI->load->config('disqus/disqus');
			
			$secret_key = $this->CI->config->item('disqus.api.secret_key');
			
			if ($secret_key != '')
			{
				$this->CI->disqusapi->setKey($secret_key);
			}
		}
		
		function carga($object)
		{
			$this->carga = $object;
		}

		/**
		 * Basic show comments method
		 *
		 * @return string
		 */
		function show_comments()
		{

			$shortname = $this->CI->config->item('disqus.shortname');
			
			// identifier will be set by object id
			
			if ($this->carga === NULL)
			{
				$identifier = 'null';
			}
			else
			{
				$identifier = get_class($this->carga).'_'.$this->carga->get_data('id');
			}
			
			$title	   = $this->CI->config->item('disqus.title');
			
			if ($this->CI->config->item('disqus.url') == TRUE)
			{
				 $url   = base_url().$this->CI->uri->uri_string();
			}
			else
			{
				$url = 'null';
			}
			
			$category_id = $this->CI->config->item('disqus.category_id');
			
			// loads the javascript to make the disqus form
			
			$this->CI->load->library('Js_load');
			
			$this->CI->js_load->add("disqus.show_form('".$shortname."', '".$identifier."', '".$title."', '".$url."', '".$category_id."')");

			return '<div id="disqus_thread"></div>';
		}

		/**
		 * Will use api to make a new category if neccesary
		 *
		 *
		 */
		function api_show_comments()
		{
			$shortname = $this->CI->config->item('disqus.shortname');

			// identifier will be set by object id

			if ($this->carga === NULL)
			{
				$identifier = 'null';
			}
			else
			{
				$identifier = $this->carga->get_data('id');
			}

			$title	   = $this->CI->config->item('disqus.title');

			if ($this->CI->config->item('disqus.url') == TRUE)
			{
				$url   = base_url().$this->CI->uri->uri_string();
			}
			else
			{
				$url = 'null';
			}

			$category_id = $this->_get_category($shortname);

			// loads the javascript to make the disqus form

			$this->CI->load->library('Js_load');

			$this->CI->js_load->add("disqus.show_form('".$shortname."', '".$identifier."', '".$title."', '".$url."', '".$category_id."')");

			return '<div id="disqus_thread"></div>';
		}


		private function _get_category($shortname)
		{
			$category_list = $this->CI->disqusapi->categories->list(array('forum' => $shortname));

			$search_name =  get_class($this->carga);

			$cat_id = FALSE;

			foreach ($category_list as $cat)
			{
				if ($cat['title'] == $search_name)
				{
					$cat_id = $cat['id'];
				}
			}

			if ($cat_id == FALSE)
			{
				// we make a new category for this object
				$category_data = $this->CI->disqusapi->categories->create(array('forum' => $shortname, 'title' => $search_name));

				$cat_id = $category_data['id'];
			}

			return $cat_id;

		}
		
		
	}