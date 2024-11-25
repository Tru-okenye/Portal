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
            display: flex;
            justify-content: center;
            align-items: center;
            background-color: #f4f4f4; /* Light background color for the entire page */
        }

        .large-container {
            display: grid;
            grid-template-columns: 1fr 1fr; /* Two equal columns */
            width: 100%;
            height: 100vh;
        }

        .img-container {
            background: url('assets/images/WhatsApp Image 2024-10-13 at 11.38.16.jpeg') no-repeat center center;
            background-size: cover;
            height: 100vh;
        }

       
        

        .login-form {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            background-color: #dedfdd;
            height: 100%;
            padding: 2rem;
            
        }

        .login-form img {
    width: 250px;
    height: auto;
    margin-bottom: 15px;
    position: absolute;
    top: 20px; /* Position the image at the top */
}

        .login-container {
            /* width: 100%;
            text-align: center; */
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            background-color: white;
            border-radius: 5px;
            padding: 35px;



        }

        h2 {
            margin-bottom: 20px;
            font-size: 1.5rem;
        }

        label {
    display: block;
    margin-bottom: 5px;
    font-weight: bold;
    text-align: left; /* Align labels to the start */
    width: 100%; /* Ensure labels take up full width */
    margin-left:50px;
}


        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 14px;
            
        }

        input[type="submit"] {
            background-color: #E39825;
            color: white;
            border: none;
            border-radius: 5px;
            padding: 10px;
            cursor: pointer;
            font-size: 16px;
            width: 100%;
        }

        input[type="submit"]:hover {
            background-color: #3B2314;
        }

        p {
            margin-top: 10px;
        }

        a {
            color: #E39825;
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
        }
.login-container h2{
    color: #E39825;
    position: relative;
    /* top: 5px; */
}
         /* Responsive Design */
         @media (max-width: 650px) {
            .large-container {
               display: flex;
               position: fixed;
            }

            .img-container {
                display: none; /* Adjust height for small screens */
            }

            input[type="text"],
        input[type="password"] {
            width: 60%;
           
        }

        input[type="submit"] {
          
            width: 50%;
        }

            .login-form {
                width: 100%;
                padding: 2rem;
                display: flex;
                justify-content: center;
                align-items: center;
                height: 100vh;
                /* background-color: red; */
            }

            .login-container {
                padding: 15px;
                width: 100%;
                height: 60vh;
                margin-top: 5px;
            }

            .login-form img {

        }
        }

        footer {
    background-color: #3B2314; /* Dark color for the footer */
    color: white;
    text-align: center;
    padding: 10px;
    position: fixed; /* Fixes the footer at the bottom */
    bottom: 0; /* Positions it at the bottom */
    left: 0; /* Ensures it spans the full width */
    width: 100%; /* Full width */
    font-size: 0.9rem;
    z-index: 1000; /* Keeps it above other content */
}

footer p {
    margin: 0;
}


    </style>
</head>
<body>
    <div class="large-container">
        <div class="img-container"></div>
     
            <form class="login-form" method="POST" action="login.php">
                <img src="assets/images/ikigai-college-logo-1.png" alt="Logo">
                <div class="login-container">
                    <h2>Fill in the form to login</h2>

                    <label for="username">Username:</label>
                    <input type="text" id="username" name="username" required>

                    <label for="password">Password:</label>
                    <input type="password" id="password" name="password" required>

                    <input type="submit" name="login" value="Login">

                    <p><a href="https://ikigaicollege.ac.ke/Portal/forgot_password.php">Forgot Password?</a></p>
                </div>
            </form>
      
    </div>
    <footer>
    <p>&copy; 2024 Ikigai College. All rights reserved.</p>
</footer>
</body>
</html>
