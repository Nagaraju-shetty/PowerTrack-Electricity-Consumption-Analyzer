<?php
session_start();
include 'db.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$userId = $_SESSION['user_id'];

// Check record ID
if (!isset($_GET['id'])) {
    header("Location: usage_history.php");
    exit();
}

$id = (int)$_GET['id'];

// Get selected record
$stmt = $conn->prepare(
    "SELECT * FROM consumption
     WHERE id = ? AND user_id = ?"
);

$stmt->bind_param("ii", $id, $userId);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows == 0) {
    die("Record not found.");
}

$record = $result->fetch_assoc();


// Update record
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $month = $_POST['month'];
    $year = (int)$_POST['year'];
    $units = (float)$_POST['units'];
    $rate = (float)$_POST['rate'];

    // Recalculate bill
    // Gruha Jyothi simplified project rule
// Check whether Gruha Jyothi is activated
$scheme = $conn->prepare(
    "SELECT gruha_jyothi FROM users WHERE id = ?"
);

$scheme->bind_param("i", $userId);
$scheme->execute();

$schemeResult = $scheme->get_result();
$schemeUser = $schemeResult->fetch_assoc();

$gruhaJyothiActive =
    $schemeUser && $schemeUser['gruha_jyothi'] == 1;

// Calculate bill
if ($gruhaJyothiActive && $units <= 200) {
    $totalBill = 0;
} else {
    $totalBill = $units * $rate;
}

    $update = $conn->prepare(
        "UPDATE consumption
         SET month = ?,
             year = ?,
             units = ?,
             rate = ?,
             total_bill = ?
         WHERE id = ? AND user_id = ?"
    );

    $update->bind_param(
        "sidddii",
        $month,
        $year,
        $units,
        $rate,
        $totalBill,
        $id,
        $userId
    );

    if ($update->execute()) {

        header("Location: usage_history.php");
        exit();

    } else {

        $message = "Unable to update record.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
content="width=device-width, initial-scale=1.0">

<title>Edit Consumption | PowerTrack</title>

<style>

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: Arial, sans-serif;

    background: #0f1720;

    color: white;

    min-height: 100vh;

    display: flex;

    justify-content: center;

    align-items: center;
}

.container {
    width: 430px;

    background: #17212b;

    border: 1px solid #2d3a45;

    border-radius: 15px;

    padding: 35px;

    box-shadow: 0 10px 35px rgba(0,0,0,0.4);
}

.logo {
    text-align: center;

    font-size: 35px;

    margin-bottom: 10px;
}

h1 {
    text-align: center;

    margin-bottom: 8px;
}

.subtitle {
    text-align: center;

    color: #91a0aa;

    margin-bottom: 30px;
}

label {
    display: block;

    margin-bottom: 8px;

    color: #c8d1d7;
}

input,
select {
    width: 100%;

    padding: 12px;

    margin-bottom: 20px;

    border-radius: 7px;

    border: 1px solid #3a4853;

    background: #101820;

    color: white;

    outline: none;
}

input:focus,
select:focus {
    border-color: #ffc107;
}

button {
    width: 100%;

    padding: 13px;

    border: none;

    border-radius: 7px;

    background: #ffc107;

    color: #111;

    font-size: 16px;

    font-weight: bold;

    cursor: pointer;
}

button:hover {
    background: #e5ad00;
}

.cancel {
    display: block;

    text-align: center;

    margin-top: 20px;

    color: #ffc107;

    text-decoration: none;
}

.cancel:hover {
    text-decoration: underline;
}

.error {
    background: #472020;

    color: #ff9b9b;

    padding: 12px;

    border-radius: 7px;

    margin-bottom: 20px;
}

</style>

</head>

<body>

<div class="container">

    <div class="logo">
        ✏️
    </div>

    <h1>Edit Consumption</h1>

    <p class="subtitle">
        Update your electricity consumption record
    </p>


    <?php if (isset($message)) { ?>

        <div class="error">

            <?php
            echo htmlspecialchars($message);
            ?>

        </div>

    <?php } ?>


    <form method="POST">


        <label>Month</label>

        <select name="month" required>

            <?php

            $months = [
                "January",
                "February",
                "March",
                "April",
                "May",
                "June",
                "July",
                "August",
                "September",
                "October",
                "November",
                "December"
            ];

            foreach ($months as $month) {

                $selected =
                    ($record['month'] == $month)
                    ? "selected"
                    : "";

                echo "<option $selected>$month</option>";
            }

            ?>

        </select>


        <label>Year</label>

        <input
            type="number"
            name="year"
            value="<?php
            echo (int)$record['year'];
            ?>"
            required
        >


        <label>Electricity Consumed (kWh)</label>

        <input
            type="number"
            name="units"
            step="0.01"
            min="0"
            value="<?php
            echo htmlspecialchars($record['units']);
            ?>"
            required
        >


        <label>Rate Per Unit (₹)</label>

        <input
            type="number"
            name="rate"
            step="0.01"
            min="0"
            value="<?php
            echo htmlspecialchars($record['rate']);
            ?>"
            required
        >


        <button type="submit">
            💾 Update Consumption
        </button>

    </form>


    <a href="usage_history.php"
       class="cancel">

        ← Cancel and Go Back

    </a>

</div>

</body>

</html>