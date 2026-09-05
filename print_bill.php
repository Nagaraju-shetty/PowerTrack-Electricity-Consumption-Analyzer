    <?php
session_start();
include 'db.php';
if (isset($_GET['payment']) && $_GET['payment'] == "success") {
    echo "<script>
    window.onload = function() {
        alert('✅ Payment completed successfully!');
    };
    </script>";
}   

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$userId = $_SESSION['user_id'];

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid bill.");
}

$billId = (int)$_GET['id'];

// Get selected bill only for logged-in user
$stmt = $conn->prepare(
    "SELECT c.*, u.name, u.email, u.gruha_jyothi
     FROM consumption c
     INNER JOIN users u ON c.user_id = u.id
     WHERE c.id = ? AND c.user_id = ?"
);

$stmt->bind_param("ii", $billId, $userId);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows != 1) {
    die("Bill not found.");
}

$bill = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Electricity Bill | PowerTrack</title>

<style>

* {
    box-sizing: border-box;
}

body {
    font-family: Arial, sans-serif;
    background: #0f1720;
    margin: 0;
    padding: 40px;
    color: white;
}

.bill-container {
    max-width: 700px;
    margin: auto;
    background: #17212b;
    border: 1px solid #2d3a45;
    border-radius: 15px;
    padding: 35px;
}

.logo {
    text-align: center;
    color: #ffc107;
    font-size: 25px;
    font-weight: bold;
}

h1 {
    text-align: center;
    margin-bottom: 5px;
}

.subtitle {
    text-align: center;
    color: #91a0aa;
    margin-bottom: 30px;
}

.row {
    display: flex;
    justify-content: space-between;
    padding: 15px 0;
    border-bottom: 1px solid #2d3a45;
}

.label {
    color: #91a0aa;
}

.value {
    font-weight: bold;
}

.total {
    margin-top: 25px;
    background: #101820;
    padding: 20px;
    border-radius: 10px;
    text-align: center;
}

.total h2 {
    color: #ffc107;
    font-size: 30px;
    margin: 10px 0 0;
}

.buttons {
    text-align: center;
    margin-top: 30px;
}

button,
.back {
    display: inline-block;
    border: none;
    border-radius: 8px;
    padding: 12px 20px;
    margin: 5px;
    font-size: 15px;
    font-weight: bold;
    cursor: pointer;
    text-decoration: none;
}

button {
    background: #ffc107;
    color: #111;
}

.back {
    background: #2d3a45;
    color: white;
}

/* Print version */

@media print {

    body {
        background: white;
        color: black;
        padding: 0;
    }

    .bill-container {
        border: 1px solid #999;
        background: white;
        color: black;
        box-shadow: none;
    }

    .label,
    .subtitle {
        color: #555;
    }

    .total {
        background: #eee;
    }

    .buttons {
        display: none;
    }
}

</style>

</head>

<body>

<div class="bill-container">

    <div class="logo">
        ⚡ PowerTrack
    </div>

    <h1>Electricity Bill</h1>

    <div class="subtitle">
        Electricity Consumption Analyzer
    </div>
<div class="row">
    <span class="label">Bill Number</span>

    <span class="value">
        PT-<?php echo str_pad($bill['id'], 5, '0', STR_PAD_LEFT); ?>
    </span>
</div>
<div class="row">
    <span class="label">Generated Date</span>

    <span class="value">
        <?php echo date("d-m-Y"); ?>
    </span>
</div>
    <div class="row">
        <span class="label">Customer Name</span>

        <span class="value">
            <?php echo htmlspecialchars($bill['name']); ?>
        </span>
    </div>

    <div class="row">
        <span class="label">Email</span>

        <span class="value">
            <?php echo htmlspecialchars($bill['email']); ?>
        </span>
    </div>

    <div class="row">
        <span class="label">Billing Month</span>

        <span class="value">
            <?php
            echo htmlspecialchars($bill['month'])
            . " "
            . (int)$bill['year'];
            ?>
        </span>
    </div>

    <div class="row">
        <span class="label">Units Consumed</span>

        <span class="value">
            <?php echo number_format($bill['units'], 2); ?>
            units
        </span>
    </div>

    <div class="row">
        <span class="label">Rate Per Unit</span>

        <span class="value">
            ₹<?php echo number_format($bill['rate'], 2); ?>
        </span>
    </div>

    <div class="row">
        <span class="label">Gruha Jyothi</span>

        <span class="value">

            <?php
            if ($bill['gruha_jyothi'] == 1 &&
                $bill['units'] <= 200) {

                echo "✅ Benefit Applied";

            } elseif ($bill['gruha_jyothi'] == 1) {

                echo "⚠️ Above 200 Units";

            } else {

                echo "❌ Not Activated";
            }
            ?>

        </span>
    </div>
<div class="row">
    <span class="label">Bill Status</span>

    <span class="value">

        <?php
        if ($bill['gruha_jyothi'] == 1 && $bill['units'] <= 200) {

            echo "✅ ₹0.00 - Gruha Jyothi Benefit";

        } elseif ($bill['total_bill'] > 0) {

            echo "💳 Amount Payable";

        } else {

            echo "✅ No Amount Payable";
        }
        ?>

    </span>
</div>
<div class="row">
    <span class="label">Bill Status</span>

    <span class="value">

        <?php
        if ($bill['gruha_jyothi'] == 1 && $bill['units'] <= 200) {

            echo "✅ ₹0.00 - Gruha Jyothi Benefit";

        } elseif ($bill['total_bill'] > 0) {

            echo "💳 Amount Payable";

        } else {

            echo "✅ No Amount Payable";
        }
        ?>

    </span>
</div>


<!-- ADD USAGE STATUS HERE -->

<div class="row">
    <span class="label">Usage Status</span>

    <span class="value">

        <?php
        if ($bill['units'] > 200) {

            echo "🔴 Very High Usage";

        } elseif ($bill['units'] > 150) {

            echo "⚠️ High Usage";

        } else {

            echo "✅ Normal Usage";
        }
        ?>

    </span>
</div>
    <div class="total">

        Final Bill Amount

        <h2>
            ₹<?php echo number_format($bill['total_bill'], 2); ?>
        </h2>

    </div>

<div style="
    margin-top: 20px;
    background: #101820;
    padding: 18px;
    border-radius: 10px;
">

    <strong>💡 Energy Saving Tip</strong>

    <p style="margin-bottom: 0; margin-top: 10px; line-height: 1.6;">

        <?php
        if ($bill['units'] > 200) {

            echo "High electricity consumption detected. Try to reduce the use of heavy appliances and switch off unused devices.";

        } elseif ($bill['units'] > 150) {

            echo "Your electricity usage is slightly high. Reduce unnecessary appliance usage to save more energy.";

        } else {

            echo "Great! Your electricity consumption is under control. Continue using energy efficiently.";
        }
        ?>

    </p>

</div>

<div class="buttons">

    <button onclick="window.print()">
        🖨️ Print / Save as PDF
    </button>

<?php if($bill['payment_status'] != "Paid") { ?>

<a href="payment.php?id=<?php echo $bill['id']; ?>">
    <button>💳 Pay Now</button>
</a>

<?php } else { ?>

<button style="background:green;color:white;cursor:default;">
    ✅ Paid
</button>

<?php } ?>
<a href="usage_history.php" class="back">
    ← Back
</a>
</div>

</body>
</html>