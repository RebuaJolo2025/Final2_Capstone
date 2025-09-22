<?php
$servername = "localhost";
$username   = "root";     // default XAMPP user
$password   = "";         // default XAMPP has no password
$database   = "ifx";      // <-- your database name

// Create connection
$conn = mysqli_connect($servername, $username, $password, $database);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Optional: Confirm database selection (not required, but safe)
if (!mysqli_select_db($conn, $database)) {
    die("Database selection failed: " . mysqli_error($conn));
}
?>
