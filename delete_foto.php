<?php
session_start();
require 'koneksi.php'; // Ensure correct database connection

// Check if fotoID is passed in the URL
if (!isset($_GET['fotoID'])) {
    die("Error: FotoID tidak ditemukan.");
}

$fotoID = $_GET['fotoID'];

// Fetch the photo's file path for deletion
$query = "SELECT LokasiFile FROM foto WHERE FotoID = ?";
$stmt = $kon->prepare($query);
$stmt->bind_param("i", $fotoID);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Error: Foto tidak ditemukan.");
}

$foto = $result->fetch_assoc();
$fileLocation = $foto['LokasiFile'];

// Delete the photo entry from the database
$query = "DELETE FROM foto WHERE FotoID = ?";
$stmt = $kon->prepare($query);
$stmt->bind_param("i", $fotoID);

if ($stmt->execute()) {
    // After successfully deleting from the database, delete the photo file
    if (file_exists($fileLocation)) {
        unlink($fileLocation); // Remove the photo file from the server
    }
    header("Location: dashboard.php"); // Redirect to the dashboard after deletion
    exit();
} else {
    die("Error: Gagal menghapus foto.");
}
?>
