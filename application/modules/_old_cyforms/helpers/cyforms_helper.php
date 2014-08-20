<?php

function generate_field($field, $options)
{

	$CI	=& get_instance();
	return $CI->cyforms->{$field}->generate($options);

}