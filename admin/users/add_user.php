<form action="index.php?page=users/manage_users" method="POST" class="user-form">
    <h2>Create User</h2>
    
    <label for="username">Username:</label>
    <input type="text" name="username" id="username" required>

    <label for="email">Email:</label>
    <input type="email" name="email" id="email" required>

    <label for="password">Password:</label>
    <input type="password" name="password" id="password" required>

    <label for="role">Role:</label>
    <select name="role" id="role" required>
        <option value="admin">Admin</option>
        <option value="teacher">Teacher</option>
    </select>

    <button type="submit">Create User</button>
</form>

<style>
    .user-form {
        max-width: 400px; /* Set max width for the form */
        margin: 20px auto; /* Center the form */
        padding: 20px;
        border: 1px solid #3B2314; /* Border color */
        border-radius: 5px; /* Rounded corners */
        background-color: #fff; /* Form background */
    }

    .user-form h2 {
        color: #E39825; /* Title color */
        margin-bottom: 20px; /* Space below title */
        text-align: center; /* Center title */
    }

    label {
        display: block; /* Stack labels and inputs */
        margin: 10px 0 5px; /* Space above labels */
        color: #3B2314; /* Label color */
        font-weight: bold; /* Bold labels */
    }

    input[type="text"],
    input[type="email"],
    input[type="password"],
    select {
        width: 80%; /* Full width inputs */
        padding: 10px; /* Padding inside inputs */
        margin-bottom: 15px; /* Space below inputs */
        border: 1px solid #3B2314; /* Border color */
        border-radius: 5px; /* Rounded corners */
        font-size: 14px; /* Font size */
        color: #3B2314; /* Text color */
    }

   .user-form button {
        width: 70%; /* Full width button */
        padding: 10px; /* Padding inside button */
        background-color: #E39825; /* Button background */
        color: white; /* Button text color */
        border: none; /* Remove border */
        border-radius: 5px; /* Rounded corners */
        cursor: pointer; /* Pointer cursor */
        font-size: 16px; /* Font size */
        margin-left: 12px;
    }

    .user-form button:hover {
        background-color: #3B2314; /* Darker color on hover */
    }
</style>
