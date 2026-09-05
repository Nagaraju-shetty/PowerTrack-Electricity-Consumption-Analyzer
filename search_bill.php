<?php
include "db.php";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Bill</title>
</head>
<body>

<h2>Search Electricity Bill</h2>

<form action="view_bill.php" method="GET">

    <label>Electricity Number</label><br><br>

    <input
        type="text"
        name="electricity_no"
        placeholder="Enter Electricity Number"
        required
    ><br><br>

    <button type="submit">
        Search Bill
    </button>

</form>

</body>
</html>