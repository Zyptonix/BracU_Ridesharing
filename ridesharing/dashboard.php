<?php
require_once('DBConnect.php'); // gives you $conn
session_start();
unset($_SESSION['selected_user_id']);

// Handle view/edit user by setting selected_user_id in session
if (isset($_GET['action']) && in_array($_GET['action'], ['view', 'edit']) && isset($_GET['id'])) {
    $_SESSION['selected_user_id'] = intval($_GET['id']);
    $redirect = $_GET['action'] === 'edit' ? 'edit_profile.php' : 'profile.php';
    header("Location: $redirect");
    exit;
}


// Search feature if a query is passed
$search_query = '';
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search_query = $conn->real_escape_string($_GET['search']);
    $sql = "SELECT Student_id, Name, Brac_mail, Phone_number, Location, Semester FROM users WHERE Name LIKE '%$search_query%' OR Brac_mail LIKE '%$search_query%' OR Student_id LIKE '%$search_query%'";
} else {
    $sql = "SELECT Student_id, Name, Brac_mail, Phone_number, Location, Semester FROM users";
}

$result = $conn->query($sql);


// Handle user deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $delete_id = intval($_POST['delete_id']);

    // Prevent deleting your own account
    if ($delete_id !== intval($_SESSION['student_id'])) {
        $delete_stmt = $conn->prepare("DELETE FROM users WHERE Student_id = ?");
        $delete_stmt->bind_param("i", $delete_id);
        $delete_stmt->execute();
    }
    // Redirect to avoid form resubmission
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Dashboard</title>
    <link rel="stylesheet" href="css/style.css" />
    <style>
        .view-btn, .edit-btn, .delete-btn {
            color: white;
            background-color: #007BFF;
            border: none;
            padding: 5px 10px;
            text-decoration: none;
            border-radius: 4px;
            margin-right: 5px;
            cursor: pointer;
        }

        .delete-btn {
            background-color: #dc3545; /* red */
        }
    </style>
</head>
<body>

<h1>Registered Users Dashboard</h1>

<!-- Search Bar -->
<div class="search-bar">
    <form method="GET">
        <input type="text" name="search" placeholder="Search by Name, ID or Email..." value="<?php echo htmlspecialchars($search_query); ?>">
    </form>
</div>

<?php if ($result && $result->num_rows > 0): ?>
<table>
    <thead>
        <tr>
            <th>Student ID</th>
            <th>Name</th>
            <th>BRAC Email</th>
            <th>Phone Number</th>
            <th>Location</th>
            <th>Semester</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php while($row = $result->fetch_assoc()): ?>
        <tr>
            <td><?php echo htmlspecialchars($row['Student_id']); ?></td>
            <td><?php echo htmlspecialchars($row['Name']); ?></td>
            <td><?php echo htmlspecialchars($row['Brac_mail']); ?></td>
            <td><?php echo htmlspecialchars($row['Phone_number']); ?></td>
            <td><?php echo htmlspecialchars($row['Location']); ?></td>
            <td><?php echo htmlspecialchars($row['Semester']); ?></td>
            <td class="action-buttons">
            <a href="dashboard.php?action=view&id=<?php echo $row['Student_id']; ?>" class="view-btn">View</a>
            <a href="dashboard.php?action=edit&id=<?php echo $row['Student_id']; ?>" class="edit-btn">Edit</a>

                <form method="POST" onsubmit="return confirm('Are you sure you want to delete this user?');" style="display:inline;">
                    <input type="hidden" name="delete_id" value="<?php echo $row['Student_id']; ?>">
                    <button type="submit" class="delete-btn" >
                        Delete
                    </button>
                </form>
            </td>
        </tr>
        <?php endwhile; ?>
    </tbody>
</table>
<?php else: ?>
    <p class="no-users">🚫 No registered users found.</p>
<?php endif; ?>


</body>
</html>
