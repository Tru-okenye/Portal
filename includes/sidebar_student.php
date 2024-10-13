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
    z-index: 10; /* Lower z-index to keep it behind the header */
    
}

.main-content {
    position: relative;
    margin-left: 250px; /* Space for the sidebar when open */
    z-index: 1; /* Lower z-index to be behind the sidebar */
    transition: margin-left 0.3s ease;
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
    top: 15px;
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
    color: #fff;
    margin-top: 0;
}
.sidebar-content h2{
    color: rgb(107, 43, 43);
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

/* Highlight the active page */
.active-page {
    background-color: #3B2314; /* Background color for active page */
    color: #fff; /* Ensure text is readable */
}

.active-page:hover {
    background-color: #3B2314; /* Keep the background color the same on hover */
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
    background: #a27634; /* Same as sidebar background */
}

/* Custom scrollbar handle */
.sidebar::-webkit-scrollbar-thumb {
    background: #a27634; /* Adjust color as needed */
    border-radius: 10px; /* Rounded corners */
}

/* Custom scrollbar handle on hover */
.sidebar::-webkit-scrollbar-thumb:hover {
    background: #a27634; /* Change color on hover */
}

/* For Firefox */
.sidebar {
    scrollbar-width: thin;
    scrollbar-color: #a27634; /* Handle and track colors */
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




.sidebar.close + .main-content {
    margin-left: 80px; /* Adjust based on reduced sidebar width */
}

/* Add background color for the active page */
.sidebar ul li.active > a {
    background-color: rgb(79, 33, 33); /* Highlight color for active page */
}

/* Hide dropdown when sidebar is closed */
.sidebar.close .dropdown-menu {
    display: none !important;
}


/* Adjust the sidebar for small and medium screens */
@media screen and (max-width: 1030px) {
    .sidebar {
        position: fixed; /* Sidebar stays above the main content */
        height: 100vh;
        z-index: 10; /* Higher z-index to ensure it's above the main content */
        width: 250px;
    }
    
    /* Sidebar in closed state */
    .sidebar.close {
        width: 60px;
    }
    
    /* Do not adjust main-content margin on small/medium screens */
    .main-content {
        margin-left: 60px !important;
    }
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
            <h2>Student Menu</h2>
            <ul>
  <!-- Dropdown items -->
  <li class="dropdown">
                <button class="dropdown-btn" data-dropdown="dashboard">
                    <i class="fa-solid fa-chart-line"></i>
                    <span>Dashboard</span>
                    <i class="fa-solid fa-chevron-right"></i>
                </button>
                <ul class="dropdown-menu" data-dropdown="dashboard">
                    <li><a href="index.php?page=student_dashboard">Home</a></li>
                </ul>
            </li>


                <li class="dropdown">
                    <button class="dropdown-btn">
                        <i class="fa-solid fa-clipboard-list"></i>
                        <span>Admission</span>
                        <i class="fa-solid fa-chevron-right"></i>
                    </button>
                    <ul class="dropdown-menu" data-dropdown="admission">
                        <li><a href="index.php?page=Enroll/session_reporting">Session Reporting</a></li>
                    </ul>
                </li>

                <li class="dropdown">
                    <button class="dropdown-btn">
                        <i class="fa-solid fa-book"></i>
                        <span>Academic</span>
                        <i class="fa-solid fa-chevron-right"></i>
                    </button>
                    <ul class="dropdown-menu" data-dropdown="academic">
                        <li><a href="index.php?page=Academics/units">Units</a></li>
                        <li><a href="#">Examcard</a></li>
                    <li><a href="index.php?page=Academics/slip_form">ResultsSlips</a></li>

                    </ul>
                </li>

                <li class="dropdown">
                    <button class="dropdown-btn">
                        <i class="fa-solid fa-user-graduate"></i>
                        <span>Financials</span>
                        <i class="fa-solid fa-chevron-right"></i>
                    </button>
                    <ul class="dropdown-menu" data-dropdown="financials">
                        <li><a href="#">Fees Structure</a></li>
                        <li><a href="#">Fees Statement</a></li>
                    </ul>
                </li>


                <li class="dropdown">
                    <button class="dropdown-btn">
                        <i class="fa-solid fa-file-invoice"></i>
                        <span>Transcripts</span>
                        <i class="fa-solid fa-chevron-right"></i>
                    </button>
                    <ul class="dropdown-menu" data-dropdown="transcripts">
                    <li><a href="index.php?page=Transcripts/transcript_form">Transcripts</a></li>


                    </ul>
                </li>
                <li class="dropdown">
                    <button class="dropdown-btn">
                        <i class="fa-solid fa-book-open"></i>
                        <span>Study Materials</span>
                        <i class="fa-solid fa-chevron-right"></i>
                    </button>
                    <ul class="dropdown-menu" data-dropdown="studymaterials">
                        <li><a href="index.php?page=StudyMaterials/reference_materials">Reference Material</a></li>
                    </ul>
                </li>

                <li class="dropdown">
                    <button class="dropdown-btn">
                        <i class="fa-solid fa-bullhorn"></i>
                        <span>Communications</span>
                        <i class="fa-solid fa-chevron-right"></i>
                    </button>
                    <ul class="dropdown-menu" data-dropdown="communications">
                        <li><a href="index.php?page=Communicate/calendar">Calendar</a></li>
                        <li><a href="#">News</a></li> <!-- Added a proper link for the News page -->
                    </ul>
                </li>
  



            </ul>
        </div>
    </div>

    <script>
document.addEventListener('DOMContentLoaded', function() {
    const sidebar = document.getElementById('sidebar');
    const toggleBtn = document.getElementById('sidebarToggle');
    const mainContent = document.querySelector('.main-content');

    // Function to check screen size and adjust sidebar class
    function adjustSidebar() {
        if (window.innerWidth <= 1030) { // Small and medium screens
            sidebar.classList.add('close'); // Add 'close' class
            mainContent.style.marginLeft = '80px'; // Adjust for closed sidebar
        } else {
            sidebar.classList.remove('close'); // Remove 'close' class
            mainContent.style.marginLeft = '250px'; // Adjust for open sidebar
        }
    }

    // Call the function to set initial state
    adjustSidebar();

    // Adjust on window resize
    window.addEventListener('resize', adjustSidebar);

    toggleBtn.addEventListener('click', function() {
        sidebar.classList.toggle('close');

        // Adjust margin based on sidebar state
        if (sidebar.classList.contains('close')) {
            mainContent.style.marginLeft = '80px'; // Adjust for closed sidebar
        } else {
            mainContent.style.marginLeft = '250px'; // Adjust for open sidebar

            // Reopen the dropdown for the active page
            const lastOpenDropdown = localStorage.getItem('openDropdown');
            if (lastOpenDropdown) {
                const dropdownMenu = document.querySelector(`.dropdown-menu[data-dropdown="${lastOpenDropdown}"]`);
                if (dropdownMenu) {
                    dropdownMenu.classList.add('show');
                    dropdownMenu.previousElementSibling.querySelector('i.fa-chevron-right').classList.add('fa-rotate-180');
                }
            }
        }
    });

    // Get the last opened dropdown from localStorage
    const lastOpenDropdown = localStorage.getItem('openDropdown');
    if (lastOpenDropdown) {
        const dropdownMenu = document.querySelector(`.dropdown-menu[data-dropdown="${lastOpenDropdown}"]`);
        if (dropdownMenu) {
            dropdownMenu.classList.add('show');
            dropdownMenu.previousElementSibling.querySelector('i.fa-chevron-right').classList.add('fa-rotate-180');
        }
    }

    // JavaScript to handle dropdowns
    document.querySelectorAll('.dropdown-btn').forEach(button => {
        button.addEventListener('click', function() {
            // Close all other dropdowns
            document.querySelectorAll('.dropdown-menu').forEach(menu => {
                if (menu !== this.nextElementSibling) {
                    menu.classList.remove('show');
                    menu.previousElementSibling.querySelector('i.fa-chevron-right').classList.remove('fa-rotate-180');
                }
            });

            // Toggle the clicked dropdown
            const menu = this.nextElementSibling;
            menu.classList.toggle('show');
            // Rotate the arrowhead icon
            this.querySelector('i.fa-chevron-right').classList.toggle('fa-rotate-180');

            // Store the opened dropdown in localStorage
            if (menu.classList.contains('show')) {
                localStorage.setItem('openDropdown', menu.dataset.dropdown);
            } else {
                localStorage.removeItem('openDropdown');
            }
        });
    });

    // Highlight the active page
    const currentUrl = window.location.href;
    document.querySelectorAll('.dropdown-menu a').forEach(link => {
        if (currentUrl.includes(link.getAttribute('href'))) {
            link.parentElement.classList.add('active');
            link.closest('.dropdown-menu').classList.add('show');
            link.closest('.dropdown-menu').previousElementSibling.querySelector('i.fa-chevron-right').classList.add('fa-rotate-180');
        }
    });
});


    </script>

