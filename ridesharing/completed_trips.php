<?php
session_start();
require_once('DBConnect.php');

if (!isset($_SESSION['student_id'])) {
    die("Please log in first.");
}

$logged_in_student_id = $_SESSION['student_id'];

// Fetch all completed trips
$sql = "
    SELECT 
        t.Card_no, 
        t.Student_id AS trip_creator_id, 
        rc.Pickup_time, 
        rc.Pickup_Date, 
        rc.Pickup_Area,
        rc.Gender,
        rc.Semester,
        CASE 
            WHEN t.Student_id = ? THEN 'Creator'
            ELSE 'Passenger'
        END AS role
    FROM trips t
    JOIN ride_cards rc ON t.Card_no = rc.Card_no
    LEFT JOIN selected_passengers sp ON t.Card_no = sp.Card_no
    WHERE t.Is_completed = 1
      AND (t.Student_id = ? OR sp.Passenger_Student_id = ?)
    GROUP BY t.Card_no
    ORDER BY rc.Pickup_Date DESC, rc.Pickup_time DESC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("iii", $logged_in_student_id, $logged_in_student_id, $logged_in_student_id);
$stmt->execute();
$result = $stmt->get_result();



// Fetch user info for nav bar
$user_id = $_SESSION['student_id'];
$nav_query = "SELECT * FROM users WHERE student_id = ?";
$nav_stmt = $conn->prepare($nav_query);
$nav_stmt->bind_param("i", $user_id);
$nav_stmt->execute();
$nav_result = $nav_stmt->get_result();
$nav_user = $nav_result->fetch_assoc();

// Handle logout request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['logout'])) {
    session_destroy();
    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Completed Trips</title>
    <link rel="stylesheet" href="css/style.css" />
    <style>
       <style>
    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        padding: 20px;
        background-color: #f9f9f9;
        color: #333;
    }

    h1 {
        color: #007BFF;
        margin-bottom: 20px;
    }


    .user-dropdown {
        display: none;
        position: absolute;
        right: 20px;
        top: 60px;
        background-color: #fff;
        border: 1px solid #ddd;
        border-radius: 8px;
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        padding: 15px;
        min-width: 200px;
        z-index: 999;
    }

    .user-dropdown a {
        display: block;
        margin-top: 10px;
        color: #007BFF;
        font-weight: bold;
    }

    table {
        width: 100%;
        max-width: 80%;
        border-collapse: collapse;
        background-color: #fff;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
    }

    th, td {
        padding: 12px;
        text-align: center;
        border-bottom: 1px solid #eee;
    }

    th {
        background-color:#007BFF;
        font-weight: bold;
        color: white;
    }

    tr:hover {
        background-color: #f1f9ff;
    }

    .creator {
        color: darkblue;
        font-weight: bold;
    }

    a.back-link {
        display: inline-block;
        margin-top: 30px;
        text-decoration: none;
        color: #007BFF;
        font-weight: bold;
    }
</style>

    </style>
</head>
<body>

   
    <nav>
    <div class="nav-left">
        <a href="home.php">Home</a>
        <a href="ride.php">Available Rides</a>
        <a href="profile.php">Profile</a>
        <a href="your_trips.php">Your Trips</a>
        <a href="select_chat.php">Chats</a>
        <a href="added_preferences.php">Preferences</a>
        <a href="completed_trips.php">Completed Trips</a>
    </div>
    <div class="nav-right">
        <a class="nav-btn" href="comment.php">Feedback</a>
        <button class="user-btn" onclick="toggleUserCard()">
        👤 <?php echo htmlspecialchars($nav_user['Name']); ?> ▼
        </button>
        <div class="user-dropdown" id="userCard">
            <strong>Name:</strong> <?php echo htmlspecialchars($nav_user['Name']); ?><br>
            <strong>ID:</strong> <?php echo htmlspecialchars($nav_user['Student_id']); ?><br>
            <strong>Email:</strong> <?php echo htmlspecialchars($nav_user['Brac_mail']); ?><br>
        <a href="profile.php">Manage Account</a>
        <form method="POST" style="margin-top: 10px;">
            <input type="hidden" name="logout" value="1">
            <button type="submit" style="background: none; border: none; color: red; font-weight: bold; cursor: pointer;text-align:left;">
                Logout
            </button>
        </form>
        </div>
    </div>
    </nav>
    
    <h1>✅ Completed Trips</h1>
    <table>
        <thead>
            <tr>
                <th>Pickup Area</th>
                <th>Date</th>
                <th>Time</th>
                <th>Gender</th>
                <th>Semester</th>
                <th>Your Role</th>
            </tr>
        </thead>
        <tbody>
        <?php if ($result && $result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['Pickup_Area']) ?></td>
                    <td><?= htmlspecialchars($row['Pickup_Date']) ?></td>
                    <td><?= htmlspecialchars($row['Pickup_time']) ?></td>
                    <td><?= htmlspecialchars($row['Gender']) ?></td>
                    <td><?= htmlspecialchars($row['Semester']) ?></td>
                    <td class="creator"><?= $row['role'] ?></td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="6">No completed trips found.</td>
            </tr>
        <?php endif; ?>
        </tbody>
    </table>

    <a href="your_trips.php" class="back-link">← Go to Current Trips</a>

    <script>
    function toggleUserCard() {
        const card = document.getElementById("userCard");
        card.style.display = card.style.display === "block" ? "none" : "block";
    }

    window.addEventListener('click', function (e) {
        if (!e.target.closest('.user-btn') && !e.target.closest('#userCard')) {
        document.getElementById("userCard").style.display = "none";
        }
    });
    </script>

</body>
</html>
