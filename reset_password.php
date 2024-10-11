<?php
session_start();
include_once __DIR__ . '/config/config.php';

$message = ''; // Variable to hold success or error messages

if (isset($_GET['token'])) {
    $token = $_GET['token'];

    // Check if the token exists and is valid
    $sql = "SELECT * FROM PasswordResets WHERE Token = ? AND Expiry > NOW()";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $reset = $result->fetch_assoc();
        $username = $reset['Username'];

        if (isset($_POST['reset'])) {
            $newPassword = $_POST['password'];

            // Hash the new password
            $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);

            // Determine if it's a user or a student
            $user = checkUserExists($username);
            $student = checkStudentExists($username);

            if ($user) {
                // Update the user password
                $sql = "UPDATE Users SET Password = ? WHERE Username = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ss", $hashedPassword, $username);
                $stmt->execute();
            } elseif ($student) {
                // Update the student password
                $sql = "UPDATE Students SET Password = ? WHERE AdmissionNumber = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ss", $hashedPassword, $username);
                $stmt->execute();
            } else {
                $message = "<div class='error'>User or student not found.</div>";
                exit;
            }

            // Delete the reset token
            $sql = "DELETE FROM PasswordResets WHERE Token = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $token);
            $stmt->execute();

            // Success message with login link
            $message = "<div class='success'>Password has been reset successfully. <a href='login_form.php'>Click here to log in</a>.</div>";
        }
    } else {
        $message = "<div class='error'>Invalid or expired token.</div>";
    }
}

function checkUserExists($username) {
    global $conn;
    $sql = "SELECT * FROM Users WHERE Username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->num_rows > 0 ? $result->fetch_assoc() : false;
}

function checkStudentExists($username) {
    global $conn;
    $sql = "SELECT * FROM Students WHERE AdmissionNumber = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->num_rows > 0 ? $result->fetch_assoc() : false;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Reset</title>
    <style>
        body {
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .container {
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
        }
        .container h2 {
            color: #3B2314;
            text-align: center;
        }
        .success, .error {
            margin-bottom: 20px;
            padding: 10px;
            border-radius: 5px;
            text-align: center;
        }
        .success {
            color: white;
            background-color: green;
        }
        .error {
            color: #ffffff;
            background-color: #d9534f;
        }
        .success a {
            color: #ffffff;
            text-decoration: underline;
        }
        label {
            display: block;
            margin-bottom: 8px;
            color: #333;
        }
        input[type="password"], input[type="submit"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 5px;
            border: 1px solid #ccc;
            box-sizing: border-box;
        }
        input[type="submit"] {
            background-color: #E39825;
            color: white;
            border: none;
            cursor: pointer;
        }
        input[type="submit"]:hover {
            background-color: #3B2314;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Password Reset</h2>

    <!-- Display the message -->
    <?php if (!empty($message)): ?>
        <?php echo $message; ?>
    <?php endif; ?>

    <form method="POST" action="reset_password.php?token=<?php echo htmlspecialchars($token); ?>">
        <label for="password">New Password:</label>
        <input type="password" id="password" name="password" required>
        <input type="submit" name="reset" value="Reset Password">
    </form>
</div>

</body>
</html>
