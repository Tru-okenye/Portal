<?php
include 'config/config.php';

// Fetch total number of students
$totalStudentsSql = "SELECT COUNT(*) AS TotalStudents FROM Students WHERE AdmissionNumber IS NOT NULL";
$totalStudentsResult = $conn->query($totalStudentsSql);
$totalStudents = $totalStudentsResult->fetch_assoc()['TotalStudents'];

// Fetch total number of courses
$totalCoursesSql = "SELECT COUNT(*) AS TotalCourses FROM Courses";
$totalCoursesResult = $conn->query($totalCoursesSql);
$totalCourses = $totalCoursesResult->fetch_assoc()['TotalCourses'];

// Fetch courses along with their categories
$courseDataSql = "
    SELECT c.CourseName, ct.CategoryName
    FROM Courses c
    JOIN Categories ct ON c.CategoryID = ct.CategoryID
";
$courseDataResult = $conn->query($courseDataSql);

$coursesForGraph = [
    'Diploma' => [],
    'Certificate' => []
];

// Organize courses into categories
if ($courseDataResult->num_rows > 0) {
    while ($row = $courseDataResult->fetch_assoc()) {
        if ($row['CategoryName'] == 'Diploma') {
            $coursesForGraph['Diploma'][] = $row['CourseName'];
        } elseif ($row['CategoryName'] == 'Certificate') {
            $coursesForGraph['Certificate'][] = $row['CourseName'];
        }
    }
}

// Count students in diploma and certificate courses
$studentCountsForGraph = [
    'Diploma' => [],
    'Certificate' => []
];

foreach ($coursesForGraph as $category => $courses) {
    foreach ($courses as $course) {
        $studentCountSql = "
            SELECT COUNT(*) AS StudentCount 
            FROM Students 
            WHERE AdmissionNumber IS NOT NULL AND CourseName = ?
        ";
        $stmt = $conn->prepare($studentCountSql);
        $stmt->bind_param("s", $course);
        $stmt->execute();
        $result = $stmt->get_result();
        $studentCount = $result->fetch_assoc()['StudentCount'];
        $studentCountsForGraph[$category][$course] = $studentCount;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enrollment Dashboard</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/6.1.8/fullcalendar.min.css">
    <link rel="stylesheet" href="../IKIGAI/assets/css/calendar.css"> 
    <link rel="stylesheet" href="../IKIGAI/assets/css/dashboard.css"> 
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>

    <style>
        .main-container {
    width: 100%;
    max-width: 1200px; /* Centered content area */
    margin: 0 auto;
}

.stats-container {
    display: flex;
    justify-content: space-around;
    margin-bottom: 40px;
}

.stat-card {
    background-color: #3B2314; /* Dark color for cards */
    color: #fff; /* Accent color for text */
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    text-align: center;
    flex: 1; /* Equal width cards */
    margin: 0 10px; /* Spacing between cards */
    transition: transform 0.2s; /* Smooth hover effect */
}

.stat-card:hover {
    transform: scale(1.05); /* Slightly enlarge on hover */
}

.stat-card h2 {
    margin: 0;
    font-size: 36px;
}

.stat-card p {
    margin: 10px 0 0;
    font-size: 18px;
}

.chart-container {
    margin-bottom: 40px; /* Spacing between charts */
    padding: 20px;
    background-color: white; /* White background for charts */
    border-radius: 8px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

h3 {
    color: #3B2314; /* Dark title color */
    margin-bottom: 20px;
}

canvas {
    width: 100% !important; /* Responsive */
    height: 400px !important; /* Fixed height */
}

.add-button {
    position: absolute;
    top: 20px;
    right: 20px;
    background-color: #E39825; /* Accent color */
    color: white;
    border: none;
    border-radius: 50%;
    width: 50px;
    height: 50px;
    font-size: 24px;
    cursor: pointer;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
    transition: background-color 0.2s; /* Smooth color change */
}

.add-button:hover {
    background-color: #d98f1f; /* Darker shade on hover */
}

#calendar {
    max-width: 100%; /* Full width */
    margin: 20px auto; /* Centered */
}

#calendar .fc {
    background-color: white; /* Calendar background */
    border-radius: 8px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

/* Media query to hide chart containers on small and medium screens */
@media (max-width: 768px) { /* Adjust the max-width as necessary for your specific breakpoints */
    .chart-container {
        display: none; /* Hides the chart containers */
    }

    .main-container {
        padding: 10px; /* Add padding for smaller screens */
        width: 100%; /* Ensure full width */
    }

    .stats-container {
        flex-direction: column; /* Stack cards vertically */
        align-items: center; /* Center align cards */
    }

    .stat-card {
        width: 80%; /* Full width for cards */
        margin: 10px 0; /* Margin between cards */
    }

    .chart-container {
        margin-bottom: 20px; /* Reduced spacing for smaller screens */
        padding: 10px; /* Less padding for charts */
    }

    canvas {
        height: 300px !important; /* Adjusted height for charts on smaller screens */
    }

    .add-button {
        width: 40px; /* Smaller button */
        height: 40px; /* Smaller button */
        font-size: 20px; /* Smaller font size */
        top: 15px; /* Adjust position */
        right: 15px; /* Adjust position */
    }

    #calendar {
        margin: 10px auto; /* Reduced margin for the calendar */
        max-width: 100%; /* Ensure full width */
    }

    #calendar .fc {
        border-radius: 4px; /* Slightly smaller border radius */
    }
}

    </style>
