<?php if ($error) { ?>
	<p>Error: <?=$error?></p>
<?php } ?>
<?php if ($label) { ?>
	<label for="<?=$id?>"><?=$label?></label>
<?php } ?>
<select class="form-control<?=$class?>"<?=$attributes?>>
	<?php foreach ($options as $key => $o) { ?>
		<option value="<?=$key?>"<?=($key==$value)?' selected':''?>><?=$o?></option>
	<?php } ?>
</select>
<?php if ($help) { ?>
	<p><?=$help?></p>
<?php } ?>