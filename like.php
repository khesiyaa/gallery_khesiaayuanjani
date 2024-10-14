<?php
session_start();
require 'koneksi.php';

if (!isset($_SESSION['UserID'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['fotoID'])) {
    $fotoID = $_POST['fotoID'];
    $userID = $_SESSION['UserID'];

    // Cek jika user sudah memberi like pada foto ini
    $queryCheckLike = "SELECT * FROM likefoto WHERE FotoID = ? AND UserID = ?";
    $stmtCheckLike = $kon->prepare($queryCheckLike);
    $stmtCheckLike->bind_param("ii", $fotoID, $userID);
    $stmtCheckLike->execute();
    $resultCheck = $stmtCheckLike->get_result();

    if ($resultCheck->num_rows > 0) {
        // Jika sudah like, lakukan proses unlike (hapus like)
        $queryUnlike = "DELETE FROM likefoto WHERE FotoID = ? AND UserID = ?";
        $stmtUnlike = $kon->prepare($queryUnlike);
        $stmtUnlike->bind_param("ii", $fotoID, $userID);
        $stmtUnlike->execute();
    } else {
        // Jika belum like, lakukan proses like (insert like)
        $queryLike = "INSERT INTO likefoto (FotoID, UserID, TanggalLike) VALUES (?, ?, NOW())";
        $stmtLike = $kon->prepare($queryLike);
        $stmtLike->bind_param("ii", $fotoID, $userID);
        $stmtLike->execute();
    }

    // Redirect kembali ke halaman utama
    header("Location: " . $_SERVER['HTTP_REFERER']);
    exit();
}
?>
