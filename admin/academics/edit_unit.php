<?php
include_once __DIR__ . '/../../config/config.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Get the unit code from the URL
$unitCode = isset($_GET['unitCode']) ? $_GET['unitCode'] : '';

// If unitCode is not set, display an error
if (!$unitCode) {
    die("No unit code provided.");
}

// Fetch unit details
$unitSql = "SELECT UnitCode, UnitName, CourseContent, reference_materials FROM units WHERE UnitCode = ?";
$stmt = $conn->prepare($unitSql);
$stmt->bind_param("s", $unitCode);

if (!$stmt->execute()) {
    die("Error executing query: " . $stmt->error);
}

$result = $stmt->get_result();
$unit = $result->fetch_assoc();

// If no unit is found, display an error
if (!$unit) {
    die("No unit found with the provided unit code.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Update unit details after form submission
    $newCourseContent = $_POST['courseContent'];
    $newReferenceMaterials = $_POST['referenceMaterials'];

    $updateSql = "UPDATE units SET CourseContent = ?, reference_materials = ? WHERE UnitCode = ?";
    $updateStmt = $conn->prepare($updateSql);
    $updateStmt->bind_param("sss", $newCourseContent, $newReferenceMaterials, $unitCode);

    if ($updateStmt->execute()) {
        echo "<p>Unit updated successfully.</p>";
        // Redirect to courses.php after successful update
        // header("Location: courses.php");
        echo '<meta http-equiv="refresh" content="2;url=index.php?page=academics/courses">';

        exit;
    } else {
        echo "<p>Error updating unit: " . $updateStmt->error . "</p>";
    }

    $updateStmt->close();
}


$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Unit</title>
    <link rel="stylesheet" href="https://ikigaicollege.ac.ke/Portal/assets/css/courses.css"> 
</head>
<body>

    <h2>Edit Unit: <?php echo htmlspecialchars($unit['UnitName']); ?> (<?php echo htmlspecialchars($unit['UnitCode']); ?>)</h2>

    <form method="POST">
        <label for="courseContent">Course Content:</label><br>
        <textarea id="courseContent" name="courseContent" rows="6" cols="50"><?php echo htmlspecialchars($unit['CourseContent']); ?></textarea><br>

        <label for="referenceMaterials">Reference Materials:</label><br>
        <textarea id="referenceMaterials" name="referenceMaterials" rows="6" cols="50"><?php echo htmlspecialchars($unit['reference_materials']); ?></textarea><br>

        <input type="submit" value="Save Changes">
    </form>

</body>
</html>
