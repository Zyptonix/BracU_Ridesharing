<?php
session_start();
include('DBconnect.php');

if (!isset($_SESSION['student_id']) || $_SESSION['P_flag'] != 1) {
    die("Access denied. Only passengers can view wishlist.");
}

$passenger_id = $_SESSION['student_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_card_no'])) {
    $remove_card_no = intval($_POST['remove_card_no']);
    $stmt = $conn->prepare("DELETE FROM wishlist WHERE Passenger_id = ? AND Card_no = ?");
    $stmt->bind_param("ii", $passenger_id, $remove_card_no);
    $stmt->execute();
}

$stmt = $conn->prepare("
    SELECT r.* FROM ride_cards r
    JOIN wishlist w ON r.Card_no = w.Card_no
    WHERE w.Passenger_id = ?
");
$stmt->bind_param("i", $passenger_id);
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
    <meta charset="UTF-8">
    <title>Your Wishlist</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/style_wishlist.css">
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


<div class="wishlist-container">
    <h1>Your Wishlisted Rides</h1>
    <?php if ($result->num_rows > 0): ?>
        <div class="ride-box-container">
        <?php while ($ride = $result->fetch_assoc()): ?>
            <div class="ride-box">
                <h3><strong>Pickup Area:</strong> <?= htmlspecialchars($ride['Pickup_Area']) ?></h3>
                <p><strong>Pickup Time:</strong> <?= date("g:i A", strtotime($ride['Pickup_time'])) ?></p>
                <p><strong>Timeslot:</strong> <?= htmlspecialchars($ride['Timeslot']) ?></p>
                <p><strong>Seats Available:</strong> <?= htmlspecialchars($ride['Number_of_empty_seats']) ?></p>
                <p><strong>Pickup Date:</strong> <?= htmlspecialchars($ride['Pickup_date']) ?></p>
                <form method="post">
                    <input type="hidden" name="remove_card_no" value="<?= $ride['Card_no'] ?>">
                    <button type="submit">❌ Remove</button>
                </form>
                <form action="ride_cards.php" method="get" style="margin-top: 10px;">
                    <input type="hidden" name="card_no" value="<?= $ride['Card_no'] ?>">
                    <button type="submit" class="view-button">🔍 View Details</button>
                </form>
            </div>
        <?php endwhile; ?>
        </div>
    <?php else: ?>
        <p>No rides wishlisted yet.</p>
    <?php endif; ?>
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
