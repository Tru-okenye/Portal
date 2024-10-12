<?php
include_once __DIR__ . '/../../config/config.php';

// include_once '../../includes/header.php'; 

$categories = [];
$courses = [];
$units = [];
$students = [];
$semesters = [];
$years = [];
$intakes = [];
$modes_of_study = [];

// Fetch categories
$catSql = "SELECT * FROM categories";
$catResult = $conn->query($catSql);
if ($catResult->num_rows > 0) {
    while ($row = $catResult->fetch_assoc()) {
        $categories[] = $row;
    }
}

// Fetch distinct years, intake options, and modes of study
$yearSql = "SELECT DISTINCT YEAR(RegistrationDate) AS Year FROM students ORDER BY Year DESC";
$yearResult = $conn->query($yearSql);
if ($yearResult->num_rows > 0) {
    while ($row = $yearResult->fetch_assoc()) {
        $years[] = $row['Year'];
    }
}

$intakeSql = "SELECT DISTINCT IntakeName FROM students";
$intakeResult = $conn->query($intakeSql);
if ($intakeResult->num_rows > 0) {
    while ($row = $intakeResult->fetch_assoc()) {
        $intakes[] = $row['IntakeName'];
    }
}

$modeSql = "SELECT DISTINCT ModeOfStudy FROM students";
$modeResult = $conn->query($modeSql);
if ($modeResult->num_rows > 0) {
    while ($row = $modeResult->fetch_assoc()) {
        $modes_of_study[] = $row['ModeOfStudy'];
    }
}

if (isset($_POST['category'])) {
    $categoryId = $_POST['category'];
    
    // Fetch courses based on selected category
    $courseSql = "SELECT * FROM courses WHERE CategoryID = ?";
    $courseStmt = $conn->prepare($courseSql);
    $courseStmt->bind_param("i", $categoryId);
    $courseStmt->execute();
    $courseResult = $courseStmt->get_result();
    if ($courseResult->num_rows > 0) {
        while ($row = $courseResult->fetch_assoc()) {
            $courses[] = $row;
        }
    }

    if (isset($_POST['course'])) {
        $courseName = $_POST['course'];
        
        // Fetch years based on selected course
        $semesterSql = "SELECT DISTINCT SemesterNumber FROM units WHERE CourseID = (SELECT CourseID FROM courses WHERE CourseName = ?)";
        $semesterStmt = $conn->prepare($semesterSql);
        $semesterStmt->bind_param("s", $courseName);
        $semesterStmt->execute();
        $semesterResult = $semesterStmt->get_result();
        if ($semesterResult->num_rows > 0) {
            while ($row = $semesterResult->fetch_assoc()) {
                $semesters[] = $row['SemesterNumber'];
            }
        }

        if (isset($_POST['year'])) {
            $year = $_POST['year'];
            $intake = isset($_POST['intake']) ? $_POST['intake'] : '';
            $mode_of_study = isset($_POST['mode_of_study']) ? $_POST['mode_of_study'] : '';

            // Fetch units based on selected course, year, and semester
            $unitSql = "SELECT * FROM units WHERE CourseID = (SELECT CourseID FROM courses WHERE CourseName = ?) AND SemesterNumber = ?";
            $unitStmt = $conn->prepare($unitSql);
            $unitStmt->bind_param("si", $courseName, $_POST['semester']);
            $unitStmt->execute();
            $unitResult = $unitStmt->get_result();
            if ($unitResult->num_rows > 0) {
                while ($row = $unitResult->fetch_assoc()) {
                    $units[] = $row;
                }
            }

            if (isset($_POST['unit'])) {
                $unitCode = $_POST['unit'];

                // Fetch students based on selected filters
                $studentSql = "SELECT AdmissionNumber, CONCAT(FirstName, ' ', LastName) AS FullName FROM students WHERE CourseName = ? AND YEAR(RegistrationDate) = ? AND IntakeName = ? AND ModeOfStudy = ?";
                $studentStmt = $conn->prepare($studentSql);
                $studentStmt->bind_param("siss", $courseName, $year, $intake, $mode_of_study);
                $studentStmt->execute();
                $studentResult = $studentStmt->get_result();
                if ($studentResult->num_rows > 0) {
                    while ($row = $studentResult->fetch_assoc()) {
                        $students[] = $row;
                    }
                }
            }
        }
    }
}
?>


<style>
    form h2, h3 {
        color: #cf881d; 
    }

    label {
        margin-right: 5px;
        color: #3B2314;
        font-weight: bold;
    }

    select, button {
        width: 15%;
        padding: 8px;
        margin-top: 15px;
        border: 1px solid;
        border-radius: 5px;
        font-size: 14px;
        color: #fff;
    }

    select {
     
        color: black; 
    }



    button {
        background-color: #3B2314;
        color: white;
        border: none;
        cursor: pointer;
        margin-top: 6px;
        width: 16%;
        padding: 6px;
        border-radius: 10px;
    }

    button:hover {
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
</style>

<form method="post" action="">
    <h2>Mark Attendance</h2>
    <label for="category">Category:</label>
    <select id="category" name="category" onchange="this.form.submit()">
        <option value="">Select Category</option>
        <?php foreach ($categories as $category): ?>
            <option value="<?php echo $category['CategoryID']; ?>" <?php echo (isset($_POST['category']) && $_POST['category'] == $category['CategoryID']) ? 'selected' : ''; ?>>
                <?php echo htmlspecialchars($category['CategoryName']); ?>
            </option>
        <?php endforeach; ?>
    </select>

    <?php if (isset($_POST['category'])): ?>
        <label for="course">Course:</label>
        <select id="course" name="course" onchange="this.form.submit()">
            <option value="">Select Course</option>
            <?php foreach ($courses as $course): ?>
                <option value="<?php echo htmlspecialchars($course['CourseName']); ?>" <?php echo (isset($_POST['course']) && $_POST['course'] == $course['CourseName']) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($course['CourseName']); ?>
                </option>
            <?php endforeach; ?>
        </select>
    <?php endif; ?>

    <?php if (isset($_POST['course'])): ?>
        <label for="year">Year:</label>
        <select id="year" name="year" onchange="this.form.submit()">
            <option value="">Select Year</option>
            <?php foreach ($years as $year): ?>
                <option value="<?php echo htmlspecialchars($year); ?>" <?php echo (isset($_POST['year']) && $_POST['year'] == $year) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($year); ?>
                </option>
            <?php endforeach; ?>
        </select>
    <?php endif; ?>

    <?php if (isset($_POST['year'])): ?>
        <label for="intake">Intake:</label>
        <select id="intake" name="intake" onchange="this.form.submit()">
            <option value="">Select Intake</option>
            <?php foreach ($intakes as $intake): ?>
                <option value="<?php echo htmlspecialchars($intake); ?>" <?php echo (isset($_POST['intake']) && $_POST['intake'] == $intake) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($intake); ?>
                </option>
            <?php endforeach; ?>
        </select>
    <?php endif; ?>

    <?php if (isset($_POST['intake'])): ?>
        <label for="semester">Semester:</label>
        <select id="semester" name="semester" onchange="this.form.submit()">
            <option value="">Select Semester</option>
            <?php foreach ($semesters as $semester): ?>
                <option value="<?php echo htmlspecialchars($semester); ?>" <?php echo (isset($_POST['semester']) && $_POST['semester'] == $semester) ? 'selected' : ''; ?>>
                    Semester <?php echo htmlspecialchars($semester); ?>
                </option>
            <?php endforeach; ?>
        </select>
    <?php endif; ?>

    <?php if (isset($_POST['semester'])): ?>
        <label for="unit">Unit:</label>
        <select id="unit" name="unit" onchange="this.form.submit()">
            <option value="">Select Unit</option>
            <?php foreach ($units as $unit): ?>
                <option value="<?php echo htmlspecialchars($unit['UnitCode']); ?>" <?php echo (isset($_POST['unit']) && $_POST['unit'] == $unit['UnitCode']) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($unit['UnitName']); ?>
                </option>
            <?php endforeach; ?>
        </select>
    <?php endif; ?>

    <?php if (isset($_POST['unit'])): ?>
        <label for="mode_of_study">Mode of Study:</label>
        <select id="mode_of_study" name="mode_of_study" onchange="this.form.submit()">
            <option value="">Select Mode of Study</option>
            <?php foreach ($modes_of_study as $mode): ?>
                <option value="<?php echo htmlspecialchars($mode); ?>" <?php echo (isset($_POST['mode_of_study']) && $_POST['mode_of_study'] == $mode) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($mode); ?>
                </option>
            <?php endforeach; ?>
        </select>
    <?php endif; ?>
</form>

<?php if (isset($_POST['mode_of_study'])): ?>
    <h3>Students Enrolled in the Selected Course</h3>
    <form method="post" action="index.php?page=students/submit_attendance">
    <input type="hidden" name="course" value="<?php echo htmlspecialchars($_POST['course']); ?>">
    <input type="hidden" name="unit" value="<?php echo htmlspecialchars($_POST['unit']); ?>">
    <input type="hidden" name="semester" value="<?php echo htmlspecialchars($_POST['semester']); ?>">
    <input type="hidden" name="year" value="<?php echo htmlspecialchars($_POST['year']); ?>">
    <input type="hidden" name="intake" value="<?php echo htmlspecialchars($_POST['intake']); ?>">
    <input type="hidden" name="mode_of_study" value="<?php echo htmlspecialchars($_POST['mode_of_study']); ?>">

    <!-- Set the date only once for all students -->
    <label for="date">Date:</label>
    <input type="date" name="date" id="date" required>

    <table border="1">
        <thead>
            <tr>
                <th>Admission Number</th>
                <th>Full Name</th>
                <th>Mark Attendance</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($students)): ?>
                <?php foreach ($students as $student): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($student['AdmissionNumber']); ?></td>
                        <td><?php echo htmlspecialchars($student['FullName']); ?></td>
                        <input type="hidden" name="attendance[<?php echo htmlspecialchars($student['AdmissionNumber']); ?>][full_name]" value="<?php echo htmlspecialchars($student['FullName']); ?>">
                        <td>
                            <input type="checkbox" name="attendance[<?php echo htmlspecialchars($student['AdmissionNumber']); ?>][status]" value="present">
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="3">No students found for the selected unit.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
    <button type="submit">Submit Attendance</button>
</form>

<?php endif; ?>


    <?php $conn->close(); ?>
