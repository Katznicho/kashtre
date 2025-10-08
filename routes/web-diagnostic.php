<?php

// Add this temporary diagnostic route to routes/web.php for troubleshooting

Route::get('/diagnostic/upload-test', function() {
    return response()->json([
        'csrf_token' => csrf_token(),
        'session_id' => session()->getId(),
        'user' => Auth::check() ? Auth::user()->email : 'Not authenticated',
        'php_upload_max_filesize' => ini_get('upload_max_filesize'),
        'php_post_max_size' => ini_get('post_max_size'),
        'php_max_file_uploads' => ini_get('max_file_uploads'),
        'php_max_execution_time' => ini_get('max_execution_time'),
        'storage_path_writable' => is_writable(storage_path()),
        'tmp_dir' => sys_get_temp_dir(),
        'tmp_dir_writable' => is_writable(sys_get_temp_dir()),
    ]);
})->middleware(['auth']);

