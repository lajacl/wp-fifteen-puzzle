<?php
$host = "";
$user = "";
$pass = "";
$dbname = "";

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    echo '<script>console.log("DATABASE Could not connect to server Connection failed:",' . json_encode($conn->connect_error) . ');</script>'; //REMOVE
} else {
    echo '<script>console.log("DATABASE Connection established");</script>'; //REMOVE
}

echo '<script>console.log("Server Info:",' . json_encode(mysqli_get_server_info($conn)) . ');</script>'; //REMOVE


?>