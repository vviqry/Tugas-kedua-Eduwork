<?php
$password1 = "12345678";
$password2 = "password123";
echo "Hash untuk 12345678: " . password_hash($password1, PASSWORD_DEFAULT) . "<br>";
echo "Hash untuk password123: " . password_hash($password2, PASSWORD_DEFAULT) . "<br>";
?>