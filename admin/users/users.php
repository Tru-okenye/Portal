<?php
// Include database connection
include_once __DIR__ . '/../../config/config.php';

// Fetch all users
$users = [];
$sql = "SELECT username, email, role FROM users";
if ($stmt = $conn->prepare($sql)) {
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $users[] = $row; // Store each user in the array
    }
    $stmt->close();
}

// Close the database connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User List</title>
    <style>
        h1 {
            color: #3B2314;
            margin-bottom: 20px;
        }

        .add-user-btn {
            padding: 10px 15px;
            background-color: #E39825; /* Button background */
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin-bottom: 20px;
            display: inline-block;
            float: right; /* Position button to the right */
        }

        .add-user-btn:hover {
            background-color: #3B2314; /* Darker color on hover */
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background-color: #fff; /* Table background */
        }

        th, td {
            padding: 12px;
            text-align: left;
            border: 1px solid #3B2314; /* Border color */
        }

        th {
            background-color: #E39825; /* Header background */
            color: white; /* Header text color */
        }

        tr:nth-child(even) {
            background-color: #f2f2f2; /* Zebra stripes for rows */
        }

        a {
            color: #E39825; /* Link color */
            text-decoration: none; /* Remove underline */
        }

        a:hover {
            text-decoration: none; /* No underline on hover */
            font-weight: bold; /* Optional: make text bold on hover */
        }

        .action-links {
            display: flex;
            gap: 5px; /* Space between links */
        }
    </style>
</head>
<body>

<h1>User List</h1>
<a href="index.php?page=users/add_user" class="add-user-btn">Add User</a>

<?php if (empty($users)): ?>
    <p>No users found.</p>
<?php else: ?>
    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Role</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $user): ?>
                <tr>
                    <td><?php echo htmlspecialchars($user['username']); ?></td>
                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                    <td><?php echo htmlspecialchars($user['role']); ?></td>
                    <td class="action-links">
                        <!-- Edit action -->
                        <a href="index.php?page=users/edit_user&username=<?php echo urlencode($user['username']); ?>">Edit</a>
                        <!-- Delete action -->
                        <a href="index.php?page=users/delete_user&username=<?php echo urlencode($user['username']); ?>" onclick="return confirm('Are you sure you want to delete this user?');">Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

</body>
</html>
