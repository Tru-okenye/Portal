
<?php
$servername = "localhost";
$username = "root"; // Change as per your configuration
$password = ""; // Change as per your configuration
$dbname = "IKIGAI"; // Change to your database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>



