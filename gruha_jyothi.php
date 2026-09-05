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
$message = "";
$messageClass = "";

// Get current Gruha Jyothi status
$stmt = $conn->prepare(
    "SELECT gruha_jyothi_status
     FROM users
     WHERE id = ?"
);

$stmt->bind_param("i", $userId);
$stmt->execute();

$result = $stmt->get_result();
$user = $result->fetch_assoc();

$schemeStatus = $user['gruha_jyothi_status'] ?? 'Not Applied';


// Submit Gruha Jyothi request
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    if ($schemeStatus == "Not Applied" || $schemeStatus == "Rejected") {

        $newStatus = "Pending";

        $updateStmt = $conn->prepare(
            "UPDATE users
             SET gruha_jyothi_status = ?
             WHERE id = ?"
        );

        $updateStmt->bind_param("si", $newStatus, $userId);

        if ($updateStmt->execute()) {

            $schemeStatus = "Pending";

            $message = "Your Gruha Jyothi request has been submitted successfully. Please wait for admin approval.";
            $messageClass = "message";

        } else {

            $message = "Unable to submit your request. Please try again.";
            $messageClass = "error-message";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
content="width=device-width, initial-scale=1.0">

<title>Gruha Jyothi | PowerTrack</title>

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


/* Scheme Card */

.scheme-card {
    max-width: 750px;

    background: #17212b;

    border: 1px solid #2b3944;

    border-radius: 15px;

    padding: 30px;
}

.scheme-icon {
    font-size: 45px;
    margin-bottom: 15px;
}

.scheme-card h2 {
    margin-bottom: 15px;
}

.description {
    color: #aab6bd;
    line-height: 1.7;
    margin-bottom: 25px;
}


/* Status */

.status-box {
    background: #101820;

    padding: 20px;

    border-radius: 10px;

    margin-bottom: 25px;
}

.pending {
    color: #ffc107;
    font-weight: bold;
}

.approved {
    color: #36d399;
    font-weight: bold;
}

.rejected {
    color: #ff6464;
    font-weight: bold;
}

.not-applied {
    color: #91a0aa;
    font-weight: bold;
}


/* Button */

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

button:hover {
    background: #e5ad00;
}


/* Message */

.message {
    background: #163b31;

    color: #55e6b1;

    padding: 15px;

    border-radius: 8px;

    margin-bottom: 20px;
}

.error-message {
    background: #472020;

    color: #ff9b9b;

    padding: 15px;

    border-radius: 8px;

    margin-bottom: 20px;
}


/* Rule */

.rule {
    margin-top: 30px;

    background: #202c36;

    padding: 20px;

    border-left: 4px solid #ffc107;

    border-radius: 8px;
}

.rule h3 {
    margin-bottom: 10px;
}

.rule p {
    color: #b9c4cc;
    line-height: 1.6;
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

        <h1>🏡 Gruha Jyothi Scheme</h1>

        <p>
            Apply for the Gruha Jyothi Scheme and wait for administrator approval.
        </p>

    </div>


    <?php if ($message != "") { ?>

        <div class="<?php echo $messageClass; ?>">
            <?php echo htmlspecialchars($message); ?>
        </div>

    <?php } ?>


    <div class="scheme-card">

        <div class="scheme-icon">
            🏠⚡
        </div>

        <h2>Scheme Status</h2>


        <div class="status-box">

            Current Status:

            <?php if ($schemeStatus == "Pending") { ?>

                <span class="pending">
                    ⏳ Pending Approval
                </span>

            <?php } elseif ($schemeStatus == "Approved") { ?>

                <span class="approved">
                    ✅ Approved
                </span>

            <?php } elseif ($schemeStatus == "Rejected") { ?>

                <span class="rejected">
                    ❌ Rejected
                </span>

            <?php } else { ?>

                <span class="not-applied">
                    Not Applied
                </span>

            <?php } ?>

        </div>


        <?php if ($schemeStatus == "Not Applied") { ?>

            <p class="description">
                You can apply for the Gruha Jyothi Scheme.
                After submitting your request, the administrator
                will review it and either approve or reject it.
            </p>

            <form method="POST">

                <button type="submit">
                    🏡 Apply for Gruha Jyothi
                </button>

            </form>


        <?php } elseif ($schemeStatus == "Pending") { ?>

            <p class="description">
                Your Gruha Jyothi request has been submitted
                successfully and is currently waiting for
                administrator approval.
            </p>


        <?php } elseif ($schemeStatus == "Approved") { ?>

            <p class="description">
                🎉 Your Gruha Jyothi Scheme request has been
                approved by the administrator. The scheme is
                now enabled for your account.
            </p>


        <?php } elseif ($schemeStatus == "Rejected") { ?>

            <p class="description">
                Your Gruha Jyothi request was rejected by the
                administrator. You can apply again if required.
            </p>

            <form method="POST">

                <button type="submit">
                    🏡 Apply Again
                </button>

            </form>

        <?php } ?>


        <div class="rule">

            <h3>📋 Project Rule</h3>

            <p>
                If your Gruha Jyothi request is approved and
                electricity consumption is 200 units or less,
                this project will display the payable bill as ₹0.00.
            </p>

            <p style="margin-top: 10px;">
                If consumption is above 200 units, the normal
                electricity bill calculation will be used.
            </p>

        </div>

    </div>

</div>

</body>

</html>