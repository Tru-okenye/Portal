<?php
include_once __DIR__ . '/../../config/config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Collect form data
    $courseID = $_POST['course_id']; // Collect CourseID manually
    $categoryID = $_POST['category'];
    $courseName = $_POST['course_name'];
    $requirements = $_POST['requirements'];
    $courseOutline = $_POST['course_outline'];

    // Insert into Courses table with CourseID manually
    $sql = "INSERT INTO courses (CourseID, CategoryID, CourseName, Requirements, CourseOutline) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sisss", $courseID, $categoryID, $courseName, $requirements, $courseOutline);

    if ($stmt->execute()) {
        echo "Course added successfully!";
    } else {
        echo "Error: " . $stmt->error;
    }
    $stmt->close();
}

$catSql = "SELECT * FROM categories";
$catResult = $conn->query($catSql);
$categories = [];
if ($catResult->num_rows > 0) {
    while ($row = $catResult->fetch_assoc()) {
        $categories[] = $row;
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Course</title>
</head>

<style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }

        h2 {
            text-align: center;
            color: #E39825;
            margin: 20px 0;
        }

        form {
            max-width: 600px;
            margin: 50px auto;
            padding: 20px;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            border: 2px solid #3B2314;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #3B2314;
        }

        input[type="text"], select, textarea {
            width: 100%;
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 4px;
            border: 1px solid #E39825;
            box-sizing: border-box;
        }

        input[type="text"]:focus, select:focus, textarea:focus {
            border-color: #3B2314;
            outline: none;
        }

        button {
            width: 100%;
            padding: 10px;
            background-color: #E39825;
            color: white;
            font-weight: bold;
            cursor: pointer;
            border: none;
            border-radius: 4px;
            transition: background-color 0.3s ease;
        }

        button:hover {
            background-color: #3B2314; /* Dark brown on hover */
        }

        textarea {
            resize: vertical;
            min-height: 100px;
        }

        p {
            text-align: center;
        }
    </style>
    
<body>
    <h2>Add New Course</h2>
    <form method="POST" action="">
        <label for="course_id">Course ID:</label>
        <input type="text" id="course_id" name="course_id" required>

        <label for="category">Category:</label>
        <select id="category" name="category">
            <?php foreach ($categories as $category): ?>
                <option value="<?php echo $category['CategoryID']; ?>">
                    <?php echo htmlspecialchars($category['CategoryName']); ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label for="course_name">Course Name:</label>
        <input type="text" id="course_name" name="course_name" required>

        <label for="requirements">Requirements:</label>
        <textarea id="requirements" name="requirements"></textarea>

        <label for="course_outline">Course Outline:</label>
        <textarea id="course_outline" name="course_outline"></textarea>

        <button type="submit">Add Course</button>
    </form>
</body>
</html>

