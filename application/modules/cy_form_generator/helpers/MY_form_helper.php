<?php
/* Change-log:
 * 		15/07/2012: Añadidos los presets y extras para placeholder, tooltip y confirm.
 *
 */

function summon_error($mensaje = FALSE)
{

	if ($mensaje == FALSE)
	{
		return FALSE;
	}
	return '<div class="alert alert-error"><a class="close" data-dismiss="alert">×</a>'.$mensaje.'</div>';
}


function open_field($config)
{

	$retornar	= '';

	if (isset($config['wysiwyg']))
	{
		$retornar	.= get_wysiwyg_toolbar($config['id']);
	}

	// Define si ha habido error para mostrar el campo resaltado
	if (form_error($config['id']) != '' && !isset($config['highlight']))
	{
		$hay_error	= ' has-error';
	}
	else
	{
		$hay_error	= '';
	}

	$retornar	.= '<div class="form-group'.$hay_error.'">';

	if ($config['label'] != '')
	{
		if (isset($config['col']))
		{
			$retornar	.= '<div class="field-'.$config['col'].'">';
		}
		else
		{
			$retornar	.= '<div class="field-4">';
		}
		$retornar	.= '<label class="control-label" for="'.$config['id'].'">'.$config['label'].'</label>';
	}

	return $retornar;

}


function close_field($config)
{

	$return	= '</div>';

	if (isset($config['optional']))
	{
		$return	.= '<p class="help-inline text-muted">Opcional</p>';
	}

	if (isset($config['help']))
	{
		$return	.= '<div class="clearfix"></div><p class="help-block">'.$config['help'].'</p>';
	}

	return $return.'</div>';
}


function get_presets($config, $type = 'text')
{

	$presets = array(
		'name'	=> $config['id'],
		'id'	=> $config['id']
	);

	if (isset($config['size']))
	{
		$presets['size'] = $config['size'];
	}

	if (isset($config['accept']))
	{
		$presets['accept'] = $config['accept'];
	}

	$presets['class']	= '';
	if (isset($config['class']))
	{
		$presets['class'] = $config['class'];
	}

	if (isset($config['value']))
	{
		$presets['value'] = $config['value'];
	}

	if (isset($config['style']))
	{
		$presets['style'] = $config['style'];
	}

	if (isset($config['maxlength']))
	{
		$presets['maxlength'] = $config['maxlength'];
	}

	if ($type == 'textarea')
	{

		if (isset($config['rows']))
		{
			$presets['rows']	= $config['rows'];
		}
		else
		{
			$presets['rows'] 	= '3';
		}

		if (isset($config['cols']))
		{
			$presets['cols']	= $config['cols'];
		}
		else
		{
			$presets['cols']	= '50';
		}

	}

	if (isset($config['data']))
	{
		foreach ($config['data'] as $key => $d)
		{
			$presets['data-'.$key]	= $d;
		}

	}

	$CI =& get_instance();
	if ($CI->input->get('field') == $config['id'])
	{
		$presets['autofocus']	= 'autofocus';
	}


	// Extras
	// -------------------

	$extra	= '';

	if (isset($config['placeholder']))
	{
		$extra	.= 'placeholder="'.$config['placeholder'].'" ';
	}

	if (isset($config['disabled']) && $config['disabled'] == TRUE)
	{
		$extra	.= 'disabled="disabled" ';
	}

	if (isset($config['confirm']))
	{
		$extra	.= 'data-confirm="'.$config['confirm'].'" ';
		$presets['class']	= trim($presets['class'].' with-confirm');
	}

	if (isset($config['tooltip']))
	{
		$presets['title']	= $config['tooltip'];
		$presets['class']	= trim($presets['class'].' con-tooltip');
	}

	$extra	= substr($extra, 0, -1);

	// Si no hay clases, no hay attr de class
	if ($presets['class'] == '')
	{
		unset($presets['class']);
	}

	return array(
		'basic'	=> $presets,
		'extra'	=> $extra
	);

}


