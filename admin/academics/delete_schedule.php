<?php
include_once __DIR__ . '/../../config/config.php';

// Check if the schedule ID is set and delete the corresponding schedule
if (isset($_GET['id'])) {
    $scheduleId = $_GET['id'];
    $deleteSql = "DELETE FROM semester_schedule WHERE id = ?";
    $stmt = $conn->prepare($deleteSql);
    $stmt->bind_param("i", $scheduleId);
    
    if ($stmt->execute()) {
        echo "Schedule deleted successfully!";
    } else {
        echo "Error deleting schedule.";
    }
    
    // Redirect back to the schedules page
    header('Location: index.php?page=schedules');
    exit;
}
?>
