<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
include_once 'config/config.php';

if (isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Check if the user is an admin or teacher
    $sql = "SELECT * FROM users WHERE Username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Admin/Teacher login logic
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['Password'])) {
            $_SESSION['role'] = $user['Role'];
            $_SESSION['admin_username'] = $user['Username']; // Unique variable for admin
            header("Location: https://ikigaicollege.ac.ke/Portal/index.php");
            exit();
        } else {
            echo "Invalid password!";
        }
    } else {
        // Check if the user is a student (login with admission number)
        $sql = "SELECT * FROM students WHERE AdmissionNumber = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $student = $result->fetch_assoc();

            // Check if the student has 'Discontinued' status
            if ($student['status'] === 'Discontinued') {
                echo "Your account is discontinued. You cannot log in.";
                exit();
            }

            // Check if the password is hashed
            if (!empty($student['password'])) {
                // If the password is hashed, only verify using the hashed password
                if (password_verify($password, $student['password'])) {
                    $_SESSION['role'] = 'student';
                    $_SESSION['student_admission_number'] = $student['AdmissionNumber']; // Unique variable for student
                    header("Location: https://ikigaicollege.ac.ke/Portal/index.php");
                    exit();
                } else {
                    echo "Invalid password!";
                }
            } else {
                // If no hashed password, use phone number as default password
                if ($password === $student['Phone']) {
                    $_SESSION['role'] = 'student';
                    $_SESSION['student_admission_number'] = $student['AdmissionNumber']; // Unique variable for student
                    header("Location: https://ikigaicollege.ac.ke/Portal/index.php");
                    exit();
                } else {
                    echo "Invalid password!";
                }
            }
        } else {
            echo "No user found!";
        }
    }
}
?>
