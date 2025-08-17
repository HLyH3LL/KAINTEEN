<?php
include 'db.php';

if (isset($_GET['token'])) {
    $token = $_GET['token'];

    // Check token validity
    $stmt = $conn->prepare("SELECT id FROM customers WHERE reset_token = ? AND reset_expires > NOW()");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            $new_password = password_hash($_POST['password'], PASSWORD_DEFAULT);

            // Update password & clear token
            $stmt = $conn->prepare("UPDATE customers SET password = ?, reset_token = NULL, reset_expires = NULL WHERE reset_token = ?");
            $stmt->bind_param("ss", $new_password, $token);
            $stmt->execute();

            echo "Password reset successful. <a href='signInStudent.html'>Login</a>";
            exit;
        }
    } else {
        die("Invalid or expired reset link.");
    }
} else {
    die("No token provided.");
}
?>

<!-- Reset Password Form -->
<form method="POST">
    <label>New Password</label>
    <input type="password" name="password" required>
    <button type="submit">Reset Password</button>
</form>