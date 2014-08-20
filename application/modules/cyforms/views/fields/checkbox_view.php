<?php if ($error) { ?>
	<p>Error: <?=$error?></p>
<?php } ?>
<label>
	<input type="checkbox" id="<?=$id?>" name="<?=$name?>"<?php if ($checked) { echo ' checked="checked"'; } ?>><?php if ($label) { echo $label; } ?>
</label>
<?php if ($help) { ?>
	<p><?=$help?></p>
<?php } ?>