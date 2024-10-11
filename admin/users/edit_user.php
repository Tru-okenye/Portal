<?php
// Include database connection
include_once __DIR__ . '/../../config/config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get user inputs
    $username = $_POST['username'];
    $email = $_POST['email'];
    $role = $_POST['role'];

    // Prepare the SQL statement
    $sql = "UPDATE users SET email = ?, role = ? WHERE username = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("sss", $email, $role, $username);
        if ($stmt->execute()) {
            echo "<p style='color: green;'>User updated successfully!</p>";
        } else {
            echo "<p style='color: red;'>Error: " . $stmt->error . "</p>";
        }
        $stmt->close();
    }
}

// Fetch user details for the given username
$username = $_GET['username'] ?? '';
$user = null;
$sql = "SELECT email, role FROM users WHERE username = ?";
if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();
}

// Close the database connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit User</title>
    <style>
        

        h1 {
            color: #E39825; /* Header color */
            margin-bottom: 20px; /* Space below header */
        }

        .user-form {
            max-width: 400px; /* Max width of the form */
            margin: auto; /* Center the form */
            padding: 20px;
            border: 1px solid #3B2314; /* Border color */
            border-radius: 5px; /* Rounded corners */
            background-color: #fff; /* Form background */
        }

        label {
            display: block; /* Stack labels and inputs */
            margin: 10px 0 5px; /* Space above labels */
            color: #3B2314; /* Label color */
            font-weight: bold; /* Bold labels */
        }

        input[type="email"],
        select {
            width: 90%; /* Full width inputs */
            padding: 10px; /* Padding inside inputs */
            margin-bottom: 15px; /* Space below inputs */
            border: 1px solid #3B2314; /* Border color */
            border-radius: 5px; /* Rounded corners */
            font-size: 14px; /* Font size */
            color: #3B2314; /* Text color */
        }

        .user-form button {
            width: 70%; /* Full width button */
            padding: 10px; /* Padding inside button */
            background-color: #E39825; /* Button background */
            color: white; /* Button text color */
            border: none; /* Remove border */
            border-radius: 5px; /* Rounded corners */
            cursor: pointer; /* Pointer cursor */
            font-size: 16px; /* Font size */
        }

        .user-form button:hover {
            background-color: #3B2314; /* Darker color on hover */
        }

        .link {
            display: block; /* Stack the link */
            text-align: center; /* Center the link */
            margin-top: 20px; /* Space above link */
            color: #E39825; /* Link color */
            text-decoration: none; /* Remove underline */
        }
    </style>
</head>
<body>

<h1>Edit User</h1>
<?php if ($user): ?>
    <form action="index.php?page=users/edit_user" method="POST" class="user-form">
        <input type="hidden" name="username" value="<?php echo htmlspecialchars($username); ?>">
        
        <label for="email">Email:</label>
        <input type="email" name="email" id="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
        
        <label for="role">Role:</label>
        <select name="role" id="role" required>
            <option value="admin" <?php echo ($user['role'] == 'admin') ? 'selected' : ''; ?>>Admin</option>
            <option value="teacher" <?php echo ($user['role'] == 'teacher') ? 'selected' : ''; ?>>Teacher</option>
            <option value="student" <?php echo ($user['role'] == 'student') ? 'selected' : ''; ?>>Student</option>
        </select>
        
        <button type="submit">Update User</button>
    </form>
<?php else: ?>
    <p>User not found.</p>
    <a href="index.php?page=users/users" class="link">Return to Users List</a> 
<?php endif; ?>

</body>
</ht
