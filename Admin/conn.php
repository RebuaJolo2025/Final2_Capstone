<?php
$conn = mysqli_connect("localhost", "root", "", "caps");
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?>
