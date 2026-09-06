<?php
session_start();

// Remove admin session
unset($_SESSION['admin_id']);
unset($_SESSION['admin_username']);

header("Location: admin_login.php");
exit();
?>