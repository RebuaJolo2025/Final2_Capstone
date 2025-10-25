<?php
session_start();
include '../conn.php';

// -------------------------
// REGISTER
// -------------------------
if (isset($_POST['submit'])) {
    $fn = $_POST['fullName'];
    $email = $_POST['email'];
    $address = $_POST['address'];
    $phone = $_POST['phone'];
    $password = $_POST['password'];

    // Optional: prevent duplicate email
    $check_email = mysqli_query($conn, "SELECT * FROM seller WHERE email='$email'");
    if (mysqli_num_rows($check_email) > 0) {
        echo "<script>
            alert('Email already exists!');
            window.location.href = 'register.php';
        </script>";
        exit;
    }

    // Insert seller data
    $insert = mysqli_query($conn, "INSERT INTO seller (fullname, email, address, phonenumber, password)
                                   VALUES ('$fn', '$email', '$address', '$phone', '$password')");

    if ($insert) {
        echo "<script>
            alert('Registered successfully! Please log in.');
            window.location.href = 'login.php';
        </script>";
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}

// -------------------------
// LOGIN
// -------------------------
if (isset($_POST['login'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $check = mysqli_query($conn, "SELECT * FROM seller WHERE email='$email' AND password='$password'");

    if (mysqli_num_rows($check) > 0) {
        $row = mysqli_fetch_assoc($check);

        // store to session
        $_SESSION['email'] = $row['email'];

        echo "<script>
            alert('Login successful!');
            window.location.href = 'index.php';
        </script>";
    } else {
        echo "<script>
            alert('Login failed! Incorrect email or password.');
            window.location.href = 'login.php';
        </script>";
    }
}
?>
