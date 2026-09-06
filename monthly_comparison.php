<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$userId = $_SESSION['user_id'];

// Get all consumption records
$stmt = $conn->prepare(
    "SELECT month, year, units, total_bill
     FROM consumption
     WHERE user_id = ?
     ORDER BY year ASC, id ASC"
);

$stmt->bind_param("i", $userId);
$stmt->execute();

$result = $stmt->get_result();

$records = [];

while ($row = $result->fetch_assoc()) {
    $records[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Monthly Comparison | PowerTrack</title>

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
    overflow-y: auto;
}

.logo {
    font-size: 21px;
    font-weight: bold;
    color: #ffc107;
    margin-bottom: 40px;
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

/* Main */

.main {
    margin-left: 240px;
    padding: 35px;
}

.header {
    margin-bottom: 30px;
}

.header h1 {
    margin-bottom: 8px;
}

.header p {
    color: #91a0aa;
}

/* Table */

.panel {
    background: #17212b;

    border: 1px solid #2b3944;
    border-radius: 15px;

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
    padding: 17px 15px;
    border-bottom: 1px solid #293640;
}

tr:hover {
    background: #1d2a35;
}

.units {
    font-weight: bold;
}

.increase {
    color: #ff6464;
    font-weight: bold;
}

.decrease {
    color: #36d399;
    font-weight: bold;
}

.same {
    color: #ffc107;
    font-weight: bold;
}

.first {
    color: #91a0aa;
}

.bill {
    color: #ffc107;
}

.no-data {
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

<?php include 'sidebar.php'; ?>


<div class="main">

    <div class="header">

        <h1>📅 Monthly Comparison</h1>

        <p>
            Compare your electricity consumption
            across all recorded months.
        </p>

    </div>


    <div class="panel">

    <?php if (count($records) > 0) { ?>

        <table>

            <thead>

                <tr>
                    <th>Month</th>
                    <th>Consumption</th>
                    <th>Bill</th>
                    <th>Comparison</th>
                </tr>

            </thead>

            <tbody>

            <?php for ($i = 0; $i < count($records); $i++) {

                $record = $records[$i];
            ?>

                <tr>

                    <td>

                        <?php
                        echo htmlspecialchars($record['month'])
                        . " "
                        . (int)$record['year'];
                        ?>

                    </td>


                    <td class="units">

                        <?php
                        echo number_format(
                            $record['units'],
                            2
                        );
                        ?> units

                    </td>


                    <td class="bill">

                        ₹<?php
                        echo number_format(
                            $record['total_bill'],
                            2
                        );
                        ?>

                    </td>


                    <td>

                    <?php if ($i == 0) { ?>

                        <span class="first">
                            First Record
                        </span>

                    <?php } else {

                        $previousUnits =
                            $records[$i - 1]['units'];

                        $difference =
                            $record['units']
                            - $previousUnits;


                        if ($difference > 0) {
                    ?>

                            <span class="increase">

                                📈 Increased by

                                <?php
                                echo number_format(
                                    abs($difference),
                                    2
                                );
                                ?>

                                units

                            </span>


                        <?php } elseif ($difference < 0) { ?>

                            <span class="decrease">

                                📉 Decreased by

                                <?php
                                echo number_format(
                                    abs($difference),
                                    2
                                );
                                ?>

                                units

                            </span>


                        <?php } else { ?>

                            <span class="same">
                                ➖ No Change
                            </span>

                        <?php } ?>

                    <?php } ?>

                    </td>

                </tr>

            <?php } ?>

            </tbody>

        </table>

    <?php } else { ?>

        <div class="no-data">

            <h3>No Consumption Data</h3>

            <p style="margin-top: 10px;">
                Add electricity consumption records
                to see monthly comparisons.
            </p>

        </div>

    <?php } ?>

    </div>

</div>

</body>
</html>