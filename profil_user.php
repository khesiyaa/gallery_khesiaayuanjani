<?php
session_start();
require 'koneksi.php'; // Pastikan koneksi database benar

// Ambil ID pengguna dari session
if (!isset($_SESSION['UserID'])) {
    header("Location: login.php");
    exit;
}
$selectedCategory = isset($_GET['category']) ? $_GET['category'] : '';
$userID = $_SESSION['UserID'];

// Ambil detail user dari database
$queryUser = "SELECT Username, Email, NamaLengkap FROM user WHERE UserID = ?";
$stmtUser = $kon->prepare($queryUser);
$stmtUser->bind_param("i", $userID);
$stmtUser->execute();
$resultUser = $stmtUser->get_result();
$userData = $resultUser->fetch_assoc();

// Ambil album yang dibuat oleh user
$queryAlbum = "SELECT AlbumID, NamaAlbum, Deskripsi FROM album WHERE UserID = ? ORDER BY AlbumID DESC";
$stmtAlbum = $kon->prepare($queryAlbum);
$stmtAlbum->bind_param("i", $userID);
$stmtAlbum->execute();
$resultAlbum = $stmtAlbum->get_result();

// Ambil foto yang diunggah oleh user
$queryFoto = "SELECT FotoID, JudulFoto, LokasiFile, TanggalUnggah FROM foto WHERE UserID = ? ORDER BY FotoID DESC";
$stmtFoto = $kon->prepare($queryFoto);
$stmtFoto->bind_param("i", $userID);
$stmtFoto->execute();
$resultFoto = $stmtFoto->get_result();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Pengguna</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #121212;
            color: #e0e0e0;
        }

        .navbar {
            background-color: #1f1f1f;
            background-color: rgba(31, 31, 31, 0.85);
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.5);
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000; /* Ensure navbar stays above other content */
        }

        .navbar .logo {
            font-size: 24px;
            font-weight: bold;
            color: #ffffff;
        }

        .navbar .nav-links {
            display: flex;
            align-items: center;
        }

        .navbar .nav-links a {
            color: #ffffff;
            text-decoration: none;
            margin: 0 10px;
            font-size: 16px;
            transition: color 0.3s;
        }

        .profile-container {
            max-width: 300px;
            margin: 50px auto;
            padding: 20px;
            background-color: #1f1f1f;
            border-radius: 70px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.5);
        }

        .profile-header {
            text-align: center;
        }

        .profile-header h1 {
            font-size: 1.8em;
            color: #ffffff;
        }

        .profile-header p {
            color: #b0b0b0;
            font-size: 1.2em;
        }

        .upload-btn {
            display: inline-block;
            padding: 12px 25px;
            background-color: #00bcd4;
            color: white;
            text-decoration: none;
            border-radius: 50px;
            font-weight: bold;
            transition: background-color 0.3s;
            margin-bottom: 20px;
        }

        .upload-btn:hover {
            background-color: #0097a7; 
        }

        .album-grid, .photo-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 15px;
            margin-top: 20px;
        }

        .album-item, .photo-item {
            background-color: #292929;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
            text-align: center;
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .album-item:hover, .photo-item:hover {
            transform: scale(1.05);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.5);
        }

        .album-info h3 a, .photo-item img {
            color: #e0e0e0;
            text-decoration: none;
        }

        .photo-item img {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-bottom: 5px solid #00bcd4;
        }

        footer {
            background-color: rgba(31, 31, 31, 0.85);
            color: #d1d1d1;
            text-align: center;
            padding: 10px 20px;
        }
    </style>
</head>
<body>
<nav class="navbar">
    <div class="logo">Profil Pengguna</div>
    <div class="nav-links">
        <?php if (isset($_SESSION['UserID'])): ?>
            <?php if (isset($_SESSION['peran']) && $_SESSION['peran'] == 'admin'): ?>
                <a href="dashboard.php" class="dashboard-btn"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
            <?php endif; ?>
            <a href="index.php">Beranda</a>
            <a href="logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
        <?php else: ?>
            <a href="login.php" class="login-btn"><i class="fas fa-sign-in-alt"></i> Login</a>
        <?php endif; ?>
    </div>
</nav>

<div class="profile-container">
    <div class="profile-header">
        <h1><?php echo htmlspecialchars($userData['NamaLengkap']); ?></h1>
        <p>Username: <?php echo htmlspecialchars($userData['Username']); ?></p>
        <p>Email: <?php echo htmlspecialchars($userData['Email']); ?></p>
        <a href="upload.php" class="upload-btn">Unggah Foto Baru</a>
        <a href="Ucreate_album.php" class="upload-btn">Buat Album</a>
    </div>
</div>

<h2>Album yang Dibuat</h2>
<div class="album-grid">
    <?php while ($album = $resultAlbum->fetch_assoc()): ?>
        <div class="album-item">
            <div class="album-info">
                <h3>
                    <a href="?category=<?php echo urlencode($album['NamaAlbum']); ?>">
                        <?php echo htmlspecialchars($album['NamaAlbum']); ?>
                    </a>
                </h3>
                <p class="album-description">
                    <?php echo htmlspecialchars($album['Deskripsi']); ?>
                </p>
            </div>
        </div>
    <?php endwhile; ?>
</div>

<h2>Foto yang Diunggah</h2>
<div class="photo-grid">
    <?php while ($foto = $resultFoto->fetch_assoc()): ?>
        <div class="photo-item">
            <a href="view_foto.php?id=<?php echo $foto['FotoID']; ?>">
                <img src="<?php echo htmlspecialchars($foto['LokasiFile']); ?>" alt="<?php echo htmlspecialchars($foto['JudulFoto']); ?>">
            </a>
            <p><?php echo htmlspecialchars($foto['JudulFoto']); ?></p>
            <p><?php echo htmlspecialchars($foto['TanggalUnggah']); ?></p>
        </div>
    <?php endwhile; ?>
</div>

<footer>
    &copy; <?php echo date("Y"); ?> Galeri Foto. All rights reserved.
</footer>

</body>
</html>
