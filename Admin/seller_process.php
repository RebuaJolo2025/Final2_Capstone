<?php
session_start();
include '../conn.php'; // go up one folder to reach conn.php

// -------------------------
// REGISTER
// -------------------------
if (isset($_POST['submit'])) {
    $fn = $_POST['fullName'];
    $email = $_POST['email'];
    $address = $_POST['address'];
    $phone = $_POST['phone'];
    $password = $_POST['password'];
    $birthdate = isset($_POST['birthdate']) ? $_POST['birthdate'] : null;
    $role = isset($_POST['role']) ? $_POST['role'] : 'admin'; // default to admin if missing

    // Check if email already exists
    $check_email = mysqli_query($conn, "SELECT * FROM userdata WHERE email='$email'");
    if (mysqli_num_rows($check_email) > 0) {
        echo "<script>
            alert('Email already exists!');
            window.location.href = 'register.html';
        </script>";
        exit;
    }

    // Insert data into userdata table (admin account)
    $insert = mysqli_query($conn, "INSERT INTO userdata (fullname, email, address, phonenumber, password, role, birthdate)
                                   VALUES ('$fn', '$email', '$address', '$phone', '$password', '$role', '$birthdate')");

    if ($insert) {
        echo "<script>
            alert('Admin registered successfully! Please log in.');
            window.location.href = 'login.php';
        </script>";
    } else {
        echo 'Error: ' . mysqli_error($conn);
    }
}

// -------------------------
// LOGIN
// -------------------------
if (isset($_POST['login'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $check = mysqli_query($conn, "SELECT * FROM userdata WHERE email='$email' AND password='$password' AND role='admin'");

    if (mysqli_num_rows($check) > 0) {
        $row = mysqli_fetch_assoc($check);
        $_SESSION['email'] = $row['email'];
        $_SESSION['role'] = $row['role'];

        echo "<script>
            alert('Login successful!');
            window.location.href = 'index.php'; // redirect to admin dashboard
        </script>";
    } else {
        echo "<script>
            alert('Login failed! Incorrect email or password.');
            window.location.href = 'login.php';
        </script>";
    }
}
?>
