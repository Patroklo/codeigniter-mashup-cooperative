<?php
	foreach ($comment_list as $comment)
	{
		echo '<div id="comment_'.$comment->get_data('id').'" class="comentario" data-id_comentario="' . $comment->get_data('id') . '">';

		if ($comment->can_update())
		{
			echo '<span class="editar" data-comment_id="'.$comment->get_data('id').'">editar</span><br />';
		}
		if ($comment->can_delete())
		{
			echo '<span class="delete" data-comment_id="'.$comment->get_data('id').'">borrar</span><br />';
		}

		echo $comment->get_data('message_text');
		echo '</div>';
	}