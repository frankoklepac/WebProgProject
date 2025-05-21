<?php
require_once __DIR__ . '/db_connect.php';


$error = '';
$success = '';



if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
        $error = "All fields are required.";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } else {
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt->bind_param("ss", $username, $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $error = "Username or email already exists.";
        } else {
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $conn->prepare("INSERT INTO users (username, password, email) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $username, $passwordHash, $email);

            if ($stmt->execute()) {
                header("Location: login.php");
            } else {
                $error = "Registration failed. Please try again.";
            }
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Document</title>
<link rel="stylesheet" href="../styles/auth.css">
</head>
<body>

    <div class="login-container">
        <h1>Welcome to SkinBaazar</h1>
        <p>Please fill in the details to register.</p>
      <div class="login-form">
          <form method="post" action="">
              <input type="text" name="username" placeholder="Username" required><br>
              <input type="email" name="email" placeholder="Email" required><br>
              <input type="password" name="password" placeholder="Password" required><br>
              <input type="password" name="confirm_password" placeholder="Confirm Password" required><br>
              <button type="submit">Register</button>
              <?php
              if (!empty($error)) echo "<p style='color:red;'>$error</p>";
              if (!empty($success)) echo "<p style='color:green;'>$success</p>";
              ?>
          </form>
          <p>Already have an account? <a href="login.php">Login here</a></p>
          <p><a href="../index.php">Back to Home</a></p    
      </div>
    </div>
</body>
</html>
