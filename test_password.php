<?php
$password = 'password123';
$hash = password_hash($password, PASSWORD_BCRYPT);
echo "Hash for password123: $hash\n";

$testHash = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';
if (password_verify($password, $testHash)) {
    echo "Password matches the test hash\n";
} else {
    echo "Password does not match the test hash\n";
}
?>