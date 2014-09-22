<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * NORMAL CONFIG
 */

/*
| -------------------------------------------------------------------------
| Tables.
| -------------------------------------------------------------------------
| Database table names.
*/
	// accepts "logged" for only registered users
	// and "anonymous" for allowing anonymous users with verification
	$config['normal']['allowed_comments']   = 'anonymous';

	$config['normal']['tableName']          = 'comments';
	$config['normal']['message_type']       = 'normal';

	$config['normal']['list_view']          = 'cy_comments/comment_list';
	$config['normal']['comment_view']       = 'cy_comments/single_comment';
	$config['normal']['new_comment_form_view']  = 'cy_comments/comment_form';
	$config['normal']['edit_comment_form_view'] = 'cy_comments/edit_comment_form';
	$config['normal']['login_view']			= 'cy_comments/login_view';

	$config['normal']['order_type']         = 'DESC';  // valid values: ASC DESC

	$config['normal']['object']             = 'comment_object';

	$config['normal']['message_type']       = 'comment';

	// defines if the comment form will be shown before or after the
	// comment list

	$config['normal']['comment_form_position'] = 'before';






/* End of file comments.php */
/* Location: ./application/config/comments.php */
