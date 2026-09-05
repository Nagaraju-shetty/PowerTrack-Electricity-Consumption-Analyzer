<?php
session_start();
include 'db.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Protect admin page
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

$adminUsername = $_SESSION['admin_username'] ?? 'Admin';


// Total users
$result = $conn->query(
    "SELECT COUNT(*) AS total FROM users"
);
$totalUsers = $result->fetch_assoc()['total'];


// Total consumption records
$result = $conn->query(
    "SELECT COUNT(*) AS total FROM consumption"
);
$totalRecords = $result->fetch_assoc()['total'];


// Approved Gruha Jyothi users
$result = $conn->query(
    "SELECT COUNT(*) AS total
     FROM users
     WHERE gruha_jyothi_status = 'Approved'"
);
$gruhaUsers = $result->fetch_assoc()['total'];


// Pending Gruha Jyothi requests
$result = $conn->query(
    "SELECT COUNT(*) AS total
     FROM users
     WHERE gruha_jyothi_status = 'Pending'"
);
$pendingRequests = $result->fetch_assoc()['total'];


// Total units
$result = $conn->query(
    "SELECT COALESCE(SUM(units), 0) AS total
     FROM consumption"
);
$totalUnits = $result->fetch_assoc()['total'];


// Get Gruha Jyothi requests
$requestResult = $conn->query(
    "SELECT id, name, email, gruha_jyothi_status
     FROM users
     WHERE gruha_jyothi_status IN ('Pending', 'Approved', 'Rejected')
     ORDER BY id DESC"
);

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0"
>

<title>Admin Dashboard | PowerTrack</title>

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
    margin-bottom: 35px;
}

.header h1 {
    font-size: 30px;
    margin-bottom: 8px;
}

.header p {
    color: #91a0aa;
}


/* Cards */

.cards {
    display: grid;

    grid-template-columns:
        repeat(auto-fit, minmax(220px, 1fr));

    gap: 20px;

    margin-bottom: 30px;
}

.card {
    background: #17212b;

    border: 1px solid #2b3944;

    border-radius: 14px;

    padding: 25px;
}

.card-icon {
    font-size: 28px;
    margin-bottom: 15px;
}

.card-title {
    color: #91a0aa;
    font-size: 14px;

    margin-bottom: 10px;
}

.card-value {
    font-size: 30px;
    font-weight: bold;
}


/* Panel */

.panel {
    background: #17212b;

    border: 1px solid #2b3944;

    border-radius: 14px;

    padding: 25px;

    margin-bottom: 25px;
}

.panel h2 {
    margin-bottom: 12px;
}

.panel p {
    color: #aab6bd;
    line-height: 1.7;
}


/* Request Table */

.request-table {
    width: 100%;

    border-collapse: collapse;

    margin-top: 20px;
}

.request-table th {
    text-align: left;

    color: #91a0aa;

    padding: 14px 10px;

    border-bottom: 1px solid #34434a;

    font-size: 13px;
}

.request-table td {
    padding: 15px 10px;

    border-bottom: 1px solid #293640;
}

.request-table tr:last-child td {
    border-bottom: none;
}


/* Status */

.status-pending {
    color: #ffc107;
    font-weight: bold;
}

.status-approved {
    color: #36d399;
    font-weight: bold;
}

.status-rejected {
    color: #ff6464;
    font-weight: bold;
}


/* Buttons */

.approve-btn {
    display: inline-block;

    background: #36d399;
    color: #111;

    padding: 8px 13px;

    border-radius: 6px;

    text-decoration: none;
    font-weight: bold;

    margin-right: 5px;
}

.reject-btn {
    display: inline-block;

    background: #ff6464;
    color: white;

    padding: 8px 13px;

    border-radius: 6px;

    text-decoration: none;
    font-weight: bold;
}

.approve-btn:hover {
    background: #28b884;
}

.reject-btn:hover {
    background: #e05252;
}


/* Empty */

.empty {
    text-align: center;

    color: #91a0aa;

    padding: 25px;
}


/* Responsive */

@media (max-width: 700px) {

    .sidebar {
        position: static;

        width: 100%;
        height: auto;

        padding: 15px;
    }

    .main {
        margin-left: 0;

        padding: 20px;
    }

    table {
        display: block;

        overflow-x: auto;

        white-space: nowrap;
    }

    .card {
        width: 100%;

        margin-bottom: 15px;
    }

    h1 {
        font-size: 25px;
    }

    .panel {
        padding: 15px;
    }

    button,
    a {
        max-width: 100%;
    }

}

</style>

</head>


<body>


<!-- SIDEBAR -->

<div class="sidebar">

    <div class="logo">
        ⚡ PowerTrack
    </div>

    <div class="admin-label">
        ADMIN PANEL
    </div>

    <div class="menu">

        <a
            href="admin_dashboard.php"
            class="active"
        >
            🏠 Dashboard
        </a>

        <a href="admin_users.php">
            👥 Manage Users
        </a>

        <a href="admin_consumption.php">
            📊 Consumption Records
        </a>

        <a href="admin_gruha_jyothi.php">
            🏡 Gruha Jyothi Users
        </a>

        <a href="admin_change_password.php">
            🔐 Change Password
        </a>

        <div class="logout">

            <a href="admin_logout.php">
                🚪 Admin Logout
            </a>

        </div>

    </div>

