<?php
/*
--------------------------------------------------------------------------
Application Routes
--------------------------------------------------------------------------
Here is where you can register all of the routes for an application.
It's a breeze. Simply tell Laravel the URIs it should respond to
and give it the Closure to execute when that URI is requested.
*/

Route::post('/octodevel/octomail/upload/{slug}', function($slug){

    /**
     * Upload directory
     */

    //create directory if doesn't exist

    define("UPLOADDIR", "./uploads/public/octodevel/octomail/" . strtolower($slug) . "/");

    if (!file_exists(UPLOADDIR)) {
        mkdir(UPLOADDIR, 0777, true);
    }

    // Detect if it is an AJAX request
    if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        $file = array_shift($_FILES);

        if(move_uploaded_file($file['tmp_name'], UPLOADDIR . basename($file['name']))) {
            $file = dirname($_SERVER['PHP_SELF']) . str_replace('./', '/', UPLOADDIR) . $file['name'];
            $data = array(
                'message' => 'uploadSuccess',
                'file'    => $file,
            );
        } else {
            $error = true;
            $data = array(
                'message' => 'uploadError',
            );
        }
    } else {
        $data = array(
            'message' => 'uploadNotAjax',
            'formData' => $_POST
        );
    }

    echo json_encode($data);
});
