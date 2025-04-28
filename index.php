<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
// Inisialisasi sesi
session_start();

// Koneksi ke database
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "ecommerce";

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// Ambil user_id dari sesi, default 0 jika belum login
$user_id = $_SESSION['user_id'] ?? 0;

// Validasi user_id
if ($user_id == 0) {
    header("Location: login.php");
    exit();
}

// Ambil data user untuk foto profil dan role
$user_query = "SELECT foto_profil, role FROM users WHERE id = ?";
$stmt = $conn->prepare($user_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_result = $stmt->get_result();
$user = $user_result->fetch_assoc();
$profile_picture = $user['foto_profil'] ?? 'default_profile.png';
$user_role = $user['role'] ?? 'buyer';
$stmt->close();

// Hitung total item di keranjang berdasarkan user_id
$cart_query = "SELECT SUM(quantity) AS total_items FROM cart WHERE user_id = ?";
$stmt = $conn->prepare($cart_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$cart_result = $stmt->get_result();
$row = $cart_result->fetch_assoc();
$total_items = $row['total_items'] ?? 0;
$stmt->close();

// Pagination
$products_per_page = 16; // Default untuk large screen
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $products_per_page;

// Ambil data produk berdasarkan kategori dan pencarian
$kategori = isset($_GET['kategori']) ? $_GET['kategori'] : '';
$search = isset($_GET['search']) ? $_GET['search'] : '';

$count_query = "SELECT COUNT(*) as total FROM products WHERE 1=1";
$query = "SELECT * FROM products WHERE 1=1";
$params = [];
$types = '';

if ($kategori) {
    $count_query .= " AND kategori = ?";
    $query .= " AND kategori = ?";
    $params[] = $kategori;
    $types .= 's';
}

if ($search) {
    $count_query .= " AND nama_produk LIKE ?";
    $query .= " AND nama_produk LIKE ?";
    $params[] = "%$search%";
    $types .= 's';
}

// Hitung total produk untuk pagination
$stmt = $conn->prepare($count_query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$count_result = $stmt->get_result();
$total_products = $count_result->fetch_assoc()['total'];
$stmt->close();

$total_pages = ceil($total_products / $products_per_page);

// Query produk dengan limit dan offset
$query .= " LIMIT ? OFFSET ?";
$params[] = $products_per_page;
$params[] = $offset;
$types .= 'ii';

$stmt = $conn->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
$stmt->close();

// Tampilkan pesan sukses jika ada
$success_message = '';
if (isset($_GET['status'])) {
    if ($_GET['status'] == 'order_success') {
        $success_message = "
        <div id='success-message' style='
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: white;
            color: black;
            padding: 20px 30px;
            font-size: 24px;
            font-family: \"Quicksand\", sans-serif;
            font-weight: bold;
            text-align: center;
            border: 2px solid green;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
            z-index: 9999;
        '>
            Pesanan Anda berhasil dikonfirmasi!
        </div>";
    } elseif ($_GET['status'] == 'cart_added') {
        $success_message = "
        <div id='success-message' style='
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: white;
            color: black;
            padding: 20px 30px;
            font-size: 24px;
            font-family: \"Quicksand\", sans-serif;
            font-weight: bold;
            text-align: center;
            border: 2px solid green;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
            z-index: 9999;
        '>
            Produk berhasil ditambahkan ke keranjang!
        </div>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Quicksand&display=swap" rel="stylesheet">

    <title>Pasa Danguang-danguang</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            padding-top: 70px; /* Beri ruang untuk navbar fixed */
            background-image: url('https://images.unsplash.com/photo-1638774264622-6d573c90ea8b?q=80&w=2001&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D');
            background-size: cover;
            background-attachment: fixed;
        }

        .navbar-custom {
            background-color: rgba(255, 255, 255, 0.95);
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            position: fixed;
            top: 0;
            width: 100%;
            z-index: 1000;
            padding: 0;
        }

        .navbar-content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 15px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            width: 100%;
            flex-wrap: nowrap; /* Pastikan tetap dalam satu baris */
        }

        .search-form {
            flex-grow: 1;
            display: flex;
            justify-content: center;
            margin: 0 10px;
        }

        .search-form input {
            width: 100%;
            max-width: 400px;
            padding: 6px 12px;
            border: 1px solid #ccc;
            border-radius: 4px 0 0 4px;
            outline: none;
        }

        .search-form button {
            padding: 6px 12px;
            background: #4682B4;
            color: white;
            border: none;
            border-radius: 0 4px 4px 0;
            cursor: pointer;
        }

        .search-form button:hover {
            background: #3a6b91;
        }

        .profile-img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #fff;
        }

        .cart-btn, .navbar-cart-btn {
            background: #4682B4;
            color: white;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 5px;
            padding: 8px 12px;
            border-radius: 8px;
        }

        .cart-btn:hover, .navbar-cart-btn:hover {
            background: #3a6b91;
        }

        .cart-btn .cart-text, .navbar-cart-btn .cart-text {
            display: inline;
        }

        .cart-badge {
            background: #666;
            color: white;
            border-radius: 4px;
            padding: 2px 6px;
            font-size: 0.9em;
            font-weight: bold;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 15px;
        }

        .header {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-bottom: 20px;
            text-align: center;
        }

        h1 {
            color: #2E8B57;
            font-size: 40px;
            font-weight: bold;
            text-shadow: 0 3px 4px rgba(1, 1, 1, 1);
            margin: 0 0 20px;
        }

        .product-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 20px;
        }

        .product-card {
            background: rgba(255, 255, 255, 0.9);
            padding: 15px;
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
        }

        .product-card h3 {
            color: #2E8B57;
            margin: 10px 0;
            font-size: 1.2em;
        }

        .product-card p {
            margin: 5px 0;
            color: #555;
            font-size: 0.9em;
        }

        .product-card .btn-container {
            margin-top: auto;
            display: flex;
            justify-content: space-between;
            gap: 5px;
            flex-wrap: wrap;
        }

        .product-card a {
            display: inline-block;
            padding: 6px 10px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            transition: background 0.3s;
        }

        .product-card a.buy-btn {
            background: #2E8B57;
            color: white;
        }

        .product-card a.buy-btn:hover {
            background: #256f44;
        }

        .product-card a.cart-btn {
            background: #4682B4;
            color: white;
        }

        .product-card a.cart-btn:hover {
            background: rgb(255, 255, 255);
            color: #4682B4;
        }

        a.btn-link {
            display: block;
            text-align: center;
            margin-top: 20px;
            background: rgba(255, 255, 255, 0.5);
            color: #2c3e50;
            padding: 8px 12px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            transition: background 0.3s;
        }

        a.btn-link:hover {
            background: rgba(255, 269, 255, 1);
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
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
            background-color: Black;
            color: white;
        }

        .pagination a.active {
            background-color: #2E8B57;
            color: white;
            border: 1px solid #2E8B57;
        }

        /* Responsif untuk medium (768px - 1200px) */
        @media (max-width: 1200px) {
            .product-grid {
                grid-template-columns: repeat(3, 1fr);
            }

            .product-card {
                padding: 12px;
            }

            .product-card h3 {
                font-size: 1.1em;
            }

            .product-card p {
                font-size: 0.85em;
            }

            .product-card a {
                padding: 5px 8px;
                font-size: 0.9em;
            }

            .search-form input {
                max-width: 300px;
            }
        }

        /* Responsif untuk small (<768px) */
        @media (max-width: 767px) {
            .product-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .product-card {
                padding: 10px;
            }

            .product-card h3 {
                font-size: 1em;
            }

            .product-card p {
                font-size: 0.8em;
            }

            .product-card a {
                padding: 4px 6px;
                font-size: 0.85em;
            }

            .search-form input {
                max-width: 150px; /* Kurangi lebar pencarian di layar kecil */
            }

            .cart-btn .cart-text, .navbar-cart-btn .cart-text {
                display: none; /* Sembunyikan teks "Keranjang" */
            }

            .cart-btn, .navbar-cart-btn {
                padding: 8px; /* Kurangi padding agar lebih ringkas */
            }

            h1 {
                font-size: 30px;
            }

            .navbar-content {
                padding: 0 10px; /* Kurangi padding agar muat */
            }

            .dropdown .form-select {
                width: 120px; /* Kurangi lebar dropdown kategori */
            }

            .dropdown .btn {
                padding: 6px 10px; /* Kurangi padding tombol menu */
            }

            .profile-img {
                width: 35px;
                height: 35px;
            }

            .navbar-custom .navbar-toggler {
                border: none;
            }

            .navbar-custom .navbar-toggler-icon {
                background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 30 30'%3e%3cpath stroke='rgba(46, 139, 87, 1)' stroke-width='2' stroke-linecap='round' stroke-miterlimit='10' d='M4 7h22M4 15h22M4 23h22'/%3e%3c/svg%3e");
            }
        }
    </style>
</head>
<body>
    <!-- Navbar Fixed -->
    <nav class="navbar navbar-expand-lg navbar-custom">
        <div class="container-fluid">
            <div class="navbar-content">
                <!-- Hamburger Menu untuk tombol Menu saja -->
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#menuCollapse" aria-controls="menuCollapse" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <!-- Dropdown Menu (Hanya ini yang masuk hamburger) -->
                <div class="collapse navbar-collapse" id="menuCollapse">
                    <div class="dropdown">
                        <button class="btn btn-light dropdown-toggle" type="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            Menu
                        </button>
                        <ul class="dropdown-menu" aria-labelledby="userDropdown">
                            <li><a class="dropdown-item" href="cart.php">Cek Keranjang</a></li>
                            <?php if ($user_role == 'seller'): ?>
                                <li><a class="dropdown-item" href="add_product.php">Tambah Produk</a></li>
                                <li><a class="dropdown-item" href="edit_products.php">Edit Produk</a></li>
                            <?php endif; ?>
                            <li><a class="dropdown-item" href="edit_profile.php">Edit Profil</a></li>
                            <li><a class="dropdown-item" href="logout.php">Logout</a></li>
                        </ul>
                    </div>
                </div>

                <!-- Dropdown Kategori (Dekat dengan Menu) -->
                <div class="dropdown ms-1"> <!-- Ubah me-3 menjadi ms-1 agar lebih dekat -->
                    <form method="GET">
                        <select name="kategori" onchange="this.form.submit()" class="form-select">
                            <option value="">Semua Kategori</option>
                            <option value="Buah" <?php echo (isset($_GET['kategori']) && $_GET['kategori'] == 'Buah') ? 'selected' : ''; ?>>Buah</option>
                            <option value="Sayur" <?php echo (isset($_GET['kategori']) && $_GET['kategori'] == 'Sayur') ? 'selected' : ''; ?>>Sayur</option>
                            <option value="Minuman" <?php echo (isset($_GET['kategori']) && $_GET['kategori'] == 'Minuman') ? 'selected' : ''; ?>>Minuman</option>
                            <option value="Bumbu" <?php echo (isset($_GET['kategori']) && $_GET['kategori'] == 'Bumbu') ? 'selected' : ''; ?>>Bumbu</option>
                            <option value="Lainnya" <?php echo (isset($_GET['kategori']) && $_GET['kategori'] == 'Lainnya') ? 'selected' : ''; ?>>Lainnya</option>
                        </select>
                    </form>
                </div>

                <!-- Kolom Pencarian -->
                <div class="search-form">
                    <form method="GET" style="display: flex; width: 100%;">
                        <input type="text" name="search" placeholder="Cari produk..." value="<?php echo htmlspecialchars($search); ?>">
                        <button type="submit"><i class="bi bi-search"></i></button>
                    </form>
                </div>

                <!-- Keranjang dan Profil -->
                <div class="d-flex align-items-center">
                    <a href="cart.php" class="navbar-cart-btn">
                        <i class="bi bi-cart4"></i>
                        <span class="cart-text">Keranjang</span>
                        <?php if ($total_items > 0): ?>
                            <span class="cart-badge"><?php echo $total_items; ?></span>
                        <?php endif; ?>
                    </a>
                    <div class="dropdown ms-3">
                        <img src="<?php echo htmlspecialchars($profile_picture); ?>" alt="Profile" class="profile-img" id="profileDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="profileDropdown">
                            <li><a class="dropdown-item" href="cart.php">Cek Keranjang</a></li>
                            <?php if ($user_role == 'seller'): ?>
                                <li><a class="dropdown-item" href="add_product.php">Tambah Produk</a></li>
                                <li><a class="dropdown-item" href="edit_products.php">Edit Produk</a></li>
                            <?php endif; ?>
                            <li><a class="dropdown-item" href="edit_profile.php">Edit Profil</a></li>
                            <li><a class="dropdown-item" href="logout.php">Logout</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Tampilkan pesan sukses -->
    <?php echo $success_message; ?>

    <div class="container">
        <div class="header">
            <h1>Pasa Danguang-danguang</h1></h1>
        </div>

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
                    echo "<p>" . htmlspecialchars($row['deskripsi']) . "</p>";
                    echo "<p>Kategori: " . htmlspecialchars($row['kategori']) . "</p>";
                    echo "<div class='btn-container'>";
                    // Tombol Beli dan Tambah ke Keranjang untuk semua pengguna
                    echo "<a href='#' class='buy-btn' data-id='" . $row['id'] . "'>Beli</a>";
                    echo "<a href='#' class='cart-btn' data-id='" . $row['id'] . "'>Tambah ke Keranjang</a>";
                    echo "</div>";
                    echo "</div>";
                }
            } else {
                echo "<p>Tidak ada produk yang ditemukan.</p>";
            }

            // Tutup koneksi
            mysqli_close($conn);
            ?>
        </div>

        <!-- Pagination -->
        <div class="pagination">
            <?php
            $base_url = "index.php?";
            if ($kategori) $base_url .= "kategori=" . urlencode($kategori) . "&";
            if ($search) $base_url .= "search=" . urlencode($search) . "&";

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

        <?php if ($user_role == 'seller'): ?>
            <a href="add_product.php" class="btn-link">Tambah Produk Baru</a>
        <?php endif; ?>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Hilangkan pesan setelah 3 detik
        setTimeout(function() {
            var msg = document.getElementById('success-message');
            if (msg) {
                msg.style.display = 'none';
            }
        }, 3000);

        // AJAX untuk tombol Tambah ke Keranjang dan Beli
        document.querySelectorAll('.cart-btn, .buy-btn').forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                const productId = this.getAttribute('data-id');
                const isBuyButton = this.classList.contains('buy-btn');

                fetch('add_to_cart.php?id=' + productId)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Perbarui badge keranjang
                            const cartBadge = document.querySelector('.cart-badge');
                            if (cartBadge) {
                                cartBadge.textContent = data.total_items;
                            } else {
                                const cartBtn = document.querySelector('.navbar-cart-btn');
                                const newBadge = document.createElement('span');
                                newBadge.className = 'cart-badge';
                                newBadge.textContent = data.total_items;
                                cartBtn.appendChild(newBadge);
                            }

                            // Jika tombol "Beli", redirect ke cart.php
                            if (isBuyButton) {
                                window.location.href = 'cart.php';
                            } else {
                                // Tampilkan pesan sukses
                                const successMsg = document.createElement('div');
                                successMsg.id = 'success-message';
                                successMsg.style = `
                                    position: fixed;
                                    top: 50%;
                                    left: 50%;
                                    transform: translate(-50%, -50%);
                                    background: white;
                                    color: black;
                                    padding: 20px 30px;
                                    font-size: 24px;
                                    font-family: "Quicksand", sans-serif;
                                    font-weight: bold;
                                    text-align: center;
                                    border: 2px solid green;
                                    border-radius: 10px;
                                    box-shadow: 0 4px 8px rgba(0,0,0,0.2);
                                    z-index: 9999;
                                `;
                                successMsg.textContent = 'Produk berhasil ditambahkan ke keranjang!';
                                document.body.appendChild(successMsg);

                                setTimeout(() => {
                                    successMsg.style.display = 'none';
                                }, 2000);
                            }
                        } else {
                            alert('Gagal menambahkan produk ke keranjang.');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Terjadi kesalahan saat menambahkan produk.');
                    });
            });
        });
    </script>
</body>
</html>