</head>
<body>
    <!-- <button class="add-button" onclick="location.href='index.php?page=admission/registration'">+</button> -->

    <div class="main-container">
        <!-- Stats Cards Section -->
        <div class="stats-container">
            <div class="stat-card">
                <h2><?php echo $totalStudents; ?></h2>
                <p>Total Students</p>
            </div>
            <div class="stat-card">
                <h2><?php echo $totalCourses; ?></h2>
                <p>Total Courses</p>
            </div>
        </div>

        <!-- Diploma and Certificate Charts -->
        <div class="chart-container">
            <h3>Diploma Courses</h3>
            <canvas id="diplomaChart"></canvas>
        </div>

        <div class="chart-container">
            <h3>Certificate Courses</h3>
            <canvas id="certificateChart"></canvas>
        </div>

        <!-- Event Calendar -->
        <h1>Events</h1>
        <div id="calendar" class="calendar-container"></div>
    </div>



    <script>
        // Diploma Courses Chart
        const diplomaCtx = document.getElementById('diplomaChart').getContext('2d');
        const diplomaCourses = <?php echo json_encode(array_keys($studentCountsForGraph['Diploma'])); ?>;
        const diplomaCounts = <?php echo json_encode(array_values($studentCountsForGraph['Diploma'])); ?>;

        new Chart(diplomaCtx, {
            type: 'bar',
            data: {
                labels: diplomaCourses,
                datasets: [{
                    label: 'Number of Students Enrolled in Diploma Courses',
                    data: diplomaCounts,
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    borderColor: 'rgba(75, 192, 192, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Number of Students'
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Diploma Courses'
                        }
                    }
                }
            }
        });

        // Certificate Courses Chart
        const certificateCtx = document.getElementById('certificateChart').getContext('2d');
        const certificateCourses = <?php echo json_encode(array_keys($studentCountsForGraph['Certificate'])); ?>;
        const certificateCounts = <?php echo json_encode(array_values($studentCountsForGraph['Certificate'])); ?>;

        new Chart(certificateCtx, {
            type: 'bar',
            data: {
                labels: certificateCourses,
                datasets: [{
                    label: 'Number of Students Enrolled in Certificate Courses',
                    data: certificateCounts,
                    backgroundColor: 'rgba(153, 102, 255, 0.2)',
                    borderColor: 'rgba(153, 102, 255, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Number of Students'
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Certificate Courses'
                        }
                    }
                }
            }
        });

        // FullCalendar Initialization
        document.addEventListener('DOMContentLoaded', function() {
            var calendarEl = document.getElementById('calendar');

            var calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                editable: false,
                selectable: false,
                events: function(fetchInfo, successCallback, failureCallback) {
                    fetch('../IKIGAI/admin/communications/load_events.php') // Adjusted path
                        .then(response => response.json())
                        .then(data => {
                            console.log('Fetched events:', data); // Debugging line
                            successCallback(data);
                        })
                        .catch(error => {
                            console.error('Error fetching events:', error); // Debugging line
                            failureCallback(error);
                        });
                },
                eventClick: function(info) {
                    alert('Event: ' + info.event.title + '\nDate: ' + info.event.start.toLocaleDateString());
                }
            });

            calendar.render();
        });
    </script>
</body>
</html>
