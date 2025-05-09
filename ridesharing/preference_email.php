<?php
session_start();
require_once("DBConnect.php");

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

function sendMail($to, $subject, $body) {
    $mail = new PHPMailer(true);
    try {
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'nszarif522@gmail.com';
                $mail->Password = '';
                $mail->SMTPSecure = 'tls';
                $mail->Port = 587;

        $mail->setFrom('your-email@gmail.com', 'RideShare System');
        $mail->addAddress($to);

        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $body;

        $mail->send();
    } catch (Exception $e) {
        error_log("Mailer Error: " . $mail->ErrorInfo);
    }
}

if (!isset($_SESSION['student_id'])) {
    header("Location: login.php");
    exit();
}

$student_id = $_SESSION['student_id'];

$area = $_GET['area'] ?? '';
$start_date = $_GET['start_date'] ?? '';
$end_date = $_GET['end_date'] ?? '';
$timeslots = $_GET['timeslots'] ?? '';
$preferred_gender = $_GET['preferred_gender'] ?? '';

$query = "SELECT * FROM ride_cards r 
          JOIN users u ON r.Student_id = u.Student_id
          WHERE r.Pickup_Area = ? 
          AND r.Pickup_date BETWEEN ? AND ? 
          AND FIND_IN_SET(r.Timeslot, ?) 
          AND (? = 'Any' OR u.Gender = ?)";
$stmt = $conn->prepare($query);
$stmt->bind_param("ssssss", $area, $start_date, $end_date, $timeslots, $preferred_gender, $preferred_gender);
$stmt->execute();
$matching_rides = $stmt->get_result();

if ($matching_rides->num_rows > 0) {
    $user_stmt = $conn->prepare("SELECT name, brac_mail FROM users WHERE student_id = ?");
    $user_stmt->bind_param("i", $student_id);
    $user_stmt->execute();
    $user_result = $user_stmt->get_result()->fetch_assoc();
    $email = $user_result['brac_mail'];
    $name = $user_result['name'];

    $body = "Hi $name,<br><br>We found rides that match your preference:<br>";
    while ($ride = $matching_rides->fetch_assoc()) {
        $body .= "<br>• " . htmlspecialchars($ride['Pickup_Area']) . " at " . htmlspecialchars($ride['Pickup_time']) . " on " . htmlspecialchars($ride['Pickup_date']);
    }
    $body .= "<br><br>Visit your dashboard to learn more!";

    sendMail($email, "Ride Match Found!", $body);
}

header("Location: added_preferences.php?");
exit();
?>
