<?php
include_once __DIR__ . '/../../config/config.php';

$years = [];
$yearSql = "SELECT DISTINCT YEAR(RegistrationDate) AS Year FROM Students ORDER BY Year DESC";
$yearResult = $conn->query($yearSql);
if ($yearResult->num_rows > 0) {
    while ($row = $yearResult->fetch_assoc()) {
        $years[] = ['value' => $row['Year'], 'text' => $row['Year']];
    }
}

echo json_encode($years);
?>
