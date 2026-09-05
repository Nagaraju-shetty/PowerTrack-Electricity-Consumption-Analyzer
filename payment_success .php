<?php
include "db.php";

if (!isset($_GET['id'])) {
    die("Invalid Request");
}

$id = (int)$_GET['id'];

// Update payment status
$sql = "UPDATE consumption
        SET payment_status='Paid'
        WHERE id=?";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $id);

if (mysqli_stmt_execute($stmt)) {

    header("Location: print_bill.php?id=$id&payment=success");
    exit();

} else {

    echo "Payment update failed.";

}
?>