<?php
include 'config/config.php';

// Fetch total number of students
$totalStudentsSql = "SELECT COUNT(*) AS TotalStudents FROM students WHERE AdmissionNumber IS NOT NULL";
$totalStudentsResult = $conn->query($totalStudentsSql);
$totalStudents = $totalStudentsResult->fetch_assoc()['TotalStudents'];

// Fetch total number of courses
$totalCoursesSql = "SELECT COUNT(*) AS TotalCourses FROM courses";
$totalCoursesResult = $conn->query($totalCoursesSql);
$totalCourses = $totalCoursesResult->fetch_assoc()['TotalCourses'];

// Fetch courses along with their categories
$courseDataSql = "
    SELECT c.CourseName, ct.CategoryName
    FROM courses c
    JOIN categories ct ON c.CategoryID = ct.CategoryID
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
            FROM students 
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
    <link rel="stylesheet" href="https://ikigaicollege.ac.ke/Portal/assets/css/calendar.css"> 
    <link rel="stylesheet" href="https://ikigaicollege.ac.ke/Portal/assets/css/dashboard.css"> 
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>

</head>
<body>
    <button class="add-button" onclick="location.href='index.php?page=admission/registration'">+</button>

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
                    fetch('admin/communications/load_events.php') // Adjusted path
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
