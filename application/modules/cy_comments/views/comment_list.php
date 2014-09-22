<?php

	if ($login_view != FALSE)
	{
		echo $this->load->view($login_view, '', TRUE);
	}

	if ($can_comment == TRUE and $comment_form_position == 'before')
	{
		echo $this->load->view($comment_form_view, $comment_form_data, TRUE);
	}

	echo '<div id="comments_list" class="comments_list">';

		$this->load->view($single_comment_view, array('comment_list' => $comment_list));

	echo '</div>';

	if ($can_comment == TRUE and $comment_form_position != 'before')
	{
		echo $this->load->view($comment_form_view, $comment_form_data, TRUE);
	}