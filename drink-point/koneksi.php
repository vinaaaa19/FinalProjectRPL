<?php
$host = "sql111.infinityfree.com";
$user = "if0_42339506";
$pass = "Apinaa191205";
$db   = "if0_42339506_DrinkPoint";

$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}

mysqli_set_charset($conn, "utf8mb4");
?>