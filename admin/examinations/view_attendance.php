<?php
include_once __DIR__ . '/../../config/config.php';

// Initialize variables for search criteria
$searchCategory = isset($_POST['category']) ? $_POST['category'] : '';
$searchCourse = isset($_POST['course']) ? $_POST['course'] : '';
$searchYear = isset($_POST['year']) ? $_POST['year'] : '';
$searchIntake = isset($_POST['intake']) ? $_POST['intake'] : '';
$searchSemester = isset($_POST['semester']) ? $_POST['semester'] : '';
$searchUnit = isset($_POST['unit']) ? $_POST['unit'] : '';

// Fetch distinct categories
$catSql = "SELECT * FROM categories";
$catResult = $conn->query($catSql);
$categories = [];
if ($catResult->num_rows > 0) {
    while ($row = $catResult->fetch_assoc()) {
        $categories[] = $row;
    }
}

// Fetch courses based on selected category
$courses = [];
if ($searchCategory) {
    $courseSql = "SELECT * FROM courses WHERE CategoryID = ?";
    $courseStmt = $conn->prepare($courseSql);
    $courseStmt->bind_param("i", $searchCategory);
    $courseStmt->execute();
    $courseResult = $courseStmt->get_result();
    if ($courseResult->num_rows > 0) {
        while ($row = $courseResult->fetch_assoc()) {
            $courses[] = $row;
        }
    }
}

// Fetch distinct years
$years = [];
$yearSql = "SELECT DISTINCT YEAR(RegistrationDate) AS Year FROM students ORDER BY Year DESC";
$yearResult = $conn->query($yearSql);
if ($yearResult->num_rows > 0) {
    while ($row = $yearResult->fetch_assoc()) {
        $years[] = $row['Year'];
    }
}

// Fetch distinct intakes
$intakes = [];
$intakeSql = "SELECT DISTINCT IntakeName FROM students";
$intakeResult = $conn->query($intakeSql);
if ($intakeResult->num_rows > 0) {
    while ($row = $intakeResult->fetch_assoc()) {
        $intakes[] = $row['IntakeName'];
    }
}

// Fetch distinct semesters based on selected course
$semesters = [];
if ($searchCourse) {
    $semesterSql = "SELECT DISTINCT SemesterNumber FROM units WHERE CourseID = (SELECT CourseID FROM courses WHERE CourseName = ?)";
    $semesterStmt = $conn->prepare($semesterSql);
    $semesterStmt->bind_param("s", $searchCourse);
    $semesterStmt->execute();
    $semesterResult = $semesterStmt->get_result();
    if ($semesterResult->num_rows > 0) {
        while ($row = $semesterResult->fetch_assoc()) {
            $semesters[] = $row['SemesterNumber'];
        }
    }
}

// Fetch units based on selected course and semester
$units = [];
if ($searchCourse && $searchSemester) {
    $unitSql = "SELECT * FROM units WHERE CourseID = (SELECT CourseID FROM courses WHERE CourseName = ?) AND SemesterNumber = ?";
    $unitStmt = $conn->prepare($unitSql);
    $unitStmt->bind_param("si", $searchCourse, $searchSemester);
    $unitStmt->execute();
    $unitResult = $unitStmt->get_result();
    if ($unitResult->num_rows > 0) {
        while ($row = $unitResult->fetch_assoc()) {
            $units[] = $row;
        }
    }
}

// Fetch attendance records based on selected filters only if all criteria are provided
$examAttendanceRecords = [];
if ($searchCategory && $searchCourse && $searchYear && $searchIntake && $searchSemester && $searchUnit) {
    $sql = "SELECT * FROM examattendance WHERE course_name = ? AND year = ? AND intake_name = ? AND semester = ? AND unit_name = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssss", $searchCourse, $searchYear, $searchIntake, $searchSemester, $searchUnit);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $examAttendanceRecords[] = $row;
        }
    }

    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Exam Attendance</title>
    <style>
    form h2, h3 {
        color: #cf881d; 
        text-align: center; /* Center align the headings */
    }

    label {
        margin-right: 5px;
        color: #3B2314;
        font-weight: bold;
    }

    select, form button {
        width: 100%; /* Full width on small screens */
        padding: 8px;
        margin-top: 15px;
        border: 1px solid;
        border-radius: 5px;
        font-size: 14px;
        color: #fff;
        box-sizing: border-box; /* Include padding in width */
    }

    select {
        color: black; 
    }

    form button {
        background-color: #3B2314;
        color: white;
        border: none;
        cursor: pointer;
        margin-top: 6px;
        width: 100%; /* Full width on small screens */
        padding: 10px; /* Increase padding for better touch targets */
        border-radius: 10px;
    }

    form button:hover {
        background-color: #E39825;
        color: #3B2314;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
    } 

    table, th {
        border: 1px solid #3B2314;
    }

    th {
        padding: 12px;
        text-align: left;
        background-color: #E39825;
        color: white;
    }

    /* Responsive Styles */
    @media screen and (min-width: 481px) and (max-width: 768px) {
        select, form button {
            width: 48%; /* Adjust width for medium screens */
            display: inline-block; /* Display buttons and selects side by side */
            margin-right: 4%; /* Space between elements */
        }

        form button {
            margin-top: 0; /* Remove margin on button for better alignment */
        }

        /* Reset the right margin for the last button */
        form button:last-of-type, select:last-of-type {
            margin-right: 0;
        }
    }

    @media screen and (min-width: 769px) {
        select, form button {
            width: 15%; /* Keep original width for large screens */
        }
    }
