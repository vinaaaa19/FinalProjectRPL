<?php
session_start();
include "koneksi.php";

if (!isset($_SESSION['id']) || $_SESSION['role'] != 'pemilik') {
    header("Location: login.php");
    exit;
}

if (!isset($_POST['hapus_id'])) {
    header("Location: validasi.php");
    exit;
}

foreach ($_POST['hapus_id'] as $id) {
    $id = mysqli_real_escape_string($conn, $id);

    mysqli_query($conn, "DELETE FROM detail_transaksi WHERE id_transaksi='$id'");

    mysqli_query($conn, "
        DELETE FROM transaksi 
        WHERE id_transaksi='$id'
        AND status_validasi='Tervalidasi'
    ");
}

header("Location: validasi.php?hapus=success");
exit;
?>