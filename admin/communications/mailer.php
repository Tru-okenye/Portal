<?php
include_once __DIR__ . '/../../vendor/autoload.php'; // Ensure this path is correct

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Fetch SMTP configuration
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Fetch distinct courses
$courseQuery = "SELECT DISTINCT CourseName FROM students";
$courseResult = $conn->query($courseQuery);

$courses = [];
if ($courseResult->num_rows > 0) {
    while ($row = $courseResult->fetch_assoc()) {
        $courses[] = $row['CourseName'];
    }
}

// Fetch distinct intakes
$intakeQuery = "SELECT DISTINCT IntakeName FROM students";
$intakeResult = $conn->query($intakeQuery);

$intakes = [];
if ($intakeResult->num_rows > 0) {
    while ($row = $intakeResult->fetch_assoc()) {
        $intakes[] = $row['IntakeName'];
    }
}

// Fetch all students
$sql = "SELECT AdmissionNumber, FirstName, LastName, Email FROM students";
$result = $conn->query($sql);

$students = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $students[] = $row;
    }
}

// Handle form submission
$message = ""; // Variable to store success or error message
if (isset($_POST['sendEmail'])) {
    $recipient = $_POST['recipient'];
    $subject = $_POST['subject'];
    $body = $_POST['body'];
    $selectedCourse = $_POST['course'];
    $selectedIntake = $_POST['intake'];

    // Modify query based on filters
    $query = "SELECT AdmissionNumber, FirstName, LastName, Email FROM students WHERE 1=1";
    
    if (!empty($selectedCourse)) {
        $query .= " AND CourseName = '" . $conn->real_escape_string($selectedCourse) . "'";
    }

    if (!empty($selectedIntake)) {
        $query .= " AND IntakeName = '" . $conn->real_escape_string($selectedIntake) . "'";
    }

    $result = $conn->query($query);
    $filteredStudents = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $filteredStudents[] = $row;
        }
    }

    // Check if there's an attachment
    $attachment = isset($_FILES['attachment']) ? $_FILES['attachment'] : null;

    // Call the sendEmail function
    $emailSent = sendEmail($recipient, $subject, $body, $filteredStudents, $smtpConfig, $attachment);

    if ($emailSent) {
        $message = "Email sent successfully!";
    } else {
        $message = "Email could not be sent. Please try again.";
    }

    // Redirect to prevent resending on reload
    header("Location: " . $_SERVER['PHP_SELF'] . "?message=" . urlencode($message));
    exit;
}

function sendEmail($recipient, $subject, $body, $students, $smtpConfig, $attachment) {
    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host = $_ENV['smtp_host']; 
        $mail->SMTPAuth = true;
        $mail->Username = $_ENV['smtp_username']; 
        $mail->Password = $_ENV['smtp_password']; 
        $mail->SMTPSecure = $_ENV['smtp_encryption'];
        $mail->Port = $_ENV['smtp_port'];

        // Sender
        $mail->setFrom('ikigaicollegeke@gmail.com', 'IKIGAI COLLEGE OF INTERIOR DESIGN');

        // Check if there is an attachment and add it to the email
        if ($attachment && $attachment['error'] == UPLOAD_ERR_OK) {
            $mail->addAttachment($attachment['tmp_name'], $attachment['name']);
        }

        // Recipients
        if ($recipient === 'all') {
            foreach ($students as $student) {
                // Personalize the email body with the admission number
                $personalizedBody = "Dear " . $student['FirstName'] . " " . $student['LastName'] . ",\n\n" .
                                    $body . "\n\nYour Admission Number is: " . $student['AdmissionNumber'];

                $mail->addAddress($student['Email']);
                
                // Set personalized content for each student
                $mail->isHTML(true);
                $mail->Subject = $subject;
                $mail->Body    = nl2br(htmlspecialchars($personalizedBody)); // Convert new lines to <br> tags
                $mail->AltBody = strip_tags($personalizedBody);

                // Send email
                $mail->send();
                $mail->clearAddresses(); // Clear address for the next loop
            }
        } else {
            // Personalize for a single student
            $student = $students[0]; // Assuming the recipient corresponds to the first match in the filtered students array
            $personalizedBody = "Dear " . $student['FirstName'] . " " . $student['LastName'] . ",\n\n" .
                                $body . "\n\nYour Admission Number is: " . $student['AdmissionNumber'];

            $mail->addAddress($recipient);

            // Set content
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = nl2br(htmlspecialchars($personalizedBody)); // Convert new lines to <br> tags
            $mail->AltBody = strip_tags($personalizedBody);

            // Send email
            $mail->send();
        }

        return true; // Success
    } catch (Exception $e) {
        return false; // Failure
    }
}
?>


<style>
    form h2, h3 {
        color: #E39825;
    }

    label {
        margin-right: 5px;
        color: #3B2314;
        font-weight: bold;
    }

    select, button {
        width: 15%;
        padding: 8px;
        margin-top: 15px;
        border: 1px solid;
        border-radius: 5px;
        font-size: 14px;
        color: #fff;
    }

    select {
      
      color: black; 
  }

    button {
        background-color: #3B2314;
        color: white;
        border: none;
        cursor: pointer;
        margin-top: 6px;
        width: 12%;
        padding: 6px;
        border-radius: 10px;
    }

    button:hover {
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
</style>
<!-- Display Message at the Top -->
<?php if (isset($_GET['message'])): ?>
    <p style="color: green; font-weight: bold;"><?php echo htmlspecialchars($_GET['message']); ?></p>
<?php endif; ?>

<!-- Email Form -->
<form method="post" action="" enctype="multipart/form-data">
    <h2>Send Email to Students</h2>
    <label for="course">Select Course:</label>
    <select name="course" id="course">
        <option value="">All Courses</option>
        <?php foreach ($courses as $course): ?>
            <option value="<?php echo htmlspecialchars($course); ?>">
                <?php echo htmlspecialchars($course); ?>
            </option>
        <?php endforeach; ?>
    </select><br><br>

    <label for="intake">Select Intake:</label>
    <select name="intake" id="intake">
        <option value="">All Intakes</option>
        <?php foreach ($intakes as $intake): ?>
            <option value="<?php echo htmlspecialchars($intake); ?>">
                <?php echo htmlspecialchars($intake); ?>
            </option>
        <?php endforeach; ?>
    </select><br><br>

    <label for="recipient">Select Student:</label>
    <select name="recipient" id="recipient">
        <option value="all">All Students</option>
        <?php foreach ($students as $student): ?>
            <option value="<?php echo htmlspecialchars($student['Email']); ?>">
                <?php echo htmlspecialchars($student['FirstName'] . ' ' . $student['LastName'] . ' (' . $student['AdmissionNumber'] . ')'); ?>
            </option>
        <?php endforeach; ?>
    </select><br><br>

    <label for="subject">Subject:</label>
    <input type="text" id="subject" name="subject" required><br><br>

    <label for="body">Email Body:</label><br>
    <textarea id="body" name="body" rows="10" cols="50" required></textarea><br><br>

    <label for="attachment">Attach File:</label><br>
    <input type="file" name="attachment" id="attachment"><br><br>

    <button type="submit" name="sendEmail">Send Email</button>
</form>

<?php
$conn->close();
?>
