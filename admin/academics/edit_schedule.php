<?php
include_once __DIR__ . '/../../config/config.php';

// Fetch schedule by ID for editing
if (isset($_GET['id'])) {
    $scheduleId = $_GET['id'];
    $fetchScheduleSql = "SELECT * FROM semester_schedule WHERE id = ?";
    $stmt = $conn->prepare($fetchScheduleSql);
    $stmt->bind_param("i", $scheduleId);
    $stmt->execute();
    $result = $stmt->get_result();
    $schedule = $result->fetch_assoc();
    
    if (!$schedule) {
        die("Schedule not found.");
    }
}

// Handle form submission for editing
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $category = $_POST['category_name'];
    $year = $_POST['year'];
    $intake = $_POST['intake'];
    $semester = $_POST['semester'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    
    $updateSql = "UPDATE semester_schedule SET category_name = ?, year = ?, intake = ?, semester = ?, start_date = ?, end_date = ? WHERE id = ?";
    $stmt = $conn->prepare($updateSql);
    $stmt->bind_param("ssssssi", $category, $year, $intake, $semester, $start_date, $end_date, $scheduleId);
    
    if ($stmt->execute()) {
        echo "Schedule updated successfully!";
        header('Location: index.php?page=schedules'); // Redirect after update
        exit;
    } else {
        echo "Error updating schedule.";
    }
}
?>

<!-- Edit Schedule Form -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Schedule</title>
</head>
<body>
<h2>Edit Schedule</h2>
<form method="POST" action="">
    <label for="category_name">Category:</label>
    <input type="text" id="category_name" name="category_name" value="<?php echo htmlspecialchars($schedule['category_name']); ?>" required><br>
    
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
    
    <button type="submit">Update Schedule</button>
</form>
</body>
</html>
