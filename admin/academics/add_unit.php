<?php
include_once __DIR__ . '/../../config/config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $courseID = $_POST['courseID'];
    $semester = $_POST['semester'];
    $unitCode = $_POST['unitCode'];
    $unitName = $_POST['unitName'];
    $courseContent = $_POST['courseContent'];
    $contactHours = $_POST['contactHours'];
    $referenceMaterials = $_POST['referenceMaterials'];

    $sql = "INSERT INTO units (CourseID, SemesterNumber, UnitCode, UnitName, CourseContent, ContactHours, reference_materials) 
            VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sisssis", $courseID, $semester, $unitCode, $unitName, $courseContent, $contactHours, $referenceMaterials);

    if ($stmt->execute()) {
        echo "Unit added successfully.";
    } else {
        echo "Error adding unit: " . $stmt->error;
    }
}
?>

<style>
      

        form {
            max-width: 600px;
            margin: 50px auto;
            padding: 20px;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            border: 2px solid #3B2314;
        }

        form h2 {
            text-align: center;
            color: #E39825;
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #3B2314;
        }

        input[type="text"],
        input[type="number"],
        textarea {
            width: 100%;
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 4px;
            border: 1px solid #E39825;
            box-sizing: border-box;
        }

        textarea {
            resize: vertical; /* Allow vertical resizing of textarea */
        }

        button {
            width: 100%;
            padding: 10px;
            background-color: #E39825; /* Orange color */
            color: white;
            font-weight: bold;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        button:hover {
            background-color: #3B2314; /* Dark brown on hover */
        }

        /* Media Queries for Responsiveness */
        @media (max-width: 768px) {
            form {
                margin: 20px; /* Reduce margin on smaller screens */
                padding: 15px; /* Reduce padding on smaller screens */
            }

            h2 {
                font-size: 1.5em; /* Smaller heading on medium screens */
            }
        }

        @media (max-width: 480px) {
            h2 {
                font-size: 1.2em; /* Smaller heading on small screens */
            }

            label {
                font-size: 0.9em; /* Slightly smaller labels */
            }

            input[type="text"],
            input[type="number"],
            textarea,
            button {
                padding: 8px; /* Smaller padding */
            }
        }
    </style>

<form method="post" action="">
    <h2>Add Unit</h2>

    <!-- Manually enter Course ID -->
    <label for="courseID">Course ID:</label>
    <input type="text" id="courseID" name="courseID" required><br><br>

    <!-- Semester passed from the URL -->
    <input type="hidden" name="semester" value="<?php echo htmlspecialchars($_GET['semester']); ?>">

    <label for="unitCode">Unit Code:</label>
    <input type="text" id="unitCode" name="unitCode" required><br><br>

    <label for="unitName">Unit Name:</label>
    <input type="text" id="unitName" name="unitName" required><br><br>

    <label for="courseContent">Course Content:</label>
    <textarea id="courseContent" name="courseContent" rows="5" required></textarea><br><br>

    <label for="contactHours">Contact Hours:</label>
    <input type="number" id="contactHours" name="contactHours" required><br><br>

    <label for="referenceMaterials">Reference Materials:</label>
    <textarea id="referenceMaterials" name="referenceMaterials" rows="5"></textarea><br><br>

    <button type="submit">Add Unit</button>
</form>
