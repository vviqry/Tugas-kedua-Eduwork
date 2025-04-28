<?php
session_start();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Koneksi ke database
$conn = mysqli_connect("localhost", "root", "", "ecommerce");
if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}

// Ambil user_id dari sesi
$user_id = $_SESSION['user_id'] ?? 0;
if ($user_id == 0) {
    header("Location: login.php");
    exit();
}

// Cek role pengguna
$user_query = "SELECT role FROM users WHERE id = ?";
$stmt = $conn->prepare($user_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_result = $stmt->get_result();
$user = $user_result->fetch_assoc();
if ($user['role'] != 'seller') {
    header("Location: index.php");
    exit();
}
$stmt->close();

// Logika untuk menghapus produk
if (isset($_GET['delete_id'])) {
    $delete_id = (int)$_GET['delete_id'];

    // Pastikan produk milik seller ini
    $query = "SELECT foto_produk FROM products WHERE id = ? AND seller_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $delete_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $product = $result->fetch_assoc();
        $foto_produk = $product['foto_produk'];

        // Hapus file foto jika ada
        if ($foto_produk && file_exists("uploads/" . $foto_produk)) {
            unlink("uploads/" . $foto_produk);
        }

        // Hapus produk dari database
        $delete_query = "DELETE FROM products WHERE id = ? AND seller_id = ?";
        $stmt = $conn->prepare($delete_query);
        $stmt->bind_param("ii", $delete_id, $user_id);
        if ($stmt->execute()) {
            $_SESSION['success'] = "Produk berhasil dihapus!";
        } else {
            $_SESSION['error'] = "Gagal menghapus produk.";
        }
    }
    $stmt->close();
    header("Location: edit_products.php");
    exit();
}

// Ambil pesan notifikasi
$success = isset($_SESSION['success']) ? $_SESSION['success'] : '';
$error = isset($_SESSION['error']) ? $_SESSION['error'] : '';
unset($_SESSION['success']);
unset($_SESSION['error']);

// Pagination
$products_per_page = 16; // Jumlah produk per halaman
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $products_per_page;

