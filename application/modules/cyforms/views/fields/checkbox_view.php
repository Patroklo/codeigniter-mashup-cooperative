<?php if ($error) { ?>
	<p>Error: <?=$error?></p>
<?php } ?>
<div class="checkbox">
	<label>
		<input type="checkbox" id="<?=$id?>" name="<?=$name?>"<?=($checked)?' checked="checked"':''?><?=($value)?' value="'.$value.'"':''?>><?php if ($label) { echo $label; } ?>
	</label>
</div>
<?php if ($help) { ?>
	<p><?=$help?></p>
<?php } ?>