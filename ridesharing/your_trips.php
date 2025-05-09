<?php
session_start();
require_once("DBConnect.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_selection'])) {
    $card_no = intval($_POST['card_no']);
    $student_id = $_SESSION['student_id'];

    // Remove from selected_passengers
    $stmt = $conn->prepare("DELETE FROM selected_passengers WHERE Passenger_student_id = ? AND Card_no = ?");
    $stmt->bind_param("ii", $student_id, $card_no);
    $stmt->execute();

    // Increase seat count
    $stmt2 = $conn->prepare("UPDATE ride_cards SET Number_of_empty_seats = Number_of_empty_seats + 1 WHERE Card_no = ?");
    $stmt2->bind_param("i", $card_no);
    $stmt2->execute();

    $message = "Application removed successfully.";
}

if (!isset($_SESSION['student_id'])) {
    header("Location: login.php");
    exit();
}

$student_id = $_SESSION['student_id'];


// Applied rides (not yet selected)
$sql = "SELECT rc.*, u.Name AS Provider_Name, u.Brac_mail 
        FROM ride_cards rc
        JOIN applies_for ap ON rc.Card_no = ap.Card_no
        JOIN users u ON rc.Student_id = u.Student_id
        LEFT JOIN trips t ON rc.Card_no = t.Card_no
        WHERE ap.Passenger_student_id = ? AND (t.Is_completed IS NULL OR t.Is_completed = 0)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();


// Selected rides (as passenger)
$sql1 = "SELECT r.* 
    FROM selected_passengers s
    JOIN ride_cards r ON s.Card_no = r.Card_no
    LEFT JOIN trips t ON r.Card_no = t.Card_no
    WHERE s.Passenger_student_id = ? AND (t.Is_completed IS NULL OR t.Is_completed = 0)
";
$stmt = $conn->prepare($sql1);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result1 = $stmt->get_result();

// Rides created by this student
$created_query = "SELECT * FROM trips  
                  INNER JOIN ride_cards 
                  ON ride_cards.Card_no = trips.Card_no 
                  AND ride_cards.Student_id = trips.Student_id
                  WHERE trips.Student_id = ? 
                  AND trips.Is_completed = 0";
$created_stmt = $conn->prepare($created_query);
$created_stmt->bind_param("i", $student_id);
$created_stmt->execute();
$created_rides = $created_stmt->get_result();

// Fetch user info
$user_id = $_SESSION['student_id'];
$query = "SELECT * FROM users WHERE student_id = ?";
$stmt2 = $conn->prepare($query);
$stmt2->bind_param("i", $user_id);
$stmt2->execute();
$result3 = $stmt2->get_result();
$user = $result3->fetch_assoc();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Your Trips</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        body { font-family: sans-serif; background: #f0f2f5; padding: 20px; }

        .trip-grid {
            display: flex;
            gap: 20px;
            align-items: flex-start;
            flex-wrap: wrap;
        }

        .column {
            flex: 1;
            min-width: 300px;
        }

        .container {
            background: white;
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }

        h1 { margin-bottom: 20px; color: #333; }
        h2 { margin-top: 0; color: #007bff; }

        .btn {
            display: inline-block;
            margin-top: 10px;
            padding: 8px 16px;
            background: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 6px;
        }

        .ride-card, .card {
            background: #fff;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
            transition: 0.3s ease;
        }

        .card:hover {
            box-shadow: 0 6px 20px rgba(0,0,0,0.12);
        }

        .ride-card p, .card p {
            margin: 8px 0;
            color: #555;
        }

        .remove-button {
            background-color: #dc3545;
            color: white;
            padding: 10px 20px;
            font-size: 14px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .remove-button:hover {
            background-color: #b02a37;
        }

        .success-message {
            background-color: #d4edda;
            border-left: 6px solid #28a745;
            color: #155724;
            padding: 12px 18px;
            margin: 20px auto;
            width: 90%;
            border-radius: 8px;
            text-align: center;
            font-weight: bold;
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



<h1>Your Trips</h1>


<div class="trip-grid">
    <!-- Left Column: Applied & Selected -->
    <div class="column">
        <div class="container">
            <h2>Applied For</h2>
            <?php if ($result->num_rows === 0): ?>
                <p style="text-align: center; font-size: 18px;">You haven’t applied to any rides yet.</p>
            <?php else: ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <div class="card">
                        <p><strong>Card No:</strong> <?php echo htmlspecialchars($row['Card_no']); ?></p>
                        <p><strong>Pickup Time:</strong> <?php echo date("g:i A, M j", strtotime($row['Pickup_time'])); ?></p>
                        <p><strong>Provider:</strong> <?php echo htmlspecialchars($row['Provider_Name']); ?> (<?php echo htmlspecialchars($row['Brac_mail']); ?>)</p>
                        <p><strong>Status:</strong> Pending Review ✅</p>
                    </div>
                <?php endwhile; ?>
            <?php endif; ?>
        </div>
    </div>
    <div class="column">
        <div class="container">
            <h2>Selected For</h2>
            <?php if ($result1->num_rows > 0): ?>
                <?php while ($ride = $result1->fetch_assoc()): ?>
                    <div class="ride-card">
                        <p><strong>Pickup Area:</strong> <?php echo htmlspecialchars($ride['Pickup_Area']); ?></p>
                   
                        <p><strong>Pickup Time:</strong> <?php echo date("g:i A", strtotime($ride['Pickup_time'])); ?></p>
                    
                        <p><strong>Timeslot:</strong> <?php echo htmlspecialchars($ride['Timeslot']); ?></p>
                 
                        <p><strong>Seats Left:</strong> <?php echo htmlspecialchars($ride['Number_of_empty_seats']); ?></p>
                     
                        <p><strong>Gender Requirement:</strong> <?php echo htmlspecialchars($ride['Gender']); ?></p>
                       
                        <p><strong>Max Semester:</strong> <?php echo htmlspecialchars($ride['Semester']); ?></p>
                        
                        <a class="button" href="trip.php?card_no=<?php echo urlencode($ride['Card_no']); ?>">View Trip Page</a>
                        <form method="POST" onsubmit="return confirm('Are you sure you want to remove yourself from this ride?');">
                            <input type="hidden" name="remove_selection" value="1">
                            <input type="hidden" name="card_no" value="<?= htmlspecialchars($ride['Card_no']) ?>">
                            <button type="submit" class="remove-button">❌ Remove</button>
                        </form>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p>You haven't been selected for any rides yet.</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Right Column: Created Rides -->
    <div class="column">
        <div class="container">
            <h2>Created By You</h2>
            <?php if ($created_rides->num_rows > 0): ?>
                <?php while ($row = $created_rides->fetch_assoc()): ?>
                    <div class="ride-card">
                        <p><strong>Card No:</strong> <?= htmlspecialchars($row['Card_no']) ?></p>
                        <p><strong>Pickup Date:</strong> <?= htmlspecialchars($row['Pickup_date']) ?></p>
                        <p><strong>Pickup Area:</strong> <?= htmlspecialchars($row['Pickup_Area']) ?></p>
                        <p><strong>Pickup Time:</strong> <?= date("g:i A", strtotime($row['Pickup_time'])) ?></p>
                        <a href="ride_cards.php?card_no=<?= $row['Card_no'] ?>" class="btn">Manage Ride card</a>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p>You haven't created any rides yet.</p>
            <?php endif; ?>
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
