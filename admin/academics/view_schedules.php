<?php
include_once __DIR__ . '/../../config/config.php';

// Fetch all semester schedules to display in the table
$fetchSchedulesSql = "SELECT * FROM semester_schedule ORDER BY year DESC";
$schedulesResult = $conn->query($fetchSchedulesSql);
$schedules = [];
if ($schedulesResult->num_rows > 0) {
    while ($row = $schedulesResult->fetch_assoc()) {
        $schedules[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Semester Schedules</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
        }

        table {
            width: 100%;
            margin: 20px auto;
            border-collapse: collapse;
            text-align: left;
        }

        table, th, td {
            border: 1px solid #E39825;
        }

        th, td {
            padding: 8px;
            text-align: center;
            font-size: 14px;
        }

        th {
            background-color: #E39825;
            color: white;
        }

        td {
            background-color: white;
            color: #3B2314;
        }

        h2 {
            color: #E39825;
            text-align: center;
            margin-top: 20px;
        }

        a {
            color: #E39825;
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
        }

        /* Responsive Styles */
        @media screen and (max-width: 768px) {
            table {
                font-size: 17px;
                width: 100%;
            }

            th, td {
                padding: 6px;
                font-size: 16px;
            }

            h2 {
                font-size: 22px;
            }
        }

        @media screen and (max-width: 480px) {
            table {
                display: block;
                border: none; /* Remove border for block display */
                margin: 0; /* Remove margin */
                padding: 0; /* Remove padding */
            }

            th, td {
                display: block; /* Make each cell a block */
                text-align: left; /* Align text to the left */
                width: 100%; /* Full width for each cell */
                padding: 10px; /* Add padding */
                border: 1px solid #E39825; /* Add border to cells */
                margin: 5px 0; /* Add margin between cells */
            }

            th {
                background-color: #E39825; /* Keep header background */
                color: white; /* Keep header text color */
            }

            h2 {
                font-size: 18px;
            }

            /* Styling for individual schedule entries */
            .schedule-entry {
                border: 1px solid #E39825; /* Optional: Border around individual entries */
                padding: 10px; /* Optional: Padding around entries */
                margin-bottom: 10px; /* Space between entries */
                background-color: #fff; /* Optional: Background color for entries */
                box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1); /* Optional: Add shadow */
            }

            /* Hide original table header in mobile view */
            thead {
                display: none; /* Hide header in small screens */
            }
        }
    </style>
</head>
<body>

<h2>Semester Schedules</h2>

<?php if (!empty($schedules)): ?>
    <div class="schedule-list">
        <?php foreach ($schedules as $schedule): ?>
            <div class="schedule-entry">
                <strong>Category:</strong> <?php echo htmlspecialchars($schedule['category_name']); ?><br>
                <strong>Year:</strong> <?php echo htmlspecialchars($schedule['year']); ?><br>
                <strong>Intake:</strong> <?php echo htmlspecialchars($schedule['intake']); ?><br>
                <strong>Semester:</strong> <?php echo htmlspecialchars($schedule['semester']); ?><br>
                <strong>Start Date:</strong> <?php echo htmlspecialchars($schedule['start_date']); ?><br>
                <strong>End Date:</strong> <?php echo htmlspecialchars($schedule['end_date']); ?><br>
                <strong>Actions:</strong> 
                <a href="index.php?page=academics/edit_schedule&schedule_id=<?php echo urlencode($schedule['id']); ?>">Edit</a>
                <a href="index.php?page=academics/delete_schedule&schedule_id=<?php echo urlencode($schedule['id']); ?>" onclick="return confirm('Are you sure you want to delete this schedule?')">Delete</a>
            </div>
        <?php endforeach; ?>
    </div>
<?php else: ?>
    <p style="text-align: center;">No semester schedules found.</p>
<?php endif; ?>

</body>
</html>
