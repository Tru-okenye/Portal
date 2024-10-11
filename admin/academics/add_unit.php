<?php
include_once __DIR__ . '/../../config/config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $courseID = $_POST['courseID'];
    $semester = $_POST['semester'];
    $unitCode = $_POST['unitCode'];
    $unitName = $_POST['unitName'];
    $courseContent = $_POST['courseContent'];
    $contactHours = $_POST['contactHours'];
    $referenceMaterials = $_POST['referenceMaterials'];

    $sql = "INSERT INTO Units (CourseID, SemesterNumber, UnitCode, UnitName, CourseContent, ContactHours, reference_materials) 
            VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sisssis", $courseID, $semester, $unitCode, $unitName, $courseContent, $contactHours, $referenceMaterials);

    if ($stmt->execute()) {
        echo "Unit added successfully.";
    } else {
        echo "Error adding unit: " . $stmt->error;
    }
}
?>

<form method="post" action="">
    <h2>Add Unit</h2>

    <!-- Manually enter Course ID -->
    <label for="courseID">Course ID:</label>
    <input type="text" id="courseID" name="courseID" required><br><br>

    <!-- Semester passed from the URL -->
    <input type="hidden" name="semester" value="<?php echo htmlspecialchars($_GET['semester']); ?>">

    <label for="unitCode">Unit Code:</label>
    <input type="text" id="unitCode" name="unitCode" required><br><br>

    <label for="unitName">Unit Name:</label>
    <input type="text" id="unitName" name="unitName" required><br><br>

    <label for="courseContent">Course Content:</label>
    <textarea id="courseContent" name="courseContent" rows="5" required></textarea><br><br>

    <label for="contactHours">Contact Hours:</label>
    <input type="number" id="contactHours" name="contactHours" required><br><br>

    <label for="referenceMaterials">Reference Materials:</label>
    <textarea id="referenceMaterials" name="referenceMaterials" rows="5"></textarea><br><br>

    <button type="submit">Add Unit</button>
</form>
