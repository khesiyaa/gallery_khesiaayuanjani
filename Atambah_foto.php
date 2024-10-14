<?php
session_start();
require 'koneksi.php'; // Pastikan koneksi database Anda benar

// Cek apakah UserID ada dalam sesi
if (isset($_SESSION['UserID'])) {
    $userID = $_SESSION['UserID'];
} else {
    echo "<script>alert('User ID tidak ditemukan. Harap login terlebih dahulu.'); window.location.href='login.php';</script>";
    exit();
}

// Proses upload foto
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Ambil data dari form
    $judulFoto = $_POST['judulFoto'];
    $deskripsiFoto = $_POST['deskripsiFoto'];
    $albumID = $_POST['albumID'];
    
    // Proses upload file
    $lokasiFile = 'uploads/' . basename($_FILES['foto']['name']);
    if (move_uploaded_file($_FILES['foto']['tmp_name'], $lokasiFile)) {
        // Simpan data foto ke database
        $query = "INSERT INTO foto (JudulFoto, DeskripsiFoto, LokasiFile, AlbumID, UserID, TanggalUnggah) 
                  VALUES (?, ?, ?, ?, ?, NOW())";
        $stmt = $kon->prepare($query);
        // Perbaiki bind_param dengan memastikan tipe data yang benar
        $stmt->bind_param("sssis", $judulFoto, $deskripsiFoto, $lokasiFile, $albumID, $userID);
        
        if ($stmt->execute()) {
            echo "<script>alert('Foto berhasil diunggah!'); window.location.href='dashboard.php';</script>";
        } else {
            echo "<script>alert('Terjadi kesalahan saat menyimpan foto: " . $stmt->error . "');</script>"; // Debugging error
        }
    } else {
        echo "<script>alert('Gagal mengunggah foto.');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Foto</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* CSS styles yang sudah ada */
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
        input[type="text"], input[type="file"], select {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        button {
            background-color: #f7b500;
            color: white;
            padding: 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            width: 100%;
        }
        button:hover {
            background-color: #f5a700;
        }
    </style>
</head>
<body>
    <header>
        <h1>Upload Foto</h1>
        <p>Unggah foto Anda ke album</p>
    </header>
    <div class="container">
        <form method="POST" enctype="multipart/form-data">
            <input type="text" name="judulFoto" placeholder="Judul Foto" required>
            <input type="text" name="deskripsiFoto" placeholder="Deskripsi Foto" required>
            <select name="albumID" required>
    <option value="">Pilih Album</option>
    <?php
    // Mengambil semua album
    $queryAlbum = "SELECT AlbumID, NamaAlbum FROM album";
    $stmtAlbum = $kon->prepare($queryAlbum);
    
    if ($stmtAlbum->execute()) {
        $resultAlbum = $stmtAlbum->get_result();
        if ($resultAlbum->num_rows > 0) {
            while ($album = $resultAlbum->fetch_assoc()) {
                echo "<option value='" . $album['AlbumID'] . "'>" . htmlspecialchars($album['NamaAlbum']) . "</option>";
            }
        } else {
            echo "<option value=''>Tidak ada album tersedia</option>";
        }
    } else {
        echo "<option value=''>Error: " . $stmtAlbum->error . "</option>"; // Debugging error
    }
    ?>
</select>

            <input type="file" name="foto" accept="image/*" required>
            <button type="submit">Unggah Foto</button>
        </form>
    </div>
</body>
</html>
