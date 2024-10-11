<?php
include_once __DIR__ . '/../../config/config.php';

$intakes = [];
$intakeSql = "SELECT DISTINCT IntakeName FROM Students";
$intakeResult = $conn->query($intakeSql);
if ($intakeResult->num_rows > 0) {
    while ($row = $intakeResult->fetch_assoc()) {
        $intakes[] = ['value' => $row['IntakeName'], 'text' => $row['IntakeName']];
    }
}

echo json_encode($intakes);
?>
