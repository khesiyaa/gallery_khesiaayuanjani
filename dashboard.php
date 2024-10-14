<?php
session_start();
require 'koneksi.php'; 

if (!isset($_SESSION['UserID'])) {
    header("Location: login.php"); 
    exit();
}

// mengambil detail album dari database tabel album
$queryAlbum = "SELECT AlbumID, NamaAlbum, Deskripsi, TanggalDibuat FROM album";
$stmtAlbum = $kon->prepare($queryAlbum);
$stmtAlbum->execute();
$resultAlbum = $stmtAlbum->get_result();

// menghandle aksi untuk menghapus album
if (isset($_GET['delete_album'])) {
    $albumID = $_GET['delete_album'];
    $queryDeleteAlbum = "DELETE FROM album WHERE AlbumID = ?";
    $stmtDeleteAlbum = $kon->prepare($queryDeleteAlbum);
    $stmtDeleteAlbum->bind_param("i", $albumID);
    $stmtDeleteAlbum->execute();
}

// menghandle aksi untuk menghapus foto
if (isset($_GET['delete_photo'])) {
    $fotoID = $_GET['delete_photo'];
    // mengambil lokasi file dari foto
    $queryFile = "SELECT LokasiFile FROM foto WHERE FotoID = ?";
    $stmtFile = $kon->prepare($queryFile);
    $stmtFile->bind_param("i", $fotoID);
    $stmtFile->execute();
    $resultFile = $stmtFile->get_result();
    $fileData = $resultFile->fetch_assoc();
    
    // hapus file dari server
    if (file_exists($fileData['LokasiFile'])) {
        unlink($fileData['LokasiFile']);
    }

    // hapus dari database
    $queryDeleteFoto = "DELETE FROM foto WHERE FotoID = ?";
    $stmtDeleteFoto = $kon->prepare($queryDeleteFoto);
    $stmtDeleteFoto->bind_param("i", $fotoID);
    $stmtDeleteFoto->execute();
}

// mengambil semua detail foto dari database
$queryFoto = "SELECT f.FotoID, f.JudulFoto, f.DeskripsiFoto, f.LokasiFile, a.NamaAlbum, u.Username 
              FROM foto f 
              JOIN album a ON f.AlbumID = a.AlbumID 
              JOIN user u ON f.UserID = u.UserID
              ORDER BY f.TanggalUnggah DESC";
$resultFoto = $kon->query($queryFoto);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f9f9f9;
        }
        .navbar {
            background-color: #f9f9f9;
            color: white;
            display: flex;
            justify-content: space-between;
            padding: 15px;
            align-items: center;
        }
        .navbar a {
            color: black;
            text-decoration: none;
            padding: 0 15px;
            font-weight: bold;
        }
        .navbar a:hover {
            text-decoration: underline;
        }
        .navbar .menu {
            display: flex;
            gap: 10px;
        }
        .navbar .logo {
            color: black;
            font-size: 24px;
            font-weight: bold;
        }
        /* Responsive navbar */
        @media (max-width: 768px) {
            .navbar {
                flex-direction: column;
                align-items: flex-start;
            }
            .navbar .menu {
                flex-direction: column;
                width: 100%;
                padding: 0;
            }
            .navbar a {
                padding: 10px 0;
                width: 100%;
            }
        }
        header {
            background-color: #f7b500;
            color: white;
            text-align: center;
            padding: 20px 0;
        }
        .container {
            width: 90%;
            max-width: 1200px;
            margin: auto;
            padding: 20px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }
        .button {
            background-color: #f7b500;
            color: white;
            padding: 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin-bottom: 20px;
        }
        .button:hover {
            background-color: #f5a700;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        table, th, td {
            border: 1px solid #ccc;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f7b500;
            color: white;
        }
        .actions {
            display: flex;
            gap: 10px;
        }
    </style>
</head>
<body>
<nav class="navbar">
    <div class="logo">Dashboard Admin</div>
    <div class="menu">
        <a href="index.php">Home</a>
        <a href="logout.php">Logout</a>
    </div>
</nav>
<header>
    <?php if (isset($_SESSION['Username'])): ?>
        <h1>Selamat datang, <?php echo htmlspecialchars($_SESSION['Username']); ?>!</h1>
    <?php else: ?>
        <h1>Selamat datang, Pengguna!</h1> <!-- Jika username belum diset -->
    <?php endif; ?>
    <p>Kelola Album dan Foto</p>
