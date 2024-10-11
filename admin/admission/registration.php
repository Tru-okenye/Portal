<?php
include_once __DIR__ . '/../../config/config.php';
$categories = [];
$courses = [];

// Fetch categories
$catSql = "SELECT * FROM Categories";
$catResult = $conn->query($catSql);
if ($catResult->num_rows > 0) {
    while ($row = $catResult->fetch_assoc()) {
        $categories[] = $row;
    }
}

// Fetch intakes for the dropdown
$intakes = $conn->query("SELECT IntakeName FROM Intakes");

// Fetch modes of study for the dropdown
$modesOfStudy = $conn->query("SELECT ModeID, ModeName FROM ModeOfStudy");
?>

<h2>Student Registration</h2>

<form id="registrationForm" method="POST" action="index.php?page=admission/register_student" enctype="multipart/form-data">
    <!-- Personal Information -->
    <fieldset>
        <legend>Personal Information</legend>
        
        <label for="admissionNumber">Admission Number:</label>
        <input type="text" id="admissionNumber" name="admissionNumber" required><br>
        
        <label for="idNumber">ID Number:</label>
        <input type="text" id="idNumber" name="idNumber" required><br>
        
        <label for="firstName">First Name:</label>
        <input type="text" id="firstName" name="firstName" required><br>
        
        <label for="lastName">Last Name:</label>
        <input type="text" id="lastName" name="lastName" required><br>
        
        <label for="email">Email:</label>
        <input type="email" id="email" name="email" required><br>
        
        <label for="phone">Phone:</label>
        <input type="text" id="phone" name="phone" required><br>
        
        <label for="gender">Gender:</label>
        <select id="gender" name="gender" required>
            <option value="Male">Male</option>
            <option value="Female">Female</option>
            <option value="Other">Other</option>
        </select><br>

        <label for="parentPhone">Parent's Phone:</label>
        <input type="text" id="parentPhone" name="parentPhone" required><br>

        
        <label for="applicationForm">Upload Application Form:</label>
        <input type="file" id="applicationForm" name="applicationForm"><br>
        
        <button type="button" onclick="document.getElementById('educationSection').style.display='block'">Next</button>
    </fieldset>

    <!-- Education Details -->
    <fieldset id="educationSection" style="display:none;">
        <legend>Education Details</legend>
        
        <label for="category">Category:</label>
        <select id="category" name="category" required onchange="fetchCourses(this.value)">
            <option value="">Select Category</option>
            <?php foreach ($categories as $row) { ?>
                <option value="<?php echo $row['CategoryName']; ?>"><?php echo $row['CategoryName']; ?></option>
            <?php } ?>
        </select><br>
        
        <label for="course">Course:</label>
        <select id="courseDropdown" name="course" required>
            <option value="">Select Course</option>
        </select><br>
        
        <label for="intake">Intake:</label>
        <select id="intake" name="intake" required>
            <option value="">Select Intake</option>
            <?php while ($row = $intakes->fetch_assoc()) { ?>
                <option value="<?php echo $row['IntakeName']; ?>"><?php echo $row['IntakeName']; ?></option>
            <?php } ?>
        </select><br>
        
        <label for="grade">Grade:</label>
        <input type="text" id="grade" name="grade" required><br>

        <label for="modeOfStudy">Mode of Study:</label>
        <select id="modeOfStudy" name="modeOfStudy" required>
            <option value="">Select Mode of Study</option>
            <?php while ($row = $modesOfStudy->fetch_assoc()) { ?>
                <option value="<?php echo $row['ModeID']; ?>"><?php echo $row['ModeName']; ?></option>
            <?php } ?>
        </select><br>

        <button type="submit">Submit</button>
    </fieldset>
</form>

<script>
function fetchCourses(category) {
    if (category) {
        var xhr = new XMLHttpRequest();
        xhr.open("GET", "../IKIGAI/admin/admission/fetch_courses.php?category=" + encodeURIComponent(category), true);
        xhr.onload = function() {
            if (xhr.status === 200) {
                var courses = JSON.parse(xhr.responseText);
                var courseDropdown = document.getElementById('courseDropdown');
                courseDropdown.innerHTML = '<option value="">Select Course</option>'; // Reset the dropdown

                courses.forEach(function(course) {
                    var option = document.createElement('option');
                    option.value = course.CourseName;
                    option.textContent = course.CourseName;
                    courseDropdown.appendChild(option);
                });
            } else {
                console.error('Error fetching courses:', xhr.statusText);
            }
        };
        xhr.onerror = function() {
            console.error('Request failed');
        };
        xhr.send();
    } else {
        document.getElementById('courseDropdown').innerHTML = '<option value="">Select Course</option>'; // Reset the dropdown
    }
}
</script>

<style>
   /* Heading */
h2 {
    color: #E39825;
    margin-bottom: 20px;
    text-align: center; /* Center the heading */
}

/* Fieldset Styles */
fieldset {
    border: 2px solid #E39825;
    border-radius: 10px;
    padding: 20px;
    background-color: white;
    margin: 20px; /* Margin for spacing around fieldsets */
}

/* Legend Styles */
legend {
    font-weight: bold;
    font-size: 1.2em;
    color: #3B2314;
}

/* Label Styles */
label {
    display: block;
    margin: 10px 0 5px;
}

/* Input Styles */
input[type="text"], input[type="email"], select {
    width: 100%; /* Full width */
    padding: 10px; /* Padding for comfort */
    border: 1px solid #ccc; /* Border for visibility */
    border-radius: 5px; /* Rounded corners */
    box-sizing: border-box; /* Include padding and border in width */
}

/* File Input Styles */
input[type="file"] {
    margin-top: 10px;
}

/* Button Styles */
button {
    background-color: #E39825; /* Button color */
    color: white; /* Text color */
    border: none; /* No border */
    padding: 10px 15px; /* Padding */
    border-radius: 5px; /* Rounded corners */
    cursor: pointer; /* Pointer cursor on hover */
    margin-top: 10px; /* Space above buttons */
    width: 100%; /* Full-width buttons */
}

/* Button Hover Effect */
button:hover {
    background-color: #3B2314; /* Darker color on hover */
}

/* Responsive Styles */
@media (max-width: 768px) {
    /* Adjustments for small screens */
    fieldset {
        margin: 10px; /* Reduced margin on small screens */
        padding: 15px; /* Reduced padding */
    }

    h2 {
        font-size: 1.5em; /* Slightly smaller heading */
    }

    input[type="text"], input[type="email"], select {
        padding: 8px; /* Less padding on small screens */
    }

    button {
        padding: 8px; /* Less padding on small screens */
        font-size: 1em; /* Standard button font size */
    }
}

</style>
