<?php if ($label) { ?>
	<label for="<?=$id?>"><?=$label?></label>
<?php } ?>
<input type="text" class="form-control" id="<?=$id?>"<?=($placeholder)?' placeholder="'.$placeholder.'"':''?>>