<?php
session_start();
include 'db.php';

$error = "";
$success = "";
$showResetForm = false;
$email = "";

// STEP 1: Check registered email
if (isset($_POST['check_email'])) {

    $email = trim($_POST['email']);

    $stmt = $conn->prepare(
        "SELECT id FROM users WHERE email = ?"
    );

    $stmt->bind_param("s", $email);
    $stmt->execute();

    $result = $stmt->get_result();

    if ($result->num_rows == 1) {

        $_SESSION['reset_email'] = $email;
        $showResetForm = true;

    } else {

        $error = "Email address is not registered.";
    }
}


// STEP 2: Reset password
if (isset($_POST['reset_password'])) {

    if (!isset($_SESSION['reset_email'])) {
        $error = "Please verify your email first.";
    } else {

        $newPassword = $_POST['new_password'];
        $confirmPassword = $_POST['confirm_password'];

        if (strlen($newPassword) < 6) {

            $error = "Password must be at least 6 characters.";
            $showResetForm = true;

        } elseif ($newPassword !== $confirmPassword) {

            $error = "Passwords do not match.";
            $showResetForm = true;

        } else {

            $hashedPassword = password_hash(
                $newPassword,
                PASSWORD_DEFAULT
            );

            $email = $_SESSION['reset_email'];

            $stmt = $conn->prepare(
                "UPDATE users
                 SET password = ?
                 WHERE email = ?"
            );

            $stmt->bind_param(
                "ss",
                $hashedPassword,
                $email
            );

            if ($stmt->execute()) {

                $success = "Password reset successfully!";

                unset($_SESSION['reset_email']);

            } else {

                $error = "Unable to reset password.";
                $showResetForm = true;
            }
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

<title>Forgot Password | PowerTrack</title>

<style>

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    min-height: 100vh;

    display: flex;
    justify-content: center;
    align-items: center;

    font-family: Arial, sans-serif;

    background:
        linear-gradient(
            135deg,
            #0d1117,
            #17252d,
            #0d1117
        );

    color: white;
}

.container {
    width: 420px;

    padding: 40px;

    background: #182126;

    border: 1px solid #34434a;
    border-radius: 18px;

    box-shadow:
        0 20px 50px rgba(0,0,0,0.5);
}

.logo {
    text-align: center;

    color: #f2b705;

    font-size: 24px;
    font-weight: bold;

    margin-bottom: 20px;
}

h2 {
    text-align: center;
    margin-bottom: 10px;
}

.subtitle {
    text-align: center;

    color: #9cabb2;

    font-size: 14px;

    margin-bottom: 25px;
}

label {
    display: block;

    margin-top: 15px;
    margin-bottom: 7px;

    color: #c2ccd1;
}

input {
    width: 100%;

    padding: 13px;

    background: #10171b;
    color: white;

    border: 1px solid #3a484e;
    border-radius: 8px;

    font-size: 15px;
}

input:focus {
    outline: none;
    border-color: #f2b705;
}
.password-box {
    position: relative;
    width: 100%;
}

.password-box input {
    padding-right: 50px;
}

.eye-button {
    position: absolute;
    right: 10px;
    top: 50%;
    transform: translateY(-50%);

    width: auto;
    margin: 0;
    padding: 5px;

    background: transparent;
    color: #c2ccd1;

    font-size: 18px;
}

.eye-button:hover {
    background: transparent;
    color: #f2b705;
}

button {
    width: 100%;

    padding: 14px;

    margin-top: 22px;

    border: none;
    border-radius: 8px;

    background: #f2b705;
    color: #171300;

    font-weight: bold;
    font-size: 15px;

    cursor: pointer;
}

.error {
    background: rgba(255,82,82,0.12);

    color: #ff8a80;

    border: 1px solid #ff5252;

    padding: 11px;

    border-radius: 7px;

    margin-bottom: 15px;

    text-align: center;
}

.success {
    background: rgba(53,208,181,0.12);

    color: #35d0b5;

    border: 1px solid #35d0b5;

    padding: 11px;

    border-radius: 7px;

    margin-bottom: 15px;

    text-align: center;
}

.back {
    text-align: center;
    margin-top: 22px;
}

.back a {
    color: #f2b705;
    text-decoration: none;
}

</style>

</head>

<body>

<div class="container">

    <div class="logo">
        ⚡ PowerTrack
    </div>

    <h2>🔑 Forgot Password</h2>

    <div class="subtitle">
        Reset your PowerTrack account password
    </div>


    <?php if ($error != "") { ?>

        <div class="error">
            <?php echo htmlspecialchars($error); ?>
        </div>

    <?php } ?>


    <?php if ($success != "") { ?>

        <div class="success">

            ✅ <?php echo htmlspecialchars($success); ?>

            <br><br>

            <a href="login.php"
               style="color:#f2b705;">
                Login with New Password
            </a>

        </div>

    <?php } ?>


    <?php
    if ($success == "" && !$showResetForm) {
    ?>

        <form method="POST">

            <label>
                Registered Email
            </label>

            <input
                type="email"
                name="email"
                placeholder="Enter your registered email"
                required
            >

            <button
                type="submit"
                name="check_email"
            >
                Continue
            </button>

        </form>

    <?php } ?>


    <?php
    if ($showResetForm) {
    ?>

       <form method="POST">

    <label>
        New Password
    </label>

    <div class="password-box">

        <input
            type="password"
            id="new_password"
            name="new_password"
            placeholder="Enter new password"
            minlength="6"
            required
        >

        <button
            type="button"
            class="eye-button"
            onclick="togglePassword('new_password', this)"
        >
            👁️
        </button>

    </div>


    <label>
        Confirm New Password
    </label>

    <div class="password-box">

        <input
            type="password"
            id="confirm_password"
            name="confirm_password"
            placeholder="Confirm new password"
            minlength="6"
            required
        >

        <button
            type="button"
            class="eye-button"
            onclick="togglePassword('confirm_password', this)"
        >
            👁️
        </button>

    </div>


    <button
        type="submit"
        name="reset_password"
    >
        🔐 Reset Password
    </button>

</form>

    <?php } ?>


    <div class="back">

        <a href="login.php">
            ← Back to Login
        </a>

    </div>

</div>
<script>

function togglePassword(fieldId, button) {

    const password = document.getElementById(fieldId);

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