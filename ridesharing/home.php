<?php
session_start();
require_once("DBconnect.php"); // your DB connection

// Handle logout request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['logout'])) {
    session_destroy();
    header("Location: index.php");
    exit();
}

// Redirect if not logged in
if (!isset($_SESSION['student_id'])) {
    header("Location: index.php");
    exit();
}

// Fetch user info
$user_id = $_SESSION['student_id'];
$query = "SELECT * FROM users WHERE student_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>BRACU RIDESHARING WEBSITE</title>
  <link rel="stylesheet" href="css/style.css" />
  <link rel="stylesheet" href="css/style_home.css" />
  
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
    <a href="added_preferences.php">Preferences</a>
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

<!-- Hero Section -->
<section class="hero">
    <div class="container">
    <h1>BRACU RIDESHARING WEBSITE</h1>
    <p>Many BRAC University students face daily transportation challenges. This platform connects students to share rides, promoting safety, community, and convenience.</p>
    </div>
</section>

<!-- CTA Buttons -->
<section class="cta-buttons">
  <a class="card" href="ride.php">
    <h2>Available Rides</h2>
    <p>Create or apply for a ride card to travel with fellow students</p>
  </a>
  <a class="card" href="profile.php">
    <h2>Profile</h2>
    <p>Verify and edit your account details and personal information</p>
  </a>
  <a class="card" href="select_chat.php">
    <h2>Chats</h2>
    <p>Chat with other students about the ride details</p>
  </a>
  <a class="card" href="your_trips.php">
    <h2>Your Trips</h2>
    <p>Details about all your trips and rides</p>
  </a>
</section>

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