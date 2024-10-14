<?php
session_start();
require 'koneksi.php'; // Pastikan path ke koneksi.php benar

$error = '';
$adminPassword = '1234'; // Password khusus untuk admin

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];
    $remember = isset($_POST['remember']); // Check if "Remember Me" is checked

    if (!empty($email) && !empty($password)) {
        // Adjust SQL query to match the new table structure
        // Modify this section where you fetch the login data
        $stmt = $kon->prepare("SELECT UserID, Username, peran, Password FROM user WHERE Email = ?");
        if ($stmt === false) {
            die("Prepare failed: " . $kon->error);
        }
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $stmt->bind_result($userId, $username, $userPeran, $hashedPassword); // Bind the Username as well
            $stmt->fetch();
            
            // Verify password for user
            if (password_verify($password, $hashedPassword)) {
                // Set session variables
                $_SESSION['UserID'] = $userId;
                $_SESSION['Username'] = $username;  // Store Username in session
                $_SESSION['Email'] = $email;
                $_SESSION['peran'] = $userPeran;

                // Remember Me: Store email and role in cookie if checkbox is checked
                if ($remember) {
                    setcookie('email', $email, time() + (86400 * 30), "/"); // Store for 30 days
                } else {
                    // Remove cookies if "Remember Me" is not checked
                    if (isset($_COOKIE['email'])) {
                        setcookie('email', '', time() - 3600, "/");
                    }
                }

                // Redirect based on role
                if ($userPeran == 'admin') {
                    header("Location: dashboard.php");
                    exit();
                } else {
                    header("Location: index.php");
                    exit();
                }
            } else {
                $error = 'Email atau password salah.';
            }
        } else {
            $error = 'Email atau password salah.';
        }
        $stmt->close();
    } else {
        $error = 'Silakan isi semua kolom.';
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #1e1e1e; /* Dark background */
            margin: 0;
            padding: 0;
            color: #f5f5f5; /* Light text */
        }
        .container {
            width: 30%;
            margin: auto;
            padding: 20px;
            background: #2b2b2b; /* Darker container background */
            border-radius: 8px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.5);
            margin-top: 50px;
            margin-bottom: 50px;
        }
        h2 {
            margin-top: 0;
            color: #00bcd4; /* Gold color */
            text-align: center;
        }
        .form-group {
            margin-bottom: 20px;
            position: relative;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #f5f5f5; /* Light label color */
        }
        .form-group input, .form-group select {
            width: 100%;
            padding: 10px;
            box-sizing: border-box;
            background-color: #3b3b3b; /* Dark input background */
            border: 1px solid #00bcd4; /* Gold border */
            border-radius: 4px;
            color: #f5f5f5; /* Light text */
        }
        .form-group input:focus, .form-group select:focus {
            outline: none;
            border-color: #e5a400; /* Darker gold focus border */
        }
        .form-group button {
            background-color: #00bcd4; /* Gold button */
            color: #1e1e1e; /* Dark text for contrast */
            border: none;
            padding: 10px;
            cursor: pointer;
            width: 100%;
            border-radius: 4px;
        }
        .form-group button:hover {
            background-color: #0097a7; /* Darker gold on hover */
        }
        .form-group.checkbox-group {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 20px;
        }

        .form-group.checkbox-group input[type="checkbox"] {
            margin: 0;
            width: 18px;
            height: 18px;
        }

        .form-group.checkbox-group label {
            margin: 0;
            color: #f5f5f5; /* Light text */
        }
        .error {
            color: red;
            margin-bottom: 15px;
            text-align: center;
        }
        .hidden {
            visibility: hidden;  
            height: 0;        
            overflow: hidden; 
        }
        p a {
            color: #00bcd4;
            text-decoration: none;
        }
        p a:hover {
            text-decoration: underline;
        }
        /* Style untuk mata */
        .password-wrapper {
            position: relative;
        }
        .password-wrapper input[type="password"],
        .password-wrapper input[type="text"] {
            padding-right: 40px;
        }
        .password-wrapper .toggle-password {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #f7b500; /* Gold for eye icon */
        }
    </style>
    <script>
        function togglePassword(inputId, eyeIconId) {
            const passwordInput = document.getElementById(inputId);
            const eyeIcon = document.getElementById(eyeIconId);
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                eyeIcon.innerText = '👁️‍🗨️'; // Change icon to open eye
            } else {
                passwordInput.type = 'password';
                eyeIcon.innerText = '👁️'; // Change icon to closed eye
            }
        }
    </script>
</head>
<body>
    <div class="container">
        <h2>Login</h2>
        <?php if (!empty($error)): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <form method="post" action="">
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required>
            </div>
            <div class="form-group password-wrapper">
                <label for="password">Password</label>
                <div class="password-wrapper">
                    <input type="password" id="password" name="password" required>
                    <span id="togglePassword" class="toggle-password" onclick="togglePassword('password', 'togglePassword')">👁️</span>
                </div>
            </div>

            <div class="form-group checkbox-group">
                <input type="checkbox" id="remember" name="remember" <?php if (isset($_COOKIE['email'])) echo 'checked'; ?>>
                <label for="remember">Ingat Saya</label>
            </div>
            <div class="form-group">
                <button type="submit">Login</button>
            </div>
            <p>Belum punya akun? <a href="register.php">Daftar di sini</a></p>
        </form>
    </div>
</body>
</html>
