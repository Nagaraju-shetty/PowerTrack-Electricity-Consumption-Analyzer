<?php
include "db.php";

if (!isset($_GET['id'])) {
    die("Invalid Bill");
}

$billId = (int)$_GET['id'];

$stmt = mysqli_prepare(
    $conn,
    "UPDATE consumption SET payment_status='Paid' WHERE id=?"
);

mysqli_stmt_bind_param($stmt, "i", $billId);

if (mysqli_stmt_execute($stmt)) {

    header("Location: print_bill.php?id=" . $billId);
    exit();

} else {

    echo "Payment update failed.";

}
?>