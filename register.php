<?php
include "db.php";

$message = "";

if (isset($_POST['register'])) {

    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $electricity_no = trim($_POST['electricity_no']);
    $password = $_POST['password'];
    $confirm = $_POST['confirm_password'];

    if ($password != $confirm) {
        $message = "Passwords do not match!";
    } else {

        $check = mysqli_prepare(
            $conn,
            "SELECT id FROM users WHERE email = ?"
        );

        mysqli_stmt_bind_param($check, "s", $email);
        mysqli_stmt_execute($check);
        mysqli_stmt_store_result($check);

        if (mysqli_stmt_num_rows($check) > 0) {
            $message = "Email already registered!";
        } else {

            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            $stmt = mysqli_prepare(
    $conn,
    "INSERT INTO users (name, email, electricity_no, password)
     VALUES (?, ?, ?, ?)"
);
            mysqli_stmt_bind_param(
    $stmt,
    "ssss",
    $name,
    $email,
    $electricity_no,
    $hashedPassword
);
            if (mysqli_stmt_execute($stmt)) {
                header("Location: login.php?registered=1");
                exit();
            } else {
                $message = "Registration failed!";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Register | Electricity Consumption Analyzer</title>

    <link rel="stylesheet" href="css/style.css">
</head>

<body>

<div class="auth-container">

    <div class="logo">⚡</div>

    <h1>Create Account</h1>

    <p class="subtitle">
        Start monitoring your electricity consumption
    </p>

    <?php if ($message != "") { ?>
        <div class="error">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php } ?>

    <form method="POST">

        <label>Full Name</label>
        <input
            type="text"
            name="name"
            placeholder="Enter your full name"
            required
        >

        <label>Email Address</label>
        <input
            type="email"
            name="email"
            placeholder="Enter your email"
            required
        >
        <label>Electricity Number</label>
<input
    type="text"
    name="electricity_no"
    placeholder="Enter your Electricity Number"
    required
>

        <label>Password</label>
        <input
            type="password"
            name="password"
            placeholder="Create a password"
            minlength="6"
            required
        >

        <label>Confirm Password</label>
        <input
            type="password"
            name="confirm_password"
            placeholder="Enter password again"
            minlength="6"
            required
        >

        <button type="submit" name="register">
            Create Account
        </button>

    </form>

    <p class="bottom-text">
        Already have an account?
        <a href="login.php">Login</a>
    </p>

</div>

</body>
</html>