<?php
session_start();
require_once('DBConnect.php');

if (!isset($_SESSION['student_id'])) {
    die("Please log in first.");
}

$student_id = $_SESSION['student_id'];

$query = "
SELECT rp.*, 
    EXISTS (
        SELECT 1 FROM ride_cards rc
        JOIN users u ON rc.Student_id = u.Student_id
        WHERE rc.Pickup_Area = rp.area
        AND rc.Pickup_date BETWEEN rp.start_date AND rp.end_date
        AND FIND_IN_SET(rc.Timeslot, rp.timeslots)
        AND (rp.preferred_gender = 'Any' OR u.Gender = rp.preferred_gender)
    ) AS match_found
FROM ride_preferences rp
WHERE rp.student_id = ?";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();


// Fetch user info
$user_id = $_SESSION['student_id'];
$query = "SELECT * FROM users WHERE student_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result1 = $stmt->get_result();
$user = $result1->fetch_assoc();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Your Ride Preferences</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/style_home.css">
    <link rel="stylesheet" href="css/style_added_pref.css">
</head>
<body>

    <nav>
    <div class="nav-left">
        <a href="home.php">Home</a>
        <a href="ride.php">Available Rides</a>
        <a href="profile.php">Profile</a>
        <a href="your_trips.php">Your Trips</a>
        <a href="select_chat.php">Chats</a>
        <a href="wishlist.php">Wishlist</a>
        <a href="added_preferences.php">Preferances</a>
        <a href="completed_trips.php">Completed Trips</a>
        
    </div>
    <div class="nav-right">
        <a class="nav-btn" href="comment.php">Feedback</a>
        <button class="user-btn" onclick="toggleUserCard()">
        👤 <?php echo htmlspecialchars($user['Name']); ?> ▼
        </button>
        <div class="user-dropdown" id="userCard">
            <strong>Name:</strong> <?php echo htmlspecialchars($user['Name']); ?><br>
            <strong>ID:</strong> <?php echo htmlspecialchars($user['Student_id']); ?><br>
            <strong>Email:</strong> <?php echo htmlspecialchars($user['Brac_mail']); ?><br>
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

    <div class="preference-container">
        <h1>Your Ride Preferences:</h1>

        <?php if ($result->num_rows === 0): ?>
            <p>No preferences added yet.</p>
        <?php else: ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <div class="preference-box">
                    <h3>Preference #<?= htmlspecialchars($row['preference_id']) ?></h3>
                    <p><strong>Area:</strong> <?= htmlspecialchars($row['area']) ?></p>
                    <p><strong>Date Range:</strong> <?= htmlspecialchars($row['start_date']) ?> to <?= htmlspecialchars($row['end_date']) ?></p>
                    <p><strong>Timeslots:</strong> <?= htmlspecialchars($row['timeslots']) ?></p>
                    <p><strong>Preferred Gender:</strong> <?= htmlspecialchars($row['preferred_gender']) ?></p>

                    <?php if ($row['match_found']): ?>
                        <div class="match-message">✔ Match Found!</div>
                    <?php endif; ?>
                </div>
            <?php endwhile; ?>
        <?php endif; ?>

        <!-- Move the button below the preferences -->
        <div class="add-preference-wrapper">
            <a href="preference.php" class="add-preference-btn">➕ Add New Preference</a>
        </div>
    </div>
</body>
</html>
