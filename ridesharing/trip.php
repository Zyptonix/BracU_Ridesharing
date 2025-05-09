<?php
session_start();
require_once('DBConnect.php');

if (!isset($_SESSION['student_id'])) {
    die("Please login first.");
}

$student_id1 = $_SESSION['student_id'];

if (!isset($_GET['card_no'])) {
    die("No ride selected.");
}
$card_no = $_GET['card_no'];

// Fetch trip
// Check if user is ride creator
$creator_query = "SELECT * FROM ride_cards WHERE Card_no='$card_no' AND Student_id='$student_id1'";
$creator_result = $conn->query($creator_query);
$is_creator = ($creator_result->num_rows > 0);

// Check if user is a selected passenger
$selected_query = "SELECT * FROM selected_passengers WHERE Card_no='$card_no' AND Passenger_Student_id='$student_id1'";
$selected_result = $conn->query($selected_query);
$is_passenger = ($selected_result->num_rows > 0);

// ✅ NEW: Check if user has a trip entry
$trip_check_query = "SELECT * FROM trips WHERE Card_no='$card_no' AND Student_id='$student_id1'";
$trip_check_result = $conn->query($trip_check_query);
$has_trip = ($trip_check_result->num_rows > 0);

// ✅ Updated access check
if (!$is_creator && !$is_passenger && !$has_trip) {
    die("Unauthorized access.");
}


// Check if trip exists for this user
$trip_query = "SELECT * FROM trips WHERE Card_no='$card_no'";
$trip_result = $conn->query($trip_query);


$trip = $trip_result->fetch_assoc();
$student_id = $trip['Student_id'];
$trip_id = $trip['Trip_id'];
$is_provider = $is_creator;
// Fetch car details
$car_query = "SELECT * FROM cars WHERE student_id = '$student_id' LIMIT 1";
$car_result = $conn->query($car_query);
$car = ($car_result->num_rows > 0) ? $car_result->fetch_assoc() : null;

// Handle trip status changes
if (isset($_POST['start_trip'])) {
    $conn->query("UPDATE trips SET Is_started=1, Starting_time=NOW() WHERE Trip_id='$trip_id'");
    header("Location: trip.php?card_no=$card_no");
    exit();
}

if (isset($_POST['finish_trip'])) {
    $feedback = $conn->real_escape_string($_POST['feedback']);

    // Complete the trip
    $conn->query("UPDATE trips SET Is_completed=1, T_comments='$feedback', Ending_time=NOW() WHERE Trip_id='$trip_id'");

    header("Location: trip.php?card_no=$card_no&finished=1");
   
}

if (isset($_POST['submit_passenger_ratings'])) {
    foreach ($_POST['passenger_ratings'] as $passenger_id => $rating) {
        $rating = (int)$rating;
        $conn->query("UPDATE selected_passengers SET Rating='$rating' WHERE Passenger_student_id='$passenger_id' AND Card_no='$card_no'");
    }
    $message = "Passenger ratings saved successfully!";
}
if (isset($_POST['submit_provider_rating'])) {
    $provider_rating = (int)$_POST['provider_rating'];
    $conn->query("UPDATE trips SET Car_provider_rating='$provider_rating' WHERE Trip_id='$trip_id'");
    $message = "Provider rating submitted successfully!";
}

// Fetch passengers
$selected_passengers_query = "SELECT * FROM selected_passengers sp
                              JOIN users u ON sp.Passenger_student_id = u.Student_id
                              WHERE sp.Card_no='$card_no'";
$passengers_result = $conn->query($selected_passengers_query);

$is_completed = $trip['Is_completed'];
$is_started = $trip['Is_started'];

// Fetch user info
$user_id = $_SESSION['student_id'];
$query1 = "SELECT * FROM users WHERE student_id = ?";
$stmt1 = $conn->prepare($query1);
$stmt1->bind_param("i", $user_id);
$stmt1->execute();
$result1 = $stmt1->get_result();
$user1 = $result1->fetch_assoc();

