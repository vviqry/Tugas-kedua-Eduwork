<?php
session_start();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Pastikan tidak ada whitespace sebelum tag <?php

$user_id = $_SESSION['user_id'] ?? 0;
if ($user_id == 0) {
    header("Location: login.php");
    exit();
}

// Koneksi ke database
$conn = mysqli_connect("localhost", "root", "", "ecommerce");
if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}

// Ambil role pengguna
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

// Ambil data produk berdasarkan ID
$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($product_id == 0) {
    header("Location: index.php");
    exit();
}

// Pastikan produk milik seller ini
$query = "SELECT * FROM products WHERE id = ? AND seller_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $product_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows == 0) {
    header("Location: index.php");
    exit();
}
$product = $result->fetch_assoc();
$stmt->close();

$name = $product['nama_produk'];
// Format harga agar tidak menampilkan desimal .00
$price = number_format($product['harga'], 0, ',', '');
$description = $product['deskripsi'];
$kategori = $product['kategori'];
$foto_name = $product['foto_produk'];
$error = $success = "";

if (isset($_SESSION['success'])) {
    $success = $_SESSION['success'];
    unset($_SESSION['success']);
}
if (isset($_SESSION['error'])) {
    $error = $_SESSION['error'];
    unset($_SESSION['error']);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST["name"] ?? "";
    $price = $_POST["price"] ?? "";
    $description = $_POST["description"] ?? "";
    $kategori = $_POST["kategori"] ?? "";

    $price = str_replace(['Rp.', '.', ',-'], ['', '', ''], $price);

    // Validasi field
    if (empty($name) || empty($price) || empty($description) || empty($kategori)) {
        $_SESSION['error'] = "Semua field harus diisi dengan benar!";
    } elseif (!is_numeric($price) || $price <= 0) {
        $_SESSION['error'] = "Harga harus berupa angka positif!";
    } else {
        // Proses upload foto jika ada
        $new_foto_name = $foto_name;
        if (!empty($_FILES['foto_produk']['name'])) {
            $target_dir = "uploads/";
            if (!file_exists($target_dir)) {
                mkdir($target_dir, 0755, true);
            }

            $new_foto_name = time() . "_" . basename($_FILES["foto_produk"]["name"]);
            $target_file = $target_dir . $new_foto_name;
            $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
            $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];

            if (!in_array($imageFileType, $allowed_types)) {
                $_SESSION['error'] = "Hanya file JPG, JPEG, PNG, atau GIF yang diperbolehkan!";
            } elseif ($_FILES["foto_produk"]["size"] > 5000000) {
                $_SESSION['error'] = "Ukuran file terlalu besar, maksimum 5MB!";
            } elseif (move_uploaded_file($_FILES["foto_produk"]["tmp_name"], $target_file)) {
                // Hapus foto lama jika ada
                if ($foto_name && file_exists("uploads/" . $foto_name)) {
                    unlink("uploads/" . $foto_name);
                }
            } else {
                $_SESSION['error'] = "Gagal mengunggah foto!";
                $new_foto_name = $foto_name; // Kembali ke foto lama jika gagal
            }
        }

        if (empty($_SESSION['error'])) {
            // Update data produk (tanpa stok)
            $stmt = $conn->prepare("UPDATE products SET nama_produk = ?, harga = ?, deskripsi = ?, kategori = ?, foto_produk = ? WHERE id = ? AND seller_id = ?");
            $stmt->bind_param("sdsssii", $name, $price, $description, $kategori, $new_foto_name, $product_id, $user_id);
            if ($stmt->execute()) {
                $_SESSION['success'] = "Produk berhasil diperbarui!";
                // Redirect ke edit_products.php setelah berhasil update
                header("Location: edit_products.php");
                exit();
            } else {
                $_SESSION['error'] = "Gagal memperbarui produk: " . $stmt->error;
            }
            $stmt->close();
        }
    }
    // Jika ada error, tetap di halaman ini
    header("Location: edit_product.php?id=" . $product_id);
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Produk - Pasa Danguang-danguang</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            background-image: url('https://images.unsplash.com/photo-1631592058858-a8c4b556df5b?q=80&w=2072&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D');
            background-size: cover;
            background-attachment: fixed;
            background-position: center;
        }
        .form-container {
            background: rgba(255, 255, 255, 0.9);
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.3);
            width: 100%;
            max-width: 450px; /* Lebar maksimum form */
            text-align: center;
        }
        h2 {
            color: #2E8B57;
            margin-bottom: 10px;
            font-weight: bold;
            text-shadow: 0 2px 3px rgba(0, 0, 0, 0.5);
        }
        p {
            color: #333;
            margin-bottom: 15px;
            font-size: 0.9em;
        }
        input, textarea, select {
            width: 100%;
            padding: 8px;
            margin: 8px 0;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
            font-size: 14px;
            color: #333;
            background-color: white;
        }
        select {
            appearance: none;
            -webkit-appearance: none;
            -moz-appearance: none;
            background: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24"><path fill="%23333" d="M7 10l5 5 5-5z"/></svg>') no-repeat right 10px center;
            background-size: 12px;
            background-color: white;
        }
        input[type="file"] {
            padding: 3px;
        }
        textarea {
            resize: vertical;
            height: 80px; /* Tinggi textarea diperkecil */
        }
        .button-container {
            display: flex;
            justify-content: space-between;
            gap: 10px;
            margin-top: 15px;
        }
        button, .back-btn {
            padding: 8px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.9em;
            text-decoration: none;
            color: white;
            flex: 1;
            text-align: center;
        }
        button {
            background-color: #2E8B57;
        }
        button:hover {
            background-color: #256C43;
        }
        .back-btn {
            background-color: #4682B4;
        }
        .back-btn:hover {
            background-color: #3a6a94;
        }
        .error, .success {
            font-size: 0.85em;
            margin-top: 10px;
        }
        .error {
            color: red;
        }
        .success {
            color: green;
        }
        select:invalid {
            color: #999;
        }
        .current-photo {
            margin: 10px 0;
            font-size: 0.85em;
            color: #555;
        }
        .current-photo img {
            max-width: 100%;
            max-height: 150px;
            height: auto;
            border-radius: 8px;
            margin-top: 5px;
        }
        @media (max-width: 767px) {
            .form-container {
                padding: 15px;
                max-width: 90%;
            }
            h2 {
                font-size: 1.5em;
            }
            p {
                font-size: 0.85em;
            }
            input, textarea, select {
                font-size: 13px;
                padding: 6px;
                margin: 6px 0;
            }
            button, .back-btn {
                font-size: 0.85em;
                padding: 6px;
            }
        }
    </style>
