<?php
session_start();
$_SESSION = []; // Clear session variables
session_destroy(); // Destroy all session data
header("Location: login_form.php"); // Redirect to the login page
exit();
?>
