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
            table, th, td {
                font-size: 14px;
                padding: 4px;
            }

            h2 {
                font-size: 11px;
            }

            td {
                word-wrap: break-word;
            }

            /* Stack table rows for very small screens */
            table {
                display: block;
                overflow-x: auto;
                white-space: nowrap;
            }

            th, td {
                display: inline-block;
                width: auto;
            }
        }
    </style>
</head>
<body>

<h2>Semester Schedules</h2>

<?php if (!empty($schedules)): ?>
    <table>
        <thead>
            <tr>
                <th>Category</th>
                <th>Year</th>
                <th>Intake</th>
                <th>Semester</th>
                <th>Start Date</th>
                <th>End Date</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($schedules as $schedule): ?>
                <tr>
                    <td><?php echo htmlspecialchars($schedule['category_name']); ?></td>
                    <td><?php echo htmlspecialchars($schedule['year']); ?></td>
                    <td><?php echo htmlspecialchars($schedule['intake']); ?></td>
                    <td><?php echo htmlspecialchars($schedule['semester']); ?></td>
                    <td><?php echo htmlspecialchars($schedule['start_date']); ?></td>
                    <td><?php echo htmlspecialchars($schedule['end_date']); ?></td>
                    <td>
                        <a href="index.php?page=academics/edit_schedule&schedule_id=<?php echo urlencode($schedule['id']); ?>">Edit</a>
                        <a href="index.php?page=academics/delete_schedule&schedule_id=<?php echo urlencode($schedule['id']); ?>" onclick="return confirm('Are you sure you want to delete this schedule?')">Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php else: ?>
    <p style="text-align: center;">No semester schedules found.</p>
<?php endif; ?>

</body>
</html>
