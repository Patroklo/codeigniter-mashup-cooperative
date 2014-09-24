	<div id="login_box">

		<h1>Acceder al panel de administración</h1>

		<?php if ($message != NULL) { ?>
			<div class="alert alert-danger"><?php echo $message;?></div>
		<?php } ?>

		<div class="box">
			<?=form_open(current_url())?>

				<?=lang('login_identity_label', 'identity')?>
				<?=form_input($identity)?>

			    <?=lang('login_password_label', 'password')?>
			    <?=form_input($password)?>

				<input type="hidden" name="referer" value="">

				<div class="checkbox">
					<?=form_checkbox('remember', '1', FALSE, 'id="remember"')?>
					<?=lang('login_remember_label', 'remember')?>
				</div>

				<?=form_submit('submit', lang('login_submit_btn'))?>

			</form>
		</div>

		<p><a href="/forgot_password"><?=lang('login_forgot_password')?></a></p>

	</div>