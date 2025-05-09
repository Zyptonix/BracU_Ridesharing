<?php
session_start();
require_once('DBConnect.php'); // Database connection

// Check if the user is logged in
if (!isset($_SESSION['student_id'])) {
    die("Please log in first.");
}

// Fetch all the rides from the database


// Base query
$query = "
SELECT r.*
FROM ride_cards r
LEFT JOIN trips t ON r.Card_no = t.Card_no AND r.Student_id = t.Student_id
WHERE t.Is_completed = 0
";

// Conditions for search
$conditions = [];

if (!empty($_GET['pickup_area'])) {
    $location = $conn->real_escape_string($_GET['pickup_area']);
    $conditions[] = "r.Pickup_Area LIKE '%$location%'";
}

if (!empty($_GET['seats_filter'])) {
    $seats = (int)$_GET['seats_filter'];
    $conditions[] = "r.Number_of_empty_seats >= $seats";
}

if (!empty($_GET['timeslot_filter'])) {
    $timeslot = $conn->real_escape_string($_GET['timeslot_filter']);
    $conditions[] = "r.Timeslot = '$timeslot'";
}

// Add filter conditions
if (!empty($conditions)) {
    $query .= " AND " . implode(" AND ", $conditions);
}

$query .= " ORDER BY r.Pickup_time, r.Number_of_empty_seats";
$result = $conn->query($query);


// Wishlist Functionality
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['wishlist_card_no'])) {
    $card_no = intval($_POST['wishlist_card_no']);
    $passenger_id = $_SESSION['student_id'] ?? null;

    if ($passenger_id && $_SESSION['P_flag'] == 1) {
        // Prevent duplicate wishlist entries
        $check = $conn->prepare("SELECT * FROM wishlist WHERE Passenger_id = ? AND Card_no = ?");
        $check->bind_param("ii", $passenger_id, $card_no);
        $check->execute();
        $check_result = $check->get_result();

        if ($check_result->num_rows === 0) {
            $stmt = $conn->prepare("INSERT INTO wishlist (Passenger_id, Card_no) VALUES (?, ?)");
            $stmt->bind_param("ii", $passenger_id, $card_no);
            $stmt->execute();
        }
    }
}


// Fetch user info for nav bar
$user_id = $_SESSION['student_id'];
$nav_query = "SELECT * FROM users WHERE student_id = ?";
$nav_stmt = $conn->prepare($nav_query);
$nav_stmt->bind_param("i", $user_id);
$nav_stmt->execute();
$nav_result = $nav_stmt->get_result();
$nav_user = $nav_result->fetch_assoc();

// Handle logout request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['logout'])) {
    session_destroy();
    header("Location: index.php");
    exit();
}


