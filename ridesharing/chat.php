<?php
include("DBconnect.php");
session_start();
$current_user_id = $_SESSION['student_id'] ?? null;
$receiver_id = $_GET['receiver_id'] ?? null;

$receiver_name = 'Unknown User';
$stmt = $conn->prepare("SELECT Name FROM users WHERE Student_id = ?");
$stmt->bind_param("i", $receiver_id);
$stmt->execute();
$name_result = $stmt->get_result();
if ($row = $name_result->fetch_assoc()) {
    $receiver_name = $row['Name'];
}

if (!$current_user_id || !$receiver_id || $current_user_id == $receiver_id) {
    die("Invalid users selected.");
}

$stmt = $conn->prepare("SELECT Chatbox_id FROM messages_in WHERE 
    (Sender_student_id = ? AND Receiver_student_id = ?) OR 
    (Sender_student_id = ? AND Receiver_student_id = ?)");
$stmt->bind_param("iiii", $current_user_id, $receiver_id, $receiver_id, $current_user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    $chatbox_id = $row['Chatbox_id'];
} else {
    $conn->query("INSERT INTO chatbox () VALUES ()");
    $chatbox_id = $conn->insert_id;

    $stmt = $conn->prepare("INSERT INTO messages_in (Chatbox_id, Sender_student_id, Receiver_student_id) VALUES (?, ?, ?)");
    $stmt->bind_param("iii", $chatbox_id, $current_user_id, $receiver_id);
    $stmt->execute();

    $stmt = $conn->prepare("INSERT INTO messages_in (Chatbox_id, Sender_student_id, Receiver_student_id) VALUES (?, ?, ?)");
    $stmt->bind_param("iii", $chatbox_id, $receiver_id, $current_user_id);
    $stmt->execute();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && !empty($_POST['message'])) {
    $message_info = trim($_POST['message']);
    $stmt = $conn->prepare("INSERT INTO messages (Chatbox_id, Message_info, Timestamps, Sender_student_id) VALUES (?, ?, NOW(), ?)");
    $stmt->bind_param("isi", $chatbox_id, $message_info, $current_user_id);
    if (!$stmt->execute()) {
        die("Message insert failed: " . $stmt->error);
    }
    header("Location: chat.php?current_user_id=$current_user_id&receiver_id=$receiver_id");
    exit;
}

$stmt = $conn->prepare("SELECT Message_info, Timestamps, Sender_student_id FROM messages WHERE Chatbox_id = ? ORDER BY Timestamps ASC");
$stmt->bind_param("i", $chatbox_id);
$stmt->execute();
$messages = $stmt->get_result();

// Fetch user info
$user_id = $_SESSION['student_id'];
$query = "SELECT * FROM users WHERE student_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Chat</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/style_chat.css">
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

    <div class="chat-wrapper">
        <div class="chat-box">
        <div class="chat-header">Chat with <?php echo htmlspecialchars($receiver_name); ?></div>

            <div class="chat-messages">
                <?php while ($row = $messages->fetch_assoc()): ?>
                    <div class="message <?php echo $row['Sender_student_id'] == $current_user_id ? 'sent' : 'received'; ?>">
                        <?php echo htmlspecialchars($row['Message_info']); ?>
                        <span class="timestamp"><?php echo date("g:i A", strtotime($row['Timestamps'])); ?></span>
                    </div>
                <?php endwhile; ?>
            </div>
            <form method="POST" class="chat-form">
                <input type="text" name="message" placeholder="Type a message..." required>
                <button type="submit">Send</button>
            </form>
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
