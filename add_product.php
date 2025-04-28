<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Product - Pasa Danguang-danguang</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            height: 100vh;
            display: flex;
            justify-content: flex-end;
            align-items: center;
            background-image: url('https://images.unsplash.com/photo-1631592058858-a8c4b556df5b?q=80&w=2072&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D');
            background-size: cover;
            background-attachment: fixed;
            background-position: center;
        }
        .form-container {
            background: rgba(255, 255, 255, 0.3);
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 30px rgba(0, 0, 0, 0.5);
            width: 350px;
            text-align: center;
            margin-right: 15%;
        }
        h2 {
            color: #2E8B57;
            margin-bottom: 10px;
            font-weight: bold;
            text-shadow: 0 3px 4px rgba(0, 0, 0, 1);
        }
        p {
            color: #f0f0f0;
            margin-bottom: 20px;
            font-size: 0.9em;
            text-shadow: 0 1px 3px rgba(0, 0, 0, 1);
        }
        input, textarea, select {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
            font-size: 16px;
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
        }
        .button-container {
            display: flex;
            justify-content: space-between;
            gap: 10px; /* Jarak antara tombol */
            margin-top: 10px;
        }
        button, .back-btn {
            padding: 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 1em;
            text-decoration: none;
            color: white;
            flex: 1; /* Membuat tombol sama lebar */
            text-align: center;
        }
        button {
            background-color: #2E8B57;
        }
        button:hover {
            background-color: #256C43;
        }
        .back-btn {
            background-color: #666;
        }
        .back-btn:hover {
            background-color: #444;
        }
        .error, .success {
            font-size: 0.9em;
            margin-top: 5px;
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
    </style>
</head>
<body>
    <?php
    session_start();

    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);

    $user_id = $_SESSION['user_id'] ?? 0;
    if ($user_id == 0) {
        header("Location: login.php");
        exit();
    }

    // Ambil role user
    $conn = mysqli_connect("localhost", "root", "", "ecommerce");
    if (!$conn) {
        die("Koneksi gagal: " . mysqli_connect_error());
    }

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

    $name = $price = $description = $kategori = $foto_name = "";
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
            $_SESSION['error'] = "Semua field harus diisi!";
            error_log("Validation failed: Empty fields - Name: '$name', Price: '$price', Description: '$description', Kategori: '$kategori'");
        } elseif (!is_numeric($price) || $price <= 0) {
            $_SESSION['error'] = "Harga harus berupa angka positif!";
            error_log("Validation failed: Price is not a positive number - Price: '$price'");
        } elseif (empty($_FILES['foto_produk']['name'])) {
            $_SESSION['error'] = "Foto produk harus diunggah!";
            error_log("Validation failed: No photo uploaded");
        } else {
            // Proses upload foto
            $target_dir = "uploads/";
            if (!file_exists($target_dir)) {
                if (!mkdir($target_dir, 0755, true)) {
                    $_SESSION['error'] = "Gagal membuat folder uploads!";
                    error_log("Failed to create uploads directory");
                    header("Location: " . $_SERVER["PHP_SELF"]);
                    exit();
                }
            }

            // Generate unique filename to avoid overwriting
            $foto_name = time() . "_" . basename($_FILES["foto_produk"]["name"]);
            $target_file = $target_dir . $foto_name;
            $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
            $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];

            if (!in_array($imageFileType, $allowed_types)) {
                $_SESSION['error'] = "Hanya file JPG, JPEG, PNG, atau GIF yang diperbolehkan!";
                error_log("Validation failed: Invalid file type - Type: '$imageFileType'");
            } elseif ($_FILES["foto_produk"]["size"] > 5000000) {
                $_SESSION['error'] = "Ukuran file terlalu besar, maksimum 5MB!";
                error_log("Validation failed: File size too large - Size: " . $_FILES["foto_produk"]["size"]);
            } else {
                if (move_uploaded_file($_FILES["foto_produk"]["tmp_name"], $target_file)) {
                    error_log("File uploaded successfully: $target_file");
                    // Koneksi ke database
                    if (!$conn) {
                        $_SESSION['error'] = "Koneksi database gagal: " . mysqli_connect_error();
                        error_log("Database connection failed: " . mysqli_connect_error());
                    } else {
                        // Check if foto_produk column exists
                        $result = mysqli_query($conn, "SHOW COLUMNS FROM products LIKE 'foto_produk'");
                        if (mysqli_num_rows($result) == 0) {
                            // Automatically add the column if it doesn't exist
                            $alter_query = "ALTER TABLE products ADD foto_produk VARCHAR(255) DEFAULT NULL";
                            if (mysqli_query($conn, $alter_query)) {
                                error_log("Column foto_produk added to products table");
                            } else {
                                $_SESSION['error'] = "Gagal menambahkan kolom foto_produk: " . mysqli_error($conn);
                                error_log("Failed to add foto_produk column: " . mysqli_error($conn));
                                mysqli_close($conn);
                                header("Location: " . $_SERVER["PHP_SELF"]);
                                exit();
                            }
                        }

                        $stmt = mysqli_prepare($conn, "INSERT INTO products (nama_produk, harga, deskripsi, kategori, foto_produk, seller_id) VALUES (?, ?, ?, ?, ?, ?)");
                        if (!$stmt) {
                            $_SESSION['error'] = "Gagal mempersiapkan statement: " . mysqli_error($conn);
                            error_log("Prepare statement failed: " . mysqli_error($conn));
                            mysqli_close($conn);
                            header("Location: " . $_SERVER["PHP_SELF"]);
                            exit();
                        }

                        mysqli_stmt_bind_param($stmt, "sdsssi", $name, $price, $description, $kategori, $foto_name, $user_id);
                        if (mysqli_stmt_execute($stmt)) {
                            $_SESSION['success'] = "Produk berhasil ditambahkan!";
                            error_log("Data inserted successfully: Name: $name, Price: $price, Kategori: $kategori, Foto: $foto_name, Seller ID: $user_id");
                        } else {
                            $_SESSION['error'] = "Gagal menambahkan produk: " . mysqli_stmt_error($stmt);
                            error_log("Insert failed: " . mysqli_stmt_error($stmt));
                        }
                        mysqli_stmt_close($stmt);
                        mysqli_close($conn);
                    }
                } else {
                    $_SESSION['error'] = "Gagal mengunggah foto! Pastikan folder uploads memiliki izin tulis.";
                    error_log("Failed to upload file: " . $_FILES["foto_produk"]["error"]);
                }
            }
        }
        header("Location: " . $_SERVER["PHP_SELF"]);
        exit();
    }
    ?>

    <div class="form-container">
        <h2>Add New Product</h2>
        <p>Fill in the details to add a product</p>

        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" id="productForm" enctype="multipart/form-data">
            <input type="text" name="name" placeholder="Product Name" value="<?php echo htmlspecialchars($name); ?>" required>
            <input type="text" name="price" placeholder="Price (Rp.)" value="<?php echo htmlspecialchars($price); ?>" pattern="[0-9]*" inputmode="numeric" required>
            <select name="kategori" required>
                <option value="" disabled selected>Pilih Kategori</option>
                <option value="Buah">Buah</option>
                <option value="Sayur">Sayur</option>
                <option value="Minuman">Minuman</option>
                <option value="Bumbu">Bumbu</option>
                <option value="Lainnya">Lainnya</option>
            </select>
            <textarea name="description" placeholder="Description" rows="4" required><?php echo htmlspecialchars($description); ?></textarea>
            <input type="file" name="foto_produk" accept="image/*" required>
            <div class="button-container">
                <button type="submit">Add Product</button>
                <a href="index.php" class="back-btn">Kembali</a>
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