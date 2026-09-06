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

$search = "";

if (isset($_GET['search'])) {
    $search = trim($_GET['search']);
}

if ($search != "") {

    $stmt = $conn->prepare(
        "SELECT id, name, email, gruha_jyothi
         FROM users
         WHERE name LIKE ? OR email LIKE ?
         ORDER BY id DESC"
    );

    $keyword = "%" . $search . "%";

    $stmt->bind_param("ss", $keyword, $keyword);
    $stmt->execute();

    $result = $stmt->get_result();

} else {

    $result = $conn->query(
        "SELECT id, name, email, gruha_jyothi
         FROM users
         ORDER BY id DESC"
    );

}
?>

<!DOCTYPE html>
<html lang="en">

<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Manage Users | PowerTrack</title>

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

.active-status {
    color: #36d399;
    font-weight: bold;
}

.inactive-status {
    color: #ff6464;
    font-weight: bold;
}

.empty {
    text-align: center;
    color: #91a0aa;
    padding: 30px;
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

        <a href="admin_users.php" class="active">
            👥 Manage Users
        </a>

        <a href="admin_consumption.php">
    📊 Consumption Records
</a>
        <a href="admin_gruha_jyoti.php">
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

        <h1>👥 Manage Users</h1>

        <p>
            View all registered PowerTrack users.
        </p>

    </div>

<form method="GET" style="margin-bottom:20px;">

    <input
        type="text"
        name="search"
        placeholder="🔍 Search by Name or Email"
        value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>"
        style="
            width:350px;
            padding:12px;
            border-radius:8px;
            border:1px solid #444;
            background:#17212b;
            color:white;
        "
    >

    <button
        type="submit"
        style="
            padding:12px 20px;
            background:#ffc107;
            border:none;
            border-radius:8px;
            font-weight:bold;
            cursor:pointer;
        "
    >
        Search
    </button>

</form>
    <div class="panel">

        <?php if ($result && $result->num_rows > 0) { ?>

            <table>

                <thead>

                    <tr>
                        <th>User ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Gruha Jyothi</th>
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

                            <?php if ($user['gruha_jyothi'] == 1) { ?>

                                <span class="active-status">
                                    ✅ Activated
                                </span>

                            <?php } else { ?>

                                <span class="inactive-status">
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
                No registered users found.
            </div>

        <?php } ?>

    </div>

</div>

</body>
</html>