<div id="login_box">

	<h1><?php echo lang('reset_password_heading');?></h1>

	<?php if ($message != NULL) { ?>
		<div class="alert alert-danger"><?php echo $message;?></div>
	<?php } ?>

	<div class="box">
		<?php echo form_open(current_url());?>

		<p>
			<label for="new_password"><?php echo sprintf(lang('reset_password_new_password_label'), $min_password_length);?></label> <br />
			<?php echo form_input($new_password);?>
		</p>

		<p>
			<?php echo lang('reset_password_new_password_confirm_label', 'new_password_confirm');?> <br />
			<?php echo form_input($new_password_confirm);?>
		</p>

		<?php echo form_input($user_id);?>


		<p><?php echo form_submit('submit', lang('reset_password_submit_btn'));?></p>


		<?php echo form_close();?>

	</div>
</div>