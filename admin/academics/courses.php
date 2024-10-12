<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

include_once __DIR__ . '/../../config/config.php';

// Initialize variables for search criteria
$searchCategory = isset($_POST['category']) ? $_POST['category'] : '';
$searchCourse = isset($_POST['course']) ? $_POST['course'] : '';
$searchSemester = isset($_POST['semester']) ? $_POST['semester'] : '';

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
    $unitSql = "SELECT UnitCode, UnitName, CourseContent, reference_materials FROM units WHERE CourseID = (SELECT CourseID FROM Courses WHERE CourseName = ?) AND SemesterNumber = ?";
    
    $unitStmt = $conn->prepare($unitSql);
        // Check for SQL errors
        if (!$unitStmt) {
            echo "SQL Error: " . $conn->error;
        }
    $unitStmt->bind_param("si", $searchCourse, $searchSemester);
    $unitStmt->execute();
    $unitResult = $unitStmt->get_result();

       // Debug: Check for errors after executing the query
       if ($unitStmt->error) {
        echo "Error: " . $unitStmt->error; // Check for SQL errors
    }

    if ($unitResult->num_rows > 0) {
        while ($row = $unitResult->fetch_assoc()) {
            $units[] = $row;
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Units</title>
    <link rel="stylesheet" href="https://ikigaicollege.ac.ke/Portal/assets/css/courses.css"> 
    <style>
        /* Add button styling */
        .add-course-btn {
            background-color: #E39825;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            position: absolute;
            top: 100px;
            right: 20px;
            font-size: 16px;
            text-decoration: none;
        }
        .add-course-btn:hover {
            background-color: #d08321;
        }
        .delete-unit{
            padding: 2px;
            border: none;
        }
        
    </style>
</head>
<body>
    
    <!-- Add Course Button -->
    <a href="index.php?page=academics/add_courses" class="add-course-btn">Add Course</a>
    
    <!-- Filter Form -->
    <form method="POST" action="">
        <h2>Units Course content and Reference Materials</h2>
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
    </form>

    <hr>

    <?php if ($searchCourse && $searchSemester): ?>
        <h3>Units for <?php echo htmlspecialchars($searchCourse); ?> - Semester <?php echo htmlspecialchars($searchSemester); ?></h3>
        <?php if (!empty($units)): ?>
            <?php foreach ($units as $unit): ?>
    <div class="unit">
        <h4><?php echo htmlspecialchars($unit['UnitCode']); ?> - <?php echo htmlspecialchars($unit['UnitName']); ?></h4>
        <p><strong>Course Content:</strong></p>
        <ul>
            <?php
            // Split the course content by commas and display each item in a list
            $courseContentItems = explode(',', $unit['CourseContent']);
            foreach ($courseContentItems as $item): ?>
                <li><?php echo htmlspecialchars(trim($item)); ?></li>
            <?php endforeach; ?>
        </ul>
        <p><strong>Reference Materials:</strong> <?php echo nl2br(htmlspecialchars($unit['reference_materials'])); ?></p>
        
        <!-- Edit Button -->
        <a href="index.php?page=academics/edit_unit&unitCode=<?php echo urlencode($unit['UnitCode']); ?>">Edit</a>
<!-- Add Unit Button -->
<a href="index.php?page=academics/add_unit&semester=<?php echo urlencode($searchSemester); ?>" class="add-unit-btn">Add Unit</a>
            <!-- Delete Button -->
            <form method="POST" action="index.php?page=academics/delete_unit" style="display:inline-block;">
                <input type="hidden" name="unitCode" value="<?php echo htmlspecialchars($unit['UnitCode']); ?>">
                <input type="hidden" name="courseID" value="<?php echo htmlspecialchars($courseID); ?>"> <!-- Pass course ID if needed -->
                <button type="submit" onclick="return confirm('Are you sure you want to delete this unit?');" class="delete-unit">Delete</button>
            </form>
    </div>
<?php endforeach; ?>


        <?php else: ?>
            <p class="no-units">No units found for the selected course and semester.</p>
        <?php endif; ?>
    <?php endif; ?>
</body>
</html>
