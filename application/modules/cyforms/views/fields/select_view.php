<select id="<?=$id?>" name="<?=$name?>">
	<?php foreach ($option_values as $value => $option) { ?>
		<option value="<?=$value?>"><?=$option?></option>
	<?php } ?>
</select>