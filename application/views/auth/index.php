<h1><?php echo lang('index_heading');?></h1>
<p><?php echo lang('index_subheading');?></p>

<div id="infoMessage"><?php echo $message;?></div>

<table cellpadding=0 cellspacing=10>
	<tr>
		<th><?php echo lang('index_fname_th');?></th>
		<th><?php echo lang('index_lname_th');?></th>
		<th><?php echo lang('index_email_th');?></th>
		<th><?php echo lang('index_groups_th');?></th>
		<th><?php echo lang('index_status_th');?></th>
		<th><?php echo lang('index_action_th');?></th>
	</tr>
	<?php foreach ($users as $user):?>
		<tr>
			<td><?php echo $user->get_data('first_name');?></td>
			<td><?php echo $user->get_data('last_name');?></td>
			<td><?php echo $user->get_data('email');?></td>
			<td>
				<?php foreach ($user->get_data('groups') as $group):?>
					<?php echo anchor("auth/edit_group/".$group->get_data('id'), $group->get_data('name')) ;?><br />
                <?php endforeach?>
			</td>
			<td><?php echo ($user->get_data('active')) ? anchor("auth/deactivate/".$user->get_data('id'), lang('index_active_link')) : anchor("auth/activate/". $user->get_data('id'), lang('index_inactive_link'));?></td>
			<td><?php echo anchor("auth/edit_user/".$user->get_data('id'), 'Edit') ;?></td>
		</tr>
	<?php endforeach;?>
</table>

<p><?php echo anchor('auth/create_user', lang('index_create_user_link'))?> | <?php echo anchor('auth/create_group', lang('index_create_group_link'))?></p>