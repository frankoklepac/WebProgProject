<?php
require_once __DIR__ . '/db_connect.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    $stmt = $conn->prepare("SELECT id, password, role FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 1) {
        $stmt->bind_result($id, $passwordHash, $role);
        $stmt->fetch();

        if (password_verify($password, $passwordHash)) {
            $_SESSION['user_id'] = $id;
            $_SESSION['username'] = $username;
            $_SESSION['role'] = $role;
            header("Location: ../index.php");
            exit;
        } else {
            $error = "Invalid username or password.";
        }
    } else {
        $error = "Invalid username or password.";
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SkinBaazar - Login</title>
    <link rel="stylesheet" href="../styles/auth.css">
</head>
<body>
    <div class="login-container">
        <h1>Welcome back!</h1>
        <p>Please enter your credentials to login.</p>
        <div class="login-form">
            <form method="post" action="">
                <input type="text" name="username" placeholder="Username" required><br>
                <input type="password" name="password" placeholder="Password" required><br>
                <button type="submit">Login</button>
                <?php if (!empty($error)) echo "<p style='color:red;'>$error</p>"; ?>
            </form>
            <p>Don't have an account? <a href="register.php">Register here</a></p>
            <p><a href="../index.php">Back to Home</a></p>  
        </div>
    </div>
</body>  
</html>
