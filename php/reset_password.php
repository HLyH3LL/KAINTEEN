<?php
include 'db.php';

$token = $_GET['token'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['token'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // Passwords must match
    if ($new_password !== $confirm_password) {
        echo "Passwords do not match.";
        exit;
    }

    // Check if token exists and is not expired
    $stmt = $conn->prepare("SELECT id FROM customers WHERE reset_token = ? AND reset_expires > NOW()");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        // Hash the new password
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

        // Update password and clear reset token
        $stmt = $conn->prepare("UPDATE customers SET password = ?, reset_token = NULL, reset_expires = NULL WHERE reset_token = ?");
        $stmt->bind_param("ss", $hashed_password, $token);
        $stmt->execute();

        echo "<script>alert('Password reset successful. You can now log in.'); window.location.href = '../html/signInStudent.html';</script>";
    } else {
        echo "Invalid or expired token.";
    }
    exit;
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Reset Password</title>
  <link rel="stylesheet" href="../css/signin.css"> 
  <style>
    .container { max-width: 400px; margin: 80px auto; padding: 20px; background: #1a1a1a; border-radius: 8px; color: white; }
    label { display: block; margin-top: 10px; }
    input { width: 100%; padding: 8px; margin-top: 5px; }
    button { margin-top: 15px; background: #ffc600; border: none; padding: 10px 20px; cursor: pointer; }
   
        .bg-video {
     position: fixed;
     right: 0;
     bottom: 0;
     min-width: 100%;
     min-height: 100%;
     z-index: -1;
     object-fit: cover;
}

  </style>
</head>
<body>
  <!-- Video background -->
  <video class="bg-video" autoplay loop muted>
    <source src="../res/bg.mp4" type="video/mp4">
    Your browser does not support the video tag.
  </video>

  <div class="container">
    <h2>Reset Your Password</h2>
    <form method="POST" action="">
      <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
      <label for="new_password">New Password</label>
      <input type="password" name="new_password" id="new_password" required>

      <label for="confirm_password">Confirm Password</label>
      <input type="password" name="confirm_password" id="confirm_password" required>

      <button type="submit">Reset Password</button>
    </form>
  </div>
</body>

</html>
