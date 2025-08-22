<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: signInStudent.html");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Student Dashboard</title>
  <link rel="stylesheet" href="../css/dashboard.css">
</head>
<body>
  <h1>Welcome, <?php echo htmlspecialchars($_SESSION['student_name']); ?>!</h1>
  <p>Your Student Number: <?php echo htmlspecialchars($_SESSION['student_number']); ?></p>
  <a href="logout.php">Logout</a>
</body>
</html>
