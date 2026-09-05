<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$userId = $_SESSION['user_id'];
$message = "";

// Save budget
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $budget = floatval($_POST['monthly_budget']);

    if ($budget > 0) {

        $stmt = $conn->prepare(
            "UPDATE users SET monthly_budget = ? WHERE id = ?"
        );

        $stmt->bind_param("di", $budget, $userId);

        if ($stmt->execute()) {
            $message = "Monthly budget saved successfully!";
        }
    }
}

// Get current budget
$stmt = $conn->prepare(
    "SELECT monthly_budget FROM users WHERE id = ?"
);

$stmt->bind_param("i", $userId);
$stmt->execute();

$result = $stmt->get_result();
$user = $result->fetch_assoc();

$currentBudget = $user['monthly_budget'] ?? 0;
// Get latest electricity bill
$stmt = $conn->prepare(
    "SELECT total_bill
     FROM consumption
     WHERE user_id = ?
     ORDER BY id DESC
     LIMIT 1"
);

$stmt->bind_param("i", $userId);
$stmt->execute();

$billResult = $stmt->get_result();
$latestBillData = $billResult->fetch_assoc();

$latestBill = $latestBillData['total_bill'] ?? 0;
?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Monthly Budget | PowerTrack</title>

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
}

.sidebar {
    position: fixed;
    left: 0;
    top: 0;
    width: 240px;
    height: 100vh;
    background: #17212b;
    padding: 25px 20px;
    border-right: 1px solid #2d3a45;
    overflow-y: auto;
}

.logo {
    font-size: 21px;
    font-weight: bold;
    color: #ffc107;
    margin-bottom: 40px;
}

.menu a {
    display: block;
    text-decoration: none;
    color: #b9c4cc;
    padding: 14px 15px;
    margin-bottom: 10px;
    border-radius: 8px;
}

.menu a:hover,
.menu .active {
    background: #ffc107;
    color: #111;
}

.main {
    margin-left: 240px;
    padding: 35px;
}

.header {
    margin-bottom: 30px;
}

.header h1 {
    margin-bottom: 8px;
}

.header p {
    color: #91a0aa;
}

.budget-card {
    max-width: 650px;
    background: #17212b;
    border: 1px solid #2b3944;
    border-radius: 15px;
    padding: 30px;
}

.current {
    background: #101820;
    padding: 20px;
    border-radius: 10px;
    margin-bottom: 25px;
}

.amount {
    color: #ffc107;
    font-size: 28px;
    font-weight: bold;
    margin-top: 8px;
}

label {
    display: block;
    margin-bottom: 10px;
}

input {
    width: 100%;
    background: #101820;
    color: white;
    border: 1px solid #3a4853;
    padding: 13px;
    border-radius: 8px;
    margin-bottom: 20px;
    font-size: 16px;
}

input:focus {
    outline: none;
    border-color: #ffc107;
}

button {
    background: #ffc107;
    color: #111;
    border: none;
    padding: 13px 22px;
    border-radius: 8px;
    font-size: 16px;
    font-weight: bold;
    cursor: pointer;
}

.message {
    background: #163b31;
    color: #55e6b1;
    padding: 15px;
    border-radius: 8px;
    margin-bottom: 20px;
}

@media(max-width: 700px) {

    .sidebar {
        position: static;
        width: 100%;
        height: auto;
    }

    .main {
        margin-left: 0;
        padding: 20px;
    }
}

</style>

</head>

<body>
<?php include 'sidebar.php'; ?>


<div class="main">

    <div class="header">

        <h1>🎯 Monthly Electricity Budget</h1>

        <p>
            Set the maximum amount you want to spend on
            electricity each month.
        </p>

    </div>


    <?php if ($message != "") { ?>

        <div class="message">
            <?php echo htmlspecialchars($message); ?>
        </div>

    <?php } ?>


    <div class="budget-card">

        <div class="current">

            Current Monthly Budget

            <div class="amount">
                ₹<?php echo number_format($currentBudget, 2); ?>
            </div>

        </div>

        <div style="
    background: #101820;
    padding: 20px;
    border-radius: 10px;
    margin-bottom: 25px;
">

    <h3 style="margin-bottom: 15px;">
        📊 Budget Status
    </h3>

    <p style="margin-bottom: 10px;">
        Latest Bill:
        <strong>
            ₹<?php echo number_format($latestBill, 2); ?>
        </strong>
    </p>

    <?php if ($currentBudget <= 0) { ?>

        <p style="color: #91a0aa;">
            Set a monthly budget to check your budget status.
        </p>

    <?php } elseif ($latestBill <= $currentBudget) { ?>

        <p style="color: #36d399; font-weight: bold;">
            ✅ Within Budget
        </p>

        <p style="margin-top: 10px;">
            Remaining:
            ₹<?php
                echo number_format(
                    $currentBudget - $latestBill,
                    2
                );
            ?>
        </p>

    <?php } else { ?>

        <p style="color: #ff6464; font-weight: bold;">
            ⚠️ Over Budget
        </p>

        <p style="margin-top: 10px;">
            Exceeded By:
            ₹<?php
                echo number_format(
                    $latestBill - $currentBudget,
                    2
                );
            ?>
        </p>

    <?php } ?>

</div>
        <form method="POST">

            <label>
                Enter Monthly Budget (₹)
            </label>

            <input
                type="number"
                name="monthly_budget"
                min="1"
                step="0.01"
                placeholder="Example: 1000"
                required
            >

            <button type="submit">
                💾 Save Budget
            </button>

        </form>

    </div>

</div>

</body>
</html>