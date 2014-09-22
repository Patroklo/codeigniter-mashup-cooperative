<?php

	echo '<div class="error_comments"></div>';
	
	$fieldNames = $this->Cy_comments_form->get_fields(TRUE);
	
	foreach ($fieldNames as $fieldName)
	{
		echo $this->Cy_comments_form->show_field($fieldName);
	}

	echo '<button id="comment" data-reference="'.$reference_id.'" data-type="'.$comment_type.'">Comentar</button>';
