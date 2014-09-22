var disqus	= {
	show_form	: function (shortname, identifier, title, url, category_id) {
		
		var disqus_shortname = shortname; 
		   
        // required: replace example with your forum shortname
        if (identifier != 'null')
		{
	   		disqus_identifier = identifier;
	    }
	    
        if (title != 'null')
		{
	   		disqus_title = title;
	    }
	    
	    if (url != 'null')
		{
	   		disqus_url = url;
	    }
	    
	    if (category_id != 'null')
		{
	   		disqus_category_id = category_id;
	    }

        /* * * DON'T EDIT BELOW THIS LINE * * */
        (function() {
            var dsq = document.createElement('script'); dsq.type = 'text/javascript'; dsq.async = true;
            dsq.src = '//' + disqus_shortname + '.disqus.com/embed.js';
            (document.getElementsByTagName('head')[0] || document.getElementsByTagName('body')[0]).appendChild(dsq);
        })();
		
	}
}
