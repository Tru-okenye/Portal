

<?php
// Include database connection
include_once __DIR__ . '/../../config/config.php';

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    // Get username from query string
    $username = $_GET['username'] ?? '';

    // Prepare the SQL statement
    $sql = "DELETE FROM users WHERE username = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("s", $username);
        if ($stmt->execute()) {
            echo "User deleted successfully!";
        } else {
            echo "Error: " . $stmt->error;
        }
        $stmt->close();
    }
}

echo "User deleted successfully!";
echo '<meta http-equiv="refresh" content="2;url=index.php?page=users/users">';

?>
