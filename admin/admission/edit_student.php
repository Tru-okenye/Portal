<?php
include_once __DIR__ . '/../../config/config.php';

include_once __DIR__ . '/../../config/config.php';

if (isset($_GET['admissionNumber'])) {
    $admissionNumber = $_GET['admissionNumber'];

    // Fetch student record
    $sql = "SELECT * FROM students WHERE AdmissionNumber = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $admissionNumber);
    $stmt->execute();
    $result = $stmt->get_result();
    $student = $result->fetch_assoc();

    if (!$student) {
        die("Student record not found.");
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Handle form submission
        $idNumber = $_POST['idNumber'];
        $firstName = $_POST['firstName'];
        $lastName = $_POST['lastName'];
        $email = $_POST['email'];
        $phone = $_POST['phone'];
        $parentPhone = $_POST['parentPhone']; // New field
        $course = $_POST['course'];
        $intake = $_POST['intake'];
        $modeOfStudy = $_POST['modeOfStudy']; // New field
        $status = $_POST['status']; // New field
        
        // Handle application form upload
        $applicationForm = $_FILES['applicationForm'];
        $applicationFormPath = '';

        if ($applicationForm['size'] > 0) {
            // Set the target directory and file name
            $targetDir = __DIR__ . '/uploads/'; // Make sure this directory exists and is writable
            $applicationFormPath = $targetDir . basename($applicationForm['name']);

            // Move the uploaded file to the target directory
            if (move_uploaded_file($applicationForm['tmp_name'], $applicationFormPath)) {
                // File uploaded successfully
            } else {
                echo "Error uploading application form.";
            }
        }

        // Update student record
        $updateSql = "UPDATE students 
                      SET IDNumber = ?, FirstName = ?, LastName = ?, Email = ?, Phone = ?, ParentPhone = ?, CourseName = ?, IntakeName = ?, ModeOfStudy = ?, status = ?, ApplicationFormPath = ? 
                      WHERE AdmissionNumber = ?";
        $updateStmt = $conn->prepare($updateSql);
        $updateStmt->bind_param("ssssssssssss", $idNumber, $firstName, $lastName, $email, $phone, $parentPhone, $course, $intake, $modeOfStudy, $status, $applicationFormPath, $admissionNumber);

        if ($updateStmt->execute()) {
            echo "Student record updated successfully.";
            // Optional: Redirect to a confirmation page
            // header("Location: confirmation.php");
        } else {
            echo "Error updating record: " . $conn->error;
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Student</title>
</head>
<body>
    <h2>Edit Student</h2>
    <form method="post" enctype="multipart/form-data">
        <label for="idNumber">ID Number:</label>
        <input type="text" id="idNumber" name="idNumber" value="<?php echo htmlspecialchars($student['IDNumber']); ?>" required>

        <label for="firstName">First Name:</label>
        <input type="text" id="firstName" name="firstName" value="<?php echo htmlspecialchars($student['FirstName']); ?>" required>

        <label for="lastName">Last Name:</label>
        <input type="text" id="lastName" name="lastName" value="<?php echo htmlspecialchars($student['LastName']); ?>" required>

        <label for="email">Email:</label>
        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($student['Email']); ?>" required>

        <label for="phone">Phone:</label>
        <input type="text" id="phone" name="phone" value="<?php echo htmlspecialchars($student['Phone']); ?>" required>

        <label for="parentPhone">Parent's Phone:</label>
        <input type="text" id="parentPhone" name="parentPhone" value="<?php echo htmlspecialchars($student['ParentPhone']); ?>" required>

        <label for="course">Course:</label>
        <input type="text" id="course" name="course" value="<?php echo htmlspecialchars($student['CourseName']); ?>" required>

        <label for="intake">Intake:</label>
        <input type="text" id="intake" name="intake" value="<?php echo htmlspecialchars($student['IntakeName']); ?>" required>

        <label for="modeOfStudy">Mode of Study:</label>
        <input type="text" id="modeOfStudy" name="modeOfStudy" value="<?php echo htmlspecialchars($student['ModeOfStudy']); ?>" required>

        <label for="status">Status:</label>
        <input type="text" id="status" name="status" value="<?php echo htmlspecialchars($student['status']); ?>" required>

        <label for="applicationForm">Application Form:</label>
        <input type="file" id="applicationForm" name="applicationForm" accept=".pdf,.doc,.docx">

        <button type="submit">Update Student</button>
    </form>
</body>
</html>
