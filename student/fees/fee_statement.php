<?php 
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

include_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../vendor/tecnickcom/tcpdf/tcpdf.php';

// Check if the admission number is set in the session
if (!isset($_SESSION['student_admission_number'])) {
    echo "Student session not found! Please log in.";
    exit();
}
$admission_number = $_SESSION['student_admission_number'];

// Step 1: Retrieve student details including CategoryName, IntakeName, and RegistrationDate
$sql = "
    SELECT FirstName, LastName, CategoryName, IntakeName, RegistrationDate 
    FROM students 
    WHERE AdmissionNumber = ?";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("SQL preparation error: " . $conn->error);
}
$stmt->bind_param("s", $admission_number);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $student = $result->fetch_assoc();
    $first_name = $student['FirstName'];
    $last_name = $student['LastName'];
    $category_name = $student['CategoryName'];
    $intake_name = $student['IntakeName'];
    $registration_date = $student['RegistrationDate'];
    $year = date('Y', strtotime($registration_date));
} else {
    echo "No student found with admission number: $admission_number";
    exit();
}
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student Fee Statement</title>
    <style>
     
        .btn-primary {
            background-color: #3B2314;
            color: #fff;
            border: none;
            padding: 10px 20px;
            cursor: pointer;
            border-radius: 5px;
        }
        .btn-primary:hover {
            background-color: #E39825;
        }
        .image {
            text-align: center;
            margin-bottom: 20px;
        }
        .student-logo {
            width: 200px;
            height: auto;
        }
        .student-details-container {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            background-color: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
        }
        .student-info {
            font-size: 16px;
            line-height: 1.6;
        }
        h3 {
            color: #E39825;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            background-color: #fff;
            border-radius: 10px;
            overflow: hidden;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            padding: 10px;
            text-align: left;
        }
        th {
            background-color: #E39825;
            color: white;
            font-weight: bold;
        }
        tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        .semester-header {
            margin-top: 20px;
            padding: 10px;
            background-color: #3B2314;
            color: white;
            border-radius: 5px;
        }
        .total-row {
            font-weight: bold;
            background-color: #E39825;
            color: white;
        }
    </style>
</head>
<body>
<form method="post" action="https://ikigaicollege.ac.ke/Portal/student/fees/download_fee_statement.php">
    <button type="submit"  class="btn btn-primary">Download</button>
</form>
<!-- <a href="download_fee_statement.php" class="btn-primary">Download Fee Statement</a> -->

<div class="image">
    <img src="assets/images/ikigai-college-logo-1.jpg" alt="Ikigai College Logo" class="student-logo">
</div>
<div class="student-details-container">
    <div class="student-info">
        <p><strong>Student:</strong> <?= strtoupper($first_name) . " " . strtoupper($last_name) ?></p>
        <p><strong>Category:</strong> <?= $category_name ?></p>
    </div>
    <div class="student-info">
        <p><strong>Intake:</strong> <?= $intake_name ?></p>
        <p><strong>Admission Year:</strong> <?= $year ?></p>
    </div>
</div>

<?php
// Step 2: Fetch semester schedules based on category, year, and intake
$schedule_sql = "
    SELECT semester, start_date, end_date 
    FROM semester_schedule
    WHERE category_name = ? AND year = ? AND intake = ?
    ORDER BY start_date";
$stmt_schedule = $conn->prepare($schedule_sql);
$stmt_schedule->bind_param("sis", $category_name, $year, $intake_name);
$stmt_schedule->execute();
$schedule_results = $stmt_schedule->get_result();

while ($schedule = $schedule_results->fetch_assoc()) {
    $semester_number = $schedule['semester'];
    $start_date = $schedule['start_date'];
    $end_date = $schedule['end_date'];

    // Compute academic year from the start date
    $start_year = date('Y', strtotime($start_date));
    $next_year = $start_year + 1;
    $academic_year = "{$start_year}/{$next_year}";

    // Fetch payments for the semester from the payments table
    $payments_sql = "
        SELECT TransactionReferenceCode, PaymentDate, PaymentAmount, PaymentMode, AdditionalInfo
        FROM payments
        WHERE DocumentReferenceNumber = ? AND PaymentDate BETWEEN ? AND ?";
    $stmt_payments = $conn->prepare($payments_sql);
    $stmt_payments->bind_param("sss", $admission_number, $start_date, $end_date);
    $stmt_payments->execute();
    $payments_result = $stmt_payments->get_result();

    // If there are no payments for the semester, skip to the next
    if ($payments_result->num_rows === 0) {
        continue;
    }

    // Display semester header and schedule dates
    echo "<h3>Semester {$semester_number} ({$academic_year})</h3>";
    echo "<p>Start Date: {$start_date} | End Date: {$end_date}</p>";

    // Display the payments in a table
    echo "<table border='1'>";
    echo "<tr><th>Transaction Reference</th><th>Payment Date</th><th>Amount</th><th>Payment Mode</th><th>Additional Info</th></tr>";

    $semester_total_paid = 0;
    while ($payment = $payments_result->fetch_assoc()) {
        $transaction_ref = $payment['TransactionReferenceCode'];
        $payment_date = $payment['PaymentDate'];
        $payment_amount = $payment['PaymentAmount'];
        $payment_mode = $payment['PaymentMode'];
        $additional_info = $payment['AdditionalInfo'];

        // Sum up the total amount paid for the semester
        $semester_total_paid += $payment_amount;

        // Display each payment record
        echo "<tr>
                <td>{$transaction_ref}</td>
                <td>{$payment_date}</td>
                <td>{$payment_amount}</td>
                <td>{$payment_mode}</td>
                <td>{$additional_info}</td>
              </tr>";
    }

    // Display the total for the semester
    echo "<tr><td colspan='2'><strong>Total Paid for Semester</strong></td><td colspan='3'><strong>{$semester_total_paid}</strong></td></tr>";
    echo "</table><br>";
}
?>

</body>
</html>
