<?php if ($error) { ?>
	<p>Error: <?=$error?></p>
<?php } ?>
<?php if ($label) { ?>
	<label for="<?=$id?>"><?=$label?></label>
<?php } ?>
<input type="text" class="form-control<?=$class?>" id="<?=$id?>"<?=$attributes?>  name="<?=$name?>" value="<?=$value?>">
<?php if ($help) { ?>
	<p><?=$help?></p>
<?php } ?>