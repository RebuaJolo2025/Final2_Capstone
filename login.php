<?php include 'conn.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Login - InfluenceX</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
  <style>
    @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap');

    * {margin:0;padding:0;box-sizing:border-box;font-family:'Poppins',sans-serif;}

    body {
      background: linear-gradient(135deg, #4CAF50, #2196F3);
      display: flex; justify-content: center; align-items: center;
      height: 100vh; padding: 20px;
    }

    .login-container {
      background: rgba(255,255,255,0.15);
      padding: 40px 35px;
      border-radius: 20px;
      box-shadow: 0 8px 25px rgba(0,0,0,0.2);
      width: 100%; max-width: 420px;
      text-align: center;
      backdrop-filter: blur(15px);
      border: 1px solid rgba(255,255,255,0.25);
    }

    .login-container h2 {color:#fff;margin-bottom:25px;font-weight:600;}
    .logo {font-size:2.5em;margin-bottom:10px;color:#fff;}

    .input-group {position: relative; margin-bottom: 20px;}
    .input-group label {
      display: block; margin-bottom: 6px; font-size:0.9em; color:#fff; text-align:left;
    }
    .input-group input {
      width: 100%; padding: 12px 40px 12px 12px;
      font-size: 1em; border: none;
      border-radius: 8px;
      outline: none;
      background: rgba(255,255,255,0.85);
    }
    .input-group input:focus {border: 2px solid #2196F3; background: #fff;}
    .input-group i {
      position: absolute; right: 12px; top: 40px; cursor: pointer; color:#555;
    }

    .forgot-password {
      display:block; margin-top:6px; font-size:0.85em; color:#f1f1f1; text-decoration:none; text-align:right;
    }
    .forgot-password:hover {text-decoration: underline;}

    .btn {
      width: 100%; padding: 12px;
      background: linear-gradient(90deg, #2196F3, #4CAF50);
      color: white; font-size: 1.1em;
      border: none; border-radius: 8px;
      cursor: pointer; font-weight:600;
    }

    .signup-link {margin-top:15px;font-size:0.9em;color:#fff;}
    .signup-link a {color:#FFD700;font-weight:bold;text-decoration:none;}
    .signup-link a:hover {text-decoration:underline;}

    .error-message,.success-message {
      font-size:0.85em;margin-bottom:15px;padding:8px;border-radius:6px;
    }
    .error-message {color:#b71c1c;background:rgba(244,67,54,0.1);border:1px solid #f44336;}
    .success-message {color:#1b5e20;background:rgba(76,175,80,0.1);border:1px solid #4CAF50;}
  </style>
</head>
<body>
  <div class="login-container">
    <div class="logo"><i class="fa-solid fa-user-circle"></i></div>
    <h2>Welcome Back</h2>

    <form action="process.php" method="POST">

      <div class="input-group">
        <label for="email">Email</label>
        <input type="email" id="email" name="email" required placeholder="Enter your email" />
        <i class="fa-solid fa-envelope"></i>
      </div>

      <div class="input-group password-wrapper">
        <label for="password">Password</label>
        <input type="password" id="password" name="password" required placeholder="Enter your password" />
        <i class="fas fa-eye toggle-password" onclick="togglePassword()"></i>
        <a href="forgot_password.php" class="forgot-password">Forgot Password?</a>
      </div>

      <button type="submit" name="login" class="btn">Login</button>
    </form>

    <div class="signup-link">
      <p>Don't have an account? <a href="register.html">Sign up here</a></p>
    </div>
  </div>

  <script>
    function togglePassword() {
      const passwordInput = document.getElementById('password');
      const toggleIcon = document.querySelector('.toggle-password');
      passwordInput.type = passwordInput.type === 'password' ? 'text' : 'password';
      toggleIcon.classList.toggle('fa-eye-slash');
    }
  </script>
</body>
</html>