</style>
</head>
<body>
    
    <!-- Filter Form -->
    <form method="POST" action="">
        <h2>View Exam Attendance</h2>
        <label for="category">Category:</label>
        <select id="category" name="category" onchange="this.form.submit()">
            <option value="">Select Category</option>
            <?php foreach ($categories as $category): ?>
                <option value="<?php echo $category['CategoryID']; ?>" <?php echo ($searchCategory == $category['CategoryID']) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($category['CategoryName']); ?>
                </option>
            <?php endforeach; ?>
        </select>

        <?php if ($searchCategory): ?>
            <label for="course">Course:</label>
            <select id="course" name="course" onchange="this.form.submit()">
                <option value="">Select Course</option>
                <?php foreach ($courses as $course): ?>
                    <option value="<?php echo htmlspecialchars($course['CourseName']); ?>" <?php echo ($searchCourse == $course['CourseName']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($course['CourseName']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        <?php endif; ?>

        <?php if ($searchCourse): ?>
            <label for="year">Year:</label>
            <select id="year" name="year" onchange="this.form.submit()">
                <option value="">Select Year</option>
                <?php foreach ($years as $year): ?>
                    <option value="<?php echo htmlspecialchars($year); ?>" <?php echo ($searchYear == $year) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($year); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        <?php endif; ?>

        <?php if ($searchYear): ?>
            <label for="intake">Intake:</label>
            <select id="intake" name="intake" onchange="this.form.submit()">
                <option value="">Select Intake</option>
                <?php foreach ($intakes as $intake): ?>
                    <option value="<?php echo htmlspecialchars($intake); ?>" <?php echo ($searchIntake == $intake) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($intake); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        <?php endif; ?>

        <?php if ($searchIntake): ?>
            <label for="semester">Semester:</label>
            <select id="semester" name="semester" onchange="this.form.submit()">
                <option value="">Select Semester</option>
                <?php foreach ($semesters as $semester): ?>
                    <option value="<?php echo htmlspecialchars($semester); ?>" <?php echo ($searchSemester == $semester) ? 'selected' : ''; ?>>
                        Semester <?php echo htmlspecialchars($semester); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        <?php endif; ?>

        <?php if ($searchSemester): ?>
            <label for="unit">Unit:</label>
            <select id="unit" name="unit" onchange="this.form.submit()">
                <option value="">Select Unit</option>
                <?php foreach ($units as $unit): ?>
                    <option value="<?php echo htmlspecialchars($unit['UnitCode']); ?>" <?php echo ($searchUnit == $unit['UnitCode']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($unit['UnitName']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        <?php endif; ?>
    </form>

    <hr>

    <?php if ($searchCategory && $searchCourse && $searchYear && $searchIntake && $searchSemester && $searchUnit): ?>
        <?php if (!empty($examAttendanceRecords)): ?>
            <table border="1">
                <thead>
                    <tr>
                        <th>Admission Number</th>
                        <th>Full Name</th>
                        <th>Semester</th>
                        <th>Unit</th>
                        <th>Date</th>
                        <th>Attendance Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($examAttendanceRecords as $record): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($record['admission_number']); ?></td>
                            <td><?php echo htmlspecialchars($record['full_name']); ?></td>
                            <td><?php echo htmlspecialchars($record['semester']); ?></td>
                            <td><?php echo htmlspecialchars($record['unit_name']); ?></td>
                            <td><?php echo htmlspecialchars($record['date']); ?></td>
                            <td><?php echo htmlspecialchars($record['attendance_status']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No records found.</p>
        <?php endif; ?>
    <?php endif; ?>
</body>
</html>
