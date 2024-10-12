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
    } else {
        echo "<p>Error: " . $stmt->error . "</p>";
    }
    $stmt->close();
}

// Fetch all semester schedules to display in the table
$fetchSchedulesSql = "SELECT * FROM semester_schedule ORDER BY year DESC";
$schedulesResult = $conn->query($fetchSchedulesSql);
$schedules = [];
if ($schedulesResult->num_rows > 0) {
    while ($row = $schedulesResult->fetch_assoc()) {
        $schedules[] = $row;
    }
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
                    semesterCount = 6; // 6 semesters for Diploma
                } else if (categorySelect.value === "Certificate") { // Change to category name
                    semesterCount = 3; // 3 semesters for Certificate
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

        h2 {
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

        input[type="submit"] {
            background-color: #E39825;
            color: white;
            font-weight: bold;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        input[type="submit"]:hover {
            background-color: #3B2314;
        }

        table {
            width: 80%;
            margin: 50px auto;
            border-collapse: collapse;
            text-align: left;
        }

        table, th, td {
            border: 1px solid #E39825;
        }

        th, td {
            padding: 12px;
            text-align: center;
        }

        th {
            background-color: #E39825;
            color: white;
        }

        td {
            background-color: white;
            color: #3B2314;
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

<!-- Table to display submitted semester schedules -->
<?php if (!empty($schedules)): ?>
    <table>
        <thead>
            <tr>
                <th>Category</th>
                <th>Year</th>
                <th>Intake</th>
                <th>Semester</th>
                <th>Start Date</th>
                <th>End Date</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($schedules as $schedule): ?>
                <tr>
                    <td><?php echo htmlspecialchars($schedule['category_name']); ?></td>
                    <td><?php echo htmlspecialchars($schedule['year']); ?></td>
                    <td><?php echo htmlspecialchars($schedule['intake']); ?></td>
                    <td><?php echo htmlspecialchars($schedule['semester']); ?></td>
                    <td><?php echo htmlspecialchars($schedule['start_date']); ?></td>
                    <td><?php echo htmlspecialchars($schedule['end_date']); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

</body>
</html>
