<?php
session_start();
include 'db.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Allow only admin
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

// Get all consumption records with user details
$sql = "
    SELECT 
        consumption.id,
        consumption.month,
        consumption.year,
        consumption.units,
        consumption.rate,
        consumption.total_bill,
        users.name,
        users.email,
        users.gruha_jyothi
    FROM consumption
    INNER JOIN users
        ON consumption.user_id = users.id
    ORDER BY consumption.year DESC, consumption.id DESC
";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Consumption Records | PowerTrack</title>

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
    left: 0;
    top: 0;

    width: 250px;
    height: 100vh;

    background: #17212b;
    border-right: 1px solid #2d3a45;

    padding: 25px 20px;
}

.logo {
    color: #ffc107;
    font-size: 21px;
    font-weight: bold;
    margin-bottom: 10px;
}

.admin-label {
    color: #91a0aa;
    font-size: 13px;
    margin-bottom: 40px;
}

.menu a {
    display: block;
    color: #b9c4cc;
    text-decoration: none;
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
    margin-top: 30px;
}

/* Main */

.main {
    margin-left: 250px;
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
    padding: 15px;
    text-align: left;
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

.eligible {
    color: #36d399;
    font-weight: bold;
}

.not-eligible {
    color: #ff6464;
    font-weight: bold;
}

.empty {
    text-align: center;
    padding: 35px;
    color: #91a0aa;
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

<div class="sidebar">

    <div class="logo">
        ⚡ PowerTrack
    </div>

    <div class="admin-label">
        ADMIN PANEL
    </div>

    <div class="menu">

        <a href="admin_dashboard.php">
            🏠 Dashboard
        </a>

        <a href="admin_users.php">
            👥 Manage Users
        </a>

        <a href="admin_consumption.php" class="active">
            📊 Consumption Records
        </a>

        <a href="admin_gruha_jyothi.php">
            🏡 Gruha Jyothi Users
        </a>

        <div class="logout">

            <a href="admin_logout.php">
                🚪 Admin Logout
            </a>

        </div>

    </div>

</div>


<div class="main">

    <div class="header">

        <h1>📊 Consumption Records</h1>

        <p>
            View electricity consumption records of all registered users.
        </p>

    </div>


    <div class="panel">

        <?php if ($result && $result->num_rows > 0) { ?>

        <table>

            <thead>

                <tr>
                    <th>User</th>
                    <th>Email</th>
                    <th>Month</th>
                    <th>Units</th>
                    <th>Rate</th>
                    <th>Bill</th>
                    <th>Gruha Jyothi</th>
                </tr>

            </thead>

            <tbody>

            <?php while ($row = $result->fetch_assoc()) { ?>

                <tr>

                    <td>
                        <?php echo htmlspecialchars($row['name']); ?>
                    </td>

                    <td>
                        <?php echo htmlspecialchars($row['email']); ?>
                    </td>

                    <td>
                        <?php
                        echo htmlspecialchars($row['month'])
                        . " "
                        . (int)$row['year'];
                        ?>
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

                        <?php
                        if (
                            $row['gruha_jyothi'] == 1 &&
                            $row['units'] <= 200
                        ) {
                        ?>

                            <span class="eligible">
                                ✅ Eligible
                            </span>

                        <?php } elseif ($row['gruha_jyothi'] == 1) { ?>

                            <span class="not-eligible">
                                ⚠️ Above 200 Units
                            </span>

                        <?php } else { ?>

                            <span class="not-eligible">
                                ❌ Not Activated
                            </span>

                        <?php } ?>

                    </td>

                </tr>

            <?php } ?>

            </tbody>

        </table>

        <?php } else { ?>

            <div class="empty">
                No consumption records found.
            </div>

        <?php } ?>

    </div>

</div>

</body>
</html>