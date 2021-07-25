<?php
$servername = "filepond-boilerplate-php_mariadbtest_1";
$username = "root";
$password = "mypass";
$dbname = "OSPREY_UPLOAD";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "INSERT INTO Uploads (username, filename,title)
VALUES ('John Doe', 'img.jpg','my title')";

if ($conn->query($sql) === TRUE) {
    echo "New record created successfully";
} else {
    echo "Error: " . $sql . "<br>" . $conn->error;
}

$conn->close();