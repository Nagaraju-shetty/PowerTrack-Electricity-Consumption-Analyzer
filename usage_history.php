<?php
session_start();
include 'db.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check whether user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$userId = $_SESSION['user_id'];

// Get all consumption records
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
?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Usage History | PowerTrack</title>

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

/* Sidebar */

.sidebar {
    position: fixed;
    top: 0;
    left: 0;
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

/* Main */

.main {
    margin-left: 240px;
    padding: 35px;
}

.header {
    margin-bottom: 30px;
}

.header h1 {
    font-size: 30px;
    margin-bottom: 8px;
}

.header p {
    color: #91a0aa;
}

/* Table */

.panel {
    background: #17212b;
    border: 1px solid #2b3944;
    border-radius: 14px;
    padding: 25px;
    overflow-x: auto;
}

table {
    width: 100%;
    border-collapse: collapse;
}

th {
    text-align: left;
    padding: 15px;
    color: #91a0aa;
    border-bottom: 1px solid #34434a;
}

td {
    padding: 16px 15px;
    border-bottom: 1px solid #293640;
}

tr:hover {
    background: #1d2a35;
}

.bill {
    color: #ffc107;
    font-weight: bold;
}

.empty {
    text-align: center;
    color: #91a0aa;
    padding: 40px;
}

.back-btn {
    display: inline-block;
    margin-top: 25px;
    background: #ffc107;
    color: #111;
    padding: 12px 20px;
    text-decoration: none;
    border-radius: 8px;
    font-weight: bold;
}

@media(max-width: 700px) {

    .sidebar {
        display: none;
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

        <h1>📊 Usage History</h1>

        <p>
            View your previous electricity consumption records.
        </p>

    </div>


    <div class="panel">

        <?php if ($result->num_rows > 0) { ?>

        <table>

            <thead>

                <tr>
                    <th>Month</th>
                    <th>Consumption</th>
                    <th>Rate / Unit</th>
                    <th>Bill Amount</th>
                    <th>Gruha Jyothi</th>
                    <th>Action</th>
                </tr>

            </thead>

            <tbody>

            <?php while ($row = $result->fetch_assoc()) { ?>

                <tr>

                    <td>
                        <?php echo htmlspecialchars($row['month']); ?>
                        <?php echo (int)$row['year']; ?>
                    </td>

                    <td>
                        <?php echo number_format($row['units'], 2); ?>
                        kWh
                    </td>

                    <td>
                        ₹<?php echo number_format($row['rate'], 2); ?>
                    </td>

                    <td class="bill">
                        ₹<?php echo number_format($row['total_bill'], 2); ?>
                    </td>
                    <td>
    <?php if ($row['units'] <= 200) { ?>

        <span style="color: #36d399; font-weight: bold;">
            ✅ Eligible
        </span>

    <?php } else { ?>

        <span style="color: #ff6464; font-weight: bold;">
            ❌ Not Eligible
        </span>

    <?php } ?>
</td>
       <td>

    <a href="edit_consumption.php?id=<?php echo $row['id']; ?>"
       style="color: #ffc107; text-decoration: none; font-weight: bold; margin-right: 15px;">
        ✏️ Edit
    </a>
    <a href="print_bill.php?id=<?php echo $row['id']; ?>"
   style="color: #36d399; text-decoration: none; font-weight: bold; margin-right: 15px;">
    🖨️ Print Bill
</a>

    <a href="delete_consumption.php?id=<?php echo $row['id']; ?>"
       onclick="return confirm('Are you sure you want to delete this record?');"
       style="color: #ff6464; text-decoration: none; font-weight: bold;">
        🗑️ Delete
    </a>

</td>

                </tr>

            <?php } ?>

            </tbody>

        </table>

        <?php } else { ?>

            <div class="empty">
                No electricity consumption records found.
            </div>

        <?php } ?>

    </div>

    <a href="dashboard.php" class="back-btn">
        ← Back to Dashboard
    </a>

</div>

</body>
</html>