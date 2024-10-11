<?php


include_once __DIR__ . '/../../config/config.php';
$smtpConfig = include_once __DIR__ . '/../../config/smtp_config.php';

include_once __DIR__ . '/../../vendor/autoload.php'; // Ensure this path is correct

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Initialize variables for search criteria
$searchCategory = isset($_POST['category']) ? $_POST['category'] : (isset($_SESSION['searchCategory']) ? $_SESSION['searchCategory'] : '');
$searchCourse = isset($_POST['course']) ? $_POST['course'] : (isset($_SESSION['searchCourse']) ? $_SESSION['searchCourse'] : '');
$searchYear = isset($_POST['year']) ? $_POST['year'] : (isset($_SESSION['searchYear']) ? $_SESSION['searchYear'] : '');
$searchIntake = isset($_POST['intake']) ? $_POST['intake'] : (isset($_SESSION['searchIntake']) ? $_SESSION['searchIntake'] : '');

// Save search criteria to session
$_SESSION['searchCategory'] = $searchCategory;
$_SESSION['searchCourse'] = $searchCourse;
$_SESSION['searchYear'] = $searchYear;
$_SESSION['searchIntake'] = $searchIntake;

// Fetch distinct categories
$catSql = "SELECT DISTINCT CategoryName FROM Students";
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
    $courseSql = "SELECT DISTINCT CourseName FROM Students WHERE CategoryName = ?";
    $courseStmt = $conn->prepare($courseSql);
    $courseStmt->bind_param("s", $searchCategory);
    $courseStmt->execute();
    $courseResult = $courseStmt->get_result();
    if ($courseResult->num_rows > 0) {
        while ($row = $courseResult->fetch_assoc()) {
            $courses[] = $row;
        }
    }
}

// Fetch distinct years
$years = [];
$yearSql = "SELECT DISTINCT YEAR(RegistrationDate) AS Year FROM Students ORDER BY Year DESC";
$yearResult = $conn->query($yearSql);
if ($yearResult->num_rows > 0) {
    while ($row = $yearResult->fetch_assoc()) {
        $years[] = $row['Year'];
    }
}

// Fetch distinct intakes
$intakes = [];
$intakeSql = "SELECT DISTINCT IntakeName FROM Students";
$intakeResult = $conn->query($intakeSql);
if ($intakeResult->num_rows > 0) {
    while ($row = $intakeResult->fetch_assoc()) {
        $intakes[] = $row['IntakeName'];
    }
}

// Fetch students based on selected filters
$students = [];
if ($searchCategory && $searchCourse && $searchYear && $searchIntake) {
    $sql = "SELECT * FROM Students 
        WHERE CategoryName = ? 
        AND CourseName = ? 
        AND YEAR(RegistrationDate) = ? 
        AND IntakeName = ? 
        ORDER BY RegistrationDate DESC";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssis", $searchCategory, $searchCourse, $searchYear, $searchIntake);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $students[] = $row;
        }
    }
}



