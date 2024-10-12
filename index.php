<?php
session_start();
include 'config/config.php';

// Check if user is logged in
if (!isset($_SESSION['admin_username']) && !isset($_SESSION['student_admission_number'])) {
    header('Location: https://ikigaicollege.ac.ke/Portal/login_form.php'); // Redirect to login page if not logged in
    exit();
}

// Determine user role and fetch appropriate data
if (isset($_SESSION['admin_username'])) {
    $username = $_SESSION['admin_username'];
    
    // Fetch user role from the database
    $sql = "SELECT Role FROM users WHERE Username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $role = $user['Role']; // Get the user's role
    } else {
        echo "User not found.";
        exit();
    }
} elseif (isset($_SESSION['student_admission_number'])) {
    $username = $_SESSION['student_admission_number'];
    $role = 'student'; // Default to student role
} else {
    echo "User not found.";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>

    <!-- Common CSS -->
    <link rel="stylesheet" href="https://ikigaicollege.ac.ke/Portal/assets/css/main.css"> 
    <link rel="stylesheet" href="https://ikigaicollege.ac.ke/Portal/assets/css/header.css"> 
    <link rel="stylesheet" href="https://ikigaicollege.ac.ke/Portal/assets/css/sidebar.css"> 
    <link rel="stylesheet" href="https://ikigaicollege.ac.ke/Portal/assets/css/footer.css"> 
    <link rel="stylesheet" href="https://ikigaicollege.ac.ke/Portal/assets/css/student_list.css">

    <!-- FontAwesome CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <!-- Header -->
    <?php include __DIR__ . '/includes/header.php'; ?>
</head>
<body>


    
        <?php 
            // Load the correct sidebar based on the user's role
            if ($_SESSION['role'] == 'admin') {
                include 'includes/sidebar.php'; // Admin sidebar
            } elseif ($_SESSION['role'] == 'teacher') {
                include 'includes/sidebar_teacher.php'; // Teacher sidebar
            } elseif ($_SESSION['role'] == 'student') {
                include 'includes/sidebar_student.php'; // Student sidebar
            } else {
                echo "Unknown role!";
                exit();
            }
        ?>


  <!-- Main content area -->
<div id="main-content" class="main-content">
    <?php
    // Determine which page to include based on the URL parameter
    $page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';
    
    // Set the default folder based on the user role
    if ($_SESSION['role'] == 'admin' || $_SESSION['role'] == 'teacher') {
        $folder = 'admin/';
    } elseif ($_SESSION['role'] == 'student') {
        $folder = 'student/';
    } else {
        echo "Unknown role!";
        exit();
    }

    // Construct the full path to the requested page
    $page_path = $folder . $page . '.php';

    // Check if the requested page exists
    if (!file_exists($page_path)) {
        // Fallback to default dashboard based on role if the page doesn't exist
        if ($_SESSION['role'] == 'admin') {
            $page_path = 'admin_dashboard.php';
        } elseif ($_SESSION['role'] == 'teacher') {
            $page_path = 'teacher_dashboard.php';
        } elseif ($_SESSION['role'] == 'student') {
            $page_path = 'student_dashboard.php';
        }
    }

    // Include the determined page
    include $page_path;
    ?>
</div>


    <!-- Footer -->
    <!-- <?php include 'https://ikigaicollege.ac.ke/Portal/includes/footer.php'; ?> -->
</body>
</html>
