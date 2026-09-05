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

// Get latest electricity consumption
$stmt = $conn->prepare(
    "SELECT * FROM consumption
     WHERE user_id = ?
     ORDER BY year DESC,
     FIELD(month,
     'December','November','October','September',
     'August','July','June','May','April',
     'March','February','January') ASC
     LIMIT 1"
);

$stmt->bind_param("i", $userId);
$stmt->execute();

$result = $stmt->get_result();
$latest = $result->fetch_assoc();

$units = 0;
$status = "No Data";
$message = "Add your electricity consumption to receive suggestions.";

if ($latest) {

    $units = (float)$latest['units'];

    if ($units <= 150) {
        $status = "Excellent";
        $message = "Your electricity consumption is low. Keep following energy-saving habits.";
    }
    elseif ($units <= 250) {
        $status = "Normal";
        $message = "Your electricity consumption is moderate. Small changes can help reduce it further.";
    }
    elseif ($units <= 350) {
        $status = "High";
        $message = "Your electricity consumption is high. Consider reducing unnecessary appliance usage.";
    }
    else {
        $status = "Very High";
        $message = "Your electricity consumption is very high. Check high-power appliances and reduce unnecessary usage.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
content="width=device-width, initial-scale=1.0">

<title>Energy Tips | PowerTrack</title>

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


/* Status */

.status-box {
    background: #17212b;

    border: 1px solid #2b3944;

    border-radius: 14px;

    padding: 25px;

    margin-bottom: 25px;
}

.status-box h2 {
    margin-bottom: 15px;
}

.usage {
    font-size: 32px;

    color: #ffc107;

    margin-bottom: 10px;
}

.status {
    display: inline-block;

    padding: 8px 15px;

    background: #26343e;

    border-radius: 20px;

    margin-bottom: 15px;
}

.status-message {
    color: #b9c4cc;
    line-height: 1.6;
}


/* Tips */

.tips-grid {
    display: grid;

    grid-template-columns:
    repeat(2, 1fr);

    gap: 20px;
}

.tip-card {
    background: #17212b;

    border: 1px solid #2b3944;

    border-radius: 14px;

    padding: 25px;

    transition: 0.3s;
}

.tip-card:hover {
    transform: translateY(-5px);

    border-color: #ffc107;
}

.tip-icon {
    font-size: 35px;

    margin-bottom: 15px;
}

.tip-card h3 {
    margin-bottom: 10px;
}

.tip-card p {
    color: #aab6bd;

    line-height: 1.6;
}


/* Back Button */

.back-btn {
    display: inline-block;

    margin-top: 30px;

    background: #ffc107;

    color: #111;

    padding: 12px 20px;

    text-decoration: none;

    border-radius: 8px;

    font-weight: bold;
}

.back-btn:hover {
    background: #e5ad00;
}


/* Mobile */

@media(max-width: 800px) {

    .tips-grid {
        grid-template-columns: 1fr;
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
}

</style>

</head>


<body>


<?php include 'sidebar.php'; ?>


<!-- Main -->

<div class="main">

    <div class="header">

        <h1>
            💡 Energy Saving Tips
        </h1>

        <p>
            Personalized suggestions based on
            your latest electricity consumption.
        </p>

    </div>


    <!-- Current Status -->

    <div class="status-box">

        <h2>
            ⚡ Your Energy Status
        </h2>

        <?php if ($latest) { ?>

            <div class="usage">

                <?php
                echo number_format($units, 2);
                ?>

                kWh

            </div>

            <div class="status">

                Status:
                <strong>
                    <?php echo $status; ?>
                </strong>

            </div>

        <?php } else { ?>

            <div class="usage">
                No Data
            </div>

        <?php } ?>


        <p class="status-message">

            <?php
            echo htmlspecialchars($message);
            ?>

        </p>

    </div>



    <!-- Tips -->

    <div class="tips-grid">


        <div class="tip-card">

            <div class="tip-icon">
                💡
            </div>

            <h3>
                Use LED Lights
            </h3>

            <p>
                Replace traditional bulbs with
                LED lights because they use less
                electricity and last longer.
            </p>

        </div>



        <div class="tip-card">

            <div class="tip-icon">
                🔌
            </div>

            <h3>
                Unplug Devices
            </h3>

            <p>
                Unplug chargers, televisions and
                other devices when they are not
                being used.
            </p>

        </div>



        <div class="tip-card">

            <div class="tip-icon">
                ❄️
            </div>

            <h3>
                Use AC Efficiently
            </h3>

            <p>
                Avoid very low AC temperatures.
                Using a moderate temperature can
                reduce electricity consumption.
            </p>

        </div>



        <div class="tip-card">

            <div class="tip-icon">
                🌞
            </div>

            <h3>
                Use Natural Light
            </h3>

            <p>
                Open curtains and windows during
                daytime instead of switching on
                unnecessary lights.
            </p>

        </div>



        <div class="tip-card">

            <div class="tip-icon">
                🧺
            </div>

            <h3>
                Use Appliances Wisely
            </h3>

            <p>
                Run washing machines with full
                loads whenever possible instead
                of using them repeatedly for
                small loads.
            </p>

        </div>



        <div class="tip-card">

            <div class="tip-icon">
                ⭐
            </div>

            <h3>
                Choose Efficient Appliances
            </h3>

            <p>
                When buying new appliances,
                consider models with good energy
                efficiency ratings.
            </p>

        </div>

    </div>


    <a href="dashboard.php"
       class="back-btn">

        ← Back to Dashboard

    </a>

</div>


</body>

</html>