<?php
include_once __DIR__ . '/../../config/config.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Fetch student details from session
$admissionNumber = $_SESSION['student_admission_number'];

// Fetch the student's category, course, intake details, and registration year
$studentDetailsSql = "
    SELECT CategoryName, CourseName, IntakeName, YEAR(RegistrationDate) AS RegistrationYear 
    FROM Students 
    WHERE AdmissionNumber = ?
";
$stmt = $conn->prepare($studentDetailsSql);
$stmt->bind_param("s", $admissionNumber);
$stmt->execute();
$result = $stmt->get_result();
$studentDetails = $result->fetch_assoc();

// Fetch CategoryID based on the CategoryName
$categoryName = $studentDetails['CategoryName'];
$categoryIdSql = "SELECT CategoryID, SemestersCount FROM Categories WHERE CategoryName = ?";
$stmt = $conn->prepare($categoryIdSql);
$stmt->bind_param("s", $categoryName);
$stmt->execute();
$categoryResult = $stmt->get_result();
$categoryDetails = $categoryResult->fetch_assoc();

// Generate semester options based on SemestersCount
$semesters = [];
for ($i = 1; $i <= $categoryDetails['SemestersCount']; $i++) {
    $semesters[$i] = "Semester $i";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generate Transcript</title>
    <style>
    
        h1 {
            color: #E39825;
        }
        form {
            background-color: white;
            border: 1px solid #ccc;
            border-radius: 5px;
            padding: 20px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }
        label {
            font-weight: bold;
        }
        select {
            padding: 5px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
            width: 100%;
        }
        input[type="submit"] {
            background-color: #E39825;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }
        input[type="submit"]:hover {
            background-color: #3B2314;
        }
    </style>
</head>
<body>
    <h1>Generate Transcript</h1>
    <form action="index.php?page=Transcripts/transcript_slip" method="POST">
        <!-- Display Category, Course, Intake, and Registration Year as plain text -->
        <p><strong>Category:</strong> <?php echo htmlspecialchars($studentDetails['CategoryName']); ?></p>
        <p><strong>Course:</strong> <?php echo htmlspecialchars($studentDetails['CourseName']); ?></p>
        <p><strong>Intake:</strong> <?php echo htmlspecialchars($studentDetails['IntakeName']); ?></p>
        <p><strong>Registration Year:</strong> <?php echo htmlspecialchars($studentDetails['RegistrationYear']); ?></p>

        <!-- Allow the student to select Year of Study -->
        <label for="yearOfStudy">Year of Study:</label>
        <select id="yearOfStudy" name="yearOfStudy" required>
            <option value="">Select Year of Study</option>
            <option value="1">Year 1</option>
            <option value="2">Year 2</option>
        </select>
        <br>

        <input type="submit" value="Generate Transcript">
    </form>
</body>
</html>
