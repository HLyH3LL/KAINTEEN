<?php
header("Content-Type: application/json");
include 'db.php'; // your database connection

$adminUsername = "admin.tip.manila";
$adminPassword = "tipmanila281962";

$input = json_decode(file_get_contents("php://input"), true);
$username = $input['username'] ?? '';
$password = $input['password'] ?? '';

// For debugging - remove or comment out after confirming inputs
// error_log("Received username: $username, password: $password");

if ($username === $adminUsername && $password === $adminPassword) {
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';

    $stmt = $conn->prepare("INSERT INTO admin_login (username, ip_address) VALUES (?, ?)");
    $stmt->bind_param("ss", $username, $ip);
    $stmt->execute();
    $stmt->close();
    $conn->close();

    echo json_encode(["success" => true]);
} else {
    echo json_encode(["success" => false, "message" => "Invalid username or password.", "received" => ["username" => $username, "password" => $password]]);
}
?>
