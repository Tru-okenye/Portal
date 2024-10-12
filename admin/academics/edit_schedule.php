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
        echo '<meta http-equiv="refresh" content="2;url=index.php?page=academics/view_schedules">';

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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Schedule</title>
    <style>
     
        form {
            max-width: 600px;
            margin: 50px auto;
            padding: 20px;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            border: 2px solid #3B2314;
        }

       form  h2 {
            text-align: center;
            color: #E39825;
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #3B2314;
        }

        input[type="text"], input[type="date"], button[type="submit"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 4px;
            border: 1px solid #E39825;
            box-sizing: border-box;
        }

        input[type="text"]:focus, input[type="date"]:focus {
            border-color: #3B2314;
            outline: none;
        }

        button[type="submit"] {
            background-color: #E39825; /* Orange color */
            color: white;
            font-weight: bold;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        button[type="submit"]:hover {
            background-color: #3B2314; /* Dark brown on hover */
        }

        /* Responsive Styles */
        @media screen and (max-width: 768px) {
            form {
                max-width: 90%;
                padding: 15px;
            }

            h2 {
                font-size: 20px;
            }

            input[type="text"], input[type="date"], button[type="submit"] {
                font-size: 14px;
                padding: 8px;
            }
        }

        @media screen and (max-width: 480px) {
            form {
                max-width: 100%;
                padding: 10px;
            }

            h2 {
                font-size: 18px;
            }

            input[type="text"], input[type="date"], button[type="submit"] {
                font-size: 12px;
                padding: 6px;
            }

            button[type="submit"] {
                padding: 10px;
                font-size: 14px;
            }
        }
    </style>
</head>
<body>

    <form method="POST">
    <h2>Edit Schedule</h2>
    <label for="category">Category:</label>
    <input type="text" id="category" name="category" value="<?php echo htmlspecialchars($schedule['category_name']); ?>" required>

    <label for="year">Year:</label>
    <input type="text" id="year" name="year" value="<?php echo htmlspecialchars($schedule['year']); ?>" required>

    <label for="intake">Intake:</label>
    <input type="text" id="intake" name="intake" value="<?php echo htmlspecialchars($schedule['intake']); ?>" required>

    <label for="semester">Semester:</label>
    <input type="text" id="semester" name="semester" value="<?php echo htmlspecialchars($schedule['semester']); ?>" required>

    <label for="start_date">Start Date:</label>
    <input type="date" id="start_date" name="start_date" value="<?php echo htmlspecialchars($schedule['start_date']); ?>" required>

    <label for="end_date">End Date:</label>
    <input type="date" id="end_date" name="end_date" value="<?php echo htmlspecialchars($schedule['end_date']); ?>" required>

    <button type="submit">Save Changes</button>
</form>

</body>
</html>
