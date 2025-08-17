<?php
$servername = "localhost";
$username = "root";  
$password = "kinangina24";       
$database = "kainteen";


$conn = new mysqli($servername, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>