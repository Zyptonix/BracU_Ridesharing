<?php  
require_once('DBconnect.php');
session_start();

// Check if user is a car provider
if (!isset($_SESSION['C_flag']) || $_SESSION['C_flag'] == 0) {
  echo "<h2>You are not a car provider.</h2>";
  exit; // Stop the page here
}
$student_id=$_SESSION['student_id'];
// Retrieve car details
$sql = "SELECT * FROM cars WHERE Student_id=$student_id"; // Fetch only one car (if exists)
$result = $conn->query($sql);
$car = $result->fetch_assoc();

// Process form submission (Add or Edit Car)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    $plateNumber = $_POST['plate_number'];
    $model = $_POST['model'];
    $year = $_POST['year'];
    $color = $_POST['color'];

    if ($car) {
        // Edit the existing car
        $sql = "UPDATE cars SET plate_number='$plateNumber', model='$model', year=$year, colour='$color' WHERE Student_id='$student_id'";
        if ($conn->query($sql) === TRUE) {
                echo "Car updated successfully!";
                // Redirect to refresh the page and show updated data
                header("Location: " . $_SERVER['PHP_SELF']);
                exit;
        } else {
            echo "Error: " . $sql . "<br>" . $conn->error;
        }
    } else {
        // Add the first car (only if no car exists)
        $sql = "INSERT INTO cars (student_id, plate_number, model, year, colour) VALUES ('$student_id','$plateNumber', '$model', $year, '$color')";
        if ($conn->query($sql) === TRUE) {
            echo "New car added successfully!";
        } else {
            echo "Error: " . $sql . "<br>" . $conn->error;
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

// Close the connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Car Details</title>
  <link rel="stylesheet" href="css/style.css" />
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }
.side-by-side-wrapper {
  display: flex;
  gap: 40px;
  justify-content: space-between;
  align-items: flex-start;
  flex-wrap: wrap;
  margin-top: 30px;
  width: 100%;
  max-width: 1000px;
  margin-left: auto;
  margin-right: auto;
}

.form-container,
.car-table-container {
  flex: 1;
  min-width: 350px;
  background: #ffffff;
  padding: 30px;
  border-radius: 12px;
  box-shadow: 0 8px 20px rgba(0, 123, 255, 0.1);
  border: 1px solid #cce0ff;
}

#car-table {
  width: 100%;
  border-collapse: collapse;
  margin-top: 10px;
  border-radius: 10px;
  overflow: hidden;
  box-shadow: 0 4px 8px rgba(0, 123, 255, 0.1);
}

#car-table th {
  background-color: #0077b6;
  color: white;
}

#car-table th, #car-table td {
  padding: 14px;
  text-align: center;
  border-bottom: 1px solid #ddd;
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

    <div class="side-by-side-wrapper">
    <!-- Form -->
    <div class="form-container">
      <h1>Manage Your Car</h1>
      <form action="" method="POST" id="car-form">
        <label for="plate-number">Plate Number</label>
        <input type="text" id="plate-number" name="plate_number" value="<?php echo htmlspecialchars($car['Plate_number'] ?? ''); ?>" placeholder="Enter Plate Number" required>
        
        <label for="model">Model</label>
        <input type="text" id="model" name="model" value="<?php echo htmlspecialchars($car['Model'] ?? ''); ?>" placeholder="Enter Car Model" required>
        
        <label for="year">Year</label>
        <input type="number" id="year" name="year" value="<?php echo htmlspecialchars($car['Year'] ?? ''); ?>" placeholder="Enter Car Year" required>
        
        <label for="color">Color</label>
        <input type="text" id="color" name="color" value="<?php echo htmlspecialchars($car['Colour'] ?? ''); ?>" placeholder="Enter Car Color" required>
        
        <button type="submit" class="submit-btn"><?php echo $car ? 'Edit Car' : 'Add Car'; ?></button>
      </form>
    </div>

    <!-- Table -->
    <div class="car-table-container">
      <h2>Your Car Details</h2>
      <?php if ($car): ?>
        <table id="car-table">
          <thead>
            <tr>
              <th>Plate Number</th>
              <th>Model</th>
              <th>Year</th>
              <th>Color</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td><?php echo htmlspecialchars($car['Plate_number']); ?></td>
              <td><?php echo htmlspecialchars($car['Model']); ?></td>
              <td><?php echo htmlspecialchars($car['Year']); ?></td>
              <td><?php echo htmlspecialchars($car['Colour']); ?></td>
            </tr>
          </tbody>
        </table>
      <?php else: ?>
        <p>No car added yet. Please add your Car details</p>
      <?php endif; ?>
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