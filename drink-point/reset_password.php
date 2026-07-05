<?php
include "koneksi.php";

$username = "pemilik";      // ganti dengan username yang ingin direset
$password_baru = password_hash("123456", PASSWORD_DEFAULT);

mysqli_query($conn, "
UPDATE users
SET password='$password_baru'
WHERE username='$username'
");

echo "Password berhasil direset menjadi: 123456";
?>