<?php
include_once __DIR__ . '/../../config/config.php';

// Fetch initial dropdown values
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
    <title>Generate Transcript</title>
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
            });
        }

        function handleCourseChange() {
            const courseName = document.getElementById('course').value;
            fetchOptions('admin/examinations/fetch_years.php', {}, function(data) {
                updateDropdown('year', data);
                document.getElementById('year').disabled = false;
            });
        }

        function handleYearChange() {
            fetchOptions('admin/examinations/fetch_intakes.php', {}, function(data) {
                updateDropdown('intake', data);
                document.getElementById('intake').disabled = false;
            });
        }
    </script>
    <style>
    .card {
        width: 70%;
        background-color: white;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        padding: 20px;
        margin: 20px auto; /* Center the card */
    }

    h1 {
        color: #E39825;
        text-align: center; /* Center the title */
    }

    label {
        display: block;
        margin-top: 10px;
        font-weight: bold;
    }

    select {
        padding: 5px;
        margin-top: 5px;
        width: 50%;
    }

    input[type="submit"] {
        background-color: #E39825;
        color: white;
        border: none;
        border-radius: 5px;
        padding: 10px 15px;
        cursor: pointer;
        margin-top: 15px;
    }

    input[type="submit"]:hover {
        background-color: #3B2314;
    }

    /* Responsive Styles */
    @media screen and (max-width: 768px) {
        .card {
            width: 90%; /* Adjust width for small screens */
            padding: 15px; /* Reduce padding for smaller screens */
        }

        select {
            width: 100%; /* Full width for select on small screens */
        }

        input[type="submit"] {
            width: 100%; /* Full width for submit button */
        }
    }

    @media screen and (max-width: 480px) {
        h1 {
            font-size: 24px; /* Adjust font size for smaller screens */
        }

        label {
            font-size: 16px; /* Larger font for better readability */
        }
    }
</style>

</head>
<body>
    <h1>Generate Transcript</h1>
    <div class="card">

        <form action="index.php?page=transcripts/trans_slip" method="POST">
            <label for="category">Category:</label>
            <select id="category" name="category" onchange="handleCategoryChange()">
                <option value="">Select Category</option>
                <?php foreach ($categories as $category): ?>
                    <option value="<?php echo $category['CategoryID']; ?>"><?php echo $category['CategoryName']; ?></option>
                <?php endforeach; ?>
            </select>
            <br><br>
    
            <label for="course">Course:</label>
            <select id="course" name="course" onchange="handleCourseChange()" disabled>
                <option value="">Select Course</option>
            </select>
            <br><br>
    
            <label for="year">Year:</label>
            <select id="year" name="year" onchange="handleYearChange()" disabled>
                <option value="">Select Year</option>
                <?php foreach ($years as $year): ?>
                    <option value="<?php echo $year; ?>"><?php echo $year; ?></option>
                <?php endforeach; ?>
            </select>
            <br><br>
    
            <label for="intake">Intake:</label>
            <select id="intake" name="intake" disabled>
                <option value="">Select Intake</option>
                <?php foreach ($intakes as $intake): ?>
                    <option value="<?php echo $intake; ?>"><?php echo $intake; ?></option>
                <?php endforeach; ?>
            </select>
            <br><br>
    
            <label for="yearOfStudy">Year of Study:</label>
            <select id="yearOfStudy" name="yearOfStudy">
                <option value="">Select Year of Study</option>
                <option value="1">Year 1</option>
                <option value="2">Year 2</option>
            </select>
            <br><br>
    
            <input type="submit" value="Generate Transcript">
        </form>
    </div>
</body>
</html>
