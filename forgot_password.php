<?php
session_start();
// Load PHPMailer
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';
require 'PHPMailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

// DB connection
$conn = new mysqli("localhost", "root", "", "ifx");
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

$message = '';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);

    // Check if email exists
    $stmt = $conn->prepare("SELECT id FROM userdata WHERE email=?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        // Generate secure token
        $token = bin2hex(random_bytes(50));
        $expiry = date("Y-m-d H:i:s", time() + 3600); // 1 hour expiration
        
        // Save to database
        $update = $conn->prepare("UPDATE userdata SET reset_token=?, reset_token_expire=? WHERE email=?");
        $update->bind_param("sss", $token, $expiry, $email);
        
        if ($update->execute()) {
            // Configure PHPMailer
            $mail = new PHPMailer(true);
            try {
                // Server settings
                $mail->SMTPDebug = SMTP::DEBUG_OFF; // Set to DEBUG_SERVER for troubleshooting
                $mail->isSMTP();
                $mail->Host       = 'smtp.gmail.com';
                $mail->SMTPAuth   = true;
                $mail->Username   = 'alejahjaneopiala@gmail.com';
                $mail->Password   = 'itlh bqaz xvjq jwgh';
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // Use TLS
                $mail->Port       = 587;
                
                // Bypass SSL verification (temporary solution)
                $mail->SMTPOptions = [
                    'ssl' => [
                        'verify_peer' => false,
                        'verify_peer_name' => false,
                        'allow_self_signed' => true
                    ]
                ];

                // Recipients
                $mail->setFrom('alejahjaneopiala@gmail.com', 'Password Reset');
                $mail->addAddress($email);
                
                // Content
                $resetUrl = (isset($_SERVER['HTTPS']) ? 'https://' : 'http://') . 
                          $_SERVER['HTTP_HOST'] . '/Caps/reset_password.php?token=' . $token;
                
                $mail->isHTML(true);
                $mail->Subject = 'Password Reset Request';
                $mail->Body    = "
                    <h2>Password Reset</h2>
                    <p>Click the button below to reset your password:</p>
                    <a href='$resetUrl' style='
                        background: #4CAF50;
                        color: white;
                        padding: 10px 15px;
                        text-decoration: none;
                        border-radius: 5px;
                        display: inline-block;
                    '>Reset Password</a>
                    <p><small>This link expires in 1 hour.</small></p>
                ";
                $mail->AltBody = "Reset your password: $resetUrl";

                $mail->send();
                $_SESSION['message'] = "Password reset link sent to your email.";
            } catch (Exception $e) {
                error_log("Mailer Error: " . $mail->ErrorInfo);
                $_SESSION['error'] = "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
            }
        } else {
            $_SESSION['error'] = "Database error. Please try again.";
        }
    } else {
        $_SESSION['error'] = "If this email exists, a reset link will be sent.";
    }
    
    header("Location: ".$_SERVER['PHP_SELF']);
    exit();
}

// Display messages
if (isset($_SESSION['message'])) {
    echo '<div style="color:green; margin:10px 0;">'.$_SESSION['message'].'</div>';
    unset($_SESSION['message']);
}
if (isset($_SESSION['error'])) {
    echo '<div style="color:red; margin:10px 0;">'.$_SESSION['error'].'</div>';
    unset($_SESSION['error']);
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Reset Password</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 500px; margin: 0 auto; padding: 20px; }
        form { background: #f9f9f9; padding: 20px; border-radius: 5px; }
        input[type="email"] { width: 100%; padding: 8px; margin: 8px 0; box-sizing: border-box; }
        button { background: #4CAF50; color: white; padding: 10px 15px; border: none; border-radius: 4px; cursor: pointer; }
    </style>
</head>
<body>
    <h2>Reset Password</h2>
    <form method="POST">
        <label for="email">Email Address:</label>
        <input type="email" id="email" name="email" required>
        <button type="submit">Send Reset Link</button>
    </form>
</body>
</html>