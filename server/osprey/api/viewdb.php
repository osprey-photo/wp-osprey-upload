
<?php

// Load our configuration for this server
require_once('config.php');
// Create connection
$conn = new mysqli(OSPREY_DB_HOST, OSPREY_DB_USER, OSPREY_DB_PASSWORD, OSPREY_DB_NAME);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "SELECT * FROM Uploads";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // output data of each row
    echo "<table>";
    echo "<tr><th>id</th>";
    echo "<th>Username</th>";
    echo "<th>filename</th>";
    echo "<th>Title</th>";
    echo "<th>Purpose</th>";
    echo "<th>Date</th>";
    echo "</tr>";
    while($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row["id"]. "</td>";
        echo "<td>" . $row["username"]. "</td>";
        echo "<td>" . $row["filename"]. "</td>";
        echo "<td>"  . $row["title"]."</td>";
        echo "<td>" . $row["purpose"]. "</td>";
        echo "<td>" . $row["reg_date"]. "</td>";
        

        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "0 results";
}

$conn->close();