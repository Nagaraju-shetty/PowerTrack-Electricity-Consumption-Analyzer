<?php
session_start();
include 'db.php';

$error = "";
$success = "";

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

if (isset($_POST['change_password'])) {

    $currentPassword = $_POST['current_password'];
    $newPassword = $_POST['new_password'];
    $confirmPassword = $_POST['confirm_password'];

    $adminId = $_SESSION['admin_id'];

    // Get current admin password
    $stmt = $conn->prepare(
        "SELECT password FROM admins WHERE id = ?"
    );

    $stmt->bind_param("i", $adminId);
    $stmt->execute();

    $result = $stmt->get_result();
    $admin = $result->fetch_assoc();

    if (!$admin) {

        $error = "Admin account not found.";

    } elseif (!password_verify($currentPassword, $admin['password'])) {

        $error = "Current password is incorrect.";

    } elseif (strlen($newPassword) < 6) {

        $error = "New password must be at least 6 characters.";

    } elseif ($newPassword !== $confirmPassword) {

        $error = "New passwords do not match.";

    } else {

        $hashedPassword = password_hash(
            $newPassword,
            PASSWORD_DEFAULT
        );

        $update = $conn->prepare(
            "UPDATE admins SET password = ? WHERE id = ?"
        );

        $update->bind_param(
            "si",
            $hashedPassword,
            $adminId
        );

        if ($update->execute()) {

            $success = "Admin password changed successfully!";

        } else {

            $error = "Unable to change password.";
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

<title>Change Admin Password | PowerTrack</title>
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

.container {
    width: 420px;
    max-width: 90%;

    background: #17212b;
    border: 1px solid #2b3944;

    border-radius: 14px;

    padding: 30px;
}

h2 {
    text-align: center;
    color: #ffc107;
    margin-bottom: 25px;
}

label {
    display: block;
    margin-bottom: 8px;

    color: #b9c4cc;
    font-weight: bold;
}

.password-box {
    position: relative;
    width: 100%;
}

.password-box input {
    width: 100%;

    padding: 13px;
    padding-right: 50px;

    background: #0f1720;
    color: white;

    border: 1px solid #34434a;
    border-radius: 8px;

    outline: none;
}

.password-box input:focus {
    border-color: #ffc107;
}

.eye-button {
    position: absolute;

    right: 8px;
    top: 50%;

    transform: translateY(-50%);

    width: 40px;
    height: 40px;

    padding: 0;
    margin: 0;

    background: transparent;
    color: #c2ccd1;

    border: none;

    cursor: pointer;

    font-size: 20px;
}

.eye-button:hover {
    background: transparent;
    color: #ffc107;
}

.change-btn {
    width: 100%;

    padding: 13px;

    margin-top: 10px;

    background: #ffc107;
    color: #111;

    border: none;
    border-radius: 8px;

    font-size: 16px;
    font-weight: bold;

    cursor: pointer;
}

.change-btn:hover {
    background: #e0a800;
}

.back-link {
    display: block;

    text-align: center;

    margin-top: 20px;

    color: #ffc107;
    text-decoration: none;

    font-weight: bold;
}

.back-link:hover {
    text-decoration: underline;
}

.error {
    background: #3a1f24;
    color: #ff6464;

    padding: 10px;
    border-radius: 8px;

    margin-bottom: 15px;

    text-align: center;
}

.success {
    background: #17352b;
    color: #36d399;

    padding: 10px;
    border-radius: 8px;

    margin-bottom: 15px;

    text-align: center;
}

@media (max-width: 500px) {

    .container {
        width: 90%;
        padding: 25px 20px;
    }

    h2 {
        font-size: 23px;
    }
}
.password-box {
    position: relative;
    width: 100%;
}

.password-box input {
    width: 100%;
    padding-right: 50px;
}

.eye-button {
    position: absolute;
    right: 8px;
    top: 50%;
    transform: translateY(-50%);

    width: 40px;
    height: 40px;

    margin: 0;
    padding: 0;

    background: transparent;
    color: #c2ccd1;

    border: none;
    cursor: pointer;

    font-size: 20px;
}

.eye-button:hover {
    background: transparent;
    color: #f2b705;
}

.eye-button:hover {
    background: transparent;
}
</style>
</head>

<body>
    <div class="container">

<h2>🔐 Change Admin Password</h2>

<?php if ($error != "") { ?>

    <p class="error">
    <?php echo htmlspecialchars($error); ?>
</p>

<?php } ?>

<?php if ($success != "") { ?>

    <p class="success">
    <?php echo htmlspecialchars($success); ?>
</p>

<?php } ?>

<form method="POST">

    <label>Current Password</label><br>

   <div class="password-box">
    <input
        type="password"
        id="current_password"
        name="current_password"
        required
    >

    <button
        type="button"
        class="eye-button"
        onclick="togglePassword('current_password', this)"
    >
        👁️
    </button>
</div>

    <br><br>

    <label>New Password</label><br>

    <div class="password-box">

    <input
        type="password"
        id="new_password"
        name="new_password"
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

    <br><br>

    <label>Confirm New Password</label><br>

    <div class="password-box">

    <input
        type="password"
        id="confirm_password"
        name="confirm_password"
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

    <br><br>

    <button
    type="submit"
    name="change_password"
    class="change-btn"
>
    🔐 Change Password
</button>

</form>

<br>

<a href="admin_dashboard.php" class="back-link">
    ← Back to Admin Dashboard
</a>
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
</div>
</body>
</html>