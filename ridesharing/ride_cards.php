<?php
session_start();
require_once('DBConnect.php');

// Check login
if (!isset($_SESSION['student_id'])) {
    die("Please login first.");
}

if (!isset($_GET['card_no'])) {
    die("No ride selected.");
}

$ride_card_no = $_GET['card_no'];
$passenger_id = $_SESSION['student_id'];
$already_applied = false;

// Fetch ride details
$sql = "SELECT r.* FROM ride_cards r LEFT JOIN users u ON r.Student_id = u.Student_id WHERE r.Card_no = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $ride_card_no);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    die("Ride not found.");
}
$ride = $result->fetch_assoc();

// Fetch passenger details
$passenger_sql = "SELECT Gender, Semester FROM users WHERE Student_id=?";
$passenger_stmt = $conn->prepare($passenger_sql);
$passenger_stmt->bind_param("i", $passenger_id);
$passenger_stmt->execute();
$passenger_result = $passenger_stmt->get_result();

if ($passenger_result && $passenger_result->num_rows > 0) {
    $passenger = $passenger_result->fetch_assoc();
} else {
    die("Passenger details not found.");
}

// Fetch user info
$query = "SELECT * FROM users WHERE student_id = ?";
$user_stmt = $conn->prepare($query);
$user_stmt->bind_param("i", $passenger_id);
$user_stmt->execute();
$user_result = $user_stmt->get_result();
$user = $user_result->fetch_assoc();

// Check if already applied
$check_stmt = $conn->prepare("SELECT 1 FROM applies_for WHERE Passenger_student_id = ? AND Card_no = ? AND Provider_student_id = ?");
$check_stmt->bind_param("iii", $passenger_id, $ride_card_no, $ride['Student_id']);
$check_stmt->execute();
$check_result = $check_stmt->get_result();
if ($check_result->num_rows > 0) {
    $already_applied = true;
}

// Handle application
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['apply']) && !$already_applied) {
    $apply_stmt = $conn->prepare("INSERT INTO applies_for (Passenger_student_id, Card_no, Provider_student_id) VALUES (?, ?, ?)");
    $apply_stmt->bind_param("iii", $passenger_id, $ride_card_no, $ride['Student_id']);
    if ($apply_stmt->execute()) {
        echo "<script>alert('✅ Applied Successfully!'); window.location.href='ride_cards.php?card_no=" . urlencode($ride_card_no) . "';</script>";
        exit;
    } else {
        echo "<script>alert('❌ Error applying: " . $conn->error . "');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Ride Details</title>
    <link rel="stylesheet" href="css/style.css" />
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
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

<div class="container">
    <h1>Ride Details</h1>

    <div class="info"><strong>Pickup Area:</strong> <?php echo htmlspecialchars($ride['Pickup_Area']); ?></div>
    <br>
    <div class="info">
        <?php
        $pickup_time_12 = date("g:i A", strtotime($ride['Pickup_time']));
        echo "<p><strong>Pickup Time:</strong> $pickup_time_12</p>";
        ?>
    </div>
    <br>
    <div class="info"><strong>Timeslot:</strong> <?php echo htmlspecialchars($ride['Timeslot']); ?></div>
    <br>
    <div class="info"><strong>Number of Empty Seats:</strong> <?php echo htmlspecialchars($ride['Number_of_empty_seats']); ?></div>
    <br>
    <div class="info"><strong>Gender:</strong> <?php echo htmlspecialchars($ride['Gender']); ?></div>
    <br>
    <div class="info"><strong>Maximum Semester:</strong> <?php echo htmlspecialchars($ride['Semester']); ?></div>
    <br>

    <div class="info-container">
        <div>
            <?php
            $error_message = "";

            if ($_SESSION['P_flag'] == 1 && $_SESSION['student_id'] != $ride['Student_id']) {
                if ($ride['Gender'] != 'Both' && $passenger['Gender'] != $ride['Gender']) {
                    $error_message = "❌ Your gender does not match the ride requirements.";
                } elseif ($passenger['Semester'] > $ride['Semester']) {
                    $error_message = "❌ Your semester is higher than allowed for this ride.";
                }

                if (!empty($error_message)) {
                    echo "<button type='button' class='button' disabled style='background: #ccc; cursor: not-allowed;'>Cannot Apply</button>";
                    echo "<div style='color: red; margin-top: 10px;'>$error_message</div>";
                } elseif ($already_applied) {
                    echo "<button type='button' class='button' disabled style='background: #ccc;'>Already Applied</button>";
                } else {
                    echo '<form method="POST"><button type="submit" name="apply" class="button">Apply for Ride</button></form>';
                }
            } elseif ($_SESSION['student_id'] == $ride['Student_id']) {
                echo "<p>You are the creator of this ride card.</p>";
            } else {
                echo "<p>You are not a passenger. Only passengers can apply for rides.</p>";
            }
            ?>
        </div>
        <br>
        <div>
        <?php if ($_SESSION['student_id'] == $ride['Student_id']) { ?>
            <a href="edit_ride.php?card_no=<?php echo urlencode($ride['Card_no']); ?>" class="button">✏️ Edit Ride</a>
            <a href="select_passengers.php?card_no=<?php echo urlencode($ride['Card_no']); ?>" class="button">Select Passengers</a>
            <a href="trip.php?card_no=<?php echo urlencode($ride['Card_no']); ?>" class="button">Go to Trip Page</a>
        <?php } ?>
        </div>
    </div>
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
