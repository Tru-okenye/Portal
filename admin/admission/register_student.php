<?php
ob_start();
include_once __DIR__ . '/../../config/config.php';

// Function to handle file uploads with validation for size and type
function uploadFile($fileInputName) {
    if (isset($_FILES[$fileInputName]) && $_FILES[$fileInputName]['error'] == 0) {
        $targetDir = __DIR__ . "/uploads/";
        $fileName = basename($_FILES[$fileInputName]['name']);
        $targetFile = $targetDir . $fileName;
        $fileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));
        $fileSize = $_FILES[$fileInputName]['size'];

        // Check if file is a valid type
        $allowedTypes = array('pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png');
        if (!in_array($fileType, $allowedTypes)) {
            return "Error: Invalid file type.";
        }

        // Check file size (5MB limit)
        if ($fileSize > 5000000) {
            return "Error: File size exceeds the 5MB limit.";
        }

        // Attempt to move the uploaded file
        if (move_uploaded_file($_FILES[$fileInputName]['tmp_name'], $targetFile)) {
            return 'uploads/' . $fileName;
        } else {
            return "Error: There was an issue uploading the file.";
        }
    }
    return '';
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitize form inputs
    $firstName = filter_var($_POST['firstName'], FILTER_SANITIZE_STRING);
    $lastName = filter_var($_POST['lastName'], FILTER_SANITIZE_STRING);
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $phone = filter_var($_POST['phone'], FILTER_SANITIZE_STRING);
    $gender = filter_var($_POST['gender'], FILTER_SANITIZE_STRING);
    $parentPhone = filter_var($_POST['parentPhone'], FILTER_SANITIZE_STRING);
    $category = filter_var($_POST['category'], FILTER_SANITIZE_STRING);
    $course = filter_var($_POST['course'], FILTER_SANITIZE_STRING);
    $intake = filter_var($_POST['intake'], FILTER_SANITIZE_STRING);
    $grade = filter_var($_POST['grade'], FILTER_SANITIZE_STRING);
    $idNumber = filter_var($_POST['idNumber'], FILTER_SANITIZE_STRING);
    $admissionNumber = filter_var($_POST['admissionNumber'], FILTER_SANITIZE_STRING);
    $modeOfStudy = filter_var($_POST['modeOfStudy'], FILTER_SANITIZE_STRING);

    // Handle file uploads

    $applicationFormPath = uploadFile('applicationForm');

    // Prepared statement to insert data into the Students table
    $stmt = $conn->prepare("INSERT INTO Students 
            (AdmissionNumber, IDNumber, FirstName, LastName, Email, Phone, Gender, 
            ParentPhone, CategoryName, CourseName, IntakeName, Grade, 
            ModeOfStudy, ApplicationFormPath) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    // Bind the parameters
    $stmt->bind_param("ssssssssssssss", 
        $admissionNumber, $idNumber, $firstName, $lastName, $email, $phone, 
        $gender, $parentPhone, $category, $course, $intake, $grade, $modeOfStudy, 
        $applicationFormPath);

    // Execute the query and check for success
    if ($stmt->execute()) {
        echo "<h3>Student Registered successfully!</h3>";
        echo '<meta http-equiv="refresh" content="2;url=index.php?page=admission/confirmation">';
        exit;
    } else {
        echo "Error: " . $stmt->error;
    }

    // Close the statement and connection
    $stmt->close();
    $conn->close();
}

ob_end_flush();
?>
