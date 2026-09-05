<?php
session_start();
include 'db.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $username = trim($_POST['username']);
    $password = $_POST['password'];

    $stmt = $conn->prepare(
        "SELECT id, username, password
         FROM admins
         WHERE username = ?"
    );

    $stmt->bind_param("s", $username);
    $stmt->execute();

    $result = $stmt->get_result();

    if ($result->num_rows == 1) {

        $admin = $result->fetch_assoc();

        if (password_verify($password, $admin['password'])) {

            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_username'] = $admin['username'];

            header("Location: admin_dashboard.php");
            exit();

        } else {
            $error = "Invalid username or password.";
        }

    } else {
        $error = "Invalid username or password.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Admin Login | PowerTrack</title>

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

    min-height: 100vh;

    display: flex;
    justify-content: center;
    align-items: center;
}

.login-box {
    width: 400px;
    background: #17212b;
    border: 1px solid #2d3a45;
    border-radius: 15px;
    padding: 35px;
    box-shadow: 0 10px 35px rgba(0,0,0,0.4);
}

.logo {
    text-align: center;
    font-size: 40px;
    margin-bottom: 10px;
}

h1 {
    text-align: center;
    margin-bottom: 8px;
}

.subtitle {
    text-align: center;
    color: #91a0aa;
    margin-bottom: 30px;
}

label {
    display: block;
    margin-bottom: 8px;
    color: #c8d1d7;
}

input {
    width: 100%;
    padding: 13px;

    margin-bottom: 20px;

    border: 1px solid #3a4853;
    border-radius: 8px;

    background: #101820;
    color: white;

    outline: none;
}

input:focus {
    border-color: #ffc107;
}

button {
    width: 100%;
    padding: 13px;

    border: none;
    border-radius: 8px;

    background: #ffc107;
    color: #111;

    font-size: 16px;
    font-weight: bold;

    cursor: pointer;
}

button:hover {
    background: #e5ad00;
}

.error {
    background: #472020;
    color: #ff9b9b;

    padding: 12px;
    border-radius: 8px;

    margin-bottom: 20px;
    text-align: center;
}

.back {
    display: block;
    text-align: center;

    margin-top: 20px;

    color: #ffc107;
    text-decoration: none;
}

.back:hover {
    text-decoration: underline;
}
@media (max-width: 500px) {

    .login-box {
        width: 90%;
        padding: 25px 20px;
    }

    h1 {
        font-size: 26px;
    }

    .subtitle {
        font-size: 14px;
    }

    input {
        padding: 12px;
    }

    button {
        padding: 13px;
    }
}
</style>

</head>

<body>

<div class="login-box">

    <div class="logo">
        ⚡
    </div>

    <h1>Admin Login</h1>

    <p class="subtitle">
        PowerTrack Administration
    </p>

    <?php if ($error != "") { ?>

        <div class="error">
            <?php echo htmlspecialchars($error); ?>
        </div>

    <?php } ?>

    <form method="POST">

        <label>Admin Username</label>

        <input
            type="text"
            name="username"
            placeholder="Enter admin username"
            required
        >

        <label>Password</label>

        <input
            type="password"
            name="password"
            placeholder="Enter password"
            required
        >

        <button type="submit">
            🔐 Login as Admin
        </button>

    </form>

    <a href="login.php" class="back">
        ← Back to User Login
    </a>

</div>

</body>

</html>