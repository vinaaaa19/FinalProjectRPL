==================================================================================================================
                    DRINK POINT
            SISTEM MANAJEMEN STOK DAN PENJUALAN MINUMAN
==================================================================================================================

Nama Proyek
------------------------------------------------------------------------------------------------------------------

Drink Point - Sistem Manajemen Stok dan Penjualan Minuman

------------------------------------------------------------------------------------------------------------------
DESKRIPSI SISTEM
------------------------------------------------------------------------------------------------------------------

Drink Point merupakan sistem informasi berbasis web yang dikembangkan untuk membantu proses pengelolaan stok
bahan baku, stok minuman, transaksi penjualan, serta laporan penjualan pada UMKM minuman.

Sistem menyediakan dua hak akses pengguna yaitu Pemilik Usaha dan Karyawan. Pemilik memiliki hak akses penuh
untuk mengelola seluruh data sistem, memvalidasi laporan penjualan, melihat statistik penjualan, mengelola akun
karyawan, serta melakukan monitoring kondisi usaha. Sedangkan Karyawan bertugas melakukan transaksi penjualan,
memperbarui stok bahan dan stok minuman sesuai kondisi di lapangan, mengunggah bukti pembayaran transaksi non
tunai, serta melihat laporan penjualan.

------------------------------------------------------------------------------------------------------------------
LINK FIGMA
------------------------------------------------------------------------------------------------------------------

https://www.figma.com/design/9tRLJs8z0xxo8Rt34xBd72/WebsiteRPL?node-id=0-1&p=f&t=18lCI3FxNbCsv2TP-0

------------------------------------------------------------------------------------------------------------------
LINK DEPLOY
------------------------------------------------------------------------------------------------------------------

https://drinkpoint.infinityfreeapp.com/login.php

------------------------------------------------------------------------------------------------------------------
LINK GITHUB
------------------------------------------------------------------------------------------------------------------

https://github.com/vinaaaa19/FinalProjectRPL

------------------------------------------------------------------------------------------------------------------
LINK LOCALHOST
------------------------------------------------------------------------------------------------------------------

http://localhost/drink-point

------------------------------------------------------------------------------------------------------------------
AKUN LOGIN
------------------------------------------------------------------------------------------------------------------

PEMILIK

Username : pinpin
Password : 123456


KARYAWAN

Username : rina
Password : 123456

------------------------------------------------------------------------------------------------------------------
FITUR SISTEM
------------------------------------------------------------------------------------------------------------------

PEMILIK USAHA

✓ Login
✓ Dashboard
✓ Kelola Data Minuman (Tambah, Edit, Hapus)
✓ Kelola Stok Bahan (Tambah, Edit, Hapus)
✓ Kelola Akun Karyawan
✓ Validasi Laporan Penjualan
✓ Filter Laporan
✓ Export PDF
✓ Export Excel
✓ Mode Hapus Banyak Data (Multiple Delete)
✓ Melihat Bukti Pembayaran
✓ Upload Bukti Pembayaran
✓ Sistem Notifikasi
✓ Kelola Profil
✓ Ganti Password
✓ Logout


KARYAWAN

✓ Login
✓ Dashboard
✓ Melihat Data Minuman
✓ Update Stok Minuman
✓ Melihat Data Stok Bahan
✓ Update Stok Bahan
✓ Melakukan Transaksi Penjualan
✓ Upload Bukti Pembayaran
✓ Melihat Riwayat Laporan
✓ Sistem Notifikasi
✓ Kelola Profil
✓ Ganti Password
✓ Logout

------------------------------------------------------------------------------------------------------------------
TECH STACK
------------------------------------------------------------------------------------------------------------------

Programming Language    : PHP Native
Frontend                : HTML5
Styling                 : CSS3
Framework CSS           : Bootstrap
Client Side             : JavaScript
Alert Library           : SweetAlert2
Database                : MySQL
Database Manager        : phpMyAdmin
Web Server              : Apache (XAMPP)
Local Development       : XAMPP
Prototype Design        : Figma
Hosting                 : InfinityFree
Code Editor             : Visual Studio Code
Browser                 : Google Chrome

------------------------------------------------------------------------------------------------------------------
CARA MENJALANKAN SISTEM SECARA ONLINE
------------------------------------------------------------------------------------------------------------------

1. Pastikan perangkat telah terhubung ke internet.

2. Buka browser (Google Chrome direkomendasikan).

3. Akses alamat berikut:

   https://drinkpoint.infinityfreeapp.com/login.php

4. Login menggunakan akun yang telah disediakan.

5. Sistem siap digunakan.

------------------------------------------------------------------------------------------------------------------
CARA MENJALANKAN SISTEM SECARA LOKAL
------------------------------------------------------------------------------------------------------------------

1. Install aplikasi XAMPP.

2. Jalankan service:

   • Apache
   • MySQL

3. Salin folder project

   drink-point

   ke dalam folder:

   C:\xampp\htdocs\

4. Buka phpMyAdmin melalui browser:

   http://localhost/phpmyadmin

5. Buat database baru dengan nama:

   drink_point

6. Import file database:

   drink_point.sql

7. Pastikan seluruh source code telah berada pada folder:

   C:\xampp\htdocs\drink-point

8. Buka browser kemudian akses:

   http://localhost/drink-point

9. Login menggunakan akun yang telah disediakan.

10. Sistem siap digunakan.

------------------------------------------------------------------------------------------------------------------
STRUKTUR FOLDER
------------------------------------------------------------------------------------------------------------------

drink-point/

│
├── assets/
├── uploads/
│   ├── bukti_pembayaran/
│   └── foto_profil/
│
├── koneksi.php
├── login.php
├── dashboard.php
├── dashboard_karyawan.php
├── data_minuman.php
├── stok_bahan.php
├── stok_minuman_karyawan.php
├── stok_bahan_karyawan.php
├── transaksi.php
├── laporan.php
├── akun_karyawan.php
├── profil_pemilik.php
├── profil_karyawan.php
├── logout.php
└── drink_point.sql

------------------------------------------------------------------------------------------------------------------
METODE PENGEMBANGAN SISTEM
------------------------------------------------------------------------------------------------------------------

Model pengembangan perangkat lunak yang digunakan adalah Prototype.

Tahapan pengembangan meliputi:

1. Pengumpulan Kebutuhan
2. Pembuatan Prototype
3. Evaluasi Prototype
4. Pengembangan Sistem
5. Pengujian Sistem

Metode pengujian sistem menggunakan Black Box Testing.

------------------------------------------------------------------------------------------------------------------
CATATAN
------------------------------------------------------------------------------------------------------------------

1. Pastikan Apache dan MySQL telah dijalankan apabila sistem digunakan secara lokal.

2. Pastikan database "drink_point" telah berhasil di-import.

3. Folder uploads harus memiliki izin tulis agar proses upload foto profil dan
   bukti pembayaran dapat berjalan dengan baik.

4. Sistem dapat dijalankan baik secara online maupun secara localhost.

5. Browser yang direkomendasikan adalah Google Chrome.

6. Apabila terdapat perubahan pada struktur database, lakukan import ulang file
   drink_point.sql agar sistem dapat berjalan dengan baik.

------------------------------------------------------------------------------------------------------------------
TIM PENGEMBANG
------------------------------------------------------------------------------------------------------------------

Nama                : Avina Pinky Firu Ananda
Nim                 : 202410370110141
Nama                : Naflah Ikbar Kanamay
Nim                 : 202410370110145
Nama                : Aminatus Zarodiyah Riberna
Nim                 : 202410370110160

Universitas         : Universitas Muhammadiyah Malang
Program Studi       : Informatika
Mata Kuliah         : Rekayasa Perangkat Lunak
Tahun               : 2026

==================================================================================================================
