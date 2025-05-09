<?php
session_start();
require_once('DBConnect.php'); // includes the $conn connection
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

// Check if the user is logged in
if (!isset($_SESSION['student_id'])) {
    die("Please log in first.");
}

// Get the current logged-in user's ID
$logged_in_student_id = $_SESSION['student_id'];

// Fetch the user's details to check if they are a car provider
$sql = "SELECT C_flag, Gender FROM users WHERE Student_id = '$logged_in_student_id' LIMIT 1";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    $user = $result->fetch_assoc();
    $creator_gender = $user['Gender']; // Save creator's gender for matching
    if ($user['C_flag'] == 0) {
        die("You must be a car provider to create a ride.");
    }
} else {
    die("User not found.");
}
$message = "";
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $pickup_time = $conn->real_escape_string($_POST['pickup_time']);
    $pickup_area = $conn->real_escape_string($_POST['pickup_area']);
    $seats = (int)$_POST['number_of_empty_seats'];
    $pickup_date = $conn->real_escape_string($_POST['pickup_date']);
    $gender = $conn->real_escape_string($_POST['gender']);
    $semester = $conn->real_escape_string($_POST['semester']);

    $hour = (int)substr($pickup_time, 0, 2);
    $minute = (int)substr($pickup_time, 3, 2);

    if (($hour == 8 && $minute >= 0) || ($hour > 8 && $hour < 12)) {
        $timeslot = 'Morning';
    } elseif (($hour == 12 && $minute >= 0) || ($hour > 12 && $hour < 15)) {
        $timeslot = 'Afternoon';
    } elseif (($hour == 15 && $minute >= 0) || ($hour > 15 && $hour <= 17)) {
        $timeslot = 'Evening';
    } else {
        $message = "Pickup time must be between 08:00 AM and 05:00 PM.";
    }

    if (empty($message)) {
        $insert = $conn->query("INSERT INTO ride_cards 
            (Student_ID, Pickup_time, Timeslot, Pickup_Area, Number_of_empty_seats, Pickup_Date, Gender, Semester)
            VALUES ($logged_in_student_id, '$pickup_time', '$timeslot', '$pickup_area', '$seats', '$pickup_date', '$gender', '$semester')");
    }

    if ($insert) {
        $card_no = $conn->insert_id;

        $insert_trip = "INSERT INTO trips (Card_no, Student_id, Pickup_time, Is_started, Is_completed)
                        VALUES ('$card_no', '$logged_in_student_id', '$pickup_time', 0, 0)";

        if ($conn->query($insert_trip)) {
            $message = "✅ Ride and trip added successfully!";
            $success = true;
        } else {
            $message = "⚠️ Ride created but trip creation failed: " . $conn->error;
        }
    }

}
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
    <title>Add New Ride</title>
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

    <h1>Add New Ride</h1>

    <div class="form-container">
        <form method="post" action="">
            <div>
                <label for="pickup_time">Pickup Time:</label>
                <input type="time" id="pickup_time" name="pickup_time" required>
            </div>
            <div>
                <label for="pickup_area">Pickup Area:</label>
                <select id="pickup_area" name="pickup_area" required>
                    <option value="">Select Area</option>
                    <option value="Mohammadpur">Mohammadpur</option>
                    <option value="Lalmatia">Lalmatia</option>
                    <option value="Dhanmondi">Dhanmondi</option>
                    <option value="Adabor">Adabor</option>
                    <option value="Banani">Banani</option>
                    <option value="Gulshban-1">Gulshan-1</option>
                    <option value="Baridhara">Baridhara</option>
                    <option value="Mohakhali">Mohakhali</option>
                    <option value="Kuril">Kuril</option>
                    <option value="Azimpur">Azimpur</option>
                    <option value="Uttara">Uttara</option>
                </select>
            </div>
            <div>
                <label for="number_of_empty_seats">Number of Empty Seats:</label>
                <input type="number" id="number_of_empty_seats" name="number_of_empty_seats" min="1" required>
            </div>
            <div>
                <label for="pickup_date">Pickup Date:</label>
                <input type="date" id="pickup_date" name="pickup_date" required>
            </div>
            <div>
                <label for="gender">Gender:</label>
                <select id="gender" name="gender" required>
                    <option value="">Select Gender</option>
                    <option value="Male">Male</option>
                    <option value="Female">Female</option>
                    <option value="Both">Both</option>
                </select>
            </div>
            <div>
                <label for="semester">Max Semester:</label>
                <input type="text" id="semester" name="semester" required>
            </div>
            <button type="submit" class="submit-btn">➕ Submit Ride</button>
        </form>

        <a href="ride.php" class="back-link">← Back to Rides</a>
    </div>

    <?php if ($success): ?>
        <script>
            alert("🎉 Ride added successfully!");
            window.location.href = "ride.php";
        </script>
    <?php elseif ($message): ?>
        <script>
            alert("⚠️ <?= addslashes($message) ?>");
        </script>
    <?php endif; ?>
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
