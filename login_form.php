<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            height: 100vh;
            background: url('assets/images/IMG_3857-scaled.jpg') no-repeat center center; /* Center the image */
            background-size: cover; /* Ensure the image covers the whole viewport */
            display: flex;
            justify-content: flex-start; /* Align the form to the left */
            align-items: center; /* Centers the form vertically */
        }
        .background-image {
            position: fixed; /* Fix the background image in place */
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url('assets/images/IMG_3857-scaled.jpg') no-repeat center center;
            background-size: cover; /* Ensure the image covers the whole viewport */
            z-index: -1; /* Places the background behind the form */
        }
        .login-form {
            background-color: #dedfdd;  /* Semi-transparent background */
            height: 100vh;
            width: 50%;
            padding: 5rem 10rem;
            display: flex;
            justify-content: center; /* Aligns the form to the left */
            flex-direction: column;
            align-items: center; /* Vertically centers the form */
        }
        .login-form img {
            width: 250px; /* Adjust the width as needed */
            height: auto; /* Ensure the height adjusts automatically */
            margin-bottom: 15px; /* Add space below the logo */
            margin-top: 50px;
        }


        .login-container {
            width: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            background-color: white;
            border-radius: 5px;
            padding: 15px;
            margin-top: 4rem;
        }

        h2 {
            margin-bottom: 20px;
            font-size: 1.5rem;
        }
        label {
            margin-bottom: 5px;
            font-weight: bold;
        }
        input[type="text"],
        input[type="password"] {
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 14px;
        }
        input[type="submit"] {
            background-color: #E39825; /* Button color */
            color: white;
            border: none;
            border-radius: 5px;
            padding: 10px;
            cursor: pointer;
            font-size: 16px;
            width: 10rem;
        }
        input[type="submit"]:hover {
            background-color: #3B2314; /* Button hover color */
        }
        p {
            margin-top: 10px;
            text-align: center;
        }
        a {
            color: #E39825; /* Link color */
            text-decoration: none;
        }
        a:hover {
            text-decoration: underline; /* Underline on hover */
        }


         /* Media query for very small screens (phones) */
         @media (max-width: 680px) {
            .login-form {
                padding: 1rem 5rem;
                width: 100%;
                
            }

           
            .login-container {
            width: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            background-color: white;
            border-radius: 5px;
            padding: 10px;
            margin-top: 4rem;
        }
          
        }


          /* Media query for tablets and smaller screens */
          @media (max-width: 1024px) {
            .login-form {
                padding: 3rem 5rem;
                width: 70%; /* Adjust form width */
            }
        }

        /* Media query for small devices (phones) */
        @media (max-width: 680px) {
            body {
                background: none; /* Hide background image on small screens */
                background-color: #f0f0f0; /* Optional: Add a solid background color */
            }
            .login-form {
                padding: 2rem 3rem;
                width: 90%; /* Full width on smaller screens */
            }

            .login-form img {
                width: 150px; /* Smaller logo for mobile */
            }

            input[type="submit"] {
                width: 100%; /* Button takes full width on smaller screens */
            }
        }

        /* Media query for very small screens */
        @media (max-width: 480px) {

            body {
                background: none; /* Hide background image on small screens */
                background-color: #f0f0f0; /* Optional: Add a solid background color */
            }
            .login-form {
                padding: 1rem 2rem;
                width: 100%; /* Almost full width */
            }

            h2 {
                font-size: 1.2rem; /* Reduce heading size */
            }
           

        }
    </style>
</head>
<body>
    <div class="background-image"></div> <!-- Background Image with Blur and Brightness -->
  
    <div class="container">
        <form class="login-form" method="POST" action="login.php">
            <!-- Logo above the heading -->
          
            <img src="assets/images/ikigai-college-logo-1.png" alt="Logo">
            <div class="login-container">
                <h2></h2>
                <h2>Fill in the form to login</h2>
                
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" required>
                
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>
                
                <input type="submit" name="login" value="Login">
                
                <p><a href="forgot_password.php">Forgot Password?</a></p>

            </div>
        </form>
    </div>

</body>
</html>