?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Available Rides</title>
    <link rel="stylesheet" href="css/style.css" />
    <style>
          h1 {
                font-size: 5em;
                position: relative;
                top: 20%; 
                padding-bottom: 4%;

            }
            .wishlist-button {
                background-color: #ff4d4d;
                color: white;
                border: none;
                border-radius: 6px;
                padding: 6px 12px;
                font-size: 14px;
                cursor: pointer;
                transition: background-color 0.2s ease;
            }

            .wishlist-button:hover {
                background-color: #e60000;
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
            👤 <?php echo htmlspecialchars($nav_user['Name']); ?> ▼
            </button>
            <div class="user-dropdown" id="userCard">
                <strong>Name:</strong> <?php echo htmlspecialchars($nav_user['Name']); ?><br>
                <strong>ID:</strong> <?php echo htmlspecialchars($nav_user['Student_id']); ?><br>
                <strong>Email:</strong> <?php echo htmlspecialchars($nav_user['Brac_mail']); ?><br>
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
    
    <div class="top-search-bar">
        <form method="GET" class="filter-form">
  
                <select name="pickup_area">
                    <option value="">🔍 Pickup Area</option>
                    <option value="Mohammadpur" <?= (isset($_GET['pickup_area']) && $_GET['pickup_area'] == 'Mohammadpur') ? 'selected' : '' ?>>Mohammadpur</option>
                    <option value="Lalmatia" <?= (isset($_GET['pickup_area']) && $_GET['pickup_area'] == 'Lalmatia') ? 'selected' : '' ?>>Lalmatia</option>
                    <option value="Dhanmondi" <?= (isset($_GET['pickup_area']) && $_GET['pickup_area'] == 'Dhanmondi') ? 'selected' : '' ?>>Dhanmondi</option>
                    <option value="Adabor" <?= (isset($_GET['pickup_area']) && $_GET['pickup_area'] == 'Adabor') ? 'selected' : '' ?>>Adaborr</option>
                    <option value="Banani" <?= (isset($_GET['pickup_area']) && $_GET['pickup_area'] == 'Banani') ? 'selected' : '' ?>>Banani</option>
                    <option value="Gulshan-1" <?= (isset($_GET['pickup_area']) && $_GET['pickup_area'] == 'Gulshan-1') ? 'selected' : '' ?>>Gulshan-1</option>
                    <option value="Baridhara" <?= (isset($_GET['pickup_area']) && $_GET['pickup_area'] == 'Baridhara') ? 'selected' : '' ?>>Baridhara</option>
                    <option value="Mohakhali" <?= (isset($_GET['pickup_area']) && $_GET['pickup_area'] == 'Mohakhali') ? 'selected' : '' ?>>Mohakhali</option>
                    <option value="Kuril" <?= (isset($_GET['pickup_area']) && $_GET['pickup_area'] == 'Kuril') ? 'selected' : '' ?>>Kuril</option>
                    <option value="Azimpur" <?= (isset($_GET['pickup_area']) && $_GET['pickup_area'] == 'Azimpur') ? 'selected' : '' ?>>Azimpur</option>
                    <option value="Uttara" <?= (isset($_GET['pickup_area']) && $_GET['pickup_area'] == 'Uttara') ? 'selected' : '' ?>>Uttara</option>
                </select>
            <select name="seats_filter">
                <option value="">🚗 Min Seats</option>
                <?php for ($i = 1; $i <= 5; $i++) { ?>
                    <option value="<?= $i ?>" <?= (isset($_GET['seats_filter']) && $_GET['seats_filter'] == $i) ? 'selected' : '' ?>><?= $i ?>+</option>
                <?php } ?>
            </select>

            <select name="timeslot_filter">
                <option value="">🕒 Timeslot</option>
                <option value="Morning" <?= (isset($_GET['timeslot_filter']) && $_GET['timeslot_filter'] == 'Morning') ? 'selected' : '' ?>>Morning</option>
                <option value="Afternoon" <?= (isset($_GET['timeslot_filter']) && $_GET['timeslot_filter'] == 'Afternoon') ? 'selected' : '' ?>>Afternoon</option>
                <option value="Evening" <?= (isset($_GET['timeslot_filter']) && $_GET['timeslot_filter'] == 'Evening') ? 'selected' : '' ?>>Evening</option>
            </select>

            <button type="submit">Search</button>
        </form>
        
    </div>

    
    <h1>Available Rides</h1>



    <div class="ride-box-container">
        <?php
        // Check if there are any rides available
        if ($result && $result->num_rows > 0) {
            // Loop through each ride and create a clickable box
            while ($ride = $result->fetch_assoc()) {
                $ride_card_no = $ride['Card_no'];
                ?>
                <a href="ride_cards.php?card_no=<?php echo $ride_card_no; ?>" class="ride-box">
                    <h3><strong>Pickup Area:</strong> <?php echo htmlspecialchars($ride['Pickup_Area']); ?></h3>
                    <?php
                        $pickup_time_24 = $ride['Pickup_time'];
                        $pickup_time_12 = date("g:i A", strtotime($pickup_time_24));
                    ?>
                    <p><strong>Pickup Time:</strong> <?= $pickup_time_12 ?></p>
                    <p><strong>Timeslot:</strong> <?php echo htmlspecialchars($ride['Timeslot']); ?></p>
                    <p><strong>Seats Available:</strong> <?php echo htmlspecialchars($ride['Number_of_empty_seats']); ?></p>
                    <p><strong>Pickup Date:</strong> <?php echo htmlspecialchars($ride['Pickup_date']); ?></p>
                    <?php if ($_SESSION['P_flag'] == 1 && $ride['Student_id'] != $_SESSION['student_id']): ?>
                        <form method="post" style="margin-top: 10px;">
                            <input type="hidden" name="wishlist_card_no" value="<?= $ride['Card_no'] ?>">
                            <button type="submit" class="wishlist-button">❤️ Wishlist</button>
                        </form>
                    <?php endif; ?>
                </a>
                <?php
            }
        } else {
            echo "<p>No rides available at the moment.</p>";
        }
        ?>
    </div>
    <br>

    <div class="add-ride-container">
    <a href="new_ride.php" class="add-ride-btn">➕ Add New Ride</a>
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
