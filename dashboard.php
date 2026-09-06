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

// Get all consumption records for this user
$stmt = $conn->prepare(
    "SELECT * FROM consumption
     WHERE user_id = ?
     ORDER BY year ASC,
     FIELD(month,
     'January','February','March','April','May','June',
     'July','August','September','October','November','December') ASC"
);

$stmt->bind_param("i", $userId);
$stmt->execute();

$result = $stmt->get_result();

$records = [];

while ($row = $result->fetch_assoc()) {
    $records[] = $row;
}

// Default values
$latestUnits = 0;
$latestBill = 0;
$change = 0;
$status = "No Data";
$statusClass = "normal";

$latest = null;
$previous = null;

$gruhaJyothi = "No Data";

// Get Gruha Jyothi request status
$schemeStmt = $conn->prepare(
    "SELECT gruha_jyothi_status FROM users WHERE id = ?"
);

$schemeStmt->bind_param("i", $userId);
$schemeStmt->execute();

$schemeResult = $schemeStmt->get_result();
$schemeUser = $schemeResult->fetch_assoc();

$gruhaJyothiStatus =
    $schemeUser['gruha_jyothi_status'] ?? 'Not Applied';
if (count($records) > 0) {

    $latest = $records[count($records) - 1];

    $latestUnits = (float)$latest['units'];
    $latestBill = (float)$latest['total_bill'];
    // Gruha Jyothi simplified project rule
if ($gruhaJyothiStatus == "Approved") {

    if ($latestUnits <= 200) {
        $gruhaJyothi = "Eligible";
    } else {
        $gruhaJyothi = "Not Eligible";
    }

} elseif ($gruhaJyothiStatus == "Pending") {

    $gruhaJyothi = "Pending";

} elseif ($gruhaJyothiStatus == "Rejected") {

    $gruhaJyothi = "Rejected";

} else {

    $gruhaJyothi = "Not Applied";
}

    // Energy status
    if ($latestUnits <= 150) {
        $status = "Excellent";
        $statusClass = "good";
    }
    elseif ($latestUnits <= 250) {
        $status = "Normal";
        $statusClass = "good";
    }
    elseif ($latestUnits <= 350) {
        $status = "High";
        $statusClass = "warning";
    }
    else {
        $status = "Very High";
        $statusClass = "danger";
    }

    // Compare with previous month
    if (count($records) >= 2) {

        $previous = $records[count($records) - 2];

        $previousUnits = (float)$previous['units'];

        if ($previousUnits > 0) {

            $change =
                (($latestUnits - $previousUnits)
                / $previousUnits) * 100;
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

<title>
Dashboard | Electricity Consumption Analyzer
</title>

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

/* SIDEBAR */

.sidebar {
    position: fixed;
    left: 0;
    top: 0;

    width: 240px;
    height: 100vh;

    overflow-y: auto;

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

    padding: 10px 15px;
    margin-bottom: 5px;

    border-radius: 8px;

    transition: 0.3s;
}

.menu a:hover,
.menu .active {
    background: #ffc107;
    color: #111;
}

.logout {
    position: static;
    width: 200px;
    margin-top: 20px;
}


/* MAIN */

.main {
    margin-left: 240px;
    padding: 35px;
}

.topbar {
    display: flex;

    justify-content: space-between;
    align-items: center;

    margin-bottom: 35px;
}

.topbar h1 {
    font-size: 30px;
}

.topbar p {
    color: #91a0aa;
    margin-top: 8px;
}

.profile {
    background: #17212b;

    padding: 12px 18px;

    border-radius: 10px;

    border: 1px solid #2d3a45;
}


/* CARDS */

.cards {
    display: grid;

    grid-template-columns:
    repeat(4, 1fr);

    gap: 20px;

    margin-bottom: 30px;
}

.card {
    background: #17212b;

    border: 1px solid #2b3944;

    border-radius: 14px;

    padding: 23px;

    transition: 0.3s;
}

.card:hover {
    transform: translateY(-5px);
    border-color: #ffc107;
}

.card-icon {
    font-size: 28px;
    margin-bottom: 15px;
}

.card p {
    color: #91a0aa;

    font-size: 13px;

    margin-bottom: 10px;
}

.card h2 {
    font-size: 27px;
}

.good {
    color: #36d399;
}

.warning {
    color: #ffc107;
}

.danger {
    color: #ff6464;
}

.normal {
    color: #91a0aa;
}


/* CONTENT */

.content-grid {
    display: grid;

    grid-template-columns: 2fr 1fr;

    gap: 20px;
}

.panel {
    background: #17212b;

    border: 1px solid #2b3944;

    border-radius: 14px;

    padding: 25px;
}

.panel h2 {
    font-size: 20px;
    margin-bottom: 20px;
}


/* TABLE */

table {
    width: 100%;

    border-collapse: collapse;
}

th {
    text-align: left;

    color: #91a0aa;

    padding: 13px 10px;

    border-bottom: 1px solid #34434a;

    font-size: 13px;
}

td {
    padding: 15px 10px;

    border-bottom: 1px solid #293640;
}

tr:last-child td {
    border-bottom: none;
}


/* INSIGHT */

.insight {
    padding: 17px;

    background: #202c36;

    border-left: 4px solid #ffc107;

    border-radius: 8px;

    line-height: 1.6;

    margin-bottom: 15px;
}

.insight p {
    color: #b7c3ca;
}

.add-btn {
    display: inline-block;

    background: #ffc107;

    color: #111;

    padding: 13px 20px;

    text-decoration: none;

    border-radius: 8px;

    font-weight: bold;

    margin-top: 20px;
}

.add-btn:hover {
    background: #e5ad00;
}


/* EMPTY */

.empty {
    text-align: center;

    color: #91a0aa;

    padding: 40px 20px;
}


/* MOBILE */

@media(max-width: 1000px) {

    .cards {
        grid-template-columns:
        repeat(2, 1fr);
    }

    .content-grid {
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

    .cards {
        grid-template-columns: 1fr;
    }

    .topbar {
        display: block;
    }

    .profile {
        display: inline-block;
        margin-top: 20px;
    }
}
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

    .cards {
        grid-template-columns: 1fr;
    }

    .card {
        width: 100%;
    }

    .header h1 {
        font-size: 25px;
    }

    .panel {
        padding: 20px;
    }
}
</style>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body>


<?php include 'sidebar.php'; ?>




<!-- MAIN -->

<div class="main">


    <!-- HEADER -->

    <div class="topbar">

        <div>

            <h1>
                Welcome,
                <?php
                echo htmlspecialchars($userName);
                ?> 👋
            </h1>

            <p>
                Monitor and understand your
                electricity consumption.
            </p>

        </div>


        <div class="profile">

            👤
            <?php
            echo htmlspecialchars($userName);
            ?>

        </div>

    </div>



    <!-- CARDS -->

    <div class="cards">


        <div class="card">

            <div class="card-icon">
                ⚡
            </div>

            <p>
                LATEST CONSUMPTION
            </p>

            <h2>

                <?php
                echo number_format(
                    $latestUnits,
                    2
                );
                ?>

                kWh

            </h2>

        </div>



        <div class="card">

            <div class="card-icon">
                💰
            </div>

            <p>
                ESTIMATED BILL
            </p>

            <h2>

                ₹<?php
                echo number_format(
                    $latestBill,
                    2
                );
                ?>

            </h2>

        </div>



        <div class="card">

            <div class="card-icon">
                📈
            </div>

            <p>
                MONTHLY CHANGE
            </p>

            <h2>

                <?php if (count($records) < 2) { ?>

                    —

                <?php } else { ?>

                    <?php
                    echo $change >= 0 ? "+" : "";
                    echo number_format($change, 1);
                    ?>%

                <?php } ?>

            </h2>

        </div>



        <div class="card">

            <div class="card-icon">
                🌱
            </div>

            <p>
                ENERGY STATUS
            </p>

            <h2 class="<?php echo $statusClass; ?>">

                <?php
                echo $status;
                ?>

            </h2>

        </div>

    </div>
<!-- MONTHLY CONSUMPTION GRAPH -->

<div class="panel" style="margin-bottom: 20px;">

    <h2>📈 Monthly Consumption Graph</h2>

    <div style="height: 350px;">
        <canvas id="consumptionChart"></canvas>
    </div>

</div>

<!-- GRUHA JYOTHI SCHEME -->

<div class="panel" style="margin-bottom: 20px;">

    <h2>🏠 Gruha Jyothi Scheme</h2>

    <?php if ($gruhaJyothi == "Eligible") { ?>

        <div style="
            margin-top: 15px;
            padding: 20px;
            background: #163b31;
            border-radius: 10px;
            border-left: 4px solid #36d399;
        ">

            <h3 style="color: #36d399;">
                ✅ Eligible for Gruha Jyothi
            </h3>

            <p style="margin-top: 10px; color: #b9c4cc;">
                Consumption is <?php echo number_format($latestUnits, 2); ?> units.
                Under the simplified project rule, consumption up to
                200 units receives a ₹0.00 payable bill.
            </p>

            <h2 style="margin-top: 15px;">
                Payable Bill: ₹0.00
            </h2>

        </div>

    <?php } elseif ($gruhaJyothi == "Not Eligible") { ?>

        <div style="
            margin-top: 15px;
            padding: 20px;
            background: #3d2920;
            border-radius: 10px;
            border-left: 4px solid #ffc107;
        ">

            <h3 style="color: #ffc107;">
                ⚠️ Not Eligible
            </h3>

            <p style="margin-top: 10px; color: #b9c4cc;">
                Consumption is <?php echo number_format($latestUnits, 2); ?> units,
                which is above the 200-unit limit used by this project.
            </p>

            <h2 style="margin-top: 15px;">
                Payable Bill:
                ₹<?php echo number_format($latestBill, 2); ?>
            </h2>

        </div>

  <?php } elseif ($gruhaJyothi == "Not Applied") { ?>

    <div style="
        padding: 20px;
        background: #202c36;
        border-radius: 10px;
        border-left: 4px solid #ffc107;
    ">

        <h3 style="color: #ffc107;">
            🏠 Gruha Jyothi Scheme
        </h3>

        <p style="margin-top: 10px; color: #b9c4cc;">
            You have not applied for the Gruha Jyothi Scheme yet.
        </p>

        <a href="gruha_jyothi.php"
           style="
               display: inline-block;
               margin-top: 15px;
               padding: 10px 16px;
               background: #ffc107;
               color: #111;
               text-decoration: none;
               border-radius: 7px;
               font-weight: bold;
           ">
            🏡 Apply for Gruha Jyothi
        </a>

    </div>

<?php } elseif ($gruhaJyothi == "Pending") { ?>

    <div style="
        padding: 20px;
        background: #3d3320;
        border-radius: 10px;
        border-left: 4px solid #ffc107;
    ">

        <h3 style="color: #ffc107;">
            ⏳ Gruha Jyothi Request Pending
        </h3>

        <p style="margin-top: 10px; color: #b9c4cc;">
            Your request has been sent to the administrator.
            Please wait for approval.
        </p>

    </div>

<?php } elseif ($gruhaJyothi == "Rejected") { ?>

    <div style="
        padding: 20px;
        background: #3d2020;
        border-radius: 10px;
        border-left: 4px solid #ff6464;
    ">

        <h3 style="color: #ff6464;">
            ❌ Gruha Jyothi Request Rejected
        </h3>

        <p style="margin-top: 10px; color: #b9c4cc;">
            Your Gruha Jyothi request was rejected by the administrator.
        </p>

        <a href="gruha_jyothi.php"
           style="
               display: inline-block;
               margin-top: 15px;
               padding: 10px 16px;
               background: #ffc107;
               color: #111;
               text-decoration: none;
               border-radius: 7px;
               font-weight: bold;
           ">
            Apply Again
        </a>

    </div>

<?php } ?>
</div>

    <!-- CONTENT -->

    <div class="content-grid">


        <!-- HISTORY -->

        <div class="panel"
             id="history">

            <h2>
                📊 Consumption History
            </h2>


            <?php if (count($records) == 0) { ?>


                <div class="empty">

                    No electricity consumption
                    has been added yet.

                    <br>

                    <a
                    href="add_consumption.php"
                    class="add-btn">

                        + Add Consumption

                    </a>

                </div>


            <?php } else { ?>


                <table>

                    <thead>

                    <tr>

                        <th>
                            MONTH
                        </th>

                        <th>
                            USAGE
                        </th>

                        <th>
                            RATE
                        </th>

                        <th>
                            BILL
                        </th>

                    </tr>

                    </thead>


                    <tbody>


                    <?php
                    foreach (
                        array_reverse($records)
                        as $record
                    ) {
                    ?>

                        <tr>

                            <td>

                                <?php
                                echo htmlspecialchars(
                                    $record['month']
                                );
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


                <a
                href="add_consumption.php"
                class="add-btn">

                    + Add New Consumption

                </a>


            <?php } ?>


        </div>



        <!-- ENERGY INSIGHTS -->

        <div class="panel"
             id="tips">

            <h2>
                💡 Energy Insights
            </h2>


            <?php if (!$latest) { ?>

                <div class="insight">

                    <p>
                        Add your first electricity
                        consumption record to receive
                        energy insights.
                    </p>

                </div>


            <?php } else { ?>


                <div class="insight">

                    <strong>
                        ⚡ Latest Usage
                    </strong>

                    <p>

                        You consumed

                        <?php
                        echo number_format(
                            $latestUnits,
                            2
                        );
                        ?>

                        kWh in

                        <?php
                        echo htmlspecialchars(
                            $latest['month']
                        );
                        ?>.

                    </p>

                </div>
<?php if ($latestUnits > 200) { ?>

    <div class="insight">

        <strong>
            🔴 Very High Usage Alert
        </strong>

        <p>
            Your electricity consumption is above 200 units.
            Try to reduce your electricity usage.
        </p>

    </div>

<?php } elseif ($latestUnits > 150) { ?>

    <div class="insight">

        <strong>
            ⚠️ High Usage Alert
        </strong>

        <p>
            Your electricity consumption is above 150 units.
            Consider reducing unnecessary electricity usage.
        </p>

    </div>

<?php } else { ?>

    <div class="insight">

        <strong>
            ✅ Normal Usage
        </strong>

        <p>
            Your electricity consumption is within the normal range.
        </p>

    </div>

<?php } ?>


                <?php if ($latestUnits <= 250) { ?>

                    <div class="insight">

                        <strong>
                            🌱 Good Usage
                        </strong>

                        <p>
                            Your electricity usage is
                            currently within the normal
                            range used by this project.
                        </p>

                    </div>

                <?php } else { ?>

                    <div class="insight">

                        <strong>
                            ⚠ High Usage
                        </strong>

                        <p>
                            Your electricity consumption
                            is high. Check appliances
                            such as ACs, water heaters,
                            pumps and refrigerators.
                        </p>

                    </div>

                <?php } ?>



                <div class="insight">

                    <strong>
                        💡 Saving Tip
                    </strong>

                    <p>
                        Switch off unused appliances,
                        use LED lighting and avoid
                        leaving devices on standby.
                    </p>

                </div>


            <?php } ?>


        </div>

    </div>

</div>
<script>

const monthLabels = [
    <?php
    foreach ($records as $record) {
        echo "'" .
        htmlspecialchars($record['month']) .
        " " .
        (int)$record['year'] .
        "',";
    }
    ?>
];

const consumptionData = [
    <?php
    foreach ($records as $record) {
        echo (float)$record['units'] . ",";
    }
    ?>
];

const ctx = document.getElementById('consumptionChart');

new Chart(ctx, {

    type: 'bar',

    data: {

        labels: monthLabels,

        datasets: [{
            label: 'Electricity Consumption (kWh)',
            data: consumptionData,
            backgroundColor: '#ffc107',
            borderColor: '#ffc107',
            borderWidth: 1,
            borderRadius: 6
        }]

    },

    options: {

        responsive: true,
        maintainAspectRatio: false,

        plugins: {

            legend: {
                labels: {
                    color: '#ffffff'
                }
            }

        },

        scales: {

            x: {
                ticks: {
                    color: '#b9c4cc'
                },

                grid: {
                    color: '#26333d'
                }
            },

            y: {
                beginAtZero: true,

                ticks: {
                    color: '#b9c4cc'
                },

                grid: {
                    color: '#26333d'
                }
            }

        }

    }

});

</script>
</body>
</html>