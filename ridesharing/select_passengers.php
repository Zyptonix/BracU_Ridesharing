<?php
session_start();
require_once('DBConnect.php');

if (!isset($_SESSION['student_id'])) {
    die("Please login first.");
}

if (!isset($_GET['card_no'])) {
    die("No ride selected.");
}

$card_no = $_GET['card_no'];
$provider_student_id = $_SESSION['student_id'];

// Fetch trip_id if exists
$tripQuery = "SELECT Trip_id FROM trips WHERE Card_no='$card_no' AND Student_id='$provider_student_id'";
$tripResult = $conn->query($tripQuery);
$trip_id = null;

if ($tripResult->num_rows > 0) {
    $tripRow = $tripResult->fetch_assoc();
    $trip_id = $tripRow['Trip_id'];
}

// Handle selection or removal
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['select_passenger'])) {
        $passenger_id = $_POST['select_passenger'];
    
        // Check current empty seats
        $seat_check = $conn->prepare("SELECT Number_of_empty_seats FROM ride_cards WHERE Card_no = ?");
        $seat_check->bind_param("i", $card_no);
        $seat_check->execute();
        $seat_result = $seat_check->get_result();
    
        if ($seat_result->num_rows > 0) {
            $seat_row = $seat_result->fetch_assoc();
            if ((int)$seat_row['Number_of_empty_seats'] > 0) {
                // Proceed with selection
                $conn->query("INSERT INTO selected_passengers (Passenger_student_id, Card_no, Provider_student_id, Trip_id)
                              VALUES ('$passenger_id', '$card_no', '$provider_student_id', " . ($trip_id ? "'$trip_id'" : "NULL") . ")");
    
                $conn->query("DELETE FROM applies_for 
                              WHERE Passenger_student_id='$passenger_id' AND Card_no='$card_no' AND Provider_student_id='$provider_student_id'");
    
                $conn->query("UPDATE ride_cards SET Number_of_empty_seats = Number_of_empty_seats - 1 
                              WHERE Card_no = '$card_no'");
    
                $message = "Passenger selected successfully!";
            } else {
                $message = "No empty seats available. Cannot select passenger.";
            }
        } else {
            $message = "Ride not found.";
        }
    }
    
    

    if (isset($_POST['remove_passenger'])) {
        $remove_id = $_POST['remove_passenger'];

        // Re-add to applicants
        $conn->query("INSERT INTO applies_for (Passenger_student_id, Card_no, Provider_student_id)
                      VALUES ('$remove_id', '$card_no', '$provider_student_id')");

        // Remove from selected
        $conn->query("DELETE FROM selected_passengers 
                      WHERE Passenger_student_id='$remove_id' AND Card_no='$card_no' AND Provider_student_id='$provider_student_id'");

        // Increase empty seats
        $conn->query("UPDATE ride_cards SET Number_of_empty_seats = Number_of_empty_seats + 1 
                      WHERE Card_no = '$card_no'");

        $message = "Passenger removed successfully!";
    }
}


// Fetch applicants
$sql_applicants = "SELECT u.Student_id, u.Name, u.Brac_mail, u.Phone_number 
                   FROM applies_for a
                   JOIN users u ON a.Passenger_student_id = u.Student_id
                   WHERE a.Card_no='$card_no' AND a.Provider_student_id='$provider_student_id'";
$result_applicants = $conn->query($sql_applicants);

// Fetch selected
$sql_selected = "SELECT u.Student_id, u.Name, u.Brac_mail, u.Phone_number 
                 FROM selected_passengers s
                 JOIN users u ON s.Passenger_student_id = u.Student_id
                 WHERE s.Card_no='$card_no' AND s.Provider_student_id='$provider_student_id'";
$result_selected = $conn->query($sql_selected);

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
    <title>Select Passengers</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}
body {
    font-family: Arial, sans-serif;
    background: #f4f6f8;
   
    margin: 0;
    padding: 20px;
}

.container {
    max-width: 900px;
    margin: auto;
    background: #ffffff;
    padding: 30px;
    border-radius: 12px;
    box-shadow: 0 0 12px rgba(0, 0, 0, 0.06);
}

h1, h2 {
    color: #004085;
    text-align: center;
    margin-bottom: 20px;
}

.message {
    background-color: rgba(0, 145, 241, 0.1);
    padding: 12px 18px;
    border-left: 4px solid #007bff;
    color: #004085;
    font-weight: bold;
    border-radius: 8px;
    margin-bottom: 25px;
    text-align: center;
}

.passenger-box {
    border: 1px solid #dbe2ea;
    border-radius: 12px;
    padding: 20px;
    margin-bottom: 20px;
    background: #ffffff;
    box-shadow: 0 2px 6px rgba(0,0,0,0.04);
}

.passenger-box strong {
    font-size: 18px;
    color: #007bff;
    margin-bottom: 6px;
    display: block;
}

.passenger-info {
    font-size: 14px;
    color: #555;
    margin-bottom: 6px;
}

.passenger-box form {
    margin-top: 10px;
}

.select-button, .remove-button {
    padding: 10px 20px;
    font-size: 15px;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    transition: background 0.3s ease;
    width: 100%;
}

.select-button {
    background-color: #007bff;
    color: white;
}

.select-button:hover {
    background-color: #0056b3;
}

.remove-button {
    background-color: #dc3545;
    color: white;
}

.remove-button:hover {
    background-color: #c82333;
}

.link-button {
    margin-top: 30px;
    text-align: center;
}

.link-button a {
    background-color: #007bff;
    color: white;
    padding: 12px 28px;
    text-decoration: none;
    border-radius: 8px;
    font-weight: bold;
    font-size: 16px;
    transition: background 0.3s ease;
}

.link-button a:hover {
    background-color: #0056b3;
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
    <h1>Select Passengers</h1>

    <?php if (isset($message)) { echo '<div class="message">'.$message.'</div>'; } ?>

    <h2>Applicants</h2>
    <?php
    if ($result_applicants->num_rows > 0) {
        while ($row = $result_applicants->fetch_assoc()) {
            echo '<div class="passenger-box">';
            echo '<strong>' . htmlspecialchars($row['Name']) . '</strong>';
            echo '<div class="passenger-info">Email: ' . htmlspecialchars($row['Brac_mail']) . '</div>';
            echo '<div class="passenger-info">Student ID: ' . htmlspecialchars($row['Student_id']) . '</div>';
            echo '<div class="passenger-info">Phone: ' . htmlspecialchars($row['Phone_number']) . '</div>';
            echo '<form method="POST">';
            echo '<button type="submit" name="select_passenger" value="' . htmlspecialchars($row['Student_id']) . '" class="select-button">Select</button>';
            echo '</form>';
            echo '</div>';
        }
    } else {
        echo "<p>No applicants found.</p>";
    }
    ?>
    <br>
    <h2>Selected Passengers</h2>
    <?php
    if ($result_selected->num_rows > 0) {
        while ($row = $result_selected->fetch_assoc()) {
            echo '<div class="passenger-box">';
            echo '<strong>' . htmlspecialchars($row['Name']) . '</strong>';
            echo '<div class="passenger-info">Email: ' . htmlspecialchars($row['Brac_mail']) . '</div>';
            echo '<div class="passenger-info">Student ID: ' . htmlspecialchars($row['Student_id']) . '</div>';
            echo '<div class="passenger-info">Phone: ' . htmlspecialchars($row['Phone_number']) . '</div>';
            echo '<form method="POST">';
            echo '<button type="submit" name="remove_passenger" value="' . htmlspecialchars($row['Student_id']) . '" class="remove-button">Remove</button>';
            echo '</form>';
            echo '</div>';
        }
    } else {
        echo "<p>No passengers selected yet.</p>";
    }
    ?>

    <div class="link-button">
        <a href="trip.php?card_no=<?php echo urlencode($card_no); ?>">Go to Trip Page</a>
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
