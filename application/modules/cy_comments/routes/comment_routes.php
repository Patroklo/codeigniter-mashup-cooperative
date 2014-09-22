<?php

Route::any('messages',					'cy_comments/Comments/message_list');
Route::post('comments/new_comment',		'cy_comments/Comments/new_comment');
