Tugas-keduA-Eduwork: Pasa Danguang-danguang
==============================================

Proyek e-commerce sederhana untuk tugas Eduwork.
Deskripsi Proyek
Pasa Danguang-danguang adalah website toko online berbasis PHP & MySQL yang menyediakan fitur untuk menambah, menampilkan, mengedit, dan menghapus produk. Proyek ini dibuat untuk memenuhi tugas CRUD (Create, Read, Update, Delete) dan manajemen produk sederhana. Proyek ini dijalankan menggunakan XAMPP dan dapat diakses melalui browser di localhost.
Fitur Utama
1. Manajemen Pengguna

Registrasi Pengguna: Pengguna dapat mendaftar sebagai buyer atau seller melalui halaman register.php. Data yang diinput meliputi nama, email, password, foto profil (opsional), nomor telepon, dan alamat.
Login Pengguna: Pengguna dapat login menggunakan email dan password melalui halaman login.php. Autentikasi menggunakan session untuk keamanan.
Logout Pengguna: Pengguna dapat logout melalui halaman logout.php untuk mengakhiri session.
Edit Profil Pengguna: Seller dan buyer dapat mengedit profil mereka (nama, email, foto profil, nomor telepon, alamat) melalui halaman edit_profile.php.

2. Manajemen Produk (CRUD)

Create (Tambah Produk): Seller dapat menambah produk baru melalui halaman add_product.php. Data produk meliputi nama produk, deskripsi, harga, kategori (buah atau sayur), dan foto produk.
Read (Menampilkan Produk): Daftar produk ditampilkan di halaman index.php (untuk semua pengguna) dan edit_products.php (khusus seller untuk produk miliknya). Fitur ini mendukung pagination untuk membatasi produk per halaman.
Update (Edit Produk): Seller dapat mengedit produk melalui halaman edit_product.php. Semua data produk (termasuk foto) dapat diperbarui.
Delete (Hapus Produk): Seller dapat menghapus produk melalui halaman edit_products.php. Tombol "Hapus" (warna merah) tersedia di samping tombol "Edit". File foto produk juga akan dihapus dari server.

3. Fitur Keranjang Belanja

Tambah ke Keranjang: Pengguna (buyer) dapat menambah produk ke keranjang belanja melalui halaman index.php menggunakan file add_to_cart.php.
Lihat Keranjang: Daftar produk di keranjang belanja ditampilkan di halaman cart.php.
Update Keranjang: Pengguna dapat memperbarui jumlah produk di keranjang melalui halaman update_cart.php.
Hapus dari Keranjang: Pengguna dapat menghapus produk dari keranjang melalui halaman delete_from_cart.php.
Checkout: Pengguna dapat melakukan checkout melalui halaman checkout.php, yang akan mengarahkan ke WhatsApp seller untuk komunikasi lebih lanjut.

4. Fitur Tambahan

Koneksi ke WhatsApp Seller: Setelah checkout, pengguna akan diarahkan ke WhatsApp seller untuk melanjutkan transaksi (menggunakan nomor telepon seller yang tersimpan di database).
Validasi Input: Semua form dilengkapi validasi sederhana (misalnya format email, panjang password, nomor telepon hanya angka, dll.).
Notifikasi: Menampilkan pesan sukses atau error (misalnya "Produk berhasil dihapus!" atau "Email sudah terdaftar!") menggunakan session.
Desain Responsif: Halaman produk (index.php dan edit_products.php) responsif untuk berbagai ukuran layar menggunakan CSS Grid dan media queries.
Keamanan: Password di-hash menggunakan password_hash() untuk keamanan. Menggunakan prepared statements untuk mencegah SQL injection.

Cara Menjalankan Proyek

Simpan File di XAMPP:

Simpan semua file proyek di folder htdocs/ecommerce (atau folder lain sesuai kebutuhan).
Pastikan XAMPP sudah terinstall dan server Apache serta MySQL sudah berjalan.


Import Database:

Buat database bernama ecommerce di phpMyAdmin.
Import file SQL (ecommerce.sql) ke database tersebut.


Konfigurasi Database:

Buka file config.php dan sesuaikan kredensial database:Host: localhost
Username: root
Password: (kosong atau sesuaikan)
Database: ecommerce




Jalankan Proyek:

Buka browser dan akses http://localhost/ecommerce/index.php.
Daftar sebagai pengguna baru (pilih role seller atau buyer) atau login dengan akun yang sudah ada.
Seller dapat menambah, mengedit, atau menghapus produk, sedangkan buyer dapat menambah produk ke keranjang dan checkout.



Struktur File

index.php: Halaman utama yang menampilkan daftar produk.
register.php: Halaman untuk registrasi pengguna.
login.php: Halaman untuk login pengguna.
logout.php: Halaman untuk logout pengguna.
add_product.php: Halaman untuk seller menambah produk baru.
edit_product.php: Halaman untuk seller mengedit produk.
edit_products.php: Halaman untuk seller melihat dan mengelola produk (edit/hapus).
edit_profile.php: Halaman untuk pengguna mengedit profil.
cart.php: Halaman untuk melihat keranjang belanja.
add_to_cart.php: Script untuk menambah produk ke keranjang.
update_cart.php: Script untuk memperbarui jumlah produk di keranjang.
delete_from_cart.php: Script untuk menghapus produk dari keranjang.
checkout.php: Halaman untuk checkout dan mengarahkan ke WhatsApp seller.
config.php: File konfigurasi koneksi database.
uploads/: Folder untuk menyimpan foto produk dan foto profil pengguna.

Kredensial Database

Host: localhost
Username: root
Password: (kosong atau sesuaikan dengan XAMPP Anda)
Database: ecommerce

Catatan...........................

Proyek ini telah memenuhi semua tugas utama:
Koneksi PHP dengan database.
Fitur CRUD untuk produk (Create, Read, Update, Delete).
Fitur keranjang belanja (tambah, lihat, update, hapus, checkout).


Fitur tambahan seperti edit profil, koneksi WhatsApp, dan validasi input telah ditambahkan untuk meningkatkan fungsionalitas.
Proyek ini dijalankan di localhost menggunakan XAMPP. File SQL untuk database telah disertakan.
Desain menggunakan CSS sederhana dengan background alam untuk estetika, dan layout responsif untuk pengalaman pengguna yang lebih baik.

Terima kasih atas perhatiannya! 
