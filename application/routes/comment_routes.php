<?php

	Route::any('messages',					    'cy_comments/Comments/list');
	Route::post('comments/new',		            'cy_comments/Comments/new');
	Route::get('comments/{id}/edit',            'cy_comments/Comments/edit/$1');
	Route::post('comments/{id}',                'cy_comments/Comments/edit/$1');
	Route::delete('comments/{id}',              'cy_comments/Comments/delete/$1');


/*     *      ------------------------------------------------------------------
     *      GET     /photos         index       displaying a list of photos
     *      GET     /photos/new     create_new  return an HTML form for creating a photo
     *      POST    /photos         create      create a new photo
     *      GET     /photos/{id}    show        display a specific photo
     *      GET     /photos/{id}/edit   edit    return the HTML form for editing a single photo
     *      PUT     /photos/{id}    update      update a specific photo
     *      DELETE  /photos/{id}    delete      delete a specific photo*/