</header>
<div class="container">
    <h2>Daftar Album</h2>

    <!-- Tambahkan input untuk search album -->
    <input type="text" id="searchAlbum" onkeyup="searchTable('searchAlbum', 'albumTable')" placeholder="Cari Album...">
    <button class="button" onclick="window.location.href='create_album.php'">Buat Album Baru</button>

    <table id="albumTable">
        <thead>
            <tr>
                <th>ID Foto</th>
                <th>Nama Album</th>
                <th>Deskripsi</th>
                <th>Tanggal Dibuat</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($album = $resultAlbum->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($album['AlbumID']); ?></td>
                    <td><?php echo htmlspecialchars($album['NamaAlbum']); ?></td>
                    <td><?php echo htmlspecialchars($album['Deskripsi']); ?></td>
                    <td><?php echo htmlspecialchars($album['TanggalDibuat']); ?></td>
                    <td class="actions">
                        <a href="edit_album.php?albumID=<?php echo $album['AlbumID']; ?>" class="button">Edit Album</a>
                        <a href="?delete_album=<?php echo $album['AlbumID']; ?>" class="button" onclick="return confirm('Apakah Anda yakin ingin menghapus album ini?')">Hapus Album</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <div class="foto-table">
        <h2>Daftar Foto</h2>

        <!-- Tambahkan input untuk search foto -->
        <input type="text" id="searchFoto" onkeyup="searchTable('searchFoto', 'fotoTable')" placeholder="Cari Foto...">

        <button class="button" onclick="window.location.href='Atambah_foto.php'">Upload foto</button>
        <table id="fotoTable">
            <thead>
                <tr>
                    <th>ID Foto</th>
                    <th>Judul Foto</th>
                    <th>Deskripsi</th>
                    <th>Album</th>
                    <th>Diunggah Oleh</th>
                    <th>Foto</th> 
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($foto = $resultFoto->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($foto['FotoID']); ?></td>
                        <td><?php echo htmlspecialchars($foto['JudulFoto']); ?></td>
                        <td><?php echo htmlspecialchars($foto['DeskripsiFoto']); ?></td>
                        <td><?php echo htmlspecialchars($foto['NamaAlbum']); ?></td>
                        <td><?php echo htmlspecialchars($foto['Username']); ?></td>
                        <td>
                            <img src="<?php echo htmlspecialchars($foto['LokasiFile']); ?>" alt="Foto" style="width:100px;height:auto;"> <!-- Display photo -->
                        </td>
                        <td class="actions">
                        <a href="edit_Admin.php?fotoID=<?php echo $foto['FotoID']; ?>" class="button">Edit</a>
                        <a href="Udownload.php?fotoID=<?php echo $foto['FotoID']; ?>" class="button">Cetak Foto</a> <br>
                        <!-- Unduh Link -->
                        <a href='Acetak_foto.php?image=<?php echo urlencode(basename($foto['LokasiFile'])); ?>' class='button'>Unduh</a>
                        <a href="?delete_photo=<?php echo $foto['FotoID']; ?>" class="button" onclick="return confirm('Apakah Anda yakin ingin menghapus foto ini?')">Hapus</a>
</td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

<script>
// Fungsi pencarian untuk tabel
function searchTable(inputId, tableId) {
    var input, filter, table, tr, td, i, j, txtValue;
    input = document.getElementById(inputId);
    filter = input.value.toUpperCase();
    table = document.getElementById(tableId);
    tr = table.getElementsByTagName("tr");

    // Loop untuk setiap row dalam tabel (kecuali header)
    for (i = 1; i < tr.length; i++) {
        tr[i].style.display = "none"; // Default: sembunyikan semua row

        // Loop untuk setiap kolom dalam row
        td = tr[i].getElementsByTagName("td");
        for (j = 0; j < td.length; j++) {
            if (td[j]) {
                txtValue = td[j].textContent || td[j].innerText;
                if (txtValue.toUpperCase().indexOf(filter) > -1) {
                    tr[i].style.display = ""; // Tampilkan row yang sesuai pencarian
                    break; // Berhenti loop jika ditemukan kecocokan dalam salah satu kolom
                }
            }
        }
    }
}
</script>
</div>
</body>
</html>
