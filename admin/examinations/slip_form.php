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
            document.getElementById('year').disabled = true;
            document.getElementById('intake').disabled = true;
            document.getElementById('semester').disabled = true;
        });
    }

    function handleCourseChange() {
        const courseName = document.getElementById('course').value;
        fetchOptions('admin/examinations/fetch_years.php', {}, function(data) {
            updateDropdown('year', data);
            document.getElementById('year').disabled = false;
            document.getElementById('intake').disabled = true;
            document.getElementById('semester').disabled = true;
        });
    }

    function handleYearChange() {
        fetchOptions('admin/examinations/fetch_intakes.php', {}, function(data) {
            updateDropdown('intake', data);
            document.getElementById('intake').disabled = false;
            document.getElementById('semester').disabled = true;
        });
    }

    function handleIntakeChange() {
        const courseName = document.getElementById('course').value;
        const intake = document.getElementById('intake').value;
        fetchOptions('admin/examinations/fetch_semesters.php', { courseName: courseName, intake: intake }, function(data) {
            updateDropdown('semester', data);
            document.getElementById('semester').disabled = false;
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
            margin: 20px 0;
        }
        h1 {
            color: #E39825;
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
    </style>
<h1>Generate Result Slip</h1>
<div class="card">

    <form action="index.php?page=examinations/result_slip" method="POST">
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
        <select id="intake" name="intake" onchange="handleIntakeChange()" disabled>
            <option value="">Select Intake</option>
            <?php foreach ($intakes as $intake): ?>
                <option value="<?php echo $intake; ?>"><?php echo $intake; ?></option>
            <?php endforeach; ?>
        </select>
        <br><br>
    
        <label for="semester">Semester:</label>
        <select id="semester" name="semester" disabled>
            <option value="">Select Semester</option>
        </select>
        <br><br>
    
        <input type="submit" value="Generate Result Slip">
    </form>

</div>    
