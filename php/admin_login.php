<?php
session_start(); // start session
header("Content-Type: application/json");
include 'db.php'; // your database connection

$adminUsername = "admin.tip.manila";
$adminPassword = "tipmanila281962";

$input = json_decode(file_get_contents("php://input"), true);
$username = $input['username'] ?? '';
$password = $input['password'] ?? '';

if ($username === $adminUsername && $password === $adminPassword) {
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';

    // Log login attempt
    $stmt = $conn->prepare("INSERT INTO admin_login (username, ip_address) VALUES (?, ?)");
    $stmt->bind_param("ss", $username, $ip);
    $stmt->execute();
    $stmt->close();
    $conn->close();

    // ✅ Save session
    $_SESSION['admin_logged_in'] = true;
    $_SESSION['admin_username'] = $username;

    echo json_encode(["success" => true]);
} else {
    echo json_encode([
        "success" => false,
        "message" => "Invalid username or password."
    ]);
}
?>
