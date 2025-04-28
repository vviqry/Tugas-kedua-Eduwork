<?php

session_start();

// Koneksi ke database
$mysqli = new mysqli("localhost", "root", "", "ecommerce");

// Cek apakah koneksi berhasil
if ($mysqli->connect_error) {
    die("Koneksi gagal: " . $mysqli->connect_error);
}
// Wejangan mang Dea
if (isset($_POST['login'])) {
    $nama = $_POST['nama'];
    echo $nama;
}


// Ambil pesan error dari session (jika ada)
$error = isset($_SESSION['login_error']) ? $_SESSION['login_error'] : '';
unset($_SESSION['login_error']); // Hapus pesan error setelah ditampilkan

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $login = $mysqli->real_escape_string($_POST['nama']);
$password = $_POST['password'];

$query = "SELECT * FROM users WHERE email = '$login'";
    $result = $mysqli->query($query);

    if ($result && $result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            header("Location: index.php");
            exit();
        } else {
            $_SESSION['login_error'] = "Password salah!";
        }
    } else {
        $_SESSION['login_error'] = "akun tidak ditemukan!";
    }
    // Redirect untuk menghindari resubmission
    header("Location: login.php");
    exit();
}

$mysqli->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Pasa Danguang-danguang</title>
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
            background-position: center;
        }
        .form-container {
            background: rgba(255, 255, 255, 0.3);
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 30px rgba(0, 0, 0, 0.5);
            width: 350px;
            text-align: center;
            margin-right: 10%;
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
        input {
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
        button {
            width: 100%;
            padding: 10px;
            background-color: #2E8B57;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 1em;
        }
        button:hover {
            background-color: #256C43;
        }
        .error {
            font-size: 0.9em;
            margin-top: 5px;
            color: red;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <h2>Login</h2>
        <p>Masuk untuk melanjutkan belanja</p>

        <?php if (!empty($error)) { ?>
            <div class="error"><?php echo $error; ?></div>
        <?php } ?>

        <form method="POST" action="">
            <input type="text" name="nama" placeholder="username" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit" name="login">Login</button>
        </form>
        <p style="color: black; background-color: orange; padding: 10px; border-radius: 5px; text-shadow: 1px 1px 2px white;">Belum punya akun? <a href="register.php" style="color: white; text-decoration: underline; text-shadow: 1px 1px 2px white;">Buat di sini</a></p>


    </div>
</body>
</html>