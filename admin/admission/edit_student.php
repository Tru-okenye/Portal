if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle form submission
    $idNumber = $_POST['idNumber'];
    $firstName = $_POST['firstName'];
    $lastName = $_POST['lastName'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $parentPhone = $_POST['parentPhone'];
    $course = $_POST['course'];
    $intake = $_POST['intake'];
    $modeOfStudy = $_POST['modeOfStudy'];
    $status = $_POST['status'];

    // Handle file upload for Application Form
    $applicationFormPath = $student['ApplicationFormPath']; // Keep current form path by default
    if (isset($_FILES['applicationForm']) && $_FILES['applicationForm']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = __DIR__ . '/uploads/forms/';
        $uploadFile = $uploadDir . basename($_FILES['applicationForm']['name']);

        if (move_uploaded_file($_FILES['applicationForm']['tmp_name'], $uploadFile)) {
            $applicationFormPath = 'uploads/forms/' . basename($_FILES['applicationForm']['name']); // Save relative path
        } else {
            echo "Failed to upload application form.";
        }
    }

    // Update student record with the new application form path
    $updateSql = "UPDATE students 
                  SET IDNumber = ?, FirstName = ?, LastName = ?, Email = ?, Phone = ?, ParentPhone = ?, CourseName = ?, IntakeName = ?, ModeOfStudy = ?, status = ?, ApplicationFormPath = ? 
                  WHERE AdmissionNumber = ?";
    $updateStmt = $conn->prepare($updateSql);
    $updateStmt->bind_param("ssssssssssss", $idNumber, $firstName, $lastName, $email, $phone, $parentPhone, $course, $intake, $modeOfStudy, $status, $applicationFormPath, $admissionNumber);
    $updateStmt->execute();

    echo "Student record updated.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Student</title>
</head>
<body>
    <h2>Edit Student Record</h2>
    <form method="post" enctype="multipart/form-data">
        <label for="idNumber">ID Number:</label>
        <input type="text" id="idNumber" name="idNumber" value="<?php echo htmlspecialchars($student['IDNumber']); ?>" required><br>

        <label for="firstName">First Name:</label>
        <input type="text" id="firstName" name="firstName" value="<?php echo htmlspecialchars($student['FirstName']); ?>" required><br>

        <label for="lastName">Last Name:</label>
        <input type="text" id="lastName" name="lastName" value="<?php echo htmlspecialchars($student['LastName']); ?>" required><br>

        <label for="email">Email:</label>
        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($student['Email']); ?>" required><br>

        <label for="phone">Phone:</label>
        <input type="text" id="phone" name="phone" value="<?php echo htmlspecialchars($student['Phone']); ?>" required><br>

        <label for="parentPhone">Parent's Phone:</label>
        <input type="text" id="parentPhone" name="parentPhone" value="<?php echo htmlspecialchars($student['ParentPhone']); ?>" required><br>

        <label for="course">Course:</label>
        <input type="text" id="course" name="course" value="<?php echo htmlspecialchars($student['CourseName']); ?>" required><br>

        <label for="intake">Intake:</label>
        <input type="text" id="intake" name="intake" value="<?php echo htmlspecialchars($student['IntakeName']); ?>" required><br>

        <label for="modeOfStudy">Mode of Study:</label>
        <input type="text" id="modeOfStudy" name="modeOfStudy" value="<?php echo htmlspecialchars($student['ModeOfStudy']); ?>" required><br>

        <label for="status">Status:</label>
        <input type="text" id="status" name="status" value="<?php echo htmlspecialchars($student['status']); ?>" required><br>

        <!-- Application Form Upload Field -->
        <label for="applicationForm">Application Form:</label>
        <input type="file" id="applicationForm" name="applicationForm"><br>

        <!-- Show existing application form link -->
        <?php if (!empty($student['ApplicationFormPath'])): ?>
            <a href="<?php echo htmlspecialchars($student['ApplicationFormPath']); ?>" target="_blank">View Existing Application Form</a><br>
        <?php endif; ?>

        <button type="submit">Update Student</button>
    </form>
</body>
</html>
