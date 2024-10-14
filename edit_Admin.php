<?php
session_start();
require 'koneksi.php'; // Ensure correct database connection

// Check if fotoID is passed in the URL
if (!isset($_GET['fotoID'])) {
    die("Error: FotoID tidak ditemukan."); // Error if no fotoID in URL
}

$fotoID = $_GET['fotoID'];

// Fetch the photo details based on fotoID
$query = "SELECT * FROM foto WHERE FotoID = ?";
$stmt = $kon->prepare($query);
$stmt->bind_param("i", $fotoID);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Error: Foto tidak ditemukan."); // Error if no matching photo found
}

$foto = $result->fetch_assoc();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $judulFoto = $_POST['JudulFoto'];
    $deskripsiFoto = $_POST['DeskripsiFoto'];

    // Check if a new file is uploaded
    if (isset($_FILES['LokasiFile']) && $_FILES['LokasiFile']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['LokasiFile']['tmp_name'];
        $fileName = $_FILES['LokasiFile']['name'];
        $fileNameCmps = explode(".", $fileName);
        $fileExtension = strtolower(end($fileNameCmps));

        // Rename file to avoid conflicts
        $newFileName = md5(time() . $fileName) . '.' . $fileExtension;

        // File upload path
        $uploadFileDir = './uploads/';
        $dest_path = $uploadFileDir . $newFileName;

        // Move the file to the uploads directory
        if (move_uploaded_file($fileTmpPath, $dest_path)) {
            // Update the photo info with the new file path
            $query = "UPDATE foto SET JudulFoto = ?, DeskripsiFoto = ?, LokasiFile = ? WHERE FotoID = ?";
            $stmt = $kon->prepare($query);
            $stmt->bind_param('sssi', $judulFoto, $deskripsiFoto, $dest_path, $fotoID);
        } else {
            die('Error: File upload failed.');
        }
    } else {
        // Update the photo info without changing the file
        $query = "UPDATE foto SET JudulFoto = ?, DeskripsiFoto = ? WHERE FotoID = ?";
        $stmt = $kon->prepare($query);
        $stmt->bind_param('ssi', $judulFoto, $deskripsiFoto, $fotoID);
    }

    if ($stmt->execute()) {
        header("Location: dashboard.php"); // Redirect to dashboard after update
        exit();
    } else {
        die('Error: Failed to update data.');
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Foto</title>
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
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1); /* Subtle shadow for container */
        }

        .container h1 {
            text-align: center;
            color: #333;
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            font-weight: bold;
            color: black; /* White labels */
            margin-bottom: 5px;
        }

        .form-group input, .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc; /* Light border */
            border-radius: 4px;
            box-sizing: border-box;
            margin-bottom: 10px;
        }

        .form-group input:focus, .form-group textarea:focus {
            border-color: #e5a400; /* Darker yellow border on focus */
            outline: none;
        }

        .form-group img {
            max-width: 100%;
            margin-top: 10px;
            border-radius: 4px; /* Round corners for images */
        }

        .button {
            background-color: #f7b500; /* White button */
            color: white; /* Yellow text */
            padding: 10px;
            border: 2px solid white;
            border-radius: 4px;
            cursor: pointer;
            display: block;
            text-align: center;
            font-weight: bold;
            text-decoration: none;
            width: 100%;
        }

        .button:hover {
            background-color: #f5a700; /* Light yellow on hover */
            border-color: #f5a700;
        }
    </style>
</head>
<body>
<div class="container">
    <h1>Edit Foto</h1>

    <form action="" method="POST" enctype="multipart/form-data">
        <div class="form-group">
            <label for="JudulFoto">Judul Foto</label>
            <input type="text" id="JudulFoto" name="JudulFoto" value="<?php echo htmlspecialchars($foto['JudulFoto']); ?>">
        </div>

        <div class="form-group">
            <label for="DeskripsiFoto">Deskripsi Foto</label>
            <textarea id="DeskripsiFoto" name="DeskripsiFoto"><?php echo htmlspecialchars($foto['DeskripsiFoto']); ?></textarea>
        </div>

        <div class="form-group">
            <label for="LokasiFile">Foto Saat Ini</label>
            <img src="<?php echo htmlspecialchars($foto['LokasiFile']); ?>" alt="Foto">
        </div>

        <div class="form-group">
            <label for="LokasiFile">Ganti Foto</label>
            <input type="file" id="LokasiFile" name="LokasiFile">
        </div>

        <button type="submit" class="button">Update Foto</button>
        <br>
        <button href="dashboard.php" class="button">Kembali ke Dashboard</a>
    </form>
</div>
</body>
</html>
