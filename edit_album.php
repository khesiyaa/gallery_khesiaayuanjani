<?php
session_start();
require 'koneksi.php'; // Ensure the correct database connection

// Check if albumID is passed in the URL
if (!isset($_GET['albumID'])) {
    die("Error: AlbumID tidak ditemukan.");
}

$albumID = $_GET['albumID'];

// Fetch the album data from the database
$query = "SELECT * FROM album WHERE AlbumID = ?";
$stmt = $kon->prepare($query);
$stmt->bind_param("i", $albumID);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Error: Album tidak ditemukan.");
}

$album = $result->fetch_assoc();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $namaAlbum = $_POST['nama_album'];
    $deskripsi = $_POST['deskripsi'];

    // Update the album details in the database
    $query = "UPDATE album SET NamaAlbum = ?, Deskripsi = ? WHERE AlbumID = ?";
    $stmt = $kon->prepare($query);
    $stmt->bind_param("ssi", $namaAlbum, $deskripsi, $albumID);

    if ($stmt->execute()) {
        header("Location: dashboard.php"); // Redirect to the dashboard after successful update
        exit();
    } else {
        echo "Error: Gagal mengupdate album.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Album</title>
    <link rel="stylesheet" href="style.css"> <!-- Include your CSS file -->
</head>
<style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4; /* Light gray background */
            margin: 0;
            padding: 0;
        }

        .container {
            width: 40%;
            margin: 50px auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1); /* Subtle shadow */
        }

        h2 {
            text-align: center;
            color: #333;
        }

        form {
            display: flex;
            flex-direction: column;
        }

        label {
            font-weight: bold;
            margin-top: 10px;
            color: #555;
        }

        input[type="text"],
        textarea {
            padding: 10px;
            margin-top: 5px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 16px;
        }

        input[type="text"]:focus,
        textarea:focus {
            border-color: #999;
            outline: none;
        }

        textarea {
            resize: vertical; /* Allow vertical resizing */
        }

        button {
            background-color: #f7b500; 
            color: white;
            border: none;
            padding: 10px;
            font-size: 16px;
            border-radius: 4px;
            margin-top: 20px;
            cursor: pointer;
        }

        button:hover {
            background-color: #f5a700; 
        }

        a.button {
            display: block;
            text-align: center;
            background-color: #f7b500; /* Blue link button */
            color: white;
            padding: 10px;
            margin-top: 20px;
            text-decoration: none;
            border-radius: 4px;
        }

        a.button:hover {
            background-color: #f5a700; /* Darker blue on hover */
        }

</style>
<body>
    <div class="container">
        <h2>Edit Album</h2>
        <form method="POST" action="">
            <label for="nama_album">Nama Album:</label>
            <input type="text" name="nama_album" id="nama_album" value="<?php echo htmlspecialchars($album['NamaAlbum']); ?>" required>

            <label for="deskripsi">Deskripsi:</label>
            <textarea name="deskripsi" id="deskripsi" rows="4" required><?php echo htmlspecialchars($album['Deskripsi']); ?></textarea>

            <button type="submit" class="button">Update Album</button>
        </form>
        <br>
        <a href="dashboard.php" class="button">Kembali ke Dashboard</a>
    </div>
</body>
</html>