if (!function_exists('get_wysiwyg_toolbar'))
{
	function get_wysiwyg_toolbar($id) {

		return '

			<div id="wysihtml5_'.$id.'" class="wysiwyg-toolbar ghost" style="display: none;">
				<div class="btn-toolbar">
					<div class="btn-group">
						<a data-wysihtml5-command="bold" class="btn"><i class="fa fa-bold"></i></a>
						<a data-wysihtml5-command="italic" class="btn"><i class="fa fa-italic"></i></a>
					</div>

					<div class="btn-group">
						<a data-wysihtml5-command="justifyLeft" class="btn"><i class="fa fa-align-left"></i></a>
						<a data-wysihtml5-command="justifyCenter" class="btn"><i class="fa fa-align-center"></i></a>
						<a data-wysihtml5-command="justifyRight" class="btn"><i class="fa fa-align-right"></i></a>
					</div>

					<div class="btn-group">
						<a data-wysihtml5-command="insertUnorderedList" class="btn"><i class="fa fa-list"></i></a>
						<a data-wysihtml5-command="insertOrderedList" class="btn"><i class="fa fa-list-ol"></i></a>
					</div>

					<div class="btn-group">
						<a data-wysihtml5-command="fontSize" class="btn" data-wysihtml5-command-value="x-large">Título 1</a>
						<a data-wysihtml5-command="fontSize" class="btn" data-wysihtml5-command-value="large">Título 2</a>
					</div>

					<a data-wysihtml5-command="createLink" class="btn"><i class="fa fa-link"></i> Enlace</a>
					<div data-wysihtml5-dialog="createLink" class="link-box" style="display: none;">
						<label>
							<input data-wysihtml5-dialog-field="href" value="http://" class="text form-control input-sm">
						</label>
						<a data-wysihtml5-dialog-action="save" class="btn btn-sm btn-primary">Enlazar</a> <a data-wysihtml5-dialog-action="cancel" class="btn btn-sm">Cancelar</a>
					</div>
				</div>
			</div>

		';

		// TODO: Idiomas

	}
}


if (!function_exists('summon_language_tabs'))
{
	function summon_language_tabs($languages, $lang_get)
	{

		$return	= '<ul class="nav nav-tabs">';

		foreach ($languages as $l)
		{

			$return	.= '<li';

			if ($l['main'] == 1 && $lang_get == NULL)
			{
				$return	.= ' class="active main"';
			}
			elseif ($lang_get == $l['id'])
			{
				$return	.= ' class="active"';
			}
			elseif ($l['main'] == 1)
			{
				$return	.= ' class="main"';
			}

			$return	.= '><a href="#tab_'.$l['id'].'" data-toggle="tab">'.$l['language'].'</a></li>';

		}

		$return	.= '</ul>';

		return $return;

	}
}


if (!function_exists('summon_input'))
{
	function summon_input($config)
	{

		// Modificadores concretos
		// --------------------------------------------------------------------------

		if (isset($config['datepicker']))
		{
			if (isset($config['value']))
			{
				$config['value'] = fecha_db_to_human($config['value']);
			}

			if (isset($config['class']))
			{
				$config['class'] = $config['class'].' datepicker';
			}
			else
			{
				$config['class'] = 'datepicker';
			}
		}
		else
		{
			if (isset($config['class']))
			{
				$config['class']	= $config['class'].' form-control';
			}
			else
			{
				$config['class'] = 'form-control';
			}
		}

		$field		= open_field($config);
		$presets	= get_presets($config);



		// Si tienen addons
		// --------------------------------------------------------------------------

			$addon_open		= '';
			$addon_close	= '';
			$addon_start	= '';
			$addon_end		= '';

			if (isset($config['addon_start']) OR isset($config['addon_end']))
			{
				$addon_open		= '<div class="input-group">';
				$addon_close	= '</div>';
			}

			if (isset($config['addon_start']))
			{
				$addon_start	= '<span class="input-group-addon">'.$config['addon_start'].'</span>';
			}

			if (isset($config['addon_end']))
			{
				$addon_end		= '<span class="input-group-addon">'.$config['addon_end'].'</span>';
			}


		// Se genera el campo según el tipo
		// --------------------------------------------------------------------------

			if ($config['type'] == 'text')
			{
				$field	.= $addon_open.$addon_start;
				$field	.= form_input($presets['basic'], '', $presets['extra']);
				$field	.= $addon_end.$addon_close;
			}
			elseif ($config['type'] == 'password')
			{
				$presets['basic']['autocomplete']	= 'off';
				$field	.= form_password($presets['basic']);
			}
			elseif ($config['type'] == 'upload')
			{
				$field	.= form_upload($presets['basic']);
			}
			elseif ($config['type'] == 'timezones')
			{
				if(!isset($presets['value']))
				{
					$field	.= timezone_menu('UP1', 'timezone_menu');
				}
				else
				{
					$field	.= timezone_menu($presets['value'], 'timezone_menu');
				}
			}


		$field	.= close_field($config);

		return $field;

	}
}


