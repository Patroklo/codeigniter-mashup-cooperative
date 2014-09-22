var comments	= {
	form	: function () {
		
		$(document).on('click','button#comment', function()
		{
			var data = {};
			
			var anon_form = (($('input#anonymous_name').length > 0)?true:false);

            if (anon_form)
            {
                data.anonymous_name = $('input#anonymous_name').val();
                data.recaptcha_response_field = $('#recaptcha_response_field').val();
                data.recaptcha_challenge_field = $('#recaptcha_challenge_field').val();
            }

			data.message_text = $('textarea#message_text').val();
			data.reference_id = $('input#reference_id').val();
            data.message_type = $('input#message_type').val();
            data.inner_id	  = $('input#inner_id').val();

			$.post( base_url + "comments/new" + in_html, data, function( return_data )
					{
						// incorporar el nuevo comentario
                        $('div#comments_list').append(return_data);
                        // ocultamos mensajes de errores
                        $('div.error_comments').hide();
                        $('div.error_comments').html('');
                        
                       // resetear textos y recargar recaptcha en caso de ser necesario
                        $('textarea#message_text').val('');
                        if (anon_form)
            			{
	                        $('input#anonymous_name').val('');
	                        Recaptcha.reload();
	                    }
                        
					}).fail(function(error_data)
					{
						// mostrar mensaje de error
                        $('div.error_comments').html(error_data.responseJSON);
						$('div.error_comments').show();
						// recargamos el recaptcha en caso de haberlo
						if (anon_form)
            			{
	                        Recaptcha.reload();
	                    }
	                    
					});
		});

        $('div#comments_list').on('click', 'span.editar', function(){

            var data = {};

            data.comment_id = $(this).data('comment_id');

            $.get( base_url + "comments/" + data.comment_id + '/edit' + in_html, function( return_data )
            {
                // incorporar el formulario
                $('div#comment_' + data.comment_id).html(return_data);

            }).fail(function(error_data)
            {
                console.log(error_data.responseText);
            });

        });

        $('div#comments_list').on('click','button#edit_comment', function()
        {
            var data = {};

            data.message_text = $('div#comments_list textarea#message_text').val();
            data.reference_id = $('div#comments_list input#reference_id').val();
            data.message_type = $('div#comments_list input#message_type').val();

            var comment_id = $(this).data('comment_id');


            $.post( base_url + "comments/" + comment_id + in_html, data, function( return_data )
            {
                // incorporar el nuevo comentario
                $('div#comment_' + comment_id).html(return_data);

            }).fail(function(error_data)
            {
                // mostrar mensaje de error
                $('div.error_comments').html(error_data.responseJSON);
                $('div.error_comments').show();
            });
        });

        $('div#comments_list').on('click','span.delete', function()
        {


            var comment_id = $(this).data('comment_id');

            $.ajax({
                url: base_url + "comments/" + comment_id,
                type: 'DELETE',
                success: function(result) {
                   $('div#comment_' + comment_id).remove();
                }
            });

        });
		
	}
}
