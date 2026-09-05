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

// Get only users who activated Gruha Jyothi
$result = $conn->query(
    "SELECT id, name, email
     FROM users
     WHERE gruha_jyothi = 1
     ORDER BY id DESC"
);
?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Gruha Jyothi Users | PowerTrack</title>

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

/* Card */

.summary {
    background: #17212b;
    border: 1px solid #2b3944;
    border-radius: 14px;
    padding: 20px;
    margin-bottom: 25px;
}

.summary span {
    color: #36d399;
    font-size: 25px;
    font-weight: bold;
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

.status {
    color: #36d399;
    font-weight: bold;
}

.empty {
    text-align: center;
    color: #91a0aa;
    padding: 35px;
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

        <a href="admin_consumption.php">
            📊 Consumption Records
        </a>

        <a href="admin_gruha_jyothi.php" class="active">
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

        <h1>🏡 Gruha Jyothi Users</h1>

        <p>
            Users who have activated the Gruha Jyothi option.
        </p>

    </div>


    <div class="summary">

        Total Activated Users:
        <span>
            <?php echo $result ? $result->num_rows : 0; ?>
        </span>

    </div>


    <div class="panel">

        <?php if ($result && $result->num_rows > 0) { ?>

            <table>

                <thead>

                    <tr>
                        <th>User ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Scheme Status</th>
                    </tr>

                </thead>

                <tbody>

                <?php while ($user = $result->fetch_assoc()) { ?>

                    <tr>

                        <td>
                            <?php echo (int)$user['id']; ?>
                        </td>

                        <td>
                            <?php echo htmlspecialchars($user['name']); ?>
                        </td>

                        <td>
                            <?php echo htmlspecialchars($user['email']); ?>
                        </td>

                        <td>
                            <span class="status">
                                ✅ Activated
                            </span>
                        </td>

                    </tr>

                <?php } ?>

                </tbody>

            </table>

        <?php } else { ?>

            <div class="empty">
                No users have activated Gruha Jyothi.
            </div>

        <?php } ?>

    </div>

</div>

</body>
</html>