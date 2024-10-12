<?php
include_once __DIR__ . '/../../config/config.php';

if (isset($_GET['schedule_id'])) {
    $scheduleId = intval($_GET['schedule_id']);
    $scheduleSql = "SELECT * FROM semester_schedule WHERE id = ?";
    $stmt = $conn->prepare($scheduleSql);
    $stmt->bind_param('i', $scheduleId);
    $stmt->execute();
    $schedule = $stmt->get_result()->fetch_assoc();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Update the schedule
        $category = $_POST['category'];
        $year = $_POST['year'];
        $intake = $_POST['intake'];
        $semester = $_POST['semester'];
        $startDate = $_POST['start_date'];
        $endDate = $_POST['end_date'];

        $updateSql = "UPDATE semester_schedule SET category_name = ?, year = ?, intake = ?, semester = ?, start_date = ?, end_date = ? WHERE id = ?";
        $stmt = $conn->prepare($updateSql);
        $stmt->bind_param('ssssssi', $category, $year, $intake, $semester, $startDate, $endDate, $scheduleId);
        if ($stmt->execute()) {
            echo "Schedule updated successfully.";
        } else {
            echo "Error updating schedule: " . $conn->error;
        }

        // Redirect back to the schedules page
        header("Location: index.php?page=academics/view_schedules");
        exit();
    }
} else {
    echo "Invalid request.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Schedule</title>
</head>
<body>
<h2>Edit Schedule</h2>
<form method="POST">
    <label for="category">Category:</label>
    <input type="text" id="category" name="category" value="<?php echo htmlspecialchars($schedule['category_name']); ?>" required><br>

    <label for="year">Year:</label>
    <input type="text" id="year" name="year" value="<?php echo htmlspecialchars($schedule['year']); ?>" required><br>

    <label for="intake">Intake:</label>
    <input type="text" id="intake" name="intake" value="<?php echo htmlspecialchars($schedule['intake']); ?>" required><br>

    <label for="semester">Semester:</label>
    <input type="text" id="semester" name="semester" value="<?php echo htmlspecialchars($schedule['semester']); ?>" required><br>

    <label for="start_date">Start Date:</label>
    <input type="date" id="start_date" name="start_date" value="<?php echo htmlspecialchars($schedule['start_date']); ?>" required><br>

    <label for="end_date">End Date:</label>
    <input type="date" id="end_date" name="end_date" value="<?php echo htmlspecialchars($schedule['end_date']); ?>" required><br>

    <button type="submit">Save Changes</button>
</form>
</body>
</html>
