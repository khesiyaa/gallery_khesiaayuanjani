<?php
session_start();
require 'koneksi.php'; // Ensure correct database connection

// Check if albumID is passed in the URL
if (!isset($_GET['albumID'])) {
    die("Error: AlbumID tidak ditemukan.");
}

$albumID = $_GET['albumID'];

// Fetch and delete all photos in the album
$query = "SELECT LokasiFile FROM foto WHERE AlbumID = ?";
$stmt = $kon->prepare($query);
$stmt->bind_param("i", $albumID);
$stmt->execute();
$result = $stmt->get_result();

// Loop through each photo, delete the file, and remove the entry from the database
while ($foto = $result->fetch_assoc()) {
    $fileLocation = $foto['LokasiFile'];
    
    // Delete photo file from the server
    if (file_exists($fileLocation)) {
        unlink($fileLocation); // Remove the photo file
    }
}

// Now delete all photos from the database for the given albumID
$query = "DELETE FROM foto WHERE AlbumID = ?";
$stmt = $kon->prepare($query);
$stmt->bind_param("i", $albumID);
$stmt->execute();

// Finally, delete the album from the database
$query = "DELETE FROM album WHERE AlbumID = ?";
$stmt = $kon->prepare($query);
$stmt->bind_param("i", $albumID);

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
