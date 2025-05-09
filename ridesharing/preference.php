<?php
session_start();
require_once("DBConnect.php");

if (!isset($_SESSION['student_id'])) {
    header("Location: login.php");
    exit();
}

$student_id = $_SESSION['student_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $area = $_POST['area'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $timeslots = implode(",", $_POST['timeslot']); // store as comma-separated string
    $preferred_gender = $_POST['preferred_gender'];
    $stmt = $conn->prepare("INSERT INTO ride_preferences (student_id, area, start_date, end_date, timeslots, preferred_gender)
                            VALUES (?, ?, ?, ?, ?, ?)
                            ON DUPLICATE KEY UPDATE area = VALUES(area), start_date = VALUES(start_date), end_date = VALUES(end_date),
                            timeslots = VALUES(timeslots), preferred_gender = VALUES(preferred_gender)");
    $stmt->bind_param("isssss", $student_id, $area, $start_date, $end_date, $timeslots, $preferred_gender);
    $stmt->execute();

    // Redirect to preference_email.php with parameters
    header("Location: preference_email.php?area=$area&start_date=$start_date&end_date=$end_date&timeslots=$timeslots&preferred_gender=$preferred_gender");
    exit();
    
}

// Fetch current preference
$pref = null;
$stmt = $conn->prepare("SELECT * FROM ride_preferences WHERE student_id = ?");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    $pref = $result->fetch_assoc();
}

// Fetch user info
$stmt = $conn->prepare("SELECT * FROM users WHERE student_id = ?");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Preferences</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/style_preferences.css">
</head>
<body>

<!-- Navigation -->
<nav>
    <div class="nav-left">
        <a href="home.php">Home</a>
        <a href="ride.php">Available Rides</a>
        <a href="profile.php">Profile</a>
        <a href="your_trips.php">Your Trips</a>
        <a href="select_chat.php">Chats</a>
        <a href="wishlist.php">Wishlist</a>
        <a href="preferance.php">Preferances</a>
        <a href="completed_trips.php">Completed Trips</a>
     </div>
    <div class="nav-right">
        <a class="nav-btn" href="comment.php">Feedback</a>
        <button class="user-btn" onclick="toggleUserCard()">👤 <?= htmlspecialchars($user['Name']) ?> ▼</button>
        <div class="user-dropdown" id="userCard">
            <strong>Name:</strong> <?= htmlspecialchars($user['Name']) ?><br>
            <strong>ID:</strong> <?= htmlspecialchars($user['Student_id']) ?><br>
            <strong>Email:</strong> <?= htmlspecialchars($user['Brac_mail']) ?><br>
            <a href="profile.php">Manage Account</a>
            <form method="POST" style="margin-top: 10px;">
                <input type="hidden" name="logout" value="1">
                <button type="submit" style="background: none; border: none; color: red; font-weight: bold;">Logout</button>
            </form>
        </div>
    </div>
</nav>

<div class="container">
    <h2>Set Your Ride Preference</h2>
    <form method="POST">
        <label>Preferred Pickup Area</label>
        <input type="text" name="area" required value="<?= htmlspecialchars($pref['area'] ?? '') ?>">

        <label>Date Range</label>
        <input type="date" name="start_date" required value="<?= htmlspecialchars($pref['start_date'] ?? '') ?>">
        <input type="date" name="end_date" required value="<?= htmlspecialchars($pref['end_date'] ?? '') ?>">

        <label>Preferred Timeslot(s)</label>
        <div class="checkbox-group">
            <?php
            $selected = explode(",", $pref['timeslots'] ?? '');
            foreach (['Morning', 'Afternoon', 'Evening'] as $slot) {
                echo "<label>$slot</label>";
                $isChecked = in_array($slot, $selected) ? "checked" : "";
                echo "<label><input type='checkbox' name='timeslot[]' value='$slot' $isChecked>   </label>";
            }
            ?>
        </div>
        
        <label>Preferred Gender</label>
        <select name="preferred_gender" required>
            <?php
            $selected_gender = $pref['preferred_gender'] ?? 'Any';
            foreach (['Any', 'Male', 'Female'] as $gender) {
                $selected = ($selected_gender === $gender) ? "selected" : "";
                echo "<option value='$gender' $selected>$gender</option>";
            }
            ?>
        </select>
        <button type="submit">Save Preference</button>
    </form>
</div>

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