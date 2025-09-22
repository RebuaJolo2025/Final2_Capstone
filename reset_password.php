<?php
$conn = new mysqli("localhost", "root", "", "ifx");
if ($conn->connect_error) {
    die("DB connection failed: " . $conn->connect_error);
}

if (!isset($_GET['token'])) {
    die("Invalid request");
}

$token = $_GET['token'];
$error = '';
$success = '';

// Check if token exists and not expired
$sql = "SELECT id FROM userdata WHERE reset_token = '$token' AND reset_token_expire > NOW()";
$result = $conn->query($sql);

if ($result->num_rows == 0) {
    die("This reset link is invalid or expired.");
}

$user = $result->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $password = $_POST['password'];
    $confirm = $_POST['confirm_password'];

    if ($password != $confirm) {
        $error = "Passwords do not match.";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters.";
    } else {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $id = $user['id'];
        $update = "UPDATE userdata SET password='$hash', reset_token=NULL, reset_token_expire=NULL WHERE id=$id";
        if ($conn->query($update)) {
            $success = "Password has been reset successfully.";
        } else {
            $error = "Failed to update password.";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head><title>Reset Password</title></head>
<body>
    <h2>Reset Password</h2>

    <?php if ($error) echo "<p style='color:red;'>$error</p>"; ?>
    <?php if ($success) echo "<p style='color:green;'>$success</p>"; ?>

    <?php if (!$success): ?>
    <form method="POST">
        <label>New Password:</label><br>
        <input type="password" name="password" required><br><br>
        <label>Confirm Password:</label><br>
        <input type="password" name="confirm_password" required><br><br>
        <button type="submit">Reset Password</button>
    </form>
    <?php endif; ?>
</body>
</html>
