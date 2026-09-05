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
$userName = $_SESSION['user_name'] ?? 'User';

// Get user's consumption records
$stmt = $conn->prepare(
    "SELECT * FROM consumption
     WHERE user_id = ?
     ORDER BY year DESC,
     FIELD(month,
     'December','November','October','September',
     'August','July','June','May','April',
     'March','February','January')"
);

$stmt->bind_param("i", $userId);
$stmt->execute();

$result = $stmt->get_result();

$records = [];

while ($row = $result->fetch_assoc()) {
    $records[] = $row;
}

// Calculate bill statistics
$totalBill = 0;
$highestBill = 0;
$lowestBill = 0;
$averageBill = 0;

if (count($records) > 0) {

    $bills = [];

    foreach ($records as $record) {
        $bill = (float)$record['total_bill'];

        $totalBill += $bill;
        $bills[] = $bill;
    }

    $highestBill = max($bills);
    $lowestBill = min($bills);
    $averageBill = $totalBill / count($records);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
content="width=device-width, initial-scale=1.0">

<title>Bill Analysis | PowerTrack</title>

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
}

.logo {
    font-size: 21px;
    font-weight: bold;

    color: #ffc107;

    margin-bottom: 45px;
}

.menu a {
    display: block;

    text-decoration: none;

    color: #b9c4cc;

    padding: 14px 15px;

    margin-bottom: 10px;

    border-radius: 8px;

    transition: 0.3s;
}

.menu a:hover,
.menu .active {
    background: #ffc107;
    color: #111;
}

.logout {
    position: absolute;
    bottom: 25px;
    width: 200px;
}

.main {
    margin-left: 240px;
    padding: 35px;
}

.header {
    margin-bottom: 35px;
}

.header h1 {
    font-size: 30px;
    margin-bottom: 8px;
}

.header p {
    color: #91a0aa;
}

.cards {
    display: grid;

    grid-template-columns:
    repeat(4, 1fr);

    gap: 20px;

    margin-bottom: 30px;
}

.card {
    background: #17212b;

    padding: 23px;

    border-radius: 14px;

    border: 1px solid #2b3944;

    transition: 0.3s;
}

.card:hover {
    transform: translateY(-5px);
    border-color: #ffc107;
}

.card .icon {
    font-size: 28px;
    margin-bottom: 15px;
}

.card p {
    color: #91a0aa;

    font-size: 13px;

    margin-bottom: 10px;
}

.card h2 {
    font-size: 25px;
}

.panel {
    background: #17212b;

    border: 1px solid #2b3944;

    border-radius: 14px;

    padding: 25px;
}

.panel h2 {
    margin-bottom: 20px;
}

table {
    width: 100%;
    border-collapse: collapse;
}

th {
    text-align: left;

    padding: 13px 10px;

    color: #91a0aa;

    border-bottom: 1px solid #34434a;
}

td {
    padding: 15px 10px;

    border-bottom: 1px solid #293640;
}

.empty {
    text-align: center;

    padding: 40px;

    color: #91a0aa;
}

@media(max-width: 1000px) {

    .cards {
        grid-template-columns:
        repeat(2, 1fr);
    }
}

@media(max-width: 700px) {

    .sidebar {
        display: none;
    }

    .main {
        margin-left: 0;
        padding: 20px;
    }

    .cards {
        grid-template-columns: 1fr;
    }
}

</style>

</head>

<body>

<?php include 'sidebar.php'; ?>

<div class="main">

    <div class="header">

        <h1>💰 Bill Analysis</h1>

        <p>
            View and analyze your electricity
            bill expenses.
        </p>

    </div>


    <div class="cards">

        <div class="card">

            <div class="icon">💰</div>

            <p>TOTAL BILL</p>

            <h2>
                ₹<?php echo number_format($totalBill, 2); ?>
            </h2>

        </div>


        <div class="card">

            <div class="icon">📊</div>

            <p>AVERAGE BILL</p>

            <h2>
                ₹<?php echo number_format($averageBill, 2); ?>
            </h2>

        </div>


        <div class="card">

            <div class="icon">⬆️</div>

            <p>HIGHEST BILL</p>

            <h2>
                ₹<?php echo number_format($highestBill, 2); ?>
            </h2>

        </div>


        <div class="card">

            <div class="icon">⬇️</div>

            <p>LOWEST BILL</p>

            <h2>
                ₹<?php echo number_format($lowestBill, 2); ?>
            </h2>

        </div>

    </div>


    <div class="panel">

        <h2>📋 Monthly Bill Details</h2>

        <?php if (count($records) > 0) { ?>

            <table>

                <thead>

                    <tr>
                        <th>Month</th>
                        <th>Consumption</th>
                        <th>Rate</th>
                        <th>Bill Amount</th>
                    </tr>

                </thead>

                <tbody>

                <?php foreach ($records as $record) { ?>

                    <tr>

                        <td>
                            <?php
                            echo htmlspecialchars($record['month']);
                            ?>
                            <?php
                            echo (int)$record['year'];
                            ?>
                        </td>

                        <td>
                            <?php
                            echo number_format(
                                $record['units'],
                                2
                            );
                            ?>
                            kWh
                        </td>

                        <td>
                            ₹<?php
                            echo number_format(
                                $record['rate'],
                                2
                            );
                            ?>
                        </td>

                        <td>
                            ₹<?php
                            echo number_format(
                                $record['total_bill'],
                                2
                            );
                            ?>
                        </td>

                    </tr>

                <?php } ?>

                </tbody>

            </table>

        <?php } else { ?>

            <div class="empty">
                No bill records available.
            </div>

        <?php } ?>

    </div>

</div>

</body>
</html>