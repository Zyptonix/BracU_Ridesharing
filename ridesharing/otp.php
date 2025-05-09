<?php
session_start();
require 'DBconnect.php'; // make sure DBconnect.php connects with $conn

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

if (!isset($_SESSION['student_id'])) {
    header('Location: index.php');
    exit;
}

$student_id = $_SESSION['student_id'];
$email_query = "SELECT Brac_mail FROM users WHERE Student_id = '$student_id'";
$result = mysqli_query($conn, $email_query);
$row = mysqli_fetch_assoc($result);
$email = $row['Brac_mail'];

// Send OTP if not submitted yet or resend OTP request
if ($_SERVER['REQUEST_METHOD'] === 'GET' && !isset($_SESSION['otp_sent_time'])) {
    $otp = rand(100000, 999999);
    $_SESSION['otp'] = $otp;
    $_SESSION['otp_sent_time'] = time(); // Track OTP sent time

    $mail = new PHPMailer(true);
    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'nszarif522@gmail.com'; // replace with yours
        $mail->Password = '';    // replace with Gmail app password
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        // Recipients
        $mail->setFrom('your-email@gmail.com', 'BRACU RideShare Verification');
        $mail->addAddress($email);

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Your Verification Code';
        $mail->Body    = "Hello,<br>Your OTP code is: <strong>$otp</strong><br>This will expire in 5 minutes.";

        $mail->send();
        $message = "OTP has been sent to your BRACU email.";
    } catch (Exception $e) {
        $error = "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
    }
}

// Resend OTP if requested
if (isset($_POST['resend_otp'])) {
    // Prevent multiple OTPs within 5 minutes
    if (isset($_SESSION['otp_sent_time']) && (time() - $_SESSION['otp_sent_time']) < 300) {
        $error = "You can resend the OTP only after 5 minutes.";
    } else {
        // Resend OTP
        $otp = rand(100000, 999999);
        $_SESSION['otp'] = $otp;
        $_SESSION['otp_sent_time'] = time(); // Update OTP sent time

        $mail = new PHPMailer(true);
        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'nszarif522@gmail.com'; // replace with yours
            $mail->Password = 'exlt lfxk otpa noxj';    // replace with Gmail app password
            $mail->SMTPSecure = 'tls';
            $mail->Port = 587;

            // Recipients
            $mail->setFrom('your-email@gmail.com', 'BRACU RideShare Verification');
            $mail->addAddress($email);

            // Content
            $mail->isHTML(true);
            $mail->Subject = 'Your Verification Code';
            $mail->Body    = "Hello,<br>Your OTP code is: <strong>$otp</strong><br>This will expire in 5 minutes.";

            $mail->send();
            $message = "OTP has been resent to your BRACU email.";
        } catch (Exception $e) {
            $error = "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        }
    }
}

// OTP form submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['resend_otp'])) {
    $entered_otp = $_POST['otp'];
    if ($_SESSION['otp'] == $entered_otp) {
        $update = "UPDATE users SET Verification_status = 1 WHERE Student_id = '$student_id'";
        if (mysqli_query($conn, $update)) {
            $success = "Your email has been verified.";
        } else {
            $error = "Database update failed.";
        }
    } else {
        $error = "Invalid OTP. Please try again.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Email Verification</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: linear-gradient(to bottom right, #e6f0ff, #ffffff);
            height: 100vh;
            margin: 0;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .otp-box {
            background: rgba(255, 255, 255, 0.85);
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
            text-align: center;
            width: 90%;
            max-width: 500px;
        }
        .otp-box h2 {
            font-size: 2rem;
            margin-bottom: 20px;
            color: #007bff;
        }
        input[type="text"] {
            padding: 14px 20px;
            width: 80%;
            border: 2px solid #007bff;
            border-radius: 10px;
            font-size: 1.2rem;
            margin-bottom: 20px;
        }
        button {
            padding: 12px 30px;
            font-size: 1rem;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            transition: 0.3s ease;
        }
        button:hover {
            background-color: #0056b3;
        }
        .message, .error {
            font-size: 1rem;
            margin-bottom: 20px;
            padding: 10px;
            border-radius: 8px;
        }
        .message {
            background-color: #d4edda;
            color: #155724;
        }
        .error {
            background-color: #f8d7da;
            color: #721c24;
        }
        .verified-btn {
            margin-top: 20px;
            background-color: #28a745;
        }
        .resend-btn {
            margin-top: 10px;
            background-color: #ffc107;
        }
    </style>
</head>
<body>
    <div class="otp-box">
        <h2>Verify Your BRACU Email</h2>

        <?php if (isset($message)) echo "<div class='message'>$message</div>"; ?>
        <?php if (isset($error)) echo "<div class='error'>$error</div>"; ?>
        <?php if (isset($success)) {
            echo "<div class='message'>$success</div>";
            echo '<a href="profile.php"><button class="verified-btn">Go to Profile</button></a>';
        } else { ?>
        <form method="POST">
            <input type="text" name="otp" placeholder="Enter the 6-digit OTP" required>
            <br>
            <button type="submit">Verify</button>
        </form>
        <form method="POST">
            <button type="submit" class="resend-btn" name="resend_otp">Resend OTP</button>
        </form>
        <?php } ?>
    </div>
</body>
</html>
