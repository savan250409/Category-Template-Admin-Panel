<?php
// Upload configuration file
// This file sets PHP upload limits for the application

// Set upload limits
@ini_set('upload_max_filesize', '100M');
@ini_set('post_max_size', '100M');
@ini_set('max_file_uploads', '1000');
@ini_set('max_input_vars', '10000');
@ini_set('memory_limit', '512M');
@ini_set('max_execution_time', '300');
@ini_set('max_input_time', '300');

// Display current settings for debugging
if (isset($_GET['debug']) && $_GET['debug'] === 'upload') {
    echo "Current Upload Settings:\n";
    echo "=======================\n";
    echo "max_file_uploads: " . ini_get('max_file_uploads') . "\n";
    echo "max_input_vars: " . ini_get('max_input_vars') . "\n";
    echo "upload_max_filesize: " . ini_get('upload_max_filesize') . "\n";
    echo "post_max_size: " . ini_get('post_max_size') . "\n";
    echo "memory_limit: " . ini_get('memory_limit') . "\n";
    echo "max_execution_time: " . ini_get('max_execution_time') . "\n";
    echo "max_input_time: " . ini_get('max_input_time') . "\n";
    exit;
}
?>
