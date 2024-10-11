<?php
include_once __DIR__ . '/../../config/config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get the course name and semester from the form
    $course = $_POST['course_name'];
    $semester = $_POST['semester'];
    // Get the checkbox value for release results (0 = not released, 1 = released)
    $releaseResults = isset($_POST['release_results']) ? 1 : 0;

    // Update the exam_marks table to set results_released flag
    $sql = "UPDATE exam_marks SET results_released = ? WHERE course_name = ? AND semester = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iss", $releaseResults, $course, $semester);

    if ($stmt->execute()) {
        echo "Results have been " . ($releaseResults ? "released" : "withheld") . " for Semester $semester in $course.";
    } else {
        echo "Error updating the results release status: " . $conn->error;
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Release Exam Results</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }
        label {
            display: block;
            margin-top: 10px;
        }
        input, select {
            padding: 5px;
            margin-top: 5px;
            width: 100%;
        }
        input[type="submit"] {
            background-color: #E39825;
            color: white;
            border: none;
            border-radius: 5px;
            padding: 10px 15px;
            cursor: pointer;
            margin-top: 15px;
        }
        input[type="submit"]:hover {
            background-color: #3B2314;
        }
    </style>
</head>
<body>
    <h1>Release Exam Results</h1>
    <form method="POST">
        <label for="course_name">Course Name:</label>
        <input type="text" name="course_name" required><br>

        <label for="semester">Semester:</label>
        <input type="number" name="semester" required><br>

        <label for="release_results">Release Results:</label>
        <input type="checkbox" name="release_results" value="1"><br>

        <input type="submit" value="Update Results Status">
    </form>
</body>
</html>
