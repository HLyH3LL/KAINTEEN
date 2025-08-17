$adminUsername = "admin.tip.manila";
$adminPassword = "tipmanila281962";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    if ($username === $adminUsername && $password === $adminPassword) {
        echo "Login successful";
    } else {
        echo "Invalid username or password.";
    }
}
?>