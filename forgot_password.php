<?php
session_start();
include_once __DIR__ . '/config/config.php';
include_once __DIR__ . '/vendor/autoload.php'; // Adjust the path as necessary

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Set the default time zone
date_default_timezone_set('Africa/Nairobi');

$message = ''; // Variable to hold success or error messages

if (isset($_POST['submit'])) {
    $username = $_POST['username'];

    // Check if the user or student exists
    $user = checkUserExists($username);
    $student = checkStudentExists($username);

    if ($user) {
        handlePasswordReset($user['Email'], $username);
    } elseif ($student) {
        handlePasswordReset($student['Email'], $username);
    } else {
        $message = "<div class='error'>No user or student found with this username/admission number.</div>";
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

function handlePasswordReset($email, $username) {
    global $conn;
    // Generate a unique password reset token
    $token = bin2hex(random_bytes(50));
    $expiry = date("Y-m-d H:i:s", strtotime('+1 hour'));

    // Save the token and expiry to the database
    $sql = "INSERT INTO PasswordResets (Username, Token, Expiry) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $username, $token, $expiry);
    $stmt->execute();

    // Send the reset email
    $resetLink = "http://localhost/IKIGAI/reset_password.php?token=$token";
    $subject = "Password Reset Request";
    $messageBody = "Click the following link to reset your password: $resetLink";
    $smtpConfig = include __DIR__ . '/config/smtp_config.php';

    sendEmail($email, $subject, $messageBody, $smtpConfig);

    // Set success message
    global $message;
    $message = "<div class='success'>Password reset link has been sent to your email address.</div>";
}

function sendEmail($to, $subject, $body, $smtpConfig) {
    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host = $smtpConfig['host'];
        $mail->SMTPAuth = true;
        $mail->Username = $smtpConfig['username'];
        $mail->Password = $smtpConfig['password'];
        $mail->SMTPSecure = $smtpConfig['encryption'];
        $mail->Port = $smtpConfig['port'];

        // Sender
        $mail->setFrom($smtpConfig['username'], 'Your Name');

        // Recipient
        $mail->addAddress($to);

        // Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $body;
        $mail->AltBody = strip_tags($body);

        $mail->send();
    } catch (Exception $e) {
        echo "Email could not be sent. Mailer Error: {$mail->ErrorInfo}";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
    <style>
        body {
            background-color: #f4f4f4;
            color: #3B2314;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .container {
            background-color: #fff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            width: 300px;
        }
        h2 {
            text-align: center;
            color: #E39825;
        }
        label {
            display: block;
            margin-bottom: 5px;
        }
        input[type="text"],
        input[type="submit"] {
            width: 80%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        input[type="submit"] {
            width: 100%;
            background-color: #E39825;
            color: #fff;
            border: none;
            cursor: pointer;
        }
        input[type="submit"]:hover {
            background-color: #d68e20;
        }
        .success, .error {
            text-align: center;
            margin: 10px 0;
        }
        .success {
            color: green;
        }
        .error {
            color: red;
        }
    </style>
</head>
<body>
    <div class="container">
        <?php if ($message): ?>
            <?php echo $message; ?>
        <?php endif; ?>
        <h2>Forgot Password</h2>
        <form method="POST" action="forgot_password.php">
            <label for="username">Username/Admission Number:</label>
            <input type="text" id="username" name="username" required>
            <input type="submit" name="submit" value="Submit">
        </form>
    </div>
</body>
</html>
