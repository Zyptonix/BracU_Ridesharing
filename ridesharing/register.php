<?php
require_once('DBconnect.php');

$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_id = $conn->real_escape_string($_POST['student_id']);
    $name = $conn->real_escape_string($_POST['name']);
    $password = $_POST['password'];
    $location = $conn->real_escape_string($_POST['location']);
    $semester = $conn->real_escape_string($_POST['semester']);
    $brac_mail = $conn->real_escape_string($_POST['brac_mail']);
    $phone = $conn->real_escape_string($_POST['phone_number']);
    $address = $conn->real_escape_string($_POST['address']);
    $p_flag = isset($_POST['p_flag']) ? 1 : 0;
    $c_flag = isset($_POST['c_flag']) ? 1 : 0;
    $gender = $conn->real_escape_string($_POST['gender']);

    // Check if valid BRAC mail
    if (!preg_match('/@((g\.)?bracu\.ac\.bd)$/', $brac_mail)) {
        $message = "❌ Please use an official BRAC email address.";
    } else {
        // Check if student_id already exists
        $check_id = $conn->query("SELECT * FROM users WHERE Student_id='$student_id' LIMIT 1");

        if ($check_id && $check_id->num_rows > 0) {
            $message = "❌ Student ID already registered.";
        } else {
            // Hash the password
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);

            $insert = $conn->query("INSERT INTO users (Student_id, Name, Passwords, Location, Semester, Brac_mail, Phone_number, Address, P_flag, C_flag, Gender)
                                    VALUES ('$student_id', '$name', '$hashed_password', '$location', '$semester', '$brac_mail', '$phone', '$address', '$p_flag', '$c_flag', '$gender')");

            if ($insert) {
                // Redirect to login page (index.php) after successful registration
                header("Location: index.php");
                exit(); // Make sure the script stops here after redirection
            } else {
                $message = "❌ Error: " . $conn->error;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register</title>
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

<h1 style="text-align:center;">Register</h1>

<div class="form-container">
    <form method="post" action="">
        <label>Student ID:</label>
        <input type="text" name="student_id" required>

        <label>Name:</label>
        <input type="text" name="name" required>

        <label for="password">Password:</label>
	<input type="password" name="password" id="password" 
       		pattern="^(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z\d]).{8,}$" 
       		title="Password must be at least 8 characters long and include one uppercase letter, one number, and one special character." 
       		required>

        <label>Location:</label>
        <input type="text" name="location">

        <label>Semester:</label>
        <input type="text" name="semester">
	
	<label>Gender:</label>
            <select name="gender" required>
                <option value="Male">Male</option>
                <option value="Female">Female</option>
            </select>

        <label>BRAC Mail:</label>
        <input type="email" name="brac_mail" required>

        <label>Phone Number:</label>
        <input type="text" name="phone_number">

        <label>Address:</label>
        <input type="text" name="address">

        <label>Are you a Passenger?</label>
        <input type="checkbox" name="p_flag">

        <label>Are you a Car Provider?</label>
        <input type="checkbox" name="c_flag">


        <button type="submit" class="submit-btn">➕ Register</button>
    </form>

    <a href="index.php" class="back-link">Already have an account? Login</a>
</div>

<?php if ($message): ?>
    <script>alert("<?= addslashes($message) ?>");</script>
<?php endif; ?>

</body>
</html>