// Handle email sending only when the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['sendEmail'])) {
    // Retrieve form data
    $admissionNumber = $_POST['admissionNumber'];
    $subject = $_POST['subject'];
    $body = $_POST['body'];
    $attachment = $_FILES['attachment'];

    // Fetch the student's email based on admission number
    $emailSql = "SELECT Email FROM Students WHERE AdmissionNumber = ?";
    $emailStmt = $conn->prepare($emailSql);
    $emailStmt->bind_param("s", $admissionNumber);
    $emailStmt->execute();
    $emailResult = $emailStmt->get_result();
    $student = $emailResult->fetch_assoc();

    if ($student) {
        $to = $student['Email'];

        // Create an instance of PHPMailer
        $mail = new PHPMailer(true); // Enable exceptions

        try {
            // Set mailer to use SMTP
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = $smtpConfig['username']; 
            $mail->Password = $smtpConfig['password']; 
            $mail->SMTPSecure = $smtpConfig['encryption'];
            $mail->Port = $smtpConfig['port'];

            // Set sender and recipient
            $mail->setFrom('okenyetru@gmail.com', 'Truphena Okenye'); // Replace with your name
            $mail->addAddress($to);

            // Add subject and body
            $mail->Subject = $subject;
            $mail->Body = "Admission Number: $admissionNumber\n\n" . $body; // Adding Admission Number to email body

            // Handle file attachment if present
            if ($attachment['size'] > 0) {
                $mail->addAttachment($attachment['tmp_name'], $attachment['name']);
            }

            // Send the email
            if (!$mail->send()) {
                echo "Email could not be sent. Mailer Error: {$mail->ErrorInfo}";
            } else {
                echo "Email has been sent to $to";
                // Optional: Redirect to avoid form resubmission
                // header("Location: confirmation.php?status=success");
        echo '<meta http-equiv="refresh" content="2;url=index.php?page=admission/confirmation">';

                exit();
            }
        } catch (Exception $e) {
            echo "Email could not be sent. Mailer Error: {$mail->ErrorInfo}";
        }
    } else {
        echo "No student found with the given admission number.";
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Confirmation List</title>
    <link rel="stylesheet" href="../IKIGAI/assets/css/confirmation.css"> 

</head>

<body>
    <h2>Student Confirmation List</h2>
<!-- Add Button -->
<a href="index.php?page=admission/registration" class="add-button">+ Add Student</a>
    <!-- Filter Form -->
    <form method="POST" action="">
        <label for="category">Category:</label>
        <select id="category" name="category" onchange="this.form.submit()">
            <option value="">Select Category</option>
            <?php foreach ($categories as $category): ?>
                <option value="<?php echo htmlspecialchars($category['CategoryName']); ?>" <?php echo ($searchCategory == $category['CategoryName']) ? 'selected' : ''; ?>>
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
            <label for="year">Year:</label>
            <select id="year" name="year" onchange="this.form.submit()">
                <option value="">Select Year</option>
                <?php foreach ($years as $year): ?>
                    <option value="<?php echo htmlspecialchars($year); ?>" <?php echo ($searchYear == $year) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($year); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        <?php endif; ?>

        <?php if ($searchYear): ?>
            <label for="intake">Intake:</label>
            <select id="intake" name="intake" onchange="this.form.submit()">
                <option value="">Select Intake</option>
                <?php foreach ($intakes as $intake): ?>
                    <option value="<?php echo htmlspecialchars($intake); ?>" <?php echo ($searchIntake == $intake) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($intake); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        <?php endif; ?>
    </form>

    <!-- Display Student Records -->
    <table border="1">
    <thead>
        <tr>
            <th>Admission Number</th>
            <th>ID Number</th>
            <th>First Name</th>
            <th>Last Name</th>
            <th>Email</th>
            <th>Phone</th>
            <th>Gender</th>
            <th>Parent's Phone</th>
            <th>Course</th>
            <th>Intake</th>
            <th>Mode of Study</th>
            <th>Application Form</th>
            <th>Registration Date</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php if (!empty($students)): ?>
            <?php foreach ($students as $row): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['AdmissionNumber']); ?></td>
                    <td><?php echo htmlspecialchars($row['IDNumber']); ?></td>
                    <td><?php echo htmlspecialchars($row['FirstName']); ?></td>
                    <td><?php echo htmlspecialchars($row['LastName']); ?></td>
                    <td><?php echo htmlspecialchars($row['Email']); ?></td>
                    <td><?php echo htmlspecialchars($row['Phone']); ?></td>
                    <td><?php echo htmlspecialchars($row['Gender']); ?></td>
                    <td><?php echo htmlspecialchars($row['ParentPhone']); ?></td>
                    <td><?php echo htmlspecialchars($row['CourseName']); ?></td>
                    <td><?php echo htmlspecialchars($row['IntakeName']); ?></td>
                    <td><?php echo htmlspecialchars($row['ModeOfStudy']); ?></td>
                    <td><a href="<?php echo htmlspecialchars('admin/admission/' . $row['ApplicationFormPath']); ?>" target="_blank">View Application Form</a></td>
                    <td><?php echo htmlspecialchars($row['RegistrationDate']); ?></td>
                    <td><?php echo htmlspecialchars($row['status']); ?></td>
                    <td>
                        <span class="email-icon" onclick="openEmailModal('<?php echo htmlspecialchars($row['AdmissionNumber']); ?>', '<?php echo htmlspecialchars($row['Email']); ?>', this)">📧</span>
                        <a href="index.php?page=admission/edit_student&admissionNumber=<?php echo urlencode($row['AdmissionNumber']); ?>">Edit</a>
                        <a href="index.php?page=admission/delete_student&admissionNumber=<?php echo urlencode($row['AdmissionNumber']); ?>" onclick="return confirm('Are you sure you want to delete this student?')">Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="15">No students found.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>



    <!-- Email Modal -->
<div id="emailModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeEmailModal()">&times;</span>
        <h2>Send Email</h2>
        <form method="post" enctype="multipart/form-data">
            <input type="hidden" id="modalAdmissionNumber" name="admissionNumber">
            <label for="subject">Subject:</label>
            <input type="text" id="subject" name="subject" required>
            <label for="body">Body:</label>
            <textarea id="body" name="body" required></textarea>
            <label for="attachment">Attachment:</label>
            <input type="file" id="attachment" name="attachment">
            <button type="submit" name="sendEmail">Send Email</button>
        </form>
    </div>
</div>
    <script>
function openEmailModal(admissionNumber, email, button) {
    var modal = document.getElementById('emailModal');
    var overlay = document.getElementById('overlay');

    document.getElementById('modalAdmissionNumber').value = admissionNumber;

    // Get the position of the button
    var rect = button.getBoundingClientRect();
    
    // Set the position of the modal
    modal.style.display = 'block';
    overlay.style.display = 'block';
    modal.style.left = (rect.right + window.scrollX + 10) + 'px'; // Position to the right of the button
    modal.style.top = (rect.top + window.scrollY) + 'px'; // Align with the button's top
}


function closeEmailModal() {
    document.getElementById('emailModal').style.display = 'none';
    document.getElementById('overlay').style.display = 'none';
}

</script>

</body>
</html>
