	<div id="login_box">

		<h1><?php echo lang('forgot_password_heading');?></h1>

		<?php if ($message != NULL) { ?>
			<div class="alert alert-danger"><?php echo $message;?></div>
		<?php } ?>

		<div class="box">
			
			<p><?php echo sprintf(lang('forgot_password_subheading'), $identity_label);?></p>
			
			<?php echo form_open(current_url());?>

			      <p>
			      	<label for="email"><?php echo sprintf(lang('forgot_password_email_label'), $identity_label);?></label> <br />
			      	<?php echo form_input($email);?>
			      </p>
			
			      <p><?php echo form_submit('submit', lang('forgot_password_submit_btn'));?></p>
			
			<?php echo form_close();?>
			
		</div>
	</div>