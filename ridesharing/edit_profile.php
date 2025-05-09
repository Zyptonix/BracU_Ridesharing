<?php
session_start();
require_once('DBconnect.php');

if (isset($_SESSION['selected_user_id'])) {
    $student_id = $_SESSION['selected_user_id'];}
    
else if (!isset($_SESSION['student_id'])) {
    header("Location: index.php");
    exit;
}
else{$student_id = $_SESSION['student_id'];}
    
$query = "SELECT * FROM users WHERE Student_id = '$student_id'";
$result = $conn->query($query);

if ($result && $result->num_rows === 1) {
    $user = $result->fetch_assoc();
} else {
    echo "User not found.";
    exit;
}

$message = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $conn->real_escape_string($_POST['name']);
    $location = $conn->real_escape_string($_POST['location']);
    $semester = $conn->real_escape_string($_POST['semester']);
    $phone = $conn->real_escape_string($_POST['phone_number']);
    $address = $conn->real_escape_string($_POST['address']);
    $gender = $conn->real_escape_string($_POST['gender']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);

    // Update query
    $update_query = "UPDATE users SET Name='$name', Location='$location', Semester='$semester', Phone_number='$phone', Address='$address', Gender='$gender', Description='$description' WHERE Student_id='$student_id'";

    if ($conn->query($update_query)) {
        $message = "✅ Profile updated successfully!";
    } else {
        $message = "❌ Error updating profile: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Profile</title>
    <link rel="stylesheet" href="css/style.css" />
    <style>
     * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }
        h3{
            color: #0077b6;
            margin-bottom: 20px;
            margin-top: 5px;
            font-weight: bold;

        }


        label {
            display: block;
            margin-bottom: 7px;
            font-weight: bold;
        }

        input[type="text"],
        select,
        textarea {
            width: 100%;
            padding: 10px;
            border-radius: 8px;
            border: 1px solid #ccc;
            box-sizing: border-box;
            font-size: 16px;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .form-group {
            width: calc(50% - 10px);
        }

        .form-group-full {
            width: 100%;
        }

        .submit-btn {
            background-color: #007bff;
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            width: 100%;
            margin-top: 10px;
        }

        .submit-btn:hover {
            background-color: #0056b3;
        }

        .message {
            text-align: center;
            color: green;
            font-weight: bold;
            margin-top: 15px;
        }

        .back-link {
            display: inline-block;
            margin-top: 20px;
            text-decoration: none;
            color: #007bff;
            margin-right: 15px;
        }

        .back-link:hover {
            text-decoration: underline;
        }

        .form-group {
            flex: 1;
            min-width: 0;
        }

        .form-group-full {
            width: 100%;
        }

        @media (max-width: 768px) {
            .form-group {
                width: 100%;
            }
        }

        .back-link {
            display: inline-block;
            margin: 40px 10px 0 0;
            padding: 12px 24px;
            font-size: 1rem;
            font-weight: bold;
            text-decoration: none;
            border-radius: 12px;
            background-color: #007bff;
            color: white;
            transition: background 0.3s ease, transform 0.2s ease;
        }

        .back-link:hover {
            background-color: #0056b3;
            transform: translateY(-2px);
            color: white;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <h1>Edit Profile</h1>
  
    <div class="form-container">
    <h3> STUDENT ID: <?php echo($student_id)?></h3>
        <form method="post" action="">
            <div style="display: flex; gap: 20px;">
                <div class="form-group">
                    <label>Name:</label>
                    <input type="text" name="name" value="<?= htmlspecialchars($user['Name']) ?>" required>
                </div>

                <div class="form-group">
                    <label>Gender:</label>
                    <select name="gender" required>
                        <option value="Male" <?= $user['Gender'] == 'Male' ? 'selected' : '' ?>>Male</option>
                        <option value="Female" <?= $user['Gender'] == 'Female' ? 'selected' : '' ?>>Female</option>
                    </select>
                </div>

            </div>

            <div style="display: flex; gap: 20px;">
                <div class="form-group">
                    <label>Semester:</label>
                    <input type="text" name="semester" value="<?= htmlspecialchars($user['Semester']) ?>" required>
                </div>

                <div class="form-group">
                    <label>Phone Number:</label>
                    <input type="text" name="phone_number" value="<?= htmlspecialchars($user['Phone_number']) ?>" required>
                </div>
            </div>

            <div class="form-group-full">
                <label>Address:</label>
                <input type="text" name="address" value="<?= htmlspecialchars($user['Address']) ?>" required>
            </div>


            
            <div class="form-group-full">
                    <label>Location:</label>
                    <input type="text" name="location" value="<?= htmlspecialchars($user['Location']) ?>" required>
                </div>

            <div class="form-group-full">
                <label for="description">Description:</label>
                <textarea id="description" name="description" rows="4" placeholder="Enter your description here"><?= htmlspecialchars($user['Description']) ?></textarea>
            </div>

            


            <div class="form-group-full">
                <button type="submit" class="submit-btn">Save Changes</button>
            </div>
        </form>
    </div>

    <?php if ($message): ?>
        <p class="message"><?= $message ?></p>
    <?php endif; ?>

    <!-- Added "Back to Dashboard" button -->
    <a href="home.php" class="back-link">🔙 Back to Home</a>
    
    <a href="profile.php" class="back-link">🔙 Back to Profile</a>
</body>
</html>
