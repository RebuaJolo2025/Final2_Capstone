<?php
session_start();
include 'conn.php';

if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit();
}

$email = $_SESSION['email'];
$successMsg = "";

// Handle profile picture upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] === UPLOAD_ERR_OK) {
    $fileTmpPath = $_FILES['profile_pic']['tmp_name'];
    $fileName = $_FILES['profile_pic']['name'];
    $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];

    if (in_array($fileExtension, $allowedExtensions)) {
        $uploadDir = './uploads/profile_pics/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        $newFileName = md5($email . time()) . '.' . $fileExtension;
        $destPath = $uploadDir . $newFileName;

        if (move_uploaded_file($fileTmpPath, $destPath)) {
            $profilePicPath = mysqli_real_escape_string($conn, $destPath);
            $emailSafe = mysqli_real_escape_string($conn, $email);
            $updateQuery = "UPDATE userdata SET profile_pic='$profilePicPath' WHERE email='$emailSafe'";
            if (mysqli_query($conn, $updateQuery)) {
                $successMsg = "✅ Profile picture updated successfully!";
            } else {
                $successMsg = "❌ Failed to update profile picture in database.";
            }
        } else {
            $successMsg = "❌ Error moving uploaded file.";
        }
    } else {
        $successMsg = "❌ Invalid file type. Allowed: jpg, jpeg, png, gif.";
    }
}

$query = "SELECT * FROM userdata WHERE email='" . mysqli_real_escape_string($conn, $email) . "'";
$result = mysqli_query($conn, $query);
$user = ($result && mysqli_num_rows($result) > 0) ? mysqli_fetch_assoc($result) : null;
if (!$user) {
    $user = [
        'fullname' => 'Unknown User',
        'email' => $email,
        'phonenumber' => '',
        'address' => '',
        'profile_pic' => ''
    ];
}
$profilePic = !empty($user['profile_pic']) ? $user['profile_pic'] : 'img/icon.png';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Profile Overview</title>
<style>
    body {
        font-family: "Poppins", sans-serif;
        background: linear-gradient(135deg, #d4f3d2, #f0fff0);
        margin: 0;
        padding: 0;
        color: #333;
    }
    header {
        background: linear-gradient(135deg, #4CAF50, #2e7d32);
        color: #fff;
        text-align: center;
        padding: 60px 20px 80px;
        font-size: 2.4rem;
        font-weight: 700;
        letter-spacing: 1px;
    }
    .container {
        max-width: 900px;
        background: #fff;
        margin: -60px auto 60px;
        border-radius: 25px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        padding: 40px;
        text-align: center;
    }
    .profile-pic {
        width: 170px;
        height: 170px;
        border-radius: 50%;
        border: 6px solid #4CAF50;
        object-fit: cover;
        cursor: pointer;
        transition: 0.3s ease;
    }
    .profile-pic:hover {
        transform: scale(1.08);
        box-shadow: 0 0 25px rgba(76, 175, 80, 0.4);
    }
    .profile-name {
        font-size: 1.8rem;
        font-weight: 700;
        margin-top: 20px;
        color: #388e3c;
    }
    .profile-email {
        color: #666;
        font-style: italic;
        margin-bottom: 25px;
    }
    .info-card {
        background: #f8fdf8;
        border-radius: 15px;
        padding: 20px;
        margin: 30px auto;
        box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        text-align: left;
        max-width: 600px;
    }
    .info-card h3 {
        color: #4CAF50;
        margin-bottom: 15px;
        border-bottom: 2px solid #a5d6a7;
        padding-bottom: 5px;
    }
    .info-item {
        display: flex;
        justify-content: space-between;
        margin: 10px 0;
        font-size: 1.1rem;
    }
    .info-item strong {
        color: #2e7d32;
    }
    .btn-group {
        margin-top: 30px;
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
        gap: 15px;
    }
    .btn {
        padding: 14px 25px;
        border: none;
        border-radius: 12px;
        font-size: 1.05rem;
        font-weight: 600;
        color: white;
        cursor: pointer;
        text-decoration: none;
        transition: all 0.3s ease;
    }
    .btn.primary { background: #4CAF50; }
    .btn.primary:hover { background: #43a047; }
    .btn.accent { background: #2196F3; }
    .btn.accent:hover { background: #1976D2; }
    .btn.danger { background: #e74c3c; }
    .btn.danger:hover { background: #c0392b; }
    .btn.secondary { background: #ddd; color: #222; }
    .btn.secondary:hover { background: #bbb; }

    .message {
        margin: 20px auto;
        padding: 15px 20px;
        border-radius: 12px;
        font-weight: 600;
        width: 90%;
        max-width: 600px;
    }
    .success { background: #d4edda; color: #155724; }
    .error { background: #f8d7da; color: #721c24; }

    @media(max-width: 600px) {
        header { font-size: 2rem; }
        .container { padding: 30px 20px; }
    }
</style>
</head>
<body>

<header>👤 My Profile</header>

<div class="container">
    <form method="POST" enctype="multipart/form-data" id="uploadForm">
        <label for="profilePicInput">
            <img src="<?= htmlspecialchars($profilePic); ?>" alt="Profile Picture" class="profile-pic">
        </label>
        <input type="file" name="profile_pic" id="profilePicInput" accept="image/*" style="display:none">
    </form>

    <div class="profile-name"><?= htmlspecialchars($user['fullname']); ?></div>
    <div class="profile-email"><?= htmlspecialchars($user['email']); ?></div>

    <?php if ($successMsg): ?>
        <div class="message <?= strpos($successMsg, '✅') === 0 ? 'success' : 'error'; ?>">
            <?= htmlspecialchars($successMsg); ?>
        </div>
    <?php endif; ?>

    <div class="info-card">
        <h3>Personal Information</h3>
        <div class="info-item"><strong>Full Name:</strong> <?= htmlspecialchars($user['fullname']); ?></div>
        <div class="info-item"><strong>Phone:</strong> <?= htmlspecialchars($user['phonenumber']); ?></div>
        <div class="info-item"><strong>Address:</strong> <?= htmlspecialchars($user['address']); ?></div>
    </div>

    <div class="btn-group">
        <a href="index.php" class="btn secondary">🏠 Home</a>
        <a href="edit-profile.php" class="btn primary">✏️ Edit Profile</a>
        <a href="track_order.php" class="btn accent">🛒 Track Orders</a>
        <a href="logout.php" class="btn danger">🚪 Logout</a>
    </div>
</div>

<script>
    document.getElementById('profilePicInput').addEventListener('change', function() {
        if (this.files.length > 0) {
            document.getElementById('uploadForm').submit();
        }
    });
</script>

</body>
</html>