if (!function_exists('summon_select'))
{
	function summon_select($config, $extra = '') {

		if (!isset($config['value']))
		{
			$config['value'] = '';
		}

		if (isset($config['class']))
		{
			$config['class']	= $config['class'].' form-control';
		}
		else
		{
			$config['class'] = 'form-control';
		}

		if (isset($config['disabled']) && $config['disabled'] == TRUE)
		{
			$disabled	= ' disabled="disabled"';
		}
		else
		{
			$disabled	= '';
		}

		$campo	= open_field($config);
		$campo	.= form_dropdown($config['id'], $config['options'], $config['value'], 'id="'.$config['id'].'" '.$extra.' class="'.$config['class'].'"'.$disabled);
		$campo	.= close_field($config);

		return $campo;

	}
}


if (!function_exists('summon_textarea'))
{
	function summon_textarea($config) {

		if (isset($config['wysiwyg']))
		{
			if (isset($config['class']))
			{
				$config['class']	= $config['class'].' wysiwyg';
			}
			else
			{
				$config['class'] = 'wysiwyg';
			}
		}

		if (isset($config['class']))
		{
			$config['class']	= $config['class'].' form-control';
		}
		else
		{
			$config['class'] = 'form-control';
		}

		$campo		= open_field($config);
		$presets	= get_presets($config, 'textarea');

		if (isset($config['wysiwyg']))
		{
			$presets['basic']	= array_merge($presets['basic'], array('data-wysiwyg' => 'wysihtml5_'.$config['id']));
		}

		$campo		.= form_textarea($presets['basic']);
		$campo		.= close_field($config);

 		return $campo;

	}
}


if (!function_exists('summon_single_checkbox'))
{
	function summon_single_checkbox($config) {

		?>
			<div class="form-group form-checkbox">
				<div class="field-4">
					<div class="checkbox">
						<label>
							<?=form_checkbox(array(
								'name'		=> $config['id'],
								'id'		=> $config['id'],
								'value'		=> $config['value'],
								'checked'	=> $config['checked']
							))?>
							<?=$config['label']?>
						</label>
					</div>
				</div>

				<?php if (isset($config['help'])) { ?>
					<div class="clearfix"></div><p class="help-block"><?=$config['help']?></p>
				<?php } ?>
			</div>
		<?php

	}
}


if (!function_exists('summon_checkbox'))
{
	function summon_checkbox($config) {

	  	$campo	= open_field($config);

		foreach ($config['options'] as $o)
		{
			$campo	.= '<label class="control-label checkbox">';
			$campo	.= form_checkbox(array(
				'name'			=> $o['id'],
				'id'			=> $o['id'],
				'value'			=> $o['value'],
				'checked'		=> $o['checked']
			));
			$campo	.= $o['label'];
			$campo	.= '</label>';
		}

		$campo	.= close_field($config);

	  	return $campo;

	}
}


if (!function_exists('summon_radio'))
{
	function summon_radio($config) {

	  	$campo	= open_field($config);

		foreach ($config['options'] as $o)
		{
			$campo	.= '<label class="radio">';
			$campo	.= form_radio(array(
				'name'			=> $config['id'],
				'id'			=> $config['id'].'_'.$o['value'],
				'value'			=> $o['value'],
				'checked'		=> ($o['value'] == $config['value'])
			));
			$campo	.= $o['label'];
			$campo	.= '</label>';
		}

		$campo	.= close_field($config);

	  	return $campo;

	}
}
