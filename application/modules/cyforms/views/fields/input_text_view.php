<?php if ($label) { ?>
	<label for="<?=$id?>"><?=$label?></label>
<?php } ?>
<input type="text" class="form-control<?=$class?>" id="<?=$id?>"<?=$attributes?>>
<?php if ($help) { ?>
	<p><?=$help?></p>
<?php } ?>