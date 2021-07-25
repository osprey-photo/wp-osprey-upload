
<?php
$servername = "localhost";
$username = "bishopsw_osprey";
$password = "]UC5sD9FDPKL";
$dbname="bishopsw_osprey";

echo "Connecting to ".$servername."<br>";
// Create connection

echo "Creating table <br>";

// Create connection


$conn = new mysqli($servername, $username, $password, $dbname);
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
// sql to create table
$sql = "CREATE TABLE Uploads (
    id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(30) NOT NULL,
    filename VARCHAR(50) NOT NULL,
    title VARCHAR(50) NOT NULL,
    purpose VARCHAR(30),
    status VARCHAR(30), 
    reg_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";

if ($conn->query($sql) === TRUE) {
  echo "Table Uploads created successfully <br>";
} else {
  echo "Error creating table: " . $conn->error;
}

//
$sql = "CREATE TABLE Purpose (
    id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(30) NOT NULL,
    description VARCHAR(50) NOT NULL,
    deadline TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";

if ($conn->query($sql) === TRUE) {
  echo "Table Purpose created successfully <br>";
} else {
  echo "Error creating table: " . $conn->error;
}


$conn->close();