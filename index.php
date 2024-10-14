<?php
session_start();
require 'koneksi.php'; // Ensure your database connection is correct

// Check if a category is set
$selectedCategory = isset($_GET['category']) ? $_GET['category'] : '';

// Fetch photos from the database based on category
$queryFoto = "SELECT f.FotoID, f.JudulFoto, f.LokasiFile, f.TanggalUnggah, a.NamaAlbum, u.Username 
              FROM foto f 
              JOIN album a ON f.AlbumID = a.AlbumID 
              JOIN user u ON f.UserID = u.UserID 
              WHERE a.NamaAlbum = ? OR ? = '' 
              ORDER BY f.FotoID DESC";
$stmtFoto = $kon->prepare($queryFoto);
$stmtFoto->bind_param("ss", $selectedCategory, $selectedCategory);
$stmtFoto->execute();
$resultFoto = $stmtFoto->get_result();

// Fetch albums from the database
$queryAlbum = "SELECT a.AlbumID, a.NamaAlbum, a.Deskripsi, u.Username 
               FROM album a 
               JOIN user u ON a.UserID = u.UserID 
               ORDER BY a.AlbumID DESC";
$resultAlbum = $kon->query($queryAlbum);


?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Halaman Index - Tema Gelap</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #121212; /* Latar belakang tema gelap */
            color: #e0e0e0; /* Warna teks terang */
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

        .navbar .nav-links a:hover {
            color: #00bcd4; 
        }

        header {
            text-align: center;
            margin: 30px 0;
        }

        header h1 {
            font-size: 2.5em;
            color: #ffffff;
        }

        header p {
            color: #b0b0b0; /* Warna teks lebih terang untuk header */
            font-size: 1.2em;
        }

        .container {
            max-width: 1200px;
            margin: auto;
            padding: 20px;
            background: #1c1c1c; /* Latar belakang gelap untuk kontainer */
            border-radius: 15px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.5);
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
            background-color: #292929; /* Warna album dan foto lebih gelap */
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

        .album-info h3 a {
            color: #e0e0e0;
            font-size: 1.2em;
            text-decoration: none;
            font-weight: bold;
        }

        .album-info h3 a:hover {
            color: #00bcd4;
        }

        .album-description {
            color: #999; 
            font-size: 14px;
            margin-top: 5px; 
        }

        .photo-item img {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-bottom: 5px solid #00bcd4; /* Garis biru bawah */
        }

        .photo-info {
            padding: 15px;
        }

        .photo-info p {
            margin: 0;
            color: #b0b0b0; /* Warna teks lebih terang */
            font-size: 0.9em;
        }

        .like-comment {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 10px;
        }

        .like-comment form {
            display: inline;
        }

        .button {
            border: none;
            background: none;
            cursor: pointer;
            font-size: 1.2em;
            color: #00bcd4;
            transition: color 0.3s;
        }

        .button:hover {
            color: #0097a7;
        }

        .like-count {
            font-size: 0.9em;
            color: #e0e0e0;
        }

        footer { 
            background-color: rgba(31, 31, 31, 0.85);
            color: #d1d1d1; 
            text-align: center;
            padding: 10px 20px; 
            margin-top: 100px; 
        }

        @media (max-width: 600px) {
            header h1 {
                font-size: 24px;
            }
            header p {
                font-size: 16px;
            }
        }
    </style>
</head>
<body>
<nav class="navbar">
    <div class="logo">Galeri Foto</div>
    <div class="nav-links">
        <?php if (isset($_GET['category'])): ?>
            <a href="index.php" style="margin-right: 20px;">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
        <?php endif; ?>
        
        <!-- Show Dashboard and Logout buttons if the user is logged in -->
        <?php if (isset($_SESSION['UserID'])): ?>
            <?php if (isset($_SESSION['peran']) && $_SESSION['peran'] == 'admin'): ?>
                <a href="dashboard.php" class="dashboard-btn"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
            <?php endif; ?>
            <a href="profil_user.php" class="upload-btn">Profil</a>
            <a href="logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
        <?php else: ?>
            <a href="login.php" class="login-btn"><i class="fas fa-sign-in-alt"></i> Login</a>
        <?php endif; ?>
    </div>
</nav>

    <br><br><br><header>
        <h1>Selamat Datang di Galeri Foto</h1>
        <p>Upload dan Temukan Foto Menarik Sesuai Album yang Anda Suka!</p>
    </header>
    <div class="container">
        <!-- Display albums with links to filter photos -->
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
            <p class="album-creator">
                Album By: <strong><?php echo htmlspecialchars($album['Username']); ?></strong>
            </p>
        </div>
    </div>
<?php endwhile; ?>

</div>

        <div class="photo-grid">
            <?php while ($foto = $resultFoto->fetch_assoc()): ?>
                <div class="photo-item">
                    <a href="view_foto.php?id=<?php echo $foto['FotoID']; ?>">
                        <img src="<?php echo htmlspecialchars($foto['LokasiFile']); ?>" alt="<?php echo htmlspecialchars($foto['JudulFoto']); ?>">
                    </a>
                    <div class="photo-info">
                        <p><strong>Diunggah oleh:</strong> <?php echo htmlspecialchars($foto['Username']); ?></p>
                        <p><strong></strong> <?php echo htmlspecialchars($foto['TanggalUnggah']); ?></p>

                        <div class="like-comment">
                            <form method="POST" action="like.php">
                                <input type="hidden" name="fotoID" value="<?php echo $foto['FotoID']; ?>">
                                <button type="submit" class="button"><i class="far fa-thumbs-up"></i></button>
                            </form>
                            <span class="like-count">
                                <?php 
                                // Count likes
                                $queryLikes = "SELECT COUNT(*) as LikeCount FROM likefoto WHERE FotoID = ?";
                                $stmtLikes = $kon->prepare($queryLikes);
                                $stmtLikes->bind_param("i", $foto['FotoID']);
                                $stmtLikes->execute();
                                $resultLikes = $stmtLikes->get_result();
                                $likeCount = $resultLikes->fetch_assoc()['LikeCount'];
                                echo $likeCount . " Likes";
                                ?>
                            </span>
                            <form method="GET" action="view_foto.php" style="display: inline;">
                                <input type="hidden" name="id" value="<?php echo $foto['FotoID']; ?>">
                                <button type="submit" class="button"><i class="far fa-comment"></i></button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>
    <footer>
        &copy; <?php echo date("Y"); ?> Galeri Foto. All rights reserved.
    </footer>
</body>
</html>
