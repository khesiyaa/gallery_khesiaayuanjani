<?php
session_start();
require 'koneksi.php'; // Koneksi ke database

if (!isset($_SESSION['UserID'])) {
    header("Location: login.php"); // Arahkan ke halaman login jika belum login
    exit();
}

// Ambil data dari form
$fotoID = isset($_POST['fotoID']) ? intval($_POST['fotoID']) : 0;
$isiKomentar = isset($_POST['isi_komentar']) ? trim($_POST['isi_komentar']) : '';

// Validasi komentar dan fotoID
if ($fotoID > 0 && !empty($isiKomentar)) {
    // Insert komentar ke database
    $queryInsert = "INSERT INTO komentarfoto (FotoID, UserID, IsiKomentar, TanggalKomentar) 
                    VALUES (?, ?, ?, NOW())";
    $stmtInsert = $kon->prepare($queryInsert);
    $stmtInsert->bind_param("iis", $fotoID, $_SESSION['UserID'], $isiKomentar);

    if ($stmtInsert->execute()) {
        // Jika berhasil, redirect ke halaman view_foto.php dengan ID foto
        header("Location: view_foto.php?id=$fotoID");
        exit();
    } else {
        echo "Gagal menyimpan komentar. Silakan coba lagi.";
    }
} else {
    echo "Isi komentar tidak boleh kosong.";
    exit();
}
