<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$userId = $_SESSION['user_id'];
$message = "";
$error = "";

// Update profile
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $name = trim($_POST['name']);
    $email = trim($_POST['email']);

    if ($name == "" || $email == "") {

        $error = "Name and email cannot be empty.";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $error = "Please enter a valid email address.";

    } else {

        // Check whether email belongs to another user
        $check = $conn->prepare(
            "SELECT id FROM users
             WHERE email = ? AND id != ?"
        );

        $check->bind_param("si", $email, $userId);
        $check->execute();

        $checkResult = $check->get_result();

        if ($checkResult->num_rows > 0) {

            $error = "This email is already registered.";

        } else {

            $stmt = $conn->prepare(
                "UPDATE users
                 SET name = ?, email = ?
                 WHERE id = ?"
            );

            $stmt->bind_param(
                "ssi",
                $name,
                $email,
                $userId
            );

            if ($stmt->execute()) {
                $message = "Profile updated successfully!";
            } else {
                $error = "Unable to update profile.";
            }
        }
    }
}
// Change password
if (isset($_POST['change_password'])) {

    $currentPassword = $_POST['current_password'];
    $newPassword = $_POST['new_password'];
    $confirmPassword = $_POST['confirm_password'];

    // Get current password from database
    $stmt = $conn->prepare(
        "SELECT password FROM users WHERE id = ?"
    );

    $stmt->bind_param("i", $userId);
    $stmt->execute();

    $passwordResult = $stmt->get_result();
    $passwordUser = $passwordResult->fetch_assoc();

    // Check current password
    if (!password_verify(
        $currentPassword,
        $passwordUser['password']
    )) {

        $error = "Current password is incorrect.";

    } elseif (strlen($newPassword) < 6) {

        $error = "New password must be at least 6 characters.";

    } elseif ($newPassword !== $confirmPassword) {

        $error = "New password and confirm password do not match.";

    } else {

        // Hash new password
        $hashedPassword = password_hash(
            $newPassword,
            PASSWORD_DEFAULT
        );

        $stmt = $conn->prepare(
            "UPDATE users
             SET password = ?
             WHERE id = ?"
        );

        $stmt->bind_param(
            "si",
            $hashedPassword,
            $userId
        );

        if ($stmt->execute()) {
            $message = "Password changed successfully!";
        } else {
            $error = "Unable to change password.";
        }
    }
}
// Get current user information
$stmt = $conn->prepare(
    "SELECT id, name, email
     FROM users
     WHERE id = ?"
);

$stmt->bind_param("i", $userId);
$stmt->execute();

$result = $stmt->get_result();
$user = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>My Profile | PowerTrack</title>

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
}

.sidebar {
    position: fixed;
    left: 0;
    top: 0;

    width: 240px;
    height: 100vh;

    background: #17212b;
    border-right: 1px solid #2d3a45;

    padding: 25px 20px;
    overflow-y: auto;
}

.logo {
    color: #ffc107;
    font-size: 21px;
    font-weight: bold;
    margin-bottom: 40px;
}

.menu a {
    display: block;

    color: #b9c4cc;
    text-decoration: none;

    padding: 14px 15px;
    margin-bottom: 10px;

    border-radius: 8px;
}

.menu a:hover,
.menu .active {
    background: #ffc107;
    color: #111;
}

.main {
    margin-left: 240px;
    padding: 35px;
}

.header {
    margin-bottom: 30px;
}

.header h1 {
    margin-bottom: 8px;
}

.header p {
    color: #91a0aa;
}

.profile-card {
    max-width: 650px;

    background: #17212b;
    border: 1px solid #2b3944;

    border-radius: 15px;
    padding: 30px;
}

.user-id {
    background: #101820;
    padding: 15px;
    border-radius: 8px;
    margin-bottom: 25px;
    color: #91a0aa;
}

label {
    display: block;
    margin-bottom: 8px;
    font-weight: bold;
}

input {
    width: 100%;

    padding: 13px;
    margin-bottom: 20px;

    background: #101820;
    color: white;

    border: 1px solid #3a4853;
    border-radius: 8px;

    font-size: 16px;
}

input:focus {
    outline: none;
    border-color: #ffc107;
}

button {
    background: #ffc107;
    color: #111;

    border: none;
    border-radius: 8px;

    padding: 13px 22px;

    font-size: 16px;
    font-weight: bold;

    cursor: pointer;
}

.message {
    max-width: 650px;

    background: #163b31;
    color: #55e6b1;

    padding: 15px;
    border-radius: 8px;

    margin-bottom: 20px;
}

.error {
    max-width: 650px;

    background: #3b1c1c;
    color: #ff7777;

    padding: 15px;
    border-radius: 8px;

    margin-bottom: 20px;
}

@media(max-width: 700px) {

    .sidebar {
        position: static;
        width: 100%;
        height: auto;
    }

    .main {
        margin-left: 0;
        padding: 20px;
    }
}

</style>

</head>

<body>

<?php include 'sidebar.php'; ?>


<div class="main">

    <div class="header">

        <h1>👤 My Profile</h1>

        <p>
            View and update your account information.
        </p>

    </div>


    <?php if ($message != "") { ?>

        <div class="message">
            ✅ <?php echo htmlspecialchars($message); ?>
        </div>

    <?php } ?>


    <?php if ($error != "") { ?>

        <div class="error">
            ⚠️ <?php echo htmlspecialchars($error); ?>
        </div>

    <?php } ?>


    <div class="profile-card">

        <div class="user-id">

            User ID:
            <strong>
                <?php echo (int)$user['id']; ?>
            </strong>

        </div>


        <form method="POST">

            <label>
                Full Name
            </label>

            <input
                type="text"
                name="name"
                value="<?php echo htmlspecialchars($user['name']); ?>"
                required
            >


            <label>
                Email Address
            </label>

            <input
                type="email"
                name="email"
                value="<?php echo htmlspecialchars($user['email']); ?>"
                required
            >


            <button type="submit">
                💾 Update Profile
            </button>

        </form>
        

  

    </div>

</div>

</body>
</html>