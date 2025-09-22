<?php
    session_start();
    include 'conn.php';


    // Register
    // Insert data to the db
    if (isset($_POST['submit'])) {
        $fn = $_POST['fullName'];
        $email = $_POST['email'];
        $address = $_POST['address'];
        $phone = $_POST['phone'];
        $password = $_POST['password'];
        $role = $_POST['role']; // Get the selected role

        $insert = mysqli_query($conn, "INSERT INTO `userdata`(`id`, `fullname`, `email`, `address`, `phonenumber`, `password`, `role`) 
                                    VALUES (NULL, '$fn', '$email', '$address', '$phone', '$password', '$role')");

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

        $check = mysqli_query($conn, "SELECT * FROM `userdata` WHERE email = '$email' AND password = '$password'");

        if(mysqli_num_rows($check) > 0){
            
            $_SESSION['email'] = $email;
            echo "<script>
                alert('Login successful');
                window.location.href = 'index.php';
                </script>";
        }else{
            echo "<script>
                alert('Login failed');
                window.location.href = 'login.php';
                </script>";
        }
    }

    // Insert product info the database
    // Check if the user is logged in
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
    
        // Check if product already exists in cart for this user
        $check_query = "SELECT * FROM cart WHERE product_id = '$product_id' AND email = '$email'";
        $check_result = mysqli_query($conn, $check_query);
    
        if (mysqli_num_rows($check_result) > 0) {
            // Product already in cart
            echo "<script>
                alert('Product is already in your cart.');
                window.location.href = 'product-detail.php?product_id=$product_id';
            </script>";

        } else {
            // Insert product into cart
            $query = "INSERT INTO cart (product_id, product_name, product_price, email, image) VALUES ('$product_id', '$product_name', '$product_price', '$email', '$image')";
            $insert = mysqli_query($conn, $query);
    
            if ($insert) {
                echo "<script>
                        alert('Product Added to Cart!');
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