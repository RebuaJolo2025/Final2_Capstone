<?php
    session_start();
    include '../conn.php';

    if (isset($_POST['submit'])) {
        $fn = $_POST['fullName'];
        $email = $_POST['email'];
        $address = $_POST['address'];
        $phone = $_POST['phone'];
        $password = $_POST['password'];

        $insert = mysqli_query($conn, "INSERT INTO `seller`(`id`, `fullname`, `email`, `address`, `phonenumber`, `password`) 
                                    VALUES (NULL, '$fn', '$email', '$address', '$phone', '$password')");

        if ($insert) {
            echo "<script>
                alert('Registered successfully');
                window.location.href = 'login.php';
                </script>";
        } else {
            echo "Error: " . mysqli_error($conn);
        }
    }

    // login query
    if(isset($_POST['login'])){
        $email = $_POST['email'];
        $password = $_POST['password'];

        $check = mysqli_query($conn, "SELECT * FROM `seller` WHERE email = '$email' AND password = '$password'");

        if(mysqli_num_rows($check) > 0){
            
            $_SESSION['email'] = $email;
            echo "<script>
                alert('Login successful');
                window.location.href = 'index.html';
                </script>";
        }else{
            echo "<script>
                alert('Login failed');
                window.location.href = 'login.php';
                </script>";
        }
    }

?>