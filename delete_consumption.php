<?php
session_start();
include 'db.php';

// Check login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Check whether record ID is provided
if (!isset($_GET['id'])) {
    header("Location: usage_history.php");
    exit();
}

$userId = $_SESSION['user_id'];
$id = (int)$_GET['id'];

// Delete only the logged-in user's record
$stmt = $conn->prepare(
    "DELETE FROM consumption
     WHERE id = ? AND user_id = ?"
);

$stmt->bind_param("ii", $id, $userId);
$stmt->execute();

// Return to Usage History
header("Location: usage_history.php");
exit();
?>