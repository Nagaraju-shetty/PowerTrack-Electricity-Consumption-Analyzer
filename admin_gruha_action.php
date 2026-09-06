<?php

session_start();
include 'db.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);


// Check admin login
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}


// Check required parameters
if (!isset($_GET['id']) || !isset($_GET['action'])) {
    header("Location: admin_dashboard.php");
    exit();
}


$userId = (int) $_GET['id'];
$action = $_GET['action'];


// Decide new status
if ($action == "approve") {

    $newStatus = "Approved";

} elseif ($action == "reject") {

    $newStatus = "Rejected";

} elseif ($action == "remove") {

    $newStatus = "Not Applied";

} else {

    header("Location: admin_dashboard.php");
    exit();
}


// Update user's Gruha Jyothi status
$stmt = $conn->prepare(
    "UPDATE users
     SET gruha_jyothi_status = ?
     WHERE id = ?"
);

$stmt->bind_param("si", $newStatus, $userId);


if ($stmt->execute()) {

    header("Location: admin_dashboard.php");
    exit();

} else {

    echo "Error updating Gruha Jyothi request: "
         . htmlspecialchars($stmt->error);
}

?>