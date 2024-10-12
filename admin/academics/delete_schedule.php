<?php
include_once __DIR__ . '/../../config/config.php';

if (isset($_GET['schedule_id'])) {
    $scheduleId = intval($_GET['schedule_id']);

    // Delete the schedule from the database
    $deleteSql = "DELETE FROM semester_schedule WHERE id = ?";
    $stmt = $conn->prepare($deleteSql);
    $stmt->bind_param('i', $scheduleId);
    if ($stmt->execute()) {
        echo "Schedule deleted successfully.";
    } else {
        echo "Error deleting schedule: " . $conn->error;
    }

    // Redirect back to the schedules page
    echo '<meta http-equiv="refresh" content="2;url=index.php?page=academics/view_schedules">';

    exit();
} else {
    echo "Invalid request.";
}