</div>


<!-- MAIN -->

<div class="main">


    <!-- HEADER -->

    <div class="header">

        <h1>
            Admin Dashboard
        </h1>

        <p>
            Welcome,
            <?php echo htmlspecialchars($adminUsername); ?>.
            Manage the PowerTrack system.
        </p>

    </div>


    <!-- CARDS -->

    <div class="cards">


        <div class="card">

            <div class="card-icon">
                👥
            </div>

            <div class="card-title">
                TOTAL USERS
            </div>

            <div class="card-value">
                <?php echo $totalUsers; ?>
            </div>

        </div>


        <div class="card">

            <div class="card-icon">
                📊
            </div>

            <div class="card-title">
                CONSUMPTION RECORDS
            </div>

            <div class="card-value">
                <?php echo $totalRecords; ?>
            </div>

        </div>


        <div class="card">

            <div class="card-icon">
                🏡
            </div>

            <div class="card-title">
                APPROVED GRUHA JYOTHI
            </div>

            <div class="card-value">
                <?php echo $gruhaUsers; ?>
            </div>

        </div>


        <div class="card">

            <div class="card-icon">
                ⏳
            </div>

            <div class="card-title">
                PENDING REQUESTS
            </div>

            <div class="card-value">
                <?php echo $pendingRequests; ?>
            </div>

        </div>


        <div class="card">

            <div class="card-icon">
                ⚡
            </div>

            <div class="card-title">
                TOTAL UNITS RECORDED
            </div>

            <div class="card-value">
                <?php echo number_format($totalUnits, 2); ?>
            </div>

        </div>


    </div>


    <!-- GRUHA JYOTHI REQUESTS -->

    <div class="panel">

        <h2>
            🏡 Gruha Jyothi Requests
        </h2>

        <p>
            Review requests submitted by users and approve
            or reject the Gruha Jyothi Scheme.
        </p>


        <?php if ($requestResult->num_rows > 0) { ?>


            <table class="request-table">

                <thead>

                    <tr>

                        <th>
                            USER ID
                        </th>

                        <th>
                            USER NAME
                        </th>

                        <th>
                            STATUS
                        </th>

                        <th>
                            ACTION
                        </th>

                    </tr>

                </thead>


                <tbody>


                <?php while ($request = $requestResult->fetch_assoc()) { ?>


                    <tr>


                        <td>
                            <?php echo $request['id']; ?>
                        </td>


                        <td>
                            <?php
                            echo htmlspecialchars(
                                $request['name']
                            );
                            ?>
                        </td>


                        <!-- STATUS -->

                        <td>

                            <?php if ($request['gruha_jyothi_status'] == "Pending") { ?>

                                <span class="status-pending">
                                    ⏳ Pending
                                </span>

                            <?php } elseif ($request['gruha_jyothi_status'] == "Approved") { ?>

                                <span class="status-approved">
                                    ✅ Approved
                                </span>

                            <?php } elseif ($request['gruha_jyothi_status'] == "Rejected") { ?>

                                <span class="status-rejected">
                                    ❌ Rejected
                                </span>

                            <?php } ?>

                        </td>


                        <!-- ACTION -->

                        <td>

                            <?php if ($request['gruha_jyothi_status'] == "Pending") { ?>


                                <a
                                    href="admin_gruha_action.php?id=<?php echo $request['id']; ?>&action=approve"
                                    class="approve-btn"
                                    onclick="return confirm('Approve this Gruha Jyothi request?');"
                                >
                                    ✓ Approve
                                </a>


                                <a
                                    href="admin_gruha_action.php?id=<?php echo $request['id']; ?>&action=reject"
                                    class="reject-btn"
                                    onclick="return confirm('Reject this Gruha Jyothi request?');"
                                >
                                    ✕ Reject
                                </a>


                            <?php } elseif ($request['gruha_jyothi_status'] == "Approved") { ?>


                                <a
                                    href="admin_gruha_action.php?id=<?php echo $request['id']; ?>&action=remove"
                                    class="reject-btn"
                                    onclick="return confirm('Remove Gruha Jyothi approval for this user?');"
                                >
                                    ✕ Remove Scheme
                                </a>


                            <?php } else { ?>

                                —

                            <?php } ?>

                        </td>


                    </tr>


                <?php } ?>


                </tbody>

            </table>


        <?php } else { ?>


            <div class="empty">

                No Gruha Jyothi requests found.

            </div>


        <?php } ?>


    </div>


    <!-- ADMIN CONTROL PANEL -->

    <div class="panel">

        <h2>
            🛡️ Administrator Control Panel
        </h2>

        <p>
            From this dashboard, the administrator can
            manage registered users, view electricity
            consumption records and review Gruha Jyothi
            scheme requests.
        </p>

    </div>


</div>


</body>

</html>