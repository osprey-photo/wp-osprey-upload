<?php

require_once('./vendor/autoload.php');
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

// Create the logger
$logger = new Logger('archive');

// Now add some handlers
$fileHandler = new StreamHandler('archive.log', Logger::DEBUG);
$fileHandler->getFormatter()->ignoreEmptyContextAndExtra(true);
$logger->pushHandler($fileHandler);
$logger->info('>> Archive creation call called');

// Comment if you don't want to allow posts from other domains
header('Access-Control-Allow-Origin: *');

// Allow the following methods to access this file
header('Access-Control-Allow-Methods: POST');

// Load our configuration for this server
require_once('config.php');

$data = json_decode(file_get_contents('php://input'), true);
$logger->info('Input ', $data);
// Create connection
$conn = new mysqli(OSPREY_DB_HOST, OSPREY_DB_USER, OSPREY_DB_PASSWORD, OSPREY_DB_NAME);
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

/* Prepared statement, stage 1: prepare */
if (!($stmt = $conn->prepare("select uploads.filename,uploads.displayname,uploads.title,purposes.title as purpose from wp_osprey_uploads as uploads, wp_osprey_purposes as purposes where uploads.id=? and uploads.purpose=purposes.id"))) {
    $logger->error("Prepare failed: (" . $conn->errno . ") " . $conn->error);
    throw new Exception("Prepare failed: (" . $conn->errno . ") " . $conn->error);
}
$anon = $data['anonymised'];
$zip = new ZipArchive();
$t = date("Ym_-His");
if (isset($data['name'])) {
    $filename=$data['name'] .'_'.$t .  ($anon?'_anon':'') . '.zip';
    $datacsv=$data['name']. 'images_info_'.$t .'.csv';
} else {
    $filename='images_'.$t . ($anon?'_anon':'') . '.zip';
    $datacsv='images_info_'.$t .'.csv';
}

$logger->info('Filenames created', array($filename, $datacsv));
if ($zip->open(DOWNLOADS_DIR.'/'.$filename, ZipArchive::CREATE)!==true) {
    exit("cannot open <$filename>\n");
}

$fp = fopen(DOWNLOADS_DIR.'/'.$datacsv, 'w');
fputcsv($fp, array( 'filename','newfilename','name','title','purpose'));


$logger->info('Anonymised =', array($anon));
foreach ($data['imageids'] as $imageid) {
    $logger->info('Handling imaged', array($imageid));

    if (!$stmt->bind_param("s", $imageid['id'])) {
        $logger->info("Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error);
        throw new Exception("Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error);
    }

    // make query
    $stmt->execute();
    $result = $stmt->get_result();

    while ($myrow = $result->fetch_assoc()) {
        if ($anon) {
            $newfilename = $myrow['title'].'.jpg';
        } else {
            $newfilename = $myrow['displayname'].'%'.$myrow['title'].'.jpg';
        }
            
        $zip->addFile(UPLOAD_DIR.'/'.$myrow['filename'], $newfilename);
        fputcsv($fp, array( $myrow['filename'],$newfilename,$myrow['displayname'],$myrow['title'],$myrow['purpose'] ));
        $logger->info(print_r($myrow, true));
    }
}
$filesAdded = $zip->numFiles; // as you can't get this after the zip is closed

fclose($fp);
if (! $anon) {
    $zip->addFile(DOWNLOADS_DIR.'/'.$datacsv, $datacsv);
}

$conn->close();
$zip->close();

$data = json_encode(array( 'zipfilename' => $filename,'size'=>$filesAdded,'code'=>200));
$logger->info("json: " . print_r($data, true));

header('Content-Type: application/json');
echo $data;

