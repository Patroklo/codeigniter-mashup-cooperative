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
	// accepts "logged" for only registered users, "oauth" for including oauth registration
	// and "anonymous" for allowing anonymous users with verification

	$config['normal']['allowed_comments']   = 'logged';

	$config['normal']['tableName']          = 'comments';
	$config['normal']['message_type']       = 'normal';

	$config['normal']['list_view']          = 'cy_comments/comment_list';
	$config['normal']['comment_form_view']  = 'cy_comments/comment_form';

	$config['normal']['order_type']         = 'ASC';

	$config['normal']['object']             = 'message_object';

	$config['normal']['url_post_comment']   = 'derp';

	$config['normal']['message_type']       = 'comment';







/* End of file comments.php */
/* Location: ./application/config/comments.php */
