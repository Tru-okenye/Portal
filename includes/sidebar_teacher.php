<style> /* Basic styles for sidebar */
 .sidebar {
    height: 90%;
    width: 250px; /* Adjust width as needed */
    position: fixed;
  
    left: 0;
    background-color: #cf881d;
    color: #fff;
    overflow-x: hidden;
    overflow-y: auto;
    transition: width 0.3s ease; 
    z-index: 1; /* Lower z-index to keep it behind the header */
}

/* Close the sidebar halfway */
.sidebar.close {
    width: 80px; /* Reduced width when closed to show only icons */
}

/* Hide text when sidebar is closed */
.sidebar.close .dropdown-btn span,
.sidebar.close .dashboard-btn span,
.sidebar.close h2 {
    display: none;
}

/* Hide close icon and show hamburger menu icon when sidebar is closed */
.sidebar.close .toggle-btn i.fa-x {
    display: none;
}

.sidebar.close .toggle-btn i.fa-bars {
    display: block;
}

/* Show close icon and hide hamburger menu icon when sidebar is open */
.sidebar .toggle-btn i.fa-x {
    display: block;
}

.sidebar .toggle-btn i.fa-bars {
    display: none;
}


.toggle-btn {
    position: absolute;
    top: 10px;
    right: 10px; /* Placed at the top right of the sidebar */
    background-color: transparent;
    color: #fff;
    border: none;
    cursor: pointer;
    font-size: 15px;
}

.sidebar .sidebar-content {
    padding: 20px;
}

.sidebar h2 {
    font-size: 20px;
    margin-top: 0;
    color: #3B2314;
}

.sidebar ul {
    list-style: none;
    padding: 0;
}

.sidebar ul li {
    margin: 15px 0;
}
.sidebar ul li button:hover{
    background-color: rgb(79, 33, 33);

}
.sidebar ul li a {
    color: #fff;
    text-decoration: none;
    display: block;
    padding: 10px;
    border-radius: 4px;
}

.sidebar ul li a:hover {
    background-color: rgb(79, 33, 33);
}


.dropdown-menu {
    display: none;
    padding-left: 20px;
}

.dropdown-menu.show {
    display: block;
    margin-left: 16px;
}

/* Custom scrollbar track */
.sidebar::-webkit-scrollbar {
    width: 8px; /* Adjust width for visibility */
}

.sidebar::-webkit-scrollbar-track {
    background: #333; /* Same as sidebar background */
}

/* Custom scrollbar handle */
.sidebar::-webkit-scrollbar-thumb {
    background: #555; /* Adjust color as needed */
    border-radius: 10px; /* Rounded corners */
}

/* Custom scrollbar handle on hover */
.sidebar::-webkit-scrollbar-thumb:hover {
    background: #777; /* Change color on hover */
}

/* For Firefox */
.sidebar {
    scrollbar-width: thin;
    scrollbar-color: #555 #333; /* Handle and track colors */
}

/* Style for dropdown buttons */
.dropdown-btn {
    background: none;
    border: none;
    color: #fff;
    text-align: left;
    width: calc(100% - 10px); /* Adjust to make room for the arrow */
    cursor: pointer;
    padding: 10px;
    font-size: 16px;
    border-radius: 4px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.dashboard-btn {
    background: none;
    border: none;
    color: #fff;
    text-align: left;
    width: calc(80% - 20px); 
    cursor: pointer;
    padding: 10px;
    font-size: 16px;
    border-radius: 4px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.dropdown-btn:hover {
    background-color: #575757;
}

.dropdown-btn i.fa-chevron-right,
.dashboard-btn i.fa-chevron-right {
    transition: transform 0.3s ease;
    font-size: 12px; /* Make the arrow smaller */
}

.dropdown-btn i.fa-chevron-right.fa-rotate-180,
.dashboard-btn i.fa-chevron-right.fa-rotate-180 {
    transform: rotate(90deg); /* Rotate right chevron to point up */
}

/* Hide chevron icons when sidebar is closed */
.sidebar.close .dropdown-btn i.fa-chevron-right,
.sidebar.close .dashboard-btn i.fa-chevron-right {
    display: none;
}




/* Adjust margin when sidebar is closed */
.sidebar.close + .main-content {
    margin-left: 80px; /* Adjust based on reduced sidebar width */
}
</style>
    <!-- Sidebar -->
    <div id="sidebar" class="sidebar">
        <!-- Toggle button for sidebar -->
        <button class="toggle-btn" id="sidebarToggle">
            <i class="fa-solid fa-x"></i>
            <i class="fa-solid fa-bars"></i> 
        </button>
        
        <div class="sidebar-content">
            <h2>Teacher's Menu</h2>
            <ul>
                <!-- Dropdown items -->
                <li class="dropdown">
                    <a href="index.php?page=teacher_dashboard" class="dashboard-link">
                        <button class="dashboard-btn">
                            <i class="fa-solid fa-chart-line"></i>
                            <span>Dashboard</span>
                        </button>
                    </a>
                </li>


                <!-- <li class="dropdown">
                    <button class="dropdown-btn">
                        <i class="fa-solid fa-clipboard-list"></i>
                        <span>Admission</span>
                        <i class="fa-solid fa-chevron-right"></i>
                    </button>
                    <ul class="dropdown-menu">
                        <li><a href="index.php?page=admission/registration">Registration</a></li>
                        <li><a href="index.php?page=admission/confirmation">Students List</a></li>
                    </ul>
                </li> -->

                <li class="dropdown">
                    <button class="dropdown-btn">
                        <i class="fa-solid fa-book"></i>
                        <span>Academics</span>
                        <i class="fa-solid fa-chevron-right"></i>
                    </button>
                    <ul class="dropdown-menu">
                        <li><a href="index.php?page=academics/courses">Courses</a></li>
                        <li><a href="#">Intakes</a></li>
                    </ul>
                </li>

                <li class="dropdown">
                    <button class="dropdown-btn">
                        <i class="fa-solid fa-user-graduate"></i>
                        <span>Manage Students</span>
                        <i class="fa-solid fa-chevron-right"></i>
                    </button>
                    <ul class="dropdown-menu">
                        <li><a href="index.php?page=students/mark_attendance">Class Attendance</a></li>
                        <li><a href="index.php?page=students/generate_pdf">Attendance Form</a></li>
                        <li><a href="index.php?page=students/view_attendance">Attendance Report</a></li>
                    </ul>
                </li>


                <li class="dropdown">
                    <button class="dropdown-btn">
                        <i class="fa-solid fa-file-alt"></i>
                        <span>Examinations</span>
                        <i class="fa-solid fa-chevron-right"></i>
                    </button>
                    <ul class="dropdown-menu">
                        <li><a href="index.php?page=examinations/exam_attendance">Exam attendance</a></li>
                        <li><a href="index.php?page=examinations/view_attendance">View attendance</a></li>
                        <li><a href="index.php?page=examinations/exam_mark">Exam Mark Ledger</a></li>
                        <li><a href="index.php?page=examinations/exam_results">Exam results</a></li>
                        <li><a href="index.php?page=examinations/exam_pass_list">Passlist</a></li>
                        <li><a href="index.php?page=examinations/supp_attendance">Supp attendance</a></li>
                        <li><a href="index.php?page=examinations/view_attendance">SuppAttendanceReport</a></li>
                        <li><a href="index.php?page=examinations/supp_exams">SuppExams</a></li>
                        <!-- <li><a href="index.php?page=examinations/supp_results">SuppResults</a></li> -->
                        <li><a href="index.php?page=examinations/supp_pass_list">Supp&Retake</a></li>
                    </ul>
                </li>
              
          
                <li class="dropdown">
                    <button class="dropdown-btn">
                        <i class="fa-solid fa-calendar-alt"></i>
                        <span>Teacher Routine</span>
                        <i class="fa-solid fa-chevron-right"></i>
                    </button>
                    <ul class="dropdown-menu">
                        <li><a href="#">Placeholder Subpage 1</a></li>
                        <li><a href="#">Placeholder Subpage 2</a></li>
                    </ul>
                </li>
                <li class="dropdown">
                <button class="dropdown-btn">
                    <i class="fa-solid fa-bullhorn"></i>
                    <span>Communications</span>
                    <i class="fa-solid fa-chevron-right"></i>
                </button>
                <ul class="dropdown-menu">
                    <li><a href="index.php?page=communications/events">Calendar</a></li>
                    <li><a href="#">News</a></li>
                </ul>
            </li>    



            </ul>
        </div>
    </div>

    <script>
        // sidebar.js
document.addEventListener('DOMContentLoaded', function() {
    const sidebar = document.getElementById('sidebar');
    const toggleBtn = document.getElementById('sidebarToggle');

    toggleBtn.addEventListener('click', function() {
        sidebar.classList.toggle('close');
    });
});


// JavaScript to handle dropdowns
document.querySelectorAll('.dropdown-btn').forEach(button => {
    button.addEventListener('click', function() {
        // Close all other dropdowns
        document.querySelectorAll('.dropdown-menu').forEach(menu => {
            if (menu !== this.nextElementSibling) {
                menu.classList.remove('show');
                // Rotate the arrowhead icon to point right
                menu.previousElementSibling.querySelector('i.fa-chevron-right').classList.remove('fa-rotate-180');
            }
        });

        // Toggle the clicked dropdown
        const menu = this.nextElementSibling;
        menu.classList.toggle('show');
        // Rotate the arrowhead icon
        this.querySelector('i.fa-chevron-right').classList.toggle('fa-rotate-180');
    });
});

    </script>

