<?php
session_start();
include 'conn.php';

// ===== REGISTER =====
if (isset($_POST['submit'])) {
    $fn = $_POST['fullName'];
    $email = $_POST['email'];
    $address = $_POST['address'];
    $phone = $_POST['phone'];
    $password = $_POST['password'];
    $role = $_POST['role']; // Automatically set by register.html script

    // Basic input validation
    if (empty($fn) || empty($email) || empty($password)) {
        echo "<script>alert('Please fill all required fields.'); history.back();</script>";
        exit();
    }

    // Prevent duplicate email
    $checkEmail = mysqli_query($conn, "SELECT * FROM userdata WHERE email='$email'");
    if (mysqli_num_rows($checkEmail) > 0) {
        echo "<script>alert('Email already registered.'); history.back();</script>";
        exit();
    }

    // Insert new user
    $insert = mysqli_query($conn, "INSERT INTO userdata (fullname, email, address, phonenumber, password, role) 
                                   VALUES ('$fn', '$email', '$address', '$phone', '$password', '$role')");

    if ($insert) {
        if ($role == 'admin') {
            echo "<script>
                alert('Admin registered successfully!');
                window.location.href = 'Admin/login.php';
                </script>";
        } else {
            echo "<script>
                alert('Customer registered successfully!');
                window.location.href = 'login.php';
                </script>";
        }
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}



// ===== LOGIN =====
if (isset($_POST['login'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $check = mysqli_query($conn, "SELECT * FROM userdata WHERE email='$email' AND password='$password'");

    if (mysqli_num_rows($check) > 0) {
        $row = mysqli_fetch_assoc($check);
        $_SESSION['email'] = $row['email'];
        $_SESSION['role'] = $row['role'];
        $_SESSION['fullname'] = $row['fullname'];

        if ($row['role'] == 'admin') {
            echo "<script>
                alert('Welcome, Admin!');
                window.location.href = 'Admin/dashboard.php';
                </script>";
        } else {
            echo "<script>
                alert('Login successful!');
                window.location.href = 'index.php';
                </script>";
        }
    } else {
        echo "<script>
            alert('Invalid email or password.');
            window.location.href = 'login.php';
            </script>";
    }
}



// ===== ADD TO CART =====
if (!isset($_SESSION['email'])) {
    echo "<script>
            alert('You must be logged in to add items to the cart');
            window.location.href = 'login.php';
          </script>";
    exit();
}

if (isset($_POST['addcart'])) {
    $product_name = $_POST['product_name'];
    $product_id = $_POST['product_id'];
    $product_price = $_POST['product_price'];
    $email = $_SESSION['email'];
    $image = $_POST['image'];

    // Check if product already exists in cart
    $check_query = "SELECT * FROM cart WHERE product_id='$product_id' AND email='$email'";
    $check_result = mysqli_query($conn, $check_query);

    if (mysqli_num_rows($check_result) > 0) {
        echo "<script>
            alert('Product already in your cart.');
            window.location.href = 'product-detail.php?product_id=$product_id';
          </script>";
    } else {
        $query = "INSERT INTO cart (product_id, product_name, product_price, email, image) 
                  VALUES ('$product_id', '$product_name', '$product_price', '$email', '$image')";
        $insert = mysqli_query($conn, $query);

        if ($insert) {
            echo "<script>
                    alert('Product added to cart!');
                    window.location.href = 'cart.php';
                  </script>";
        } else {
            echo "<script>
                    alert('Failed to add product to cart.');
                    window.location.href = 'product-detail.php';
                  </script>";
        }
    }
}
?>
