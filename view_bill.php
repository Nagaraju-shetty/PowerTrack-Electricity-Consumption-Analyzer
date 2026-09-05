<?php
include "db.php";

if (!isset($_GET['electricity_no'])) {
    die("Electricity Number not provided.");
}

$electricity_no = $_GET['electricity_no'];

$sql = "SELECT u.name, u.email, u.electricity_no,
               c.month, c.year, c.units, c.rate, c.total_bill
        FROM users u
        INNER JOIN consumption c ON u.id = c.user_id
        WHERE u.electricity_no = ?";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "s", $electricity_no);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) == 0) {
    die("No bill found for this Electricity Number.");
}

$bill = mysqli_fetch_assoc($result);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Bill Details</title>
</head>
<body>

<h2>Electricity Bill Details</h2>

<p><strong>Name:</strong> <?php echo $bill['name']; ?></p>

<p><strong>Email:</strong> <?php echo $bill['email']; ?></p>

<p><strong>Electricity Number:</strong> <?php echo $bill['electricity_no']; ?></p>

<p><strong>Month:</strong> <?php echo $bill['month']; ?> <?php echo $bill['year']; ?></p>

<p><strong>Units:</strong> <?php echo $bill['units']; ?></p>

<p><strong>Rate:</strong> ₹<?php echo $bill['rate']; ?></p>

<p><strong>Total Bill:</strong> ₹<?php echo $bill['total_bill']; ?></p>

</body>
</html>