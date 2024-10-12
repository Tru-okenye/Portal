<?php
include_once __DIR__ . '/../../config/config.php';

$categories = [];
$years = [];

// Fetch categories
$catSql = "SELECT * FROM categories";
$catResult = $conn->query($catSql);
if ($catResult->num_rows > 0) {
    while ($row = $catResult->fetch_assoc()) {
        $categories[] = $row;
    }
}

// Fetch distinct years
$yearSql = "SELECT DISTINCT YEAR(RegistrationDate) AS Year FROM students ORDER BY Year DESC";
$yearResult = $conn->query($yearSql);
if ($yearResult->num_rows > 0) {
    while ($row = $yearResult->fetch_assoc()) {
        $years[] = $row['Year'];
    }
}

// If form is submitted, insert the semester schedule into the database
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $categoryName = $_POST['category']; // Change to category name
    $year = $_POST['year'];
    $intake = $_POST['intake'];
    $semester = $_POST['semester'];
    $startDate = $_POST['start_date'];
    $endDate = $_POST['end_date'];

    $insertSql = "INSERT INTO semester_schedule (category_name, year, intake, semester, start_date, end_date) 
                  VALUES (?, ?, ?, ?, ?, ?)"; // Change column name to category_name
    $stmt = $conn->prepare($insertSql);
    $stmt->bind_param('ssssss', $categoryName, $year, $intake, $semester, $startDate, $endDate); // Change parameter types accordingly
    
    if ($stmt->execute()) {
        echo "<p>Semester schedule added successfully!</p>";

        echo '<meta http-equiv="refresh" content="2;url=index.php?page=academics/view_schedules">';

    } else {
        echo "<p>Error: " . $stmt->error . "</p>";
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Set Upcoming Semester</title>
    <script>
        function updateSemesters() {
            const categorySelect = document.getElementById('category');
            const semesterSelect = document.getElementById('semester');
            semesterSelect.innerHTML = '<option value="">Select Semester</option>'; // Clear previous options
            
            // Populate semesters based on category selection
            let semesterCount = 0;
            if (categorySelect.value) {
                if (categorySelect.value === "Diploma") { // Change to category name
                    semesterCount = 6; // 3 semesters for Diploma
                } else if (categorySelect.value === "Certificate") { // Change to category name
                    semesterCount = 3; // 6 semesters for Certificate
                }

                // Add semester options based on the semester count
                for (let i = 1; i <= semesterCount; i++) {
                    semesterSelect.innerHTML += `<option value="${i}">Semester ${i}</option>`;
                }
            }
        }
    </script>
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

        select, input[type="date"], input[type="submit"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 4px;
            border: 1px solid #E39825;
            box-sizing: border-box;
        }

        select:focus, input[type="date"]:focus {
            border-color: #3B2314;
            outline: none;
        }

        input[type="submit"] {
            background-color: #E39825; /* Orange color */
            color: white;
            font-weight: bold;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        input[type="submit"]:hover {
            background-color: #3B2314; /* Dark brown on hover */
        }

        /* Responsive Styles */
        @media screen and (max-width: 768px) {
            form {
                max-width: 90%;
                padding: 15px;
            }

            h2 {
                font-size: 20px;
            }

            select, input[type="date"], input[type="submit"] {
                font-size: 14px;
                padding: 8px;
            }
        }

        @media screen and (max-width: 480px) {
            form {
                max-width: 100%;
                padding: 10px;
            }

            h2 {
                font-size: 18px;
            }

            select, input[type="date"], input[type="submit"] {
                font-size: 12px;
                padding: 6px;
            }

            input[type="submit"] {
                padding: 10px;
                font-size: 14px;
            }
        }
    </style>
</head>
<body>

<form method="post">
    <h2>Set Upcoming Semester</h2>
    
    <!-- Category Dropdown -->
    <label for="category">Category:</label>
    <select id="category" name="category" onchange="updateSemesters()" required>
        <option value="">Select Category</option>
        <?php foreach ($categories as $category): ?>
            <option value="<?php echo htmlspecialchars($category['CategoryName']); ?>"><?php echo htmlspecialchars($category['CategoryName']); ?></option>
        <?php endforeach; ?>
    </select>

    <!-- Year Dropdown -->
    <label for="year">Year:</label>
    <select id="year" name="year" required>
        <option value="">Select Year</option>
        <?php foreach ($years as $year): ?>
            <option value="<?php echo htmlspecialchars($year); ?>"><?php echo htmlspecialchars($year); ?></option>
        <?php endforeach; ?>
    </select>

    <!-- Intake Dropdown -->
    <label for="intake">Intake:</label>
    <select id="intake" name="intake" required>
        <option value="">Select Intake</option>
        <option value="January">January</option>
        <option value="May">May</option>
        <option value="September">September</option>
    </select>

    <!-- Semester Dropdown -->
    <label for="semester">Semester:</label>
    <select id="semester" name="semester" required>
        <option value="">Select Semester</option>
    </select>

    <!-- Start Date -->
    <label for="start_date">Start Date:</label>
    <input type="date" id="start_date" name="start_date" required>

    <!-- End Date -->
    <label for="end_date">End Date:</label>
    <input type="date" id="end_date" name="end_date" required>

    <!-- Submit Button -->
    <input type="submit" value="Set Semester">
</form>

</body>
</html>
