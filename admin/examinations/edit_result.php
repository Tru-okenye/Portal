<?php
include_once __DIR__ . '/../../config/config.php';

// Check if admission number is provided
if (isset($_GET['admissionNumber'])) {
    $admissionNumber = $_GET['admissionNumber'];

    // Fetch the student's current result based on admission number
    $sql = "SELECT admission_number, student_name, cat_marks, exam_marks FROM exam_marks WHERE admission_number = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $admissionNumber);
    $stmt->execute();
    $result = $stmt->get_result();
    $student = $result->fetch_assoc();

    // Handle form submission to update the cat_marks and exam_marks
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $newCatMarks = isset($_POST['cat_marks']) ? (float)$_POST['cat_marks'] : 0;
        $newExamMarks = isset($_POST['exam_marks']) ? (float)$_POST['exam_marks'] : 0;

        // Calculate total marks
        $totalMarks = $newCatMarks  + $newExamMarks;

        // Determine grade and class based on total marks
        $grade = '';
        $class = '';
        if ($totalMarks >= 70) {
            $grade = 'A';
            $class = 'Distinction';
        } elseif ($totalMarks >= 60) {
            $grade = 'B';
            $class = 'Credit';
        } elseif ($totalMarks >= 50) {
            $grade = 'C';
            $class = 'Credit';
        } elseif ($totalMarks >= 40) {
            $grade = 'D';
            $class = 'Pass';
        } else {
            $grade = 'E';
            $class = 'Fail';
        }

        // Update the result in the database
        $updateSql = "UPDATE exam_marks SET cat_marks = ?, exam_marks = ?, total_marks = ?, grade = ?, class = ? WHERE admission_number = ?";
        $updateStmt = $conn->prepare($updateSql);
        $updateStmt->bind_param('dddsss', $newCatMarks, $newExamMarks, $totalMarks, $grade, $class, $admissionNumber);

        if ($updateStmt->execute()) {
            echo "Marks updated successfully!";
            echo '<meta http-equiv="refresh" content="2;url=index.php?page=examinations/exam_results">';
        } else {
            echo "Error updating marks: " . $conn->error;
        }

        $updateStmt->close();
    }

    $stmt->close();
} else {
    echo "No admission number provided.";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Result</title>
</head>
<body>
    <?php if ($student): ?>
        <h2>Edit Marks for <?php echo htmlspecialchars($student['student_name']); ?> (Admission Number: <?php echo htmlspecialchars($student['admission_number']); ?>)</h2>

        <form method="POST" action="">
            <label for="cat_marks">Cat Marks:</label>
            <input type="text" id="cat_marks" name="cat_marks" value="<?php echo htmlspecialchars($student['cat_marks']); ?>" required>

            <label for="exam_marks">Exam Marks:</label>
            <input type="text" id="exam_marks" name="exam_marks" value="<?php echo htmlspecialchars($student['exam_marks']); ?>" required>

            <input type="submit" value="Update Marks">
        </form>
    <?php else: ?>
        <p>Student result not found.</p>
    <?php endif; ?>
</body>
</html>
