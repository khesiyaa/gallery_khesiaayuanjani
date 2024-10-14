<?php
session_start();
require 'koneksi.php'; 

// Get the photo ID from the URL
$fotoID = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($fotoID > 0) {
    // Query to fetch the specific photo details
    $queryFoto = "SELECT f.FotoID, f.JudulFoto, f.LokasiFile, f.TanggalUnggah, f.DeskripsiFoto, a.NamaAlbum, u.Username 
                  FROM foto f 
                  JOIN album a ON f.AlbumID = a.AlbumID 
                  JOIN user u ON f.UserID = u.UserID 
                  WHERE f.FotoID = ?";
    $stmtFoto = $kon->prepare($queryFoto);
    $stmtFoto->bind_param("i", $fotoID);
    $stmtFoto->execute();
    $resultFoto = $stmtFoto->get_result();

    // Check if the photo was found
    if ($resultFoto->num_rows > 0) {
        $foto = $resultFoto->fetch_assoc(); // Fetch photo details
    } else {
        echo "Foto tidak ditemukan.";
        exit();
    }
} else {
    echo "ID foto tidak valid.";
    exit();
}

// mengambil komentar untuk foto dari tabel komentar
$queryKomentar = "SELECT k.KomentarID, k.IsiKomentar, k.TanggalKomentar, u.Username 
                  FROM komentarfoto k 
                  JOIN user u ON k.UserID = u.UserID 
                  WHERE k.FotoID = ?";
$stmtKomentar = $kon->prepare($queryKomentar);
$stmtKomentar->bind_param("i", $fotoID);
$stmtKomentar->execute();
$resultKomentar = $stmtKomentar->get_result();

// Cek user jika sudah like foto dari fotoid
$queryUserLiked = "SELECT COUNT(*) FROM likefoto WHERE FotoID = ? AND UserID = ?";
$stmtUserLiked = $kon->prepare($queryUserLiked);
$stmtUserLiked->bind_param("ii", $fotoID, $_SESSION['UserID']);
$stmtUserLiked->execute();
$resultUserLiked = $stmtUserLiked->get_result();
$userLiked = $resultUserLiked->fetch_row()[0] > 0;

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($foto['JudulFoto']); ?></title>
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

        .navbar .nav-links a:hover {
            color: #00bcd4; 
        }

        .container {
            max-width: 35%;
            margin: 80px auto; 
            padding: 20px;
            background-color: #1f1f1f;
            border-radius: 8px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.7);
        }

        .photo-detail {
            text-align: center;
        }

        .photo-detail img {
            width: 40%;
            height: auto;
            border-radius: 8px;
            margin-bottom: 15px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.5);
        }

        .photo-detail h2 {
            color: #00bcd4;
            font-size: 28px;
            margin-bottom: 10px;
        }

        .photo-detail p {
            font-size: 14px;
            color: #bdbdbd;
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

        .like-count {
            font-size: 0.9em;
            color: #e0e0e0;
        }

        .link {
            display: inline-block;
            margin-top: 10px;
            color: #00bcd4;
            text-decoration: none;
            font-weight: bold;
            transition: color 0.3s;
        }

        .link:hover {
            color: #0097a7;
        }

        .comment-section {
            margin-top: 20px;
        }

        .comment-section h4 {
            color: #00bcd4;
            margin-bottom: 15px;
        }

        .comment {
            margin-bottom: 15px;
            padding: 10px;
            background-color: #292929;
            border-left: 4px solid #00bcd4;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.3);
        }

        .comment-form {
            margin-top: 20px;
        }

        .comment-form textarea {
            width: 80%;
            height: 80px;
            background-color: #292929;
            color: #e0e0e0;
            border-radius: 5px;
            border: 1px solid #444;
            padding: 10px;
            margin-bottom: 10px;
            resize: none;
        }

        .comment-form button {
            padding: 10px 15px;
            background-color: #00bcd4;
            color: #1f1f1f;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
            font-weight: bold;
        }

        .comment-form button:hover {
            background-color: #0097a7;
        }
        .print-btn {
            padding: 10px 15px;
            background-color: #00bcd4;
            color: #1f1f1f;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
            font-weight: bold;
            margin-top: 15px;
        }

        .print-btn:hover {
            background-color: #0097a7;
        }

        @media (max-width: 600px) {
            .navbar .logo, .navbar .nav-links a {
                font-size: 14px;
            }

            .container {
                padding: 15px;
            }

            .photo-detail h2 {
                font-size: 24px;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="logo">Galeri Foto</div>
        <div class="nav-links">
            <a href="index.php"><i class="fas fa-arrow-left"></i> Kembali</a>
            <?php if (isset($_SESSION['UserID'])): ?>
                <a href="logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
            <?php else: ?>
                <a href="login.php" class="login-btn"><i class="fas fa-sign-in-alt"></i> Login</a>
            <?php endif; ?>
        </div>
    </nav>

    <div class="container">
        <div class="photo-detail">
            <h2><?php echo htmlspecialchars($foto['JudulFoto']); ?></h2>
            <img src="<?php echo htmlspecialchars($foto['LokasiFile']); ?>" alt="<?php echo htmlspecialchars($foto['JudulFoto']); ?>">
            <p><strong>Diunggah oleh:</strong> <?php echo htmlspecialchars($foto['Username']); ?></p>
            <p><strong></strong> <?php echo htmlspecialchars($foto['TanggalUnggah']); ?></p>
            <!-- Like Button -->
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
                        </div>
                    </div>

            <!-- Edit Link -->
            <a href="Uedit_foto.php?fotoID=<?php echo $foto['FotoID']; ?>" class="link">Edit</a><br>
            <!-- Unduh Link -->
            <a href="Udownload.php?fotoID=<?php echo $foto['FotoID']; ?>" class="link">Cetak Foto</a> <br>
            <a href='Acetak_foto.php?image=<?php echo urlencode(basename($foto['LokasiFile'])); ?>' class='link'>Unduh</a>
                    


        <p><strong><?php echo htmlspecialchars($foto['Username']); ?></strong> <?php echo htmlspecialchars($foto['DeskripsiFoto']); ?></p>

        <div class="comment-section">
            <h4>Komentar:</h4>
            <?php while ($komentar = $resultKomentar->fetch_assoc()): ?>
                <div class="comment">
                    <strong><?php echo htmlspecialchars($komentar['Username']); ?>:</strong> 
                    <?php echo htmlspecialchars($komentar['IsiKomentar']); ?> <em>(<?php echo htmlspecialchars($komentar['TanggalKomentar']); ?>)</em>
                </div>
            <?php endwhile; ?>
        </div>

        <div class="comment-form">
            <h4>Tinggalkan Komentar:</h4>
            <form action="comment.php" method="POST">
                <input type="hidden" name="fotoID" value="<?php echo $fotoID; ?>">
                <textarea name="isi_komentar" required placeholder="Tulis komentar di sini..."></textarea>
                <button type="submit">Kirim Komentar</button>
            </form>
        </div>
    </div>
    </div>
</body>
</html>
