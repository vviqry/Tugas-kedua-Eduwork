<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nature Lover Marketplace</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-image: url('https://images.unsplash.com/photo-1638774264622-6d573c90ea8b?q=80&w=2001&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D');
            background-size: cover;

        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        h1 {
            text-align: center;
            color: #2E8B57;
            font-size: 40px;
        }
        .filter {
            margin-bottom: 20px;
            text-align: center;
        }
        .product-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
        }
        .product-card {
            background: rgba(255, 255, 255, 0.9);
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
        .product-card h3 {
            color: #2E8B57;
            margin: 0 0 10px;
        }
        .product-card p {
            margin: 5px 0;
            color: #555;
        }
        select {
            padding: 5px;
            font-size: 1em;
        }
        a {
            display: block;
            text-align: center;
            margin-top: 20px;
            color: #2E8B57;
            text-decoration: none;
        }
        a:hover {
            text-decoration: underline;
        }
        h1 {
            color: #2E8B57;
            margin-bottom: 10px;
            font-weight: bold;
            text-shadow: 0 3px 4px rgba(1, 1, 1, 1);
        }
        a {
    background: rgba(255, 255, 255, 0.5);
    color: #2c3e50;
    padding: 8px 12px;
    border-radius: 8px;
    text-decoration: none;
    font-weight: 600;
    transition: background 0.3s;
}
a:hover {
    background: rgba(255, 255, 255, 1);
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
}


    </style>
</head>
<body>
    <div class="container">
        <h1>Nature Lover Marketplace</h1>
        <div class="filter">
            <form method="GET">
                <select name="kategori" onchange="this.form.submit()">
    <option value="">Semua Kategori</option>
    <option value="Buah" <?php echo (isset($_GET['kategori']) && $_GET['kategori'] == 'Buah') ? 'selected' : ''; ?>>Buah</option>
    <option value="Sayur" <?php echo (isset($_GET['kategori']) && $_GET['kategori'] == 'Sayur') ? 'selected' : ''; ?>>Sayur</option>
    <option value="Minuman" <?php echo (isset($_GET['kategori']) && $_GET['kategori'] == 'Minuman') ? 'selected' : ''; ?>>Minuman</option>
    <option value="Bumbu" <?php echo (isset($_GET['kategori']) && $_GET['kategori'] == 'Bumbu') ? 'selected' : ''; ?>>Bumbu</option>
    <option value="Lainnya" <?php echo (isset($_GET['kategori']) && $_GET['kategori'] == 'Lainnya') ? 'selected' : ''; ?>>Lainnya</option>
</select>

            </form>
        </div>
        <div class="product-grid">
    <?php
    $conn = mysqli_connect("localhost", "root", "", "ecommerce");
    if (!$conn) {
        die("Koneksi gagal: " . mysqli_connect_error());
    }

    $kategori = isset($_GET['kategori']) ? $_GET['kategori'] : '';
    $query = "SELECT * FROM products";
    if ($kategori) {
        $query .= " WHERE kategori = '" . mysqli_real_escape_string($conn, $kategori) . "'";
    }

    $result = mysqli_query($conn, $query);
    if (mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            echo "<div class='product-card'>";
            if (!empty($row['foto_produk'])) {
                echo "<img src='uploads/" . htmlspecialchars($row['foto_produk']) . "' alt='" . htmlspecialchars($row['nama_produk']) . "' style='max-width: 100%; height: auto; border-radius: 8px;'>";
            }
            echo "<h3>" . htmlspecialchars($row['nama_produk']) . "</h3>";
            echo "<p>Harga: Rp " . number_format($row['harga'], 0, ',', '.') . "</p>";
            echo "<p>" . htmlspecialchars($row['deskripsi']) . "</p>";
            echo "<p>Kategori: " . htmlspecialchars($row['kategori']) . "</p>";
            echo "</div>";
        }
    } else {
        echo "<p>Tidak ada produk yang ditemukan.</p>";
    }
    mysqli_close($conn);
    ?>
</div>
        <a href="add_product.php" class="btn-link">Tambah Produk Baru</a>

    </div>
</body>
</html>