<?php
require 'koneksi.php';

// Ambil FotoID dari URL
if (isset($_GET['fotoID'])) {
    $fotoID = $_GET['fotoID'];

    // Ambil detail foto berdasarkan FotoID dari database, termasuk Judul dan Deskripsi
    $query = "SELECT LokasiFile, JudulFoto, DeskripsiFoto FROM foto WHERE FotoID = ?";
    $stmt = $kon->prepare($query);
    $stmt->bind_param("i", $fotoID);
    $stmt->execute();
    $result = $stmt->get_result();
    $foto = $result->fetch_assoc();

    if (!$foto) {
        echo "Foto tidak ditemukan.";
        exit();
    }
} else {
    echo "FotoID tidak valid.";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Foto</title>
    <style>
        body {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            text-align: center;
        }
        img {
            max-width: 100%;
            max-height: 80%; /* Menjaga ruang untuk link */
        }
        a {
            margin-bottom: 20px; /* Spasi antara tautan dan gambar */
            text-decoration: none;
            color: blue; 
            font-weight: bold; 
        }
        h1, p {
            margin: 10px;
        }
    </style>
</head>
<body>
    <h1><?php echo htmlspecialchars($foto['JudulFoto']); ?></h1> <!-- Tampilkan Judul -->
    <p><?php echo htmlspecialchars($foto['DeskripsiFoto']); ?></p> <!-- Tampilkan Deskripsi -->
    <img src="<?php echo htmlspecialchars($foto['LokasiFile']); ?>" alt="Foto" id="photoToPrint">
    <script>
        window.onload = function() {
            window.print(); // Buka jendela cetak saat halaman dimuat
        };
    </script>
</body>
</html>
