<?php

require_once('./vendor/autoload.php');
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

// Create the logger
$logger = new Logger('submit');
// Now add some handlers
$logger->pushHandler(new StreamHandler('my_app.log', Logger::DEBUG));
$logger->info('Submit called');

// Comment if you don't want to allow posts from other domains
header('Access-Control-Allow-Origin: *');

// Allow the following methods to access this file
header('Access-Control-Allow-Methods: POST');

// Load the FilePond class
require_once('FilePond.class.php');

// Load our configuration for this server
require_once('config.php');

// Catch server exceptions and auto jump to 500 response code if caught
FilePond\catch_server_exceptions();
FilePond\route_form_post(ENTRY_FIELD, [
    'FILE_OBJECTS' => 'handle_file_post',
    'BASE64_ENCODED_FILE_OBJECTS' => 'handle_base64_encoded_file_post',
    'TRANSFER_IDS' => 'handle_transfer_ids_post'
]);

function handle_file_post($files)
{
    global $logger;
    $logger->info('handle_file_post' . $files);
    // This is a very basic implementation of a classic PHP upload function, please properly
    // validate all submitted files before saving to disk or database, more information here
    // http://php.net/manual/en/features.file-upload.php
    
    foreach ($files as $file) {
        FilePond\move_file($file, UPLOAD_DIR);
    }
}

function handle_base64_encoded_file_post($files)
{
    global $logger;
    $logger->info('handle_base64_encoded_file_post' . $files);

    foreach ($files as $file) {

        // Suppress error messages, we'll assume these file objects are valid
        /* Expected format:
        {
            "id": "iuhv2cpsu",
            "name": "picture.jpg",
            "type": "image/jpeg",
            "size": 20636,
            "metadata" : {...}
            "data": "/9j/4AAQSkZJRgABAQEASABIAA..."
        }
        */
        $file = @json_decode($file);

        // Skip files that failed to decode
        if (!is_object($file)) {
            continue;
        }

        // write file to disk
        FilePond\write_file(
            UPLOAD_DIR,
            base64_decode($file->data),
            FilePond\sanitize_filename($file->name)
        );
    }

}

function handle_transfer_ids_post($ids) {
    global $logger;
    $logger->info('handle_transfer_ids_post' . implode(',',$ids));
    foreach ($ids as $id) {
      
        // create transfer wrapper around upload
        $transfer = FilePond\get_transfer(TRANSFER_DIR, trim($id));
        $logger->info('got transfer');
        // transfer not found
        if (!$transfer) continue;
        
        // move files
        $logger->info('Moving files');
        $files = $transfer->getFiles(defined('TRANSFER_PROCESSOR') ? TRANSFER_PROCESSOR : null);
        
        foreach($files as $file) {
            $logger->info('File to move is'.print_r($file,true));
            FilePond\move_file($file, UPLOAD_DIR);
        }

        // handle the metadata file
        $metadata = $transfer->getMetadata();
        $logger->info(print_r($metadata,true));
        $title=$metadata->title;
        $username=$metadata->username;
        $displayname=$metadata->displayname;
        $filename=$metadata->filename; 
        $purpose=$metadata->purpose;
        $logger->info('handling '.$username." ".$displayname." ".$filename." ".$title." ".$purpose);
        // Create connection
        $conn = new mysqli(OSPREY_DB_HOST, OSPREY_DB_USER, OSPREY_DB_PASSWORD, OSPREY_DB_NAME);
        // Check connection
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }
        /* Prepared statement, stage 1: prepare */
        if (!($stmt = $conn->prepare("INSERT INTO wp_osprey_uploads (username, displayname, filename, title, purpose) VALUES (?, ?, ?, ?,?)"))) {
            $logger->error( "Prepare failed: (" . $conn->errno . ") " . $conn->error);
            throw new Exception("Prepare failed: (" . $conn->errno . ") " . $conn->error);
        }

        if (!$stmt->bind_param("sssss",$username, $displayname, $filename, $title, $purpose)) {
            $logger->info("Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error);
            throw new Exception("Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error);
        }

        if (!$stmt->execute()) {
            $logger->info("Execute failed: (" . $stmt->errno . ") " . $stmt->error);
            throw new Exception("Execute failed: (" . $stmt->errno . ") " . $stmt->error);
        } else {
            $logger->info( "New record created successfully");
        } 
         
        
        $conn->close();

        // remove transfer directory
        FilePond\remove_transfer_directory(TRANSFER_DIR, $id);
    }

}
