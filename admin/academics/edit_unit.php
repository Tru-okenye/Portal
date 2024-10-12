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
    <style>
  
       form h2 {
            color: #E39825;
            text-align: center;
            margin-bottom: 20px;
        }

        form {
            max-width: 800px;
            margin: 0 auto;
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0px 2px 8px rgba(0, 0, 0, 0.1);
        }

        label {
            font-weight: bold;
            color: #3B2314;
            display: block;
            margin-bottom: 10px;
        }

        textarea, input[type="submit"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 14px;
        }

        input[type="submit"] {
            background-color: #E39825;
            color: white;
            cursor: pointer;
            border: none;
        }

        input[type="submit"]:hover {
            background-color: #d38520;
        }

        /* Responsive Styles */
        @media screen and (max-width: 768px) {
            h2 {
                font-size: 22px;
            }

            form {
                padding: 15px;
            }

            textarea {
                font-size: 13px;
            }

            input[type="submit"] {
                padding: 12px;
                font-size: 16px;
            }
        }

        @media screen and (max-width: 480px) {
            h2 {
                font-size: 18px;
            }

            form {
                padding: 10px;
            }

            textarea {
                font-size: 12px;
                height: auto;
            }

            input[type="submit"] {
                padding: 10px;
                font-size: 14px;
            }
        }
    </style>
</head>
<body>

    
    <form method="POST">
        <h2>Edit Unit: <?php echo htmlspecialchars($unit['UnitName']); ?> (<?php echo htmlspecialchars($unit['UnitCode']); ?>)</h2>
        <label for="courseContent">Course Content:</label>
        <textarea id="courseContent" name="courseContent" rows="6"><?php echo htmlspecialchars($unit['CourseContent']); ?></textarea>

        <label for="referenceMaterials">Reference Materials:</label>
        <textarea id="referenceMaterials" name="referenceMaterials" rows="6"><?php echo htmlspecialchars($unit['reference_materials']); ?></textarea>

        <input type="submit" value="Save Changes">
    </form>

</body>
</html>

