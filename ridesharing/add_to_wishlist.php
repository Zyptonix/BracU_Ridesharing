<?php
session_start();
require_once('DBconnect.php');

if (!isset($_SESSION['student_id']) || $_SESSION['P_flag'] != 1) {
    die("Access denied. Only passengers can add to wishlist.");
}

$passenger_id = $_SESSION['student_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['card_no'])) {
    $card_no = intval($_POST['card_no']);

    $check = $conn->prepare("SELECT * FROM wishlist WHERE Passenger_id = ? AND Card_no = ?");
    $check->bind_param("ii", $passenger_id, $card_no);
    $check->execute();
    $result = $check->get_result();

    if ($result->num_rows === 0) {
        $stmt = $conn->prepare("INSERT INTO wishlist (Passenger_id, Card_no) VALUES (?, ?)");
        $stmt->bind_param("ii", $passenger_id, $card_no);
        $stmt->execute();
    }
}

$redirect = strtok($_SERVER['HTTP_REFERER'], '?') . "?wishlist=added";
header("Location: $redirect");
exit;
