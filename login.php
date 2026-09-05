<?php

session_start();
include "db.php";

$message = "";

if (isset($_POST['login'])) {

    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $stmt = mysqli_prepare(
        $conn,
        "SELECT id, name, password FROM users WHERE email = ?"
    );

    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);

    if ($user = mysqli_fetch_assoc($result)) {

        if (password_verify($password, $user['password'])) {

            session_regenerate_id(true);

            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];

            header("Location: dashboard.php");
            exit();

        } else {

            $message = "Incorrect email or password!";
        }

    } else {

        $message = "Incorrect email or password!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Login | PowerTrack</title>

    <link rel="stylesheet" href="css/style.css">

</head>

<body>

<div class="auth-container">

    <!-- LOGO -->

    <div class="logo">
        💡
    </div>


    <!-- HEADING -->

    <h1>
        Welcome Back
    </h1>


    <!-- SUBTITLE -->

    <p class="subtitle">
        Login to your Electricity Consumption Analyzer
    </p>


    <!-- REGISTRATION MESSAGE -->

    <?php if (isset($_GET['registered'])) { ?>

        <div class="success">
            Registration successful. Please login.
        </div>

    <?php } ?>


    <!-- LOGIN ERROR -->

    <?php if ($message != "") { ?>

        <div class="error">
            <?php echo htmlspecialchars($message); ?>
        </div>

    <?php } ?>


    <!-- LOGIN FORM -->

    <form method="POST">


        <!-- EMAIL -->

        <label>
            Email Address
        </label>
<div class="input-icon-box" style="
    position: relative;
    width: 100%;
">

    <svg class="field-icon" viewBox="0 0 24 24" style="
        position: absolute;
        left: 18px;
        top: 50%;
        transform: translateY(-50%);
        width: 25px;
        height: 25px;
        fill: none;
        stroke: #12355B;
        stroke-width: 2;
        z-index: 2;
    ">
        <circle cx="12" cy="7" r="4"></circle>
        <path d="M4 21c0-4.4 3.6-7 8-7s8 2.6 8 7"></path>
    </svg>

    <input
        type="email"
        name="email"
        placeholder="Enter your email"
        required
        style="
            width: 100%;
            height: 64px;
            padding: 15px 55px;
            background: #F8FAFD;
            color: #111827;
            border: 2px solid #2878B5;
            border-radius: 12px;
            font-size: 17px;
            box-sizing: border-box;
            outline: none;
        "
    >

</div>


        <!-- PASSWORD -->

        <label>
            Password
        </label>
<div class="password-box" style="
    position: relative;
    width: 100%;
">

    <svg class="field-icon" viewBox="0 0 24 24" style="
        position: absolute;
        left: 18px;
        top: 50%;
        transform: translateY(-50%);
        width: 25px;
        height: 25px;
        fill: none;
        stroke: #12355B;
        stroke-width: 2;
        z-index: 2;
    ">
        <rect x="5" y="10" width="14" height="11" rx="2"></rect>
        <path d="M8 10V7a4 4 0 0 1 8 0v3"></path>
    </svg>

    <input
        type="password"
        id="password"
        name="password"
        placeholder="Enter your password"
        required
        style="
            width: 100%;
            height: 64px;
            padding: 15px 55px;
            background: #F8FAFD;
            color: #111827;
            border: 2px solid #2878B5;
            border-radius: 12px;
            font-size: 17px;
            box-sizing: border-box;
            outline: none;
        "
    >

    <button
        type="button"
        class="eye-button"
        onclick="togglePassword()"
        style="
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            width: 42px;
            height: 42px;
            margin: 0;
            padding: 0;
            background: transparent;
            color: #12355B;
            border: none;
            font-size: 20px;
            z-index: 3;
        "
    >
        👁️
    </button>

</div>


        <!-- FORGOT PASSWORD -->

        <div class="forgot-password" style="
    text-align: right !important;
    margin-top: 12px !important;
">

    <a href="forgot_password.php" style="
        color: #f2b705 !important;
        text-decoration: none !important;
        font-size: 16px !important;
        font-weight: normal !important;
    ">
        Forgot Password?
    </a>

</div>


        <!-- LOGIN BUTTON -->

        <button
            type="submit"
            name="login"
            class="login-button"
        >

            <span class="login-icon">
                ⇥
            </span>

            Login

        </button>

    </form>


    <!-- CREATE ACCOUNT -->

    <p class="bottom-text">

        Don't have an account?

        <a href="register.php">
            Create Account
        </a>

    </p>


    <!-- ADMIN LOGIN -->

    <div class="admin-login">

        <a href="admin_login.php">
            🔐 Login as Admin
        </a>

    </div>

</div>


<!-- SHOW / HIDE PASSWORD -->

<script>

function togglePassword() {

    const password =
        document.getElementById("password");

    const button =
        document.querySelector(".eye-button");


    if (password.type === "password") {

        password.type = "text";

        button.innerHTML = "🙈";

    } else {

        password.type = "password";

        button.innerHTML = "👁️";
    }
}

</script>

</body>

</html>