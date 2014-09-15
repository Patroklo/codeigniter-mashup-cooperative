<?php if ($error) { ?>
	<p>Error: <?=$error?></p>
<?php } ?>
<?php if ($label) { ?>
	<label for="<?=$id?>"><?=$label?></label>
<?php } ?>
<textarea class="form-control <?=$class?>" id="<?=$id?>" rows="<?=$rows?>" name="<?=$name?>"<?=$attributes?>><?=$value?></textarea>
<?php if ($help) { ?>
	<p><?=$help?></p>
<?php } ?>