<?php 
include_once __DIR__ . '/../../config/config.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$admissionNumber = $_SESSION['student_admission_number'];  // Replace with actual session variable if different.

// Fetch the student's category, course, intake details, and registration year.
$studentDetailsSql = "SELECT CategoryName, CourseName, IntakeName, YEAR(RegistrationDate) AS RegistrationYear FROM Students WHERE AdmissionNumber = ?";
$stmt = $conn->prepare($studentDetailsSql);
$stmt->bind_param("s", $admissionNumber);
$stmt->execute();
$result = $stmt->get_result();
$studentDetails = $result->fetch_assoc();

// Fetch the CategoryID based on the CategoryName
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
    <title>Generate Result Slip</title>
    <style>
        .card {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 20px;
            margin: 20px 0;
        }
        h1 {
            color: #E39825;
        }
        label {
            display: block;
            margin-top: 10px;
            font-weight: bold;
        }
        select {
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
    <div class="card">
        <h1>Generate Result Slip</h1>
        <form action="index.php?page=Academics/result_slip" method="POST">
            <!-- Display Category, Course, Intake, and Registration Year as plain text -->
            <p><strong>Category:</strong> <?php echo $studentDetails['CategoryName']; ?></p>
            <p><strong>Course:</strong> <?php echo $studentDetails['CourseName']; ?></p>
            <p><strong>Intake:</strong> <?php echo $studentDetails['IntakeName']; ?></p>
            <p><strong>Registration Year:</strong> <?php echo $studentDetails['RegistrationYear']; ?></p>

            <!-- Allow the student to select Semester -->
            <label for="semester">Semester:</label>
            <select id="semester" name="semester" required>
                <option value="">Select Semester</option>
                <?php foreach ($semesters as $semNumber => $semName): ?>
                    <option value="<?php echo $semNumber; ?>"><?php echo $semName; ?></option>
                <?php endforeach; ?>
            </select>

            <input type="submit" value="Generate Result Slip">
        </form>
    </div>
</body>
</html>
