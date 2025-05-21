<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>SkinBaazar</title>
  <link rel="stylesheet" href="styles/style.css">
</head>
<body>
  <div class="navbar">
    <a href="index.php">Home</a>
    <a href="products.php">Products</a>
    <a href="about.php">About Us</a>
    <a href="contact.php">Contact Us</a>
  </div>
  <div class="container">

  </div>
  <div class="footer">

  </div>

  <h1>Welcome to SkinBaazar</h1>
  <p>Your one-stop solution for all skin-related products.</p>
  <?php
  if (isset($_SESSION['username'])) {
      echo "<p>Hello, " . htmlspecialchars($_SESSION['username']) . "!</p>";
      echo "<p><a href='auth/logout.php'>Logout</a></p>";
  } else {
      echo "<p><a href='auth/login.php'>Login</a> | <a href='auth/register.php'>Register</a></p>";
  } 
  ?>
  
</body>
</html>