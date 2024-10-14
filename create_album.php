<?php
session_start(); // Memulai sesi

include 'koneksi.php';

// Proses pembuatan album
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $namaAlbum = $_POST['namaAlbum'];
    $deskripsi = $_POST['deskripsi'];
    
    // Memastikan UserID ada di dalam sesi
    if (isset($_SESSION['UserID'])) {
        $userID = $_SESSION['UserID']; // Ambil UserID dari sesi
    } else {
        die("Anda harus login terlebih dahulu."); // Menangani kasus di mana sesi tidak ada
    }

    $sql = "INSERT INTO album (NamaAlbum, Deskripsi, TanggalDibuat, UserID) VALUES (?, ?, NOW(), ?)";
    $stmt = $kon->prepare($sql);
    $stmt->bind_param("ssi", $namaAlbum, $deskripsi, $userID);
    
    if ($stmt->execute()) {
        echo "Album berhasil dibuat!";
    } else {
        echo "Gagal membuat album: " . $kon->error;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buat Album</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f9f9f9;
        }
        header {
            background-color: #f7b500;
            color: white;
            text-align: center;
            padding: 20px 0;
        }
        .container {
            width: 90%;
            max-width: 600px;
            margin: auto;
            padding: 20px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }
        input[type="text"] {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        a {
            background-color: #f7b500;
            color: white;
            padding: 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .button {
            background-color: #f7b500;
            color: white;
            padding: 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .button:hover {
            background-color: #f5a700;
        }
    </style>
</head>
<body>
    <header>
        <h1>Buat Album Baru</h1>
    </header>
    <div class="container">
    <form method="POST">
    <input type="text" name="namaAlbum" placeholder="Nama Album" required>
    <textarea name="deskripsi" placeholder="Deskripsi Album" required></textarea>
    <br>
    <button type="submit" class="button">Buat Album</button>
    <a href="dashboard.php" class="a">Kembali</a>
</form>
    </div>
</body>
</html>
