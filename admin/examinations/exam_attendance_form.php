<?php
include_once __DIR__ . '/../../config/config.php';

$categories = [];
$courses = [];
$units = [];
$students = [];
$semesters = [];
$years = [];
$intakes = [];

// Fetch categories
$catSql = "SELECT * FROM Categories";
$catResult = $conn->query($catSql);
if ($catResult->num_rows > 0) {
    while ($row = $catResult->fetch_assoc()) {
        $categories[] = $row;
    }
}

// Fetch distinct years and intake options
$yearSql = "SELECT DISTINCT YEAR(RegistrationDate) AS Year FROM Students ORDER BY Year DESC";
$yearResult = $conn->query($yearSql);
if ($yearResult->num_rows > 0) {
    while ($row = $yearResult->fetch_assoc()) {
        $years[] = $row['Year'];
    }
}

$intakeSql = "SELECT DISTINCT IntakeName FROM Students";
$intakeResult = $conn->query($intakeSql);
if ($intakeResult->num_rows > 0) {
    while ($row = $intakeResult->fetch_assoc()) {
        $intakes[] = $row['IntakeName'];
    }
}

if (isset($_POST['category'])) {
    $categoryId = $_POST['category'];
    
    // Fetch courses based on selected category
    $courseSql = "SELECT * FROM Courses WHERE CategoryID = ?";
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
        
        // Fetch semesters based on selected course
        $semesterSql = "SELECT DISTINCT SemesterNumber FROM Units WHERE CourseID = (SELECT CourseID FROM Courses WHERE CourseName = ?)";
        $semesterStmt = $conn->prepare($semesterSql);
        $semesterStmt->bind_param("s", $courseName);
        $semesterStmt->execute();
        $semesterResult = $semesterStmt->get_result();
        if ($semesterResult->num_rows > 0) {
            while ($row = $semesterResult->fetch_assoc()) {
                $semesters[] = $row['SemesterNumber'];
            }
        }

        if (isset($_POST['semester'])) {
            $semesterNumber = $_POST['semester'];

            // Fetch units based on selected course and semester
            $unitSql = "SELECT * FROM Units WHERE CourseID = (SELECT CourseID FROM Courses WHERE CourseName = ?) AND SemesterNumber = ?";
            $unitStmt = $conn->prepare($unitSql);
            $unitStmt->bind_param("si", $courseName, $semesterNumber);
            $unitStmt->execute();
            $unitResult = $unitStmt->get_result();
            if ($unitResult->num_rows > 0) {
                while ($row = $unitResult->fetch_assoc()) {
                    $units[] = $row;
                }
            }

            if (isset($_POST['unit'])) {
                $unitCode = $_POST['unit'];
                $year = isset($_POST['year']) ? $_POST['year'] : '';
                $intake = isset($_POST['intake']) ? $_POST['intake'] : '';

                // Fetch students based on selected filters
                $studentSql = "SELECT AdmissionNumber, CONCAT(FirstName, ' ', LastName) AS FullName FROM Students WHERE CourseName = ? AND YEAR(RegistrationDate) = ? AND IntakeName = ?";
                $studentStmt = $conn->prepare($studentSql);
                $studentStmt->bind_param("sis", $courseName, $year, $intake);
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
</form>

<?php if (isset($_POST['unit'])): ?>
<form method="post" action="../IKIGAI/admin/examinations/download_exam_attendance.php">
    <input type="hidden" name="course" value="<?php echo htmlspecialchars($_POST['course']); ?>">
    <input type="hidden" name="unit" value="<?php echo htmlspecialchars($_POST['unit']); ?>">
    <input type="hidden" name="semester" value="<?php echo htmlspecialchars($_POST['semester']); ?>">
    <input type="hidden" name="year" value="<?php echo htmlspecialchars($_POST['year']); ?>">
    <input type="hidden" name="intake" value="<?php echo htmlspecialchars($_POST['intake']); ?>">
    
    <?php if (!empty($students)): ?>
                <?php foreach ($students as $student): ?>
                    <input type="hidden" name="students[<?php echo htmlspecialchars($student['AdmissionNumber']); ?>][AdmissionNumber]" value="<?php echo htmlspecialchars($student['AdmissionNumber']); ?>">
                    <input type="hidden" name="students[<?php echo htmlspecialchars($student['AdmissionNumber']); ?>][FullName]" value="<?php echo htmlspecialchars($student['FullName']); ?>">
                <?php endforeach; ?>
                <button type="submit" name="generate_pdf">Generate PDF</button>
            <?php else: ?>
                <p style="color: red;">No students found for the selected filters.</p>
            <?php endif; ?>
        </form>
<?php endif; ?>

<?php $conn->close(); ?>