</head>
<body>
    <div class="form-container">
        <h2>Edit Produk</h2>
        <p>Perbarui detail produk Anda</p>

        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) . "?id=" . $product_id; ?>" id="productForm" enctype="multipart/form-data">
            <input type="text" name="name" placeholder="Nama Produk" value="<?php echo htmlspecialchars($name); ?>" required>
            <input type="text" name="price" placeholder="Harga (Rp.)" value="<?php echo htmlspecialchars($price); ?>" pattern="[0-9]*" inputmode="numeric" required>
            <select name="kategori" required>
                <option value="" disabled>Pilih Kategori</option>
                <option value="Buah" <?php echo $kategori == 'Buah' ? 'selected' : ''; ?>>Buah</option>
                <option value="Sayur" <?php echo $kategori == 'Sayur' ? 'selected' : ''; ?>>Sayur</option>
                <option value="Minuman" <?php echo $kategori == 'Minuman' ? 'selected' : ''; ?>>Minuman</option>
                <option value="Bumbu" <?php echo $kategori == 'Bumbu' ? 'selected' : ''; ?>>Bumbu</option>
                <option value="Lainnya" <?php echo $kategori == 'Lainnya' ? 'selected' : ''; ?>>Lainnya</option>
            </select>
            <textarea name="description" placeholder="Deskripsi" required><?php echo htmlspecialchars($description); ?></textarea>
            <input type="file" name="foto_produk" accept="image/*">
            <div class="current-photo">
                <p>Foto Saat Ini: <?php echo htmlspecialchars($foto_name); ?></p>
                <?php if (!empty($foto_name)) { ?>
                    <img src="uploads/<?php echo htmlspecialchars($foto_name); ?>" alt="Foto Produk">
                <?php } ?>
            </div>
            <div class="button-container">
                <button type="submit">Perbarui Produk</button>
                <a href="edit_products.php" class="back-btn">Kembali</a>
            </div>
        </form>

        <?php if ($error) { ?>
            <div class="error"><?php echo $error; ?></div>
        <?php } ?>
        <?php if ($success) { ?>
            <div class="success" id="successMessage"><?php echo $success; ?></div>
        <?php } ?>
    </div>

    <script>
        const successMessage = document.getElementById('successMessage');
        if (successMessage) {
            setTimeout(() => {
                successMessage.style.display = 'none';
            }, 1500);
        }

        const select = document.querySelector('select[name="kategori"]');
        select.addEventListener('change', () => {
            if (select.value === '') {
                select.setCustomValidity("Silakan pilih kategori");
            } else {
                select.setCustomValidity("");
            }
        });
    </script>
</body>
</html>