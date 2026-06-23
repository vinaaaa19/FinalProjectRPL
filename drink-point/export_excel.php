<?php
include "koneksi.php";

header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=laporan_penjualan.xls");

$data = mysqli_query($conn, "
    SELECT 
        transaksi.tanggal,
        users.nama AS nama_kasir,
        minuman.nama_minuman,
        minuman.harga,
        detail_transaksi.qty,
        detail_transaksi.subtotal,
        transaksi.metode_pembayaran,
        transaksi.total
    FROM detail_transaksi
    JOIN transaksi ON detail_transaksi.id_transaksi = transaksi.id_transaksi
    JOIN minuman ON detail_transaksi.id_minuman = minuman.id_minuman
    JOIN users ON transaksi.id_user = users.id
    ORDER BY transaksi.tanggal DESC
");
?>

<table border="1">
    <tr>
        <th>No</th>
        <th>Tanggal</th>
        <th>Kasir</th>
        <th>Nama Minuman</th>
        <th>Harga</th>
        <th>Jumlah</th>
        <th>Subtotal</th>
        <th>Metode Pembayaran</th>
    </tr>

    <?php
    $no = 1;
    while ($row = mysqli_fetch_assoc($data)) {
    ?>
    <tr>
        <td><?php echo $no++; ?></td>
        <td><?php echo $row['tanggal']; ?></td>
        <td><?php echo $row['nama_kasir']; ?></td>
        <td><?php echo $row['nama_minuman']; ?></td>
        <td><?php echo $row['harga']; ?></td>
        <td><?php echo $row['qty']; ?></td>
        <td><?php echo $row['subtotal']; ?></td>
        <td><?php echo $row['metode_pembayaran']; ?></td>
    </tr>
    <?php } ?>
</table>