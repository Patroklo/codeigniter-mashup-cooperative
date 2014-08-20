<?php if ($error) { ?>
	<p>Error: <?=$error?></p>
<?php } ?>
<?php if ($label) { ?>
	<?=$label?>
<?php } ?>

<?php foreach ($options as $key => $o) { ?>
	<div class="radio">
		<label>
			<input type="radio" name="<?=$name?>" id="<?=$id?>_<?=$key?>" value="<?=$o['value']?>"<?=($o['value']==$value)?' checked':''?><?=(isset($o['disabled']) OR isset($disabled))?' disabled':''?>><?=$o['label']?>
		</label>
	</div>
<?php } ?>

<?php if ($help) { ?>
	<p><?=$help?></p>
<?php } ?>