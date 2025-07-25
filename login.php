<?php
require 'db.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $mysqli->prepare("SELECT id, password FROM users WHERE username = ?");
    $stmt->bind_param("s", $_POST['username']);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows === 1) {
        $stmt->bind_result($user_id, $hash);
        $stmt->fetch();
        if (password_verify($_POST['password'], $hash)) {
            $_SESSION['user_id'] = $user_id;
            header("Location: index.php");
            exit;
        }
    }
    $error = "Invalid credentials.";
}
?>

<!DOCTYPE html>
<html>
<head><title>Login</title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"></head>
<body class="container py-5">
<h2>Login</h2>
<form method="POST">
    <input type="text" name="username" placeholder="Username" class="form-control mb-2" required>
    <input type="password" name="password" placeholder="Password" class="form-control mb-2" required>
    <button type="submit" class="btn btn-primary">Login</button>
    <p class="mt-2">No account? <a href="register.php">Register</a></p>
    <?php if (!empty($error)) echo "<p class='text-danger'>$error</p>"; ?>
</form>
</body>
</html>
