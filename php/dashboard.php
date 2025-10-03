<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: ../auth/signInStudent.html");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Student Dashboard</title>
  <link rel="shortcut icon" href="../res/logo.png" type="image/x-icon">
  <link rel="stylesheet" href="../css/dashboard.css">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
  <h1>Welcome, <?php echo htmlspecialchars($_SESSION['student_name']); ?>!</h1>
  <p>Your Student Number: <?php echo htmlspecialchars($_SESSION['student_number']); ?></p>
  <a href="../auth/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
</body>
</html>
