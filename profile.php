<?php
session_start();
include 'conn.php';

if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit();
}

$email = $_SESSION['email'];

// Handle profile picture upload
$successMsg = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] === UPLOAD_ERR_OK) {
    $fileTmpPath = $_FILES['profile_pic']['tmp_name'];
    $fileName = $_FILES['profile_pic']['name'];
    $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

    $allowedfileExtensions = ['jpg', 'jpeg', 'png', 'gif'];
    if (in_array($fileExtension, $allowedfileExtensions)) {
        $uploadFileDir = './uploads/profile_pics/';
        if (!is_dir($uploadFileDir)) {
            mkdir($uploadFileDir, 0755, true);
        }
        $newFileName = md5($email . time()) . '.' . $fileExtension;
        $dest_path = $uploadFileDir . $newFileName;

        if (move_uploaded_file($fileTmpPath, $dest_path)) {
            $profilePicPath = mysqli_real_escape_string($conn, $dest_path);
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

// Fetch updated user info
$query = "SELECT * FROM userdata WHERE email='$email'";
$result = mysqli_query($conn, $query);
$user = mysqli_fetch_assoc($result);
$profilePic = $user['profile_pic'] ?? './img/jolo.jpg';

// Create orders table if not exists
mysqli_query($conn, "
    CREATE TABLE IF NOT EXISTS orders (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_email VARCHAR(255) NOT NULL,
        order_total DECIMAL(10,2) NOT NULL,
        order_date DATETIME DEFAULT CURRENT_TIMESTAMP
    )
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Profile Overview</title>
<style>
  :root {
    --primary: #4CAF50;
    --accent: #2196F3;
    --danger: #e74c3c;
    --bg: #f0f4f8;
    --text: #333;
    --card-bg: #fff;
    --radius: 18px;
    --shadow: 0 10px 30px rgba(0,0,0,0.08);
  }
  * { box-sizing: border-box; }
  body {
    margin: 0;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background: linear-gradient(135deg, #e8f0fe, #f9fafd);
    color: var(--text);
    line-height: 1.6;
  }
  header {
    background: linear-gradient(135deg, var(--primary), #2e7d32);
    color: white;
    text-align: center;
    padding: 65px 20px 45px;
    font-size: 2.5rem;
    font-weight: 700;
    box-shadow: var(--shadow);
    letter-spacing: 0.05em;
  }
  .container {
    max-width: 900px;
    margin: -50px auto 60px;
    background: var(--card-bg);
    border-radius: var(--radius);
    box-shadow: var(--shadow);
    padding: 45px 35px 65px;
    display: flex;
    flex-wrap: wrap;
    gap: 45px;
  }
  .back-home-btn-wrapper {
    width: 100%;
    text-align: center;
    margin-bottom: 20px;
  }
  .profile-section {
    flex: 1 1 270px;
    text-align: center;
    border-right: 1px solid #eee;
    padding-right: 35px;
  }
  .profile-pic {
    width: 170px;
    height: 170px;
    border-radius: 50%;
    object-fit: cover;
    border: 6px solid var(--primary);
    cursor: pointer;
    transition: transform 0.3s ease;
  }
  .profile-pic:hover {
    transform: scale(1.1);
  }
  .profile-name {
    margin-top: 22px;
    font-size: 2rem;
    font-weight: 700;
    color: var(--primary);
  }
  .profile-email {
    margin-top: 8px;
    font-size: 1.15rem;
    color: #666;
    font-style: italic;
    user-select: text;
  }
  .info-section {
    flex: 2 1 520px;
  }
  .info-section h3 {
    font-size: 1.5rem;
    margin-bottom: 18px;
    color: var(--primary);
    border-bottom: 2.5px solid var(--primary);
    padding-bottom: 8px;
  }
  .info-list {
    list-style: none;
    padding: 0;
    margin: 0;
  }
  .info-list li {
    background: #fafafa;
    padding: 18px 22px;
    margin-bottom: 15px;
    border-radius: 14px;
    font-size: 1.15rem;
    display: flex;
    justify-content: space-between;
    box-shadow: 0 3px 12px rgba(0,0,0,0.04);
    transition: background-color 0.3s ease;
  }
  .info-list li:hover {
    background-color: #f0fff0;
  }
  .info-list li strong {
    color: var(--primary);
  }
  .actions {
    margin-top: 48px;
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
    gap: 20px;
    justify-items: center;
  }
  .btn {
    width: 100%;
    max-width: 180px;
    padding: 16px 0;
    border: none;
    border-radius: var(--radius);
    cursor: pointer;
    font-weight: 700;
    font-size: 1.1rem;
    color: white;
    box-shadow: 0 6px 16px rgba(0,0,0,0.14);
    transition: background-color 0.35s ease, box-shadow 0.35s ease;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    text-decoration: none;
    user-select: none;
    text-align: center;
  }
  .btn.primary {
    background-color: var(--primary);
  }
  .btn.primary:hover {
    background-color: #388e3c;
    box-shadow: 0 8px 25px rgba(56,142,60,0.6);
  }
  .btn.accent {
    background-color: var(--accent);
  }
  .btn.accent:hover {
    background-color: #1976D2;
    box-shadow: 0 8px 25px rgba(25,118,210,0.6);
  }
  .btn.danger {
    background-color: var(--danger);
  }
  .btn.danger:hover {
    background-color: #c0392b;
    box-shadow: 0 8px 25px rgba(192,57,43,0.6);
  }
  .btn.secondary {
    background-color: #ddd;
    color: #222;
    max-width: 160px;
  }
  .btn.secondary:hover {
    background-color: #bbb;
    box-shadow: 0 8px 25px rgba(170,170,170,0.6);
  }
  .message {
    margin-bottom: 35px;
    padding: 16px 24px;
    border-radius: var(--radius);
    font-weight: 600;
    font-size: 1.05rem;
    max-width: 840px;
    margin-left: auto;
    margin-right: auto;
    text-align: center;
    user-select: none;
  }
  .success {
    background-color: #d4edda;
    color: #155724;
    border: 1.5px solid #c3e6cb;
  }
  .error {
    background-color: #f8d7da;
    color: #721c24;
    border: 1.5px solid #f5c6cb;
  }
  footer {
    text-align: center;
    font-size: 0.95rem;
    color: #aaa;
    padding-bottom: 28px;
  }
  @media (max-width: 720px) {
    .container {
      flex-direction: column;
      margin: 20px auto 40px;
      padding: 35px 25px 50px;
    }
    .profile-section {
      border-right: none;
      padding-right: 0;
      margin-bottom: 35px;
    }
  }
</style>
</head>
<body>

<header>👤 Profile Overview</header>

<div class="container">

    <div class="back-home-btn-wrapper">
        <a href="index.php" class="btn secondary">⬅ Back to Home</a>
    </div>

    <?php if ($successMsg): ?>
        <div class="message <?= strpos($successMsg, '✅') === 0 ? 'success' : 'error'; ?>">
            <?= htmlspecialchars($successMsg); ?>
        </div>
    <?php endif; ?>

    <div class="profile-section">
        <form method="POST" enctype="multipart/form-data" id="uploadForm">
            <label for="profilePicInput" title="Click to change profile picture" style="cursor:pointer; display:inline-block;">
                <img 
                    src="<?= htmlspecialchars($profilePic); ?>" 
                    alt="Profile Picture" 
                    class="profile-pic" 
                    onerror="this.src='default-avatar.png';"
                />
            </label>
            <input type="file" name="profile_pic" id="profilePicInput" accept="image/*" style="display:none" />
        </form>

        <div class="profile-name"><?= htmlspecialchars($user['fullname']); ?></div>
        <div class="profile-email"><?= htmlspecialchars($user['email']); ?></div>

        <div class="actions">
            <a href="edit-profile.php" class="btn primary">✏️ Edit Profile</a>
            <a href="track_order.php" class="btn accent">🛒 Track My Orders</a>
            <a href="logout.php" class="btn danger">🚪 Log Out</a>
        </div>
    </div>

    <div class="info-section">
        <h3>Personal Information</h3>
        <ul class="info-list">
            <li><strong>Full Name:</strong> <span><?= htmlspecialchars($user['fullname']); ?></span></li>
            <li><strong>Phone Number:</strong> <span><?= htmlspecialchars($user['phonenumber']); ?></span></li>
            <li><strong>Location:</strong> <span><?= htmlspecialchars($user['address']); ?></span></li>
        </ul>
    </div>
</div>



<script>
    document.getElementById('profilePicInput').addEventListener('change', function() {
        if(this.files.length > 0) {
            document.getElementById('uploadForm').submit();
        }
    });
</script>

</body>
</html>
