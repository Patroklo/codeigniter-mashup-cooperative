<?php if ($error) { ?>
	<p>Error: <?=$error?></p>
<?php } ?>
<?php if ($label) { ?>
	<label for="<?=$id?>"><?=$label?></label>
<?php } ?>
<input type="date" class="form-control<?=$class?>" id="<?=$id?>" name="<?=$name?>" value="<?=$value?>" <?=$attributes?>>
<?php if ($help) { ?>
	<p><?=$help?></p>
<?php } ?>