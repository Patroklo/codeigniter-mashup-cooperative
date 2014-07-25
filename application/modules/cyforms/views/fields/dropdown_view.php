<?php if ($label) { ?>
	<label for="<?=$id?>"><?=$label?></label>
<?php } ?>
<select class="form-control">
	<?php foreach ($options as $key => $o) { ?>
		<option value="<?=$key?>"<?=($key==$value)?' selected':''?>><?=$o?></option>
	<?php } ?>
</select>