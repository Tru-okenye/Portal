<?php
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
        // If needed, handle Application Form and Registration Date accordingly
        $status = $_POST['status']; // New field

        // Update student record
        $updateSql = "UPDATE students 
                      SET IDNumber = ?, FirstName = ?, LastName = ?, Email = ?, Phone = ?, ParentPhone = ?, CourseName = ?, IntakeName = ?, ModeOfStudy = ?, status = ? 
                      WHERE AdmissionNumber = ?";
        $updateStmt = $conn->prepare($updateSql);
        $updateStmt->bind_param("sssssssssss", $idNumber, $firstName, $lastName, $email, $phone, $parentPhone, $course, $intake, $modeOfStudy, $status, $admissionNumber);

        if ($updateStmt->execute()) {
            echo "<h3>Record Updated successfully!</h3>";
            echo '<meta http-equiv="refresh" content="2;url=index.php?page=admission/confirmation">';
            exit();
        } else {
            echo "Error updating record: " . $conn->error;
        }
    }
} else {
    die("No admission number provided.");
}
?>

<h2>Edit Student Record</h2>
<form method="POST">
    <!-- Personal Information Fields -->
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
    <input type="text" id="parentPhone" name="parentPhone" value="<?php echo htmlspecialchars($student['ParentPhone']); ?>"><br>

    <!-- Academic Information Fields -->
    <label for="course">Course:</label>
    <input type="text" id="course" name="course" value="<?php echo htmlspecialchars($student['CourseName']); ?>" required><br>

    <label for="intake">Intake:</label>
    <input type="text" id="intake" name="intake" value="<?php echo htmlspecialchars($student['IntakeName']); ?>" required><br>

    <label for="modeOfStudy">Mode of Study:</label>
    <input type="text" id="modeOfStudy" name="modeOfStudy" value="<?php echo htmlspecialchars($student['ModeOfStudy']); ?>" required><br>

    <label for="status">Status:</label>
    <input type="text" id="status" name="status" value="<?php echo htmlspecialchars($student['status']); ?>" required><br>

    
    <button type="submit">Update</button>
</form>
