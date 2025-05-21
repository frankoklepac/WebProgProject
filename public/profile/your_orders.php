<?php

session_start();


?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo htmlspecialchars($_SESSION['username']); ?>'s Profile</title>
</head>
<body>
  
  <h1>Welcome to your profile, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h1>
  <p>Your role: <?php echo htmlspecialchars($_SESSION['role']); ?></p>
  <p><a href="../index.php">Back to Home</a></p>
  <p><a href="edit_profile.php">Edit Profile</a></p>
  <p><a href="../auth/logout.php">Logout</a></p>

  <h2>Your Details:</h2>
  <ul>
    <li>Username: <?php echo htmlspecialchars($_SESSION['username']); ?></li>
    <li>Role: <?php echo htmlspecialchars($_SESSION['role']); ?></li>
    <!-- Add more user details as needed -->
  </ul>
</body>
</html>