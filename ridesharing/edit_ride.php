<?php
session_start();
require_once('DBConnect.php');

// Ensure user is logged in
if (!isset($_SESSION['student_id'])) {
    die("Unauthorized access. Please login.");
}

// Ensure a ride is selected
if (!isset($_GET['card_no'])) {
    die("No ride selected.");
}

$card_no = intval($_GET['card_no']);
$student_id = $_SESSION['student_id'];

// Fetch ride details
$sql = "SELECT * FROM ride_cards WHERE Card_no = ? AND Student_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $card_no, $student_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Ride not found or you're not the creator.");
}
$ride = $result->fetch_assoc();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pickup_area = $_POST['pickup_area'];
    $pickup_time = $_POST['pickup_time'];
    $timeslot = $_POST['timeslot'];
    $seats = intval($_POST['seats']);
    $gender = $_POST['gender'];
    $semester = intval($_POST['semester']);

    $update_sql = "UPDATE ride_cards 
                   SET Pickup_Area=?, Pickup_time=?, Timeslot=?, Number_of_empty_seats=?, Gender=?, Semester=? 
                   WHERE Card_no=? AND Student_id=?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("sssissii", $pickup_area, $pickup_time, $timeslot, $seats, $gender, $semester, $card_no, $student_id);

    if ($update_stmt->execute()) {
        echo "<script>alert('✅ Ride updated successfully!'); window.location.href='ride_cards.php?card_no=" . urlencode($card_no) . "';</script>";
        exit;
    } else {
        echo "<script>alert('❌ Error updating ride.');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Ride</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<h1>Edit Ride</h1>
<form method="POST">
    <label>Pickup Area:</label>
    <input type="text" name="pickup_area" value="<?php echo htmlspecialchars($ride['Pickup_Area']); ?>" required>

    <label>Pickup Time:</label>
    <input type="time" name="pickup_time" value="<?php echo htmlspecialchars($ride['Pickup_time']); ?>" required>

    <label>Timeslot:</label>
    <select name="timeslot" required>
        <option value="Morning" <?php if ($ride['Timeslot'] === 'Morning') echo 'selected'; ?>>Morning</option>
        <option value="Afternoon" <?php if ($ride['Timeslot'] === 'Afternoon') echo 'selected'; ?>>Afternoon</option>
        <option value="Evening" <?php if ($ride['Timeslot'] === 'Evening') echo 'selected'; ?>>Evening</option>
    </select>

    <label>Number of Empty Seats:</label>
    <input type="number" name="seats" value="<?php echo $ride['Number_of_empty_seats']; ?>" required min="1">

    <label>Gender Preference:</label>
    <select name="gender" required>
        <option value="Male" <?php if ($ride['Gender'] === 'Male') echo 'selected'; ?>>Male</option>
        <option value="Female" <?php if ($ride['Gender'] === 'Female') echo 'selected'; ?>>Female</option>
        <option value="Both" <?php if ($ride['Gender'] === 'Both') echo 'selected'; ?>>Both</option>
    </select>

    <label>Maximum Semester Allowed:</label>
    <input type="number" name="semester" value="<?php echo $ride['Semester']; ?>" required min="1">

    <button type="submit"class="submit-btn">Save Changes</button>
</form>

</body>
</html>