// Hitung total produk untuk pagination
$count_query = "SELECT COUNT(*) as total FROM products WHERE seller_id = ?";
$stmt = $conn->prepare($count_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$count_result = $stmt->get_result();
$total_products = $count_result->fetch_assoc()['total'];
$stmt->close();

$total_pages = ceil($total_products / $products_per_page);

// Ambil daftar produk milik seller ini dengan limit dan offset
$query = "SELECT * FROM products WHERE seller_id = ? LIMIT ? OFFSET ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("iii", $user_id, $products_per_page, $offset);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Produk untuk Diedit - Pasa Danguang-danguang</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-image: url('https://images.unsplash.com/photo-1631592058858-a8c4b556df5b?q=80&w=2072&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D');
            background-size: cover;
            background-attachment: fixed;
            background-position: center;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: rgba(255, 255, 255, 0.9);
            padding: 20px;
            border-radius: 10px;
        }
        h2 {
            color: #2E8B57;
            text-align: center;
            margin-bottom: 20px;
        }
        .product-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 15px;
        }
        .product-card {
            background: white;
            padding: 10px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            text-align: left;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        .product-card img {
            max-width: 100%;
            height: auto;
            border-radius: 8px;
            margin-bottom: 8px;
        }
        .product-card h3 {
            color: #2E8B57;
            margin: 8px 0;
            font-size: 1em;
        }
        .product-card p {
            margin: 4px 0;
            color: #555;
            font-size: 0.85em;
        }
        .product-card .description {
            font-size: 0.8em;
            color: #666;
            margin: 4px 0;
            line-height: 1.3;
            display: -webkit-box;
            -webkit-line-clamp: 2; /* Batasi deskripsi menjadi 2 baris */
            -webkit-box-orient: vertical;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .button-group {
            display: flex;
            gap: 8px; /* Jarak antar tombol */
            margin-top: 8px;
        }
        .product-card a.edit-btn, .product-card a.delete-btn {
            display: block;
            text-align: center;
            padding: 5px 10px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.85em;
            flex: 1; /* Membuat tombol memiliki lebar yang sama */
        }
        .product-card a.edit-btn {
            background: #4682B4;
            color: white;
        }
        .product-card a.edit-btn:hover {
            background: #3a6a94;
        }
        .product-card a.delete-btn {
            background: #e74c3c; /* Warna merah untuk tombol hapus */
            color: white;
        }
        .product-card a.delete-btn:hover {
            background: #c0392b;
        }
        .back-btn {
            display: block;
            text-align: center;
            margin: 20px auto;
            padding: 10px 20px;
            background: #4682B4;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            width: fit-content;
        }
        .back-btn:hover {
            background: #3a6a94;
        }
        .pagination {
            display: flex;
            justify-content: center;
            margin-top: 20px;
            margin-bottom: 30px;
        }
        .pagination a {
            color: #2E8B57;
            padding: 8px 16px;
            text-decoration: none;
            border: 2px solid #ddd;
            margin: 0 4px;
            border-radius: 4px;
        }
        .pagination a:hover {
            background-color: #2E8B57;
            color: white;
        }
        .pagination a.active {
            background-color: #2E8B57;
            color: white;
            border: 1px solid #2E8B57;
        }
        .error, .success {
            font-size: 0.9em;
            margin-bottom: 15px;
            text-align: center;
        }
        .error {
            color: red;
        }
        .success {
            color: green;
        }
        /* Responsif untuk medium (768px - 1200px) */
        @media (max-width: 1200px) {
            .product-grid {
                grid-template-columns: repeat(3, 1fr);
            }
            .product-card {
                padding: 8px;
            }
            .product-card h3 {
                font-size: 0.95em;
            }
            .product-card p {
                font-size: 0.8em;
            }
            .product-card .description {
                font-size: 0.75em;
            }
            .product-card a.edit-btn, .product-card a.delete-btn {
                padding: 4px 8px;
                font-size: 0.8em;
            }
        }
        /* Responsif untuk small (<768px) */
        @media (max-width: 767px) {
            .product-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            .product-card {
                padding: 6px;
            }
            .product-card h3 {
                font-size: 0.9em;
            }
            .product-card p {
                font-size: 0.75em;
            }
            .product-card .description {
                font-size: 0.7em;
            }
            .product-card a.edit-btn, .product-card a.delete-btn {
                padding: 3px 6px;
                font-size: 0.75em;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Daftar Produk Anda</h2>

        <!-- Tampilkan notifikasi -->
        <?php if ($error) { ?>
            <div class="error"><?php echo $error; ?></div>
        <?php } ?>
        <?php if ($success) { ?>
            <div class="success" id="successMessage"><?php echo $success; ?></div>
        <?php } ?>

        <div class="product-grid">
            <?php
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo "<div class='product-card'>";
                    if (!empty($row['foto_produk'])) {
                        echo "<img src='uploads/" . htmlspecialchars($row['foto_produk']) . "' alt='" . htmlspecialchars($row['nama_produk']) . "'>";
                    }
                    echo "<h3>" . htmlspecialchars($row['nama_produk']) . "</h3>";
                    echo "<p>Harga: Rp " . number_format($row['harga'], 0, ',', '.') . "</p>";
                    echo "<p>Kategori: " . htmlspecialchars($row['kategori']) . "</p>";
                    echo "<p class='description'>" . htmlspecialchars($row['deskripsi']) . "</p>";
                    echo "<div class='button-group'>";
                    echo "<a href='edit_product.php?id=" . $row['id'] . "' class='edit-btn'>Edit</a>";
                    echo "<a href='#' class='delete-btn' onclick=\"return confirmDelete(" . $row['id'] . ")\">Hapus</a>";

                    echo "</div>";
                    echo "</div>";
                }
            } else {
                echo "<p>Anda belum memiliki produk untuk diedit.</p>";
            }
            $stmt->close();
            ?>
        </div>

        <!-- Pagination -->
        <div class="pagination">
            <?php
            $base_url = "edit_products.php?";
            if ($page > 1) {
                echo "<a href='" . $base_url . "page=" . ($page - 1) . "'>« Prev</a>";
            }

            for ($i = 1; $i <= $total_pages; $i++) {
                $active = ($i == $page) ? "active" : "";
                echo "<a href='" . $base_url . "page=" . $i . "' class='$active'>$i</a>";
            }

            if ($page < $total_pages) {
                echo "<a href='" . $base_url . "page=" . ($page + 1) . "'>Next »</a>";
            }
            ?>
        </div>

        <a href="index.php" class="back-btn">Kembali ke Beranda</a>
    </div>

    <script>
        const successMessage = document.getElementById('successMessage');
        if (successMessage) {
            setTimeout(() => {
                successMessage.style.display = 'none';
            }, 1500);
        }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function confirmDelete(productId) {
    Swal.fire({
        title: 'Apakah Anda yakin?',
        text: "Produk ini akan dihapus secara permanen!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#e74c3c',
        cancelButtonColor: '#4682B4',
        confirmButtonText: 'Ya, hapus!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = 'edit_products.php?delete_id=' + productId;
        }
    });
    return false;
}
</script>

    <?php
    mysqli_close($conn);
    ?>
</body>
</html>