<?php
include_once __DIR__ . '/../../config/config.php';

// Fetch categories, years, and intake options
$categories = [];
$years = [];
$intakes = [];

// Fetch categories
$catSql = "SELECT * FROM categories";
$catResult = $conn->query($catSql);
if ($catResult->num_rows > 0) {
    while ($row = $catResult->fetch_assoc()) {
        $categories[] = $row;
    }
}

// Fetch distinct years and intake options
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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Exam Results</title>
    <script>
        function fetchOptions(url, data, callback) {
            fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: new URLSearchParams(data)
            })
            .then(response => response.json())
            .then(result => callback(result))
            .catch(error => console.error('Error:', error));
        }

        function updateDropdown(selectId, options) {
            const select = document.getElementById(selectId);
            select.innerHTML = '<option value="">Select</option>';
            options.forEach(option => {
                select.innerHTML += `<option value="${option.value}">${option.text}</option>`;
            });
        }

        function handleCategoryChange() {
            const categoryId = document.getElementById('category').value;
            fetchOptions('admin/examinations/fetch_courses.php', { categoryId: categoryId }, function(data) {
                updateDropdown('course', data);
                document.getElementById('course').disabled = false;
                document.getElementById('semester').disabled = true;
            });
        }

        function handleCourseChange() {
            const courseName = document.getElementById('course').value;
            fetchOptions('admin/examinations/fetch_semesters.php', { courseName: courseName }, function(data) {
                updateDropdown('semester', data);
                document.getElementById('semester').disabled = false;
            });
        }
    </script>
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

    .button {
        background-color: #3B2314;
        color: white;
        border: none;
        cursor: pointer;
        margin-top: 6px;
        width: 100%; /* Full width on small screens */
        padding: 10px; /* Increase padding for better touch targets */
        border-radius: 10px;
    }

    .button:hover {
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
        select, .button {
            width: 48%; /* Adjust width for medium screens */
            display: inline-block; /* Display buttons and selects side by side */
            margin-right: 4%; /* Space between elements */
        }

        .button {
            margin-top: 0; /* Remove margin on button for better alignment */
        }

        /* Reset the right margin for the last button */
        .button:last-of-type, select:last-of-type {
            margin-right: 0;
        }
    }

    @media screen and (min-width: 769px) {
        select, .button {
            width: 15%; /* Keep original width for large screens */
        }
    }


    </style>
</head>
<body>
    <form method="post" action="">
        <h2>View Exam Results</h2>
        <!-- Category Dropdown -->
        <label for="category">Category:</label>
        <select id="category" name="category" required onchange="handleCategoryChange()">
            <option value="">Select Category</option>
            <?php foreach ($categories as $category): ?>
                <option value="<?php echo htmlspecialchars($category['CategoryID']); ?>"><?php echo htmlspecialchars($category['CategoryName']); ?></option>
            <?php endforeach; ?>
        </select>

        <!-- Course Dropdown -->
        <label for="course">Course:</label>
        <select id="course" name="course" required onchange="handleCourseChange()" disabled>
            <option value="">Select Course</option>
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
            <?php foreach ($intakes as $intake): ?>
                <option value="<?php echo htmlspecialchars($intake); ?>"><?php echo htmlspecialchars($intake); ?></option>
            <?php endforeach; ?>
        </select>

        <!-- Semester Dropdown -->
        <label for="semester">Semester:</label>
        <select id="semester" name="semester" required disabled>
            <option value="">Select Semester</option>
        </select>

        <input type="submit" value="Submit" class="button">
    </form>

    <!-- Display results -->
    <?php
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        // Retrieve form data
        $categoryId = $_POST['category'] ?? '';
        $courseName = $_POST['course'] ?? '';
        $year = $_POST['year'] ?? '';
        $intake = $_POST['intake'] ?? '';
        $semester = $_POST['semester'] ?? '';

        // Fetch category name based on category ID
        $catNameSql = "SELECT CategoryName FROM categories WHERE CategoryID = ?";
        if ($catStmt = $conn->prepare($catNameSql)) {
            $catStmt->bind_param('i', $categoryId);
            $catStmt->execute();
            $catResult = $catStmt->get_result();
            $category = $catResult->fetch_assoc();
            $categoryName = $category['CategoryName'] ?? '';

            $catStmt->close();
        } else {
            echo "Error: " . $conn->error;
        }

      // Fetch results including cat_marks, exam_marks, total_marks
      $sql = "SELECT admission_number, student_name, grade, class, cat_marks, exam_marks, total_marks, unit_name 
      FROM exam_marks 
      WHERE category_name = ? AND course_name = ? AND year = ? AND intake = ? AND semester = ? 
      ORDER BY unit_name, admission_number";

if ($stmt = $conn->prepare($sql)) {
  $stmt->bind_param('sssss', $categoryName, $courseName, $year, $intake, $semester);
  $stmt->execute();
  $result = $stmt->get_result();

  $results = [];
  while ($row = $result->fetch_assoc()) {
      $results[$row['unit_name']][] = $row;
  }

  $stmt->close();
} else {
  echo "Error: " . $conn->error;
}

$conn->close();

// Display results
if (!empty($results)) {
  // Display heading
  echo "<h3>Results for $categoryName - $courseName, $year, $intake Intake, Semester $semester</h3>";

  foreach ($results as $unitName => $records) {
      echo "<h4>Unit: " . htmlspecialchars($unitName) . "</h4>";
      echo "<table border='1'>";
      echo "<thead>
              <tr>
                  <th>Admission Number</th>
                  <th>Name</th>
                  <th>Cat Marks</th>
                  <th>Exam Marks</th>
                  <th>Total Marks</th>
                    <th>Grade</th>
                  <th>Class</th>
                  <th>Edit</th>
              </tr>
          </thead>
          <tbody>";
      foreach ($records as $record) {
          echo "<tr>
                  <td>" . htmlspecialchars($record['admission_number']) . "</td>
                  <td>" . htmlspecialchars($record['student_name']) . "</td>
                  <td>" . htmlspecialchars($record['cat_marks']) . "</td>
                  <td>" . htmlspecialchars($record['exam_marks']) . "</td>
                  <td>" . htmlspecialchars($record['total_marks']) . "</td>
                  <td>" . htmlspecialchars($record['grade']) . "</td>
                  <td>" . htmlspecialchars($record['class']) . "</td>
                  <td><a href='index.php?page=examinations/edit_result&admissionNumber=" . urlencode($record['admission_number']) . "'>Edit</a></td> <!-- Add Edit button -->
              </tr>";
      }
      echo "</tbody></table>";
  }
} else {
  echo "<p>No exam results found.</p>";
}
}
?>
</body>
</html>