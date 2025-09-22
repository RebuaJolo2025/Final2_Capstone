<?php
session_start();
include 'conn.php'; // Make sure this file contains your database connection

// Check if the user is logged in
if (!isset($_SESSION['email'])) {
    header("Location: login.php"); // Redirect to login if not logged in
    exit();
}

$email = $_SESSION['email'];

// Fetch user data from the database
$query = "SELECT * FROM userdata WHERE email='$email'";
$result = mysqli_query($conn, $query);
$user = mysqli_fetch_assoc($result);

// Handle the form submission (update profile)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fullname = mysqli_real_escape_string($conn, $_POST['fullname']);
    $phonenumber = mysqli_real_escape_string($conn, $_POST['phonenumber']);
    $address = mysqli_real_escape_string($conn, $_POST['address']);
    $birthdate = mysqli_real_escape_string($conn, $_POST['birthdate']);

    // Update the user data in the database
    $update = "UPDATE userdata SET fullname='$fullname', phonenumber='$phonenumber', address='$address', birthdate='$birthdate' WHERE email='$email'";
    
    if (mysqli_query($conn, $update)) {
        header("Location: profile.php"); // Redirect to profile page after successful update
        exit();
    } else {
        echo "Update failed: " . mysqli_error($conn); // Show error if the update fails
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Profile</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: #e8f5e9;
            margin: 0;
            padding: 0;
        }

        .back-link {
            position: absolute;
            top: 20px;
            left: 20px;
            text-decoration: none;
            color: #388E3C;
            font-size: 1rem;
            font-weight: 500;
            background: white;
            padding: 8px 12px;
            border-radius: 6px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
            transition: background 0.3s;
        }

        .back-link:hover {
            background: #f0f0f0;
        }

        .container {
            max-width: 500px;
            margin: 100px auto;
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.1);
        }

        h2 {
            text-align: center;
            color: #388E3C;
            font-weight: 600;
            margin-bottom: 20px;
        }

        form {
            display: flex;
            flex-direction: column;
        }

        label {
            font-size: 1rem;
            margin-bottom: 5px;
            color: #555;
        }

        input[type="text"],
        input[type="tel"],
        input[type="date"] {
            padding: 15px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 10px;
            font-size: 1rem;
            transition: border-color 0.3s ease-in-out;
        }

        input:focus {
            border-color: #388E3C;
            outline: none;
        }

        button {
            margin-top: 20px;
            padding: 15px;
            background: #388E3C;
            color: white;
            border: none;
            border-radius: 10px;
            font-weight: 600;
            font-size: 1.2rem;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        button:hover {
            background-color: #2c6e29;
        }
    </style>
</head>
<body>

<a href="profile.php" class="back-link">⬅ Back to Profile</a>

<div class="container">
    <h2>Edit Profile</h2>
    <form method="POST" action="">
        <label for="fullname">Full Name</label>
        <input type="text" name="fullname" id="fullname" value="<?php echo htmlspecialchars($user['fullname']); ?>" required>

        <label for="phonenumber">Phone Number</label>
        <input type="tel" name="phonenumber" id="phonenumber" value="<?php echo htmlspecialchars($user['phonenumber']); ?>" required>

        <label for="address">Address</label>
        <input type="text" name="address" id="address" value="<?php echo htmlspecialchars($user['address']); ?>" required>

        <label for="birthdate">Birth Date</label>
        <input type="date" name="birthdate" id="birthdate" value="<?php echo isset($user['birthdate']) ? htmlspecialchars($user['birthdate']) : ''; ?>" required>

        <button type="submit">Save Changes</button>
    </form>
</div>

</body>
</html>
