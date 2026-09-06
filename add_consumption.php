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

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $user_id = $_SESSION['user_id'];
    $month = $_POST['month'];
    $year = $_POST['year'];
    $units = $_POST['units'];
    $rate = $_POST['rate'];
// Check Gruha Jyothi approval status
$scheme = $conn->prepare(
    "SELECT gruha_jyothi_status
     FROM users
     WHERE id = ?"
);

$scheme->bind_param("i", $user_id);
$scheme->execute();

$schemeResult = $scheme->get_result();
$schemeUser = $schemeResult->fetch_assoc();

$gruhaJyothiApproved =
    $schemeUser &&
    $schemeUser['gruha_jyothi_status'] == "Approved";


// Calculate bill
// Gruha Jyothi applies only after admin approval
// and when consumption is 200 units or less.

if ($gruhaJyothiApproved && $units <= 200) {

    $total_bill = 0;

} else {

    $total_bill = $units * $rate;

}
    $stmt = $conn->prepare(
        "INSERT INTO consumption
        (user_id, month, year, units, rate, total_bill)
        VALUES (?, ?, ?, ?, ?, ?)"
    );

    $stmt->bind_param(
        "isiddd",
        $user_id,
        $month,
        $year,
        $units,
        $rate,
        $total_bill
    );

    if ($stmt->execute()) {
        $message = "Electricity consumption added successfully!";
    } else {
        $message = "Error adding consumption.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
content="width=device-width, initial-scale=1.0">

<title>Add Consumption</title>

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

    display: flex;
    justify-content: center;
    align-items: center;

    min-height: 100vh;
}

.container {
    width: 420px;

    background: #17212b;

    padding: 35px;

    border-radius: 15px;

    border: 1px solid #2d3a45;

    box-shadow: 0 10px 35px rgba(0,0,0,0.4);
}

.logo {
    text-align: center;
    font-size: 30px;
    margin-bottom: 10px;
}

h1 {
    text-align: center;
    margin-bottom: 10px;
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

.message {
    background: #163b31;

    color: #4ee0ad;

    padding: 12px;

    border-radius: 7px;

    margin-bottom: 20px;

    text-align: center;
}

.back {
    display: block;

    text-align: center;

    margin-top: 20px;

    color: #ffc107;

    text-decoration: none;
}

.back:hover {
    text-decoration: underline;
}

</style>

</head>

<body>

<div class="container">

    <div class="logo">
        ⚡
    </div>

    <h1>Add Consumption</h1>

    <p class="subtitle">
        Enter your monthly electricity usage
    </p>

    <?php if ($message != "") { ?>

        <div class="message">
            <?php echo htmlspecialchars($message); ?>
        </div>

    <?php } ?>


    <form method="POST">

        <label>Month</label>

        <select name="month" required>

            <option value="">Select Month</option>

            <option>January</option>
            <option>February</option>
            <option>March</option>
            <option>April</option>
            <option>May</option>
            <option>June</option>
            <option>July</option>
            <option>August</option>
            <option>September</option>
            <option>October</option>
            <option>November</option>
            <option>December</option>

        </select>


        <label>Year</label>

        <input
            type="number"
            name="year"
            value="<?php echo date('Y'); ?>"
            required
        >


        <label>Electricity Consumed (kWh)</label>

        <input
            type="number"
            name="units"
            step="0.01"
            min="0"
            placeholder="Example: 250"
            required
        >


        <label>Rate Per Unit (₹)</label>

        <input
            type="number"
            name="rate"
            step="0.01"
            min="0"
            placeholder="Example: 7.50"
            required
        >


        <button type="submit">
            ⚡ Save Consumption
        </button>

    </form>


    <a href="dashboard.php" class="back">
        ← Back to Dashboard
    </a>

</div>

</body>
</html>