<header class="header">
<style>
    .header {
        display: flex;
        align-items: center;
        justify-content: space-between; /* Space between logo, school name, and profile icon */
        padding: 10px 20px;
        background-color: white; /* Header background */
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        position: fixed;
        top: 0; 
        left: 0; 
        right: 0; 
        z-index: 1000;
    }

    .logo img {
        height: 50px; /* Adjust logo size */
    }

    .school-name {
        flex: 1;
        text-align: center;
        font-size: 24px; /* Adjust font size */
        font-weight: bold;
        color: #3B2314; /* Dark text color for school name */
        transition: opacity 0.3s ease; /* Smooth transition for visibility */
    }

    .profile-icon {
        position: relative;
        cursor: pointer;
        display: flex;
        flex-direction: column; /* Stack icon and username vertically */
        align-items: center; /* Center align the profile icon and username */
        color: #E39825; /* Yellow color for the profile icon */
        font-size: 24px; /* Adjust icon size */
    }

    .profile-dropdown {
        display: none; /* Hidden by default */
        position: absolute;
        top: 40px; /* Adjust based on your layout */
        right: 0;
        background-color: #fff;
        border: 1px solid #ccc;
        border-radius: 8px; /* Slightly round the corners */
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        z-index: 100;
        padding: 5px 10px; /* Adjust padding for a more compact look */
        min-width: 150px; /* Minimum width for consistency */
        transition: opacity 0.2s ease; /* Smooth transition */
        opacity: 0; /* Start as invisible */
        pointer-events: none; /* Prevent interactions when invisible */
    }

    .profile-icon:hover .profile-dropdown {
        display: block; /* Show the dropdown when profile icon is hovered */
        opacity: 1; /* Make it visible */
        pointer-events: auto; /* Allow interactions */
    }

    .profile-dropdown ul {
        list-style-type: none;
        margin: 0;
        padding: 0;
    }

    .profile-dropdown ul li {
        padding: 8px 12px; /* Slightly smaller padding for items */
        border-bottom: 1px solid #eee;
        transition: background-color 0.2s; /* Smooth background transition */
    }

    .profile-dropdown ul li:last-child {
        border-bottom: none;
    }

    .profile-dropdown ul li a {
        text-decoration: none;
        color: #E39825;
        display: block;
        font-size: 18px;
    }

    .profile-dropdown ul li a:hover {
        background-color: #f1f1f1; /* Change background on hover */
        border-radius: 4px; /* Slightly round the hover effect */
    }

    body {
        padding-top: 70px; /* Adjust padding to prevent content from hiding behind the fixed header */
    }

    /* Responsive styles for medium and small screens */
    @media (max-width: 768px) {
        .school-name {
            font-size: 16px; /* Adjust font size for medium screens */
        }

        .profile-icon {
            font-size: 20px; /* Adjust icon size */
        }
    }

    @media (max-width: 600px) {
        .school-name {
            display: none; /* Hide school name on small screens */
        }

        .logo img {
            height: 40px; /* Smaller logo for small screens */
        }

        .header {
            padding: 10px; /* Reduce padding for small screens */
        }

        .profile-icon {
            font-size: 18px; /* Adjust icon size for small screens */
        }
    }
</style>


    <div class="logo">
        <img src="assets/images/ikigai-college-logo-1.png" alt="Ikigai College Logo">
    </div>
    
    <div class="school-name">
        Ikigai College of Interior Design
    </div>
    
    <div class="profile-icon">
        <i class="fa-solid fa-user"></i>
        <div class="username">
            <?php 
                // Display the logged-in username from the session
                if (isset($_SESSION['admin_username'])) {
                    echo htmlspecialchars($_SESSION['admin_username']); // Output the username securely for admin
                } elseif (isset($_SESSION['student_admission_number'])) {
                    echo htmlspecialchars($_SESSION['student_admission_number']); // Output the admission number securely for student
                } else {
                    echo "Guest"; // Default to "Guest" if no user is logged in
                }
            ?>
        </div>
        <!-- Dropdown for Profile Menu -->
        <div class="profile-dropdown">
            <ul>
                <!-- <li><a href="profile.php">Profile</a></li>  -->
                <li><a href="logout.php">Log Out</a></li> <!-- Logout link -->
            </ul>
        </div>
    </div>
</header>
