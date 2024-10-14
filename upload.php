<?php
session_start();
require 'koneksi.php'; 

if (!isset($_SESSION['UserID'])) {
    echo "<script>alert('User ID tidak ditemukan. Harap login terlebih dahulu.'); window.location.href='login.php';</script>";
    exit();
}

$userID = $_SESSION['UserID'];

// Proses upload foto
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Ambil data dari form
    $judulFoto = $_POST['judulFoto'];
    $deskripsiFoto = $_POST['deskripsiFoto'];
    $albumID = $_POST['albumID'];
    
    // Periksa apakah albumID milik user yang sedang login
    $queryCheckAlbum = "SELECT AlbumID FROM album WHERE AlbumID = ? AND UserID = ?";
    $stmtCheckAlbum = $kon->prepare($queryCheckAlbum);
    $stmtCheckAlbum->bind_param("ii", $albumID, $userID);
    $stmtCheckAlbum->execute();
    $resultCheckAlbum = $stmtCheckAlbum->get_result();

    if ($resultCheckAlbum->num_rows == 0) {
        echo "<script>alert('Anda tidak memiliki akses ke album ini.'); window.location.href='profil_user.php';</script>";
        exit();
    }
    
    // Proses upload file
    $lokasiFile = 'uploads/' . basename($_FILES['foto']['name']);
    if (move_uploaded_file($_FILES['foto']['tmp_name'], $lokasiFile)) {
        // Simpan data foto ke database
        $query = "INSERT INTO foto (JudulFoto, DeskripsiFoto, LokasiFile, AlbumID, UserID, TanggalUnggah) 
                  VALUES (?, ?, ?, ?, ?, NOW())";
        $stmt = $kon->prepare($query);
        $stmt->bind_param("sssii", $judulFoto, $deskripsiFoto, $lokasiFile, $albumID, $userID);
        
        if ($stmt->execute()) {
            echo "<script>alert('Foto berhasil diunggah!'); window.location.href='profil_user.php';</script>";
        } else {
            echo "<script>alert('Terjadi kesalahan saat menyimpan foto: " . $stmt->error . "');</script>";
        }
    } else {
        echo "<script>alert('Gagal mengunggah foto.');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #1a1a1a; 
            color: #e0e0e0;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        
        .container {
            width: 100%;
            max-width: 400px;
            background-color: #2c2c2c; 
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.8);
            transition: transform 0.3s ease;
        }

        .container:hover {
            transform: scale(1.03);
        }

        h1 {
            text-align: center;
            font-size: 24px;
            margin-bottom: 20px;
            color: #fff;
        }

        p {
            text-align: center;
            margin-bottom: 20px;
            font-size: 14px;
        }

        input[type="text"], select, input[type="file"] {
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            border: none;
            border-radius: 8px;
            background-color: #3a3a3a; 
            color: #e0e0e0;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        input[type="text"]:focus, select:focus, input[type="file"]:focus {
            outline: none;
            background-color: #444444;
            box-shadow: 0 0 10px rgba(255, 255, 255, 0.1);
        }

        button {
            width: 100%;
            padding: 15px;
            background-color: #00bcd4;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            color: white;
            cursor: pointer;
            margin-top: 20px;
            transition: background-color 0.3s ease, transform 0.2s;
        }

        button:hover {
            background-color: #0097a7;
            transform: translateY(-2px);
        }

        .back-button {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
            font-size: 18px;
            color: #00bcd4;
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .back-button i {
            margin-right: 10px;
            font-size: 20px;
        }

        .back-button:hover {
            color: #0097a7;
        }

        ::placeholder {
            color: #bbbbbb;
        }

        @media (max-width: 600px) {
            .container {
                padding: 20px;
            }

            h1 {
                font-size: 20px;
            }

            p {
                font-size: 12px;
            }

            button {
                padding: 12px;
                font-size: 14px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="javascript:history.back()" class="back-button">
            <i class="fas fa-arrow-left"></i>
            Kembali
        </a>
        <h1>Upload Foto</h1>
        <p>Unggah foto terbaik Anda ke dalam album</p>
        <form method="POST" enctype="multipart/form-data">
            <input type="text" name="judulFoto" placeholder="Judul Foto" required>
            <input type="text" name="deskripsiFoto" placeholder="Deskripsi Foto" required>
            <select name="albumID" required>
                <option value="">Pilih Album</option>
                
                <?php
                // Hanya menampilkan album milik user yang sedang login
                $queryAlbum = "SELECT AlbumID, NamaAlbum FROM album WHERE UserID = ?";
                $stmtAlbum = $kon->prepare($queryAlbum);
                $stmtAlbum->bind_param("i", $userID);
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
                    echo "<option value=''>Error: " . $stmtAlbum->error . "</option>";
                }
                ?>
            </select>
            <input type="file" name="foto" accept="image/*" required>
            <button type="submit">Unggah Foto</button>
        </form>
    </div>
</body>
</html>
