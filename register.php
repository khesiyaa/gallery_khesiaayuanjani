<?php
include_once("koneksi.php");

session_start();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Collect user input
    $username = $_POST['username'];
    $password = $_POST['password'];
    $email = $_POST['email'];
    $namaLengkap = $_POST['nama_lengkap'];
    $alamat = $_POST['alamat'];
    $peran = $_POST['peran'];

    // Validate user role
    $valid_peran = ['admin', 'user']; 
    if (!in_array($peran, $valid_peran)) {
        $error = 'Peran yang dipilih tidak valid.';
    } else {
        // Check for empty fields
        if (!empty($username) && !empty($password) && !empty($email) && !empty($namaLengkap) && !empty($alamat)) {
            // Prepare to check for existing username or email
            $stmt = $kon->prepare("SELECT UserID FROM user WHERE Username = ? OR Email = ?");
            $stmt->bind_param("ss", $username, $email);
            $stmt->execute();
            $stmt->store_result();

            // Check if username or email already exists
            if ($stmt->num_rows > 0) {
                $error = 'Username atau Email sudah digunakan.';
            } else {
                // Hash the password
                $hashed_password = password_hash($password, PASSWORD_BCRYPT);

                // Prepare the insert statement
                $stmt = $kon->prepare("INSERT INTO user (Username, Password, Peran, Email, NamaLengkap, Alamat) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("ssssss", $username, $hashed_password, $peran, $email, $namaLengkap, $alamat);

                // Execute the insert
                if ($stmt->execute()) {
                    $success = 'Pendaftaran berhasil. Silakan login.';
                } else {
                    $error = 'Pendaftaran gagal. Silakan coba lagi.';
                }
                $stmt->close();
            }
        } else {
            $error = 'Silakan isi semua kolom.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #1e1e1e; 
            margin: 0;
            padding: 0;
            color: #f5f5f5; 
        }
        .container {
            width: 30%;
            margin: auto;
            padding: 20px;
            background: #2b2b2b; 
            border-radius: 8px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.5); 
            margin-top: 50px;
        }
        h2 {
            margin-top: 0;
            color: #00bcd4;
            text-align: center;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            color: #f5f5f5; 
        }
        .form-group input, .form-group select {
            width: 100%;
            padding: 8px;
            box-sizing: border-box;
            background-color: #3b3b3b; 
            border: 1px solid #00bcd4;
            border-radius: 4px;
            color: #f5f5f5; 
        }
        .form-group input:focus, .form-group select:focus {
            outline: none;
            border-color: #0097a7; 
        }
        .form-group button {
            background-color: #00bcd4; 
            color: #1e1e1e; 
            border: none;
            padding: 10px;
            cursor: pointer;
            border-radius: 4px;
        }
        .form-group button:hover {
            background-color: #0097a7; /* Darker gold on hover */
        }
        .error {
            color: #ff6666; /* Light red for errors */
            margin-bottom: 15px;
        }
        .success {
            color: #66ff66; /* Light green for success messages */
            margin-bottom: 15px;
        }
        .login-link {
            margin-top: 15px;
        }
        .login-link a {
            color: #00bcd4;
            text-decoration: none;
        }
        .login-link a:hover {
            text-decoration: underline;
        }
        .password-wrapper {
            position: relative;
        }
        .password-wrapper input[type="password"],
        .password-wrapper input[type="text"] {
            padding-right: 40px;
            background-color: #3b3b3b; /* Dark background for password fields */
            color: #f5f5f5; /* Light text for password fields */
        }
        .password-wrapper .toggle-password {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #f7b500; /* Gold color for the toggle icon */
        }

    </style>
    <script>
        function togglePassword(inputId, eyeIconId) {
            const passwordInput = document.getElementById(inputId);
            const eyeIcon = document.getElementById(eyeIconId);
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                eyeIcon.innerText = '👁️‍🗨️';
            } else {
                passwordInput.type = 'password';
                eyeIcon.innerText = '👁️';
            }
        }
    </script>
</head>
<body>
    <div class="container">
        <h2>Register</h2>
        <?php if (!empty($error)): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <?php if (!empty($success)): ?>
            <div class="success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>
        <form method="post" action="">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required>
            </div>
            <div class="form-group">
                <label for="nama_lengkap">Nama Lengkap</label>
                <input type="text" id="nama_lengkap" name="nama_lengkap" required>
            </div>
            <div class="form-group">
                <label for="alamat">Alamat</label>
                <input type="text" id="alamat" name="alamat" required>
            </div>
            <div class="form-group password-wrapper">
                <label for="password">Password</label>
                <div class="password-wrapper">
                <input type="password" id="password" name="password" required>
                <span id="togglePassword" class="toggle-password" onclick="togglePassword('password', 'togglePassword')">👁️</span>
                </div>
            </div>
            <div class="form-group">
                <label for="peran">Peran</label>
                <select id="peran" name="peran" required>
                    <option value="admin">Admin</option>
                    <option value="user">User</option>
                </select>
            </div>
            <div class="form-group">
                <button type="submit">Register</button>
            </div>
        </form>
        <div class="login-link">
            <p>Sudah punya akun? <a href="login.php">Login di sini</a></p>
        </div>
    </div>
</body>
</html>
