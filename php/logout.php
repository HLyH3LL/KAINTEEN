<?php
session_start();
session_unset();
session_destroy();
header("Location: signInStudent.html");
exit;
?>
