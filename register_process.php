<?php
include 'conn.php';

if (isset($_POST['submit'])) {
    $fullName = $_POST['fullName'];
    $email    = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $sql = "INSERT INTO users (fullName, email, password) 
            VALUES ('$fullName', '$email', '$password')";

    if (mysqli_query($conn, $sql)) {
        echo "<script>alert('Registration successful! Please login.'); window.location='login.php';</script>";
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}
?>
