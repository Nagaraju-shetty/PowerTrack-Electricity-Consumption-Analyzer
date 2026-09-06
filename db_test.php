<?php

mysqli_report(MYSQLI_REPORT_OFF);

$conn = mysqli_init();

mysqli_options($conn, MYSQLI_OPT_CONNECT_TIMEOUT, 5);

if (!mysqli_real_connect(
    $conn,
    "127.0.0.1",
    "root",
    "",
    "electricity_analyzer",
    3306
)) {
    die("MySQL Connection Error: " . mysqli_connect_error());
}

echo "Database connection successful!";

?>