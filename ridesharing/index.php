<?php
require_once('DBconnect.php');
session_start();
$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $brac_mail = trim($_POST['brac_mail']);
    $password = $_POST['password'];

    // Admin shortcut login (check this before anything else)
    if ($brac_mail === 'admin@bracu.ac.bd' && $password === 'admin') {
        $_SESSION['admin'] = true;
        header("Location: dashboard.php");
        exit;
    }

    // Only validate if not admin
    if (!preg_match('/@((g\.)?bracu\.ac\.bd)$/', $brac_mail)) {
        $message = "❌ Invalid BRAC email address.";
    } else {
        $brac_mail_safe = $conn->real_escape_string($brac_mail);
        $result = $conn->query("SELECT * FROM users WHERE Brac_mail='$brac_mail_safe' LIMIT 1");

        if ($result && $result->num_rows == 1) {
            $user = $result->fetch_assoc();

            if (password_verify($password, $user['Passwords'])) {
                $_SESSION['student_id'] = $user['Student_id'];
                $_SESSION['name'] = $user['Name'];
                $_SESSION['brac_mail'] = $user['Brac_mail'];
                $_SESSION['P_flag'] = $user['P_flag'];
                $_SESSION['C_flag'] = $user['C_flag'];

                header("Location: home.php");
                exit;
            } else {
                $message = "❌ Incorrect password.";
            }
        } else {
            $message = "❌ No account found with this email.";
        }
    }
}
   

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <link rel="stylesheet" href="css/style.css" />
    <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }
    </style>
</head>
<body>

<h1>Login</h1>

<div class="form-container">
    <form method="post" action="">
        <div>
            <label>BRAC Mail:</label>
            <input type="email" name="brac_mail" required>
        </div>

        <div>
            <label>Password:</label>
            <input type="password" name="password" required>
        </div>

        <button type="submit" class="submit-btn">🔒 Login</button>
    </form>

    <a href="register.php" class="back-link">Don't have an account? Register</a>
</div>



<?php if ($message): ?>
    <script>alert("<?= addslashes($message) ?>");</script>
<?php endif; ?>

</body>
</html>
