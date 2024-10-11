<?php
session_start();
include 'config/config.php';

// Check if user is logged in
if (!isset($_SESSION['admin_username']) && !isset($_SESSION['student_admission_number'])) {
    header('Location: login_form.php'); // Redirect to login page if not logged in
    exit();
}

// Determine user role and fetch appropriate data
if (isset($_SESSION['admin_username'])) {
    $username = $_SESSION['admin_username'];
    
    // Fetch user role from the database
    $sql = "SELECT Role FROM Users WHERE Username = ?";
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
    <link rel="stylesheet" href="assets/css/main.css"> 
    <link rel="stylesheet" href="assets/css/header.css"> 
    <link rel="stylesheet" href="assets/css/sidebar.css"> 
    <link rel="stylesheet" href="assets/css/footer.css"> 
    <link rel="stylesheet" href="assets/css/student_list.css">

    <!-- FontAwesome CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <!-- Header -->
    <?php include 'includes/header.php'; ?>
    
</head>
<body>


    
    <?php 
    if ($role == 'admin') {
        include 'includes/sidebar.php'; // Sidebar for admin
    } elseif ($role == 'teacher') {
        include 'includes/sidebar_teacher.php'; // Sidebar for teacher
    } else {
        include 'includes/sidebar_student.php'; // Default to student sidebar
    }
    ?>

    <!-- Main content area -->
    <div id="main-content" class="main-content">
        <?php
        // Determine which page to include based on the URL parameter
        $page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';
        
        // Set default folder based on user role
        $folder = ($role == 'admin' || $role == 'teacher') ? 'admin/' : 'student/';
        $page_path = $folder . $page . '.php';
        // Construct the full path to the page

        // Check if the requested page exists
        if (!file_exists($page_path)) {
            $page_path = ($role == 'admin' ? 'admin_dashboard.php' : ($role == 'teacher' ? 'teacher_dashboard.php' : 'student_dashboard.php')); // Default page based on role
        }

        include $page_path;
        ?>
    </div>

    <!-- Footer -->
    <!-- <?php include 'includes/footer.php'; ?> -->
</body>
</html>