if (isset($_POST['return_to_rides'])) {   
     header("Location: ride.php");
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Trip Management</title>
    <link rel="stylesheet" href="css/style.css" />
    <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
        font-family: 'Segoe UI', sans-serif;
        background: #eef1f4;
        padding: 20px;
        color: #333;
    }

    .container {
        max-width: 1100px;
        margin: auto;
        background: white;
        padding: 30px;
        border-radius: 14px;
        box-shadow: 0 8px 20px rgba(0,0,0,0.1);
    }

    h1, h2 {
        text-align: center;
        color: #222;
        margin-bottom: 20px;
    }

    .trip-car-wrapper {
        display: flex;
        flex-wrap: wrap;
        gap: 20px;
        margin-bottom: 40px;
    }


      /* Trip Summary */
  .trip-summary {
    background: #f0f9ff;
    padding: 20px;
    margin-bottom: 30px;
    border-left: 5px solid #3498db;
    border-radius: 12px;
  }
  
  .trip-summary h2,, .car-details h2 {
    margin-top: 0;
    color: #3498db;
  }

  
    .trip-summary, .car-details {
        background: #fafafa;

        margin-bottom: 30px;
        border-left: 5px solid #3498db;
        border-bottom: 5px solid #3498db;
        border-radius: 12px;
        padding: 20px;
        flex: 1 1 45%;
    }

    .trip-summary p, .car-details p {
        font-size: 18px;
        margin: 8px 0;
    }

    .form-section {
        background: #f9fbfc;
        border: 1px solid #dde3ea;
        border-left: 5px solid #339af0;
        border-radius: 10px;
        padding: 25px;
        margin-bottom: 30px;
    }

    .form-section label {
        display: block;
        font-weight: 600;
        margin-bottom: 8px;
    }

    textarea, input[type="number"] {
        width: 100%;
        padding: 10px;
        border: 1px solid #ccc;
        border-radius: 8px;
        margin-bottom: 15px;
        font-size: 14px;
    }

    .button {
        background: linear-gradient(135deg, #007bff, #0056b3);
        color: white;
        padding: 12px 20px;
        border: none;
        border-radius: 10px;
        font-size: 16px;
        cursor: pointer;
        transition: background 0.3s;
    }

    .button:hover {
        background: linear-gradient(135deg, #0056b3, #003f91);
    }

    .message {
        background: #e3fcef;
        border-left: 5px solid #28a745;
        padding: 15px;
        margin-bottom: 20px;
        border-radius: 8px;
        color: #155724;
    }

    .passenger-box {
        background: #f0f4f8;
        padding: 15px;
        margin-bottom: 15px;
        border-left: 4px solid #339af0;
        border-radius: 8px;
    }

    .rating-group {
        display: flex;
        gap: 8px;
        margin-top: 10px;
    }

    .rating-group input[type="radio"] {
        display: none;
    }

    .rating-group label {
        background: #e0e0e0;
        width: 32px;
        height: 32px;
        text-align: center;
        line-height: 32px;
        border-radius: 6px;
        font-weight: bold;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .rating-group input[type="radio"]:checked + label {
        background-color: #007bff;
        color: #fff;
    }

    .rating-group label:hover {
        background-color: #339af0;
        color: #fff;
    }

    @media (max-width: 768px) {
        .trip-car-wrapper {
            flex-direction: column;
        }

        .trip-summary, .car-details {
            flex: 1 1 100%;
        }
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
        👤 <?php echo htmlspecialchars($user1['Name']); ?> ▼
        </button>
        <div class="user-dropdown" id="userCard">
            <strong>Name:</strong> <?php echo htmlspecialchars($user1['Name']); ?><br>
            <strong>ID:</strong> <?php echo htmlspecialchars($user1['Student_id']); ?><br>
            <strong>Email:</strong> <?php echo htmlspecialchars($user1['Brac_mail']); ?><br>
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
    <h1>Manage Trip</h1>

   
    <div class="trip-car-wrapper">
        <div class="trip-summary">
            <h2>Trip Summary</h2>
            <p><strong>Card Number:</strong> <?php echo htmlspecialchars($trip['Card_no']); ?></p>
            <p><strong>Trip ID:</strong> <?php echo htmlspecialchars($trip['Trip_id']); ?></p>
            <p><strong>Provider ID:</strong> <?php echo htmlspecialchars($trip['Student_id']); ?></p>
            <p><strong>Pickup Time:</strong> <?php echo date("g:i A", strtotime($trip['Pickup_time'])); ?></p>
            <p><strong>Status:</strong> 
                <?php
                    if ($trip['Is_completed']) {
                        echo "Completed ✅";
                    } elseif ($trip['Is_started']) {
                        echo "Ongoing 🚗";
                    } else {
                        echo "Not Started ⏳";
                    }
                ?>
            </p>
        </div>

        <?php if ($car) { ?>
        <div class="car-details">
            <h2>Car Details</h2>
            <p><strong>Model:</strong> <?php echo htmlspecialchars($car['Model']); ?></p>
            <p><strong>Color:</strong> <?php echo htmlspecialchars($car['Colour']); ?></p>
            <p><strong>Year:</strong> <?php echo htmlspecialchars($car['Year']); ?></p>
            <p><strong>Plate Number:</strong> <?php echo htmlspecialchars($car['Plate_number']); ?></p>
        </div>
        <?php } ?>
    </div>


    <?php if (!$is_started && $_SESSION['student_id']==$student_id) { ?>
        <form method="POST">
            <button type="submit" name="start_trip" class="button">Start Trip</button>
        </form>
    <?php } elseif ($is_started && $_SESSION['student_id']==$student_id) { ?>
        <form method="POST" class="form-section">
            <h2>Finish Trip</h2>
            <label for="feedback">Trip Feedback (Optional):</label>
            <textarea name="feedback" placeholder="Write your comments here..."></textarea>
            <button type="submit" name="finish_trip" class="button">Finish Trip</button>
        </form>
    <?php } ?>

  <?php if ($is_completed && $is_provider) { ?>
    <div class="form-section">
        <h2>Rate Your Passengers</h2>
        <form method="POST">
            <?php
            if ($passengers_result->num_rows > 0) {
                while ($row = $passengers_result->fetch_assoc()) {
                    echo '<div class="passenger-box">';
                    echo '<strong>' . htmlspecialchars($row['Name']) . '</strong><br>';
                    echo 'Email: ' . htmlspecialchars($row['Brac_mail']) . '<br>';
                    echo 'Phone: ' . htmlspecialchars($row['Phone_number']) . '<br>';
                    echo '<label>Rate Passenger (1-5):</label>';
                    echo '<div class="rating-group">';
                    for ($i=1; $i<=5; $i++) {
                        echo '<input type="radio" id="passenger_' . htmlspecialchars($row['Passenger_Student_id']) . '_' . $i . '" 
                                    name="passenger_ratings[' . htmlspecialchars($row['Passenger_Student_id']) . ']" 
                                    value="' . $i . '" required>';
                        echo '<label for="passenger_' . htmlspecialchars($row['Passenger_Student_id']) . '_' . $i . '">' . $i . '</label>';
                    }
                    echo '</div>';
                    echo '</div>';
                }
                ?>
                <button type="submit" name="submit_passenger_ratings" class="button">Save Passenger Ratings</button>
            <?php } ?>
        </form>
    </div>
<?php } ?>


<?php if ($is_completed && !$is_provider) { ?>
    <div class="form-section">
        <h2>Rate Your Car Provider</h2>
        <form method="POST">
            <label>Rate Provider (1-5):</label>
            <div class="rating-group">
                <?php for ($i=1; $i<=5; $i++) { ?>
                    <input type="radio" id="provider_<?php echo $i; ?>" name="provider_rating" value="<?php echo $i; ?>" required>
                    <label for="provider_<?php echo $i; ?>"><?php echo $i; ?></label>
                <?php } ?>
            </div>
            <button type="submit" name="submit_provider_rating" class="button">Submit Provider Rating</button>
        </form>
    </div>
<?php } ?>

<?php if (isset($message)) { echo '<div class="message">'.$message.'</div>'; } ?>
<form method="POST">
    <input type="hidden" name="return_to_rides" value="1">
    <button type="submit" class="button" style="background: linear-gradient(135deg, #28a745, #218838);">
        🧹 Return to Ridecards
    </button>
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
