<?php

	class Disqus
	{

		private $CI;
		
		function __construct()
		{
			$this->CI = &get_instance();
			
			$this->CI->load->library('disqus/disqusapi/disqusapi');
			
			$this->CI->load->config('disqus/disqus');
			
			$secret_key = $this->CI->config->item('disqus.api.secret_key');
			
			$key = $this->CI->config->item('disqus.api.key');
			
			$access_token = $this->CI->config->item('disqus.api.access_token');
			
			if ($secret_key != '')
			{
				$this->CI->disqusapi->setKey($secret_key);
			}
			
			if ($key != '')
			{
				$this->CI->disqusapi->setPublicKey($key);
			}
			
			if ($access_token != '')
			{
				$this->CI->disqusapi->setToken($access_token);
			}		
		}

		// simplest way of using disqus comments
		// uses url as identifier
		function simple_show_comments()
		{
			$shortname = $this->CI->config->item('disqus.shortname');

			$category_id = $this->CI->config->item('disqus.category_id');
			
			// loads the javascript to make the disqus form
			
			$this->CI->load->library('Js_load');
			
			$this->CI->js_load->add("disqus.show_form('".$shortname."')");

			return '<div id="disqus_thread"></div>';
		}

		/**
		 * Basic show comments method (passing identifier)
		 *
		 * @return string
		 */
		function show_comments($comment_type, $reference_id)
		{
			$shortname = $this->CI->config->item('disqus.shortname');
			
			// identifier will be set manually
			$identifier = $comment_type.'_'.$reference_id;
			
			
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
		 * and add the identifier
		 *
		 */
		function api_show_comments($comment_type, $reference_id)
		{
			$shortname = $this->CI->config->item('disqus.shortname');

			// identifier will be set manually
			$identifier = $reference_id;
			
			$title	   = $this->CI->config->item('disqus.title');

			if ($this->CI->config->item('disqus.url') == TRUE)
			{
				$url   = base_url().$this->CI->uri->uri_string();
			}
			else
			{
				$url = 'null';
			}

			$category_id = $this->_get_category($shortname, $comment_type);

			// loads the javascript to make the disqus form

			$this->CI->load->library('Js_load');

			$this->CI->js_load->add("disqus.show_form('".$shortname."', '".$identifier."', '".$title."', '".$url."', '".$category_id."')");

			return '<div id="disqus_thread"></div>';
		}


		private function _get_category($shortname, $comment_type)
		{
			$category_list = $this->CI->disqusapi->categories->list(array('forum' => $shortname));

    echo '<pre>';
      echo var_dump($category_list);
    echo '</pre>';
			$search_name =  $comment_type;

			$cat_id = FALSE;

			foreach ($category_list as $cat)
			{
				if ($cat->title == $search_name)
				{
					$cat_id = $cat->id;
				}
			}

			if ($cat_id == FALSE)
			{
				// we make a new category for this object
				$category_data = $this->CI->disqusapi->categories->create(array('forum' => $shortname, 'title' => $search_name));

				$cat_id = $category_data->id;
			}

    echo '<pre>';
      echo var_dump($cat_id);
    echo '</pre>';
			return $cat_id;

		}
		
		
	}