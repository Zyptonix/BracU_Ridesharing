<?php
session_start();
require_once("DBconnect.php");

$current_user_id = $_SESSION['student_id'] ?? null;
if (!$current_user_id) {
    die("Please log in first.");
}

// Fetch all users except the current user
$query = "SELECT Student_id, Name, Brac_mail FROM users WHERE Student_id != ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $current_user_id);
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
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Select a User to Chat</title>
    <link rel="stylesheet" href="css/style.css" />
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 30px;
        }

        h2 {
            margin-bottom: 20px;
        }

        .user-box {
            padding: 12px;
            margin-bottom: 12px;
            background: #f4f8fb;
            border-left: 5px solid #007bff;
            border-radius: 8px;
            min-width: 20%;
        }

        .user-box a {
            text-decoration: none;
            font-weight: bold;
            color: #007bff;
        }

        .user-box a:hover {
            text-decoration: underline;
        }
    </style>
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

    <h2>Select a User to Start Chat</h2>

    <?php while ($row = $result->fetch_assoc()): ?>
        <div class="user-box">
            <div><strong><?php echo htmlspecialchars($row['Name']); ?></strong></div>
            <div>Email: <?php echo htmlspecialchars($row['Brac_mail']); ?></div>
            <a href="chat.php?receiver_id=<?php echo $row['Student_id']; ?>">💬 Chat Now</a>
        </div>
    <?php endwhile; ?>

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
