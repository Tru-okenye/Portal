<?php
// Include database connection
include_once __DIR__ . '/../../config/config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get user inputs
    $username = $_POST['username'];  // Or admission number for students
    $email = $_POST['email'];
    $password = $_POST['password'];
    $role = $_POST['role'];  // Could be 'admin', 'teacher'
    
    // Check if the username or email already exists
    $checkSql = "SELECT COUNT(*) FROM users WHERE username = ? OR email = ?";
    if ($checkStmt = $conn->prepare($checkSql)) {
        $checkStmt->bind_param("ss", $username, $email);
        $checkStmt->execute();
        $checkStmt->bind_result($count);
        $checkStmt->fetch();
        $checkStmt->close();

        if ($count > 0) {
            echo "Error: Username or Email already exists.";
            exit; // Stop further processing
        }
    }

    // Hash the password using password_hash()
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    // Prepare the SQL statement for insertion
    $sql = "INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)";
    
    // Initialize the database connection
    if ($stmt = $conn->prepare($sql)) {
        // Bind the user inputs to the prepared statement
        $stmt->bind_param("ssss", $username, $email, $hashed_password, $role);
        
        // Execute the statement
        if ($stmt->execute()) {
            echo "New user added successfully!";
            echo '<meta http-equiv="refresh" content="2;url=index.php?page=users/users">';

        } else {
            echo "Error: " . $stmt->error;
        }
        
        // Close the statement
        $stmt->close();
    }
    
    // Close the database connection
    $conn->close();
}
?>
