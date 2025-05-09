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


$studentId = $_SESSION['student_id']; 



// Car Provider Rating
$stmt1 = $conn->prepare("SELECT AVG(car_provider_rating) AS avg_provider_rating FROM trips WHERE Student_id = ? AND car_provider_rating IS NOT NULL");
$stmt1->bind_param("i", $studentId);
$stmt1->execute();
$result1 = $stmt1->get_result();
$row1 = $result1->fetch_assoc();
$avgProvider = $row1['avg_provider_rating'] ?? null;

// Passenger Rating
$stmt2 = $conn->prepare("SELECT AVG(rating) AS avg_passenger_rating FROM selected_passengers WHERE Passenger_Student_id = ? AND rating IS NOT NULL");
$stmt2->bind_param("i", $studentId);
$stmt2->execute();
$result2 = $stmt2->get_result();
$row2 = $result2->fetch_assoc();
$avgPassenger = $row2['avg_passenger_rating'] ?? null;

function renderStars($rating) {
    $fullStars = floor($rating);
    $halfStar = ($rating - $fullStars) >= 0.5;
    $emptyStars = 5 - $fullStars - ($halfStar ? 1 : 0);

    $stars = str_repeat('<span class="star">&#9733;</span>', $fullStars);
    if ($halfStar) {
        $stars .= '<span class="star">&#9733;</span>'; // can replace with half-star if needed
    }
    $stars .= str_repeat('<span class="star empty">&#9734;</span>', $emptyStars);
    return $stars;
}

if ($result && $result->num_rows === 1) {
    $user = $result->fetch_assoc();
} else {
    echo "User not found.";
    exit;
}

// Handle image upload
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['profile_image'])) {
    $target_dir = "uploads/";
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    
    $imageFileType = strtolower(pathinfo($_FILES["profile_image"]["name"], PATHINFO_EXTENSION));
    $target_file = $target_dir . $student_id . '.' . $imageFileType;
    
    // Check if image file is a actual image
    $check = getimagesize($_FILES["profile_image"]["tmp_name"]);
    if ($check === false) {
        $upload_error = "File is not an image.";
    }
    
    // Check file size (5MB max)
    elseif ($_FILES["profile_image"]["size"] > 5000000) {
        $upload_error = "Sorry, your file is too large (max 5MB).";
    }
    
    // Allow certain file formats
    elseif (!in_array($imageFileType, ['jpg', 'png', 'jpeg', 'gif'])) {
        $upload_error = "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
    }
    
    // If everything is ok, try to upload file
    else {
        if (move_uploaded_file($_FILES["profile_image"]["tmp_name"], $target_file)) {
            // Update database with image path
            $update_query = "UPDATE users SET profile_image = '$target_file' WHERE Student_id = '$student_id'";
            if ($conn->query($update_query)) {
                $user['profile_image'] = $target_file;
                $upload_success = "Profile image updated successfully!";
            } else {
                $upload_error = "Error updating database: " . $conn->error;
            }
        } else {
            $upload_error = "Sorry, there was an error uploading your file.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary: #4361ee;
            --secondary: #3f37c9;
            --success: #4cc9f0;
            --danger: #f72585;
            --warning: #f8961e;
            --light: #f8f9fa;
            --dark: #212529;
            --gray: #6c757d;
            --light-gray: #e9ecef;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            padding: 2rem;
            color: var(--dark);
        }

        .profile-wrapper {
            max-width: 900px;
            margin: 0 auto;
            animation: fadeIn 0.5s ease-out;
        }

        .profile-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .profile-container:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.15);
        }

        .profile-header {
            background: linear-gradient(to right, var(--primary), var(--secondary));
            color: white;
            padding: 2rem;
            text-align: center;
            position: relative;
        }

        .profile-header h1 {
            font-size: 2.2rem;
            margin-bottom: 0.5rem;
            font-weight: 600;
        }

        .profile-content {
            padding: 2rem;
            display: grid;
            grid-template-columns: 1fr 2fr;
            gap: 2rem;
        }

        @media (max-width: 768px) {
            .profile-content {
                grid-template-columns: 1fr;
            }
        }

        .profile-sidebar {
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .profile-image-container {
            position: relative;
            margin-bottom: 1.5rem;
        }

        .profile-image {
            width: 180px;
            height: 180px;
            border-radius: 50%;
            object-fit: cover;
            border: 5px solid white;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
        }

        .profile-image:hover {
            transform: scale(1.05);
        }

        .upload-form {
            width: 100%;
            margin-top: 1.5rem;
        }

        .file-input-wrapper {
            position: relative;
            margin-bottom: 1rem;
        }

        .file-input-label {
            display: block;
            padding: 0.6rem 1rem;
            background-color: var(--primary);
            color: white;
            border-radius: 8px;
            text-align: center;
            cursor: pointer;
            font-size: 0.9rem;
            transition: all 0.3s ease;
            box-shadow: 0 3px 6px rgba(0, 0, 0, 0.1);
        }

        .file-input-label:hover {
            background-color: var(--secondary);
            transform: translateY(-2px);
            box-shadow: 0 5px 10px rgba(0, 0, 0, 0.15);
        }

        .file-input {
            position: absolute;
            left: 0;
            top: 0;
            opacity: 0;
            width: 100%;
            height: 100%;
            cursor: pointer;
        }

        .file-name {
            font-size: 0.8rem;
            color: var(--gray);
            text-align: center;
            margin-top: 0.5rem;
        }

        .upload-btn {
            width: 100%;
            padding: 0.7rem;
            background-color: var(--success);
            color: white;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .upload-btn:hover {
            background-color: #3aa8d8;
            transform: translateY(-2px);
        }

        .profile-details {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 1.5rem;
        }

        .detail-card {
            background: var(--light);
            border-radius: 10px;
            padding: 1.2rem;
            box-shadow: 0 3px 6px rgba(0, 0, 0, 0.05);
            transition: transform 0.3s ease;
        }
        .detail-card-full {
            background: var(--light);
            border-radius: 10px;
            padding: 1.2rem;
            min-width: 100%;
            box-shadow: 0 3px 6px rgba(0, 0, 0, 0.05);
            transition: transform 0.3s ease;
            grid-column: 1 / -1;
        }
        .detail-card:hover,.detail-card-full:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .detail-card h3,.detail-card-full h3 {
            font-size: 0.9rem;
            color: var(--gray);
            margin-bottom: 0.5rem;
            font-weight: 500;
        }

        .detail-card p,.detail-card-full p {
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--dark);
        }

        .verification-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
            font-size: 0.9rem;
            padding: 0.3rem 0.6rem;
            border-radius: 20px;
            background-color: var(--success);
            color: white;
            margin-left: 0.5rem;
        }

        .unverified-badge {
            background-color: var(--danger);
        }

        .action-buttons {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            margin-top: 2rem;
            grid-column: 1 / -1;
        }

        .btn {
            padding: 0.8rem 1.5rem;
            border-radius: 8px;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background-color: var(--primary);
            color: white;
        }

        .btn-primary:hover {
            background-color: var(--secondary);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 5px 10px rgba(0, 0, 0, 0.1);
        }

        .btn-success {
            background-color: var(--success);
            color: white;
        }

        .btn-success:hover {
            background-color: #3aa8d8;
            transform: translateY(-2px);
        }

        .btn-warning {
            background-color: var(--warning);
            color: white;
        }

        .btn-warning:hover {
            background-color: #e07d0e;
            transform: translateY(-2px);
        }

        .btn-light {
            background-color: var(--light-gray);
            color: var(--dark);
        }

        .btn-light:hover {
            background-color: #d8dee5;
            transform: translateY(-2px);
        }

        .message {
            padding: 0.8rem;
            border-radius: 8px;
            margin: 1rem 0;
            font-weight: 500;
            text-align: center;
        }

        .success {
            background-color: rgba(76, 201, 240, 0.2);
            color: var(--success);
            border-left: 4px solid var(--success);
        }

        .error {
            background-color: rgba(247, 37, 133, 0.2);
            color: var(--danger);
            border-left: 4px solid var(--danger);
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @media (max-width: 576px) {
            .profile-header h1 {
                font-size: 1.8rem;
            }
            
            .profile-image {
                width: 140px;
                height: 140px;
            }
            
            .action-buttons {
                flex-direction: column;
            }
            
            .btn {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <div class="profile-wrapper">
        <div class="profile-container">
            <div class="profile-header">
                <h1>Your Profile</h1>
                <p>Manage your personal information and settings</p>
            </div>
            
            <div class="profile-content">
                <div class="profile-sidebar">
                    <div class="profile-image-container">
                        <?php if (!empty($user['profile_image'])): ?>
                            <img src="<?= htmlspecialchars($user['profile_image']) ?>" alt="Profile Image" class="profile-image">
                        <?php else: ?>
                            <img src="https://ui-avatars.com/api/?name=<?= urlencode($user['Name']) ?>&size=200&background=random" alt="Profile Image" class="profile-image">
                        <?php endif; ?>
                    </div>
                    
                    <form action="profile.php" method="post" enctype="multipart/form-data" class="upload-form">
                        <div class="file-input-wrapper">
                            <label for="profile_image" class="file-input-label">
                                <i class="fas fa-camera"></i> Choose Image
                            </label>
                            <input type="file" name="profile_image" id="profile_image" class="file-input" accept="image/*" required>
                            <div class="file-name" id="file-name">No file selected</div>
                        </div>
                        <button type="submit" class="upload-btn">
                            <i class="fas fa-cloud-upload-alt"></i> Upload Image
                        </button>
                    </form>
                    
                    <?php if (isset($upload_success)): ?>
                        <div class="message success"><?= $upload_success ?></div>
                    <?php elseif (isset($upload_error)): ?>
                        <div class="message error"><?= $upload_error ?></div>
                    <?php endif; ?>
                </div>
                
                <div class="profile-main">
                    <div class="profile-details">
                        <div class="detail-card">
                            <h3>Full Name</h3>
                            <p><?= htmlspecialchars($user['Name']) ?></p>
                        </div>
                        
                        <div class="detail-card">
                            <h3>Student ID</h3>
                            <p><?= htmlspecialchars($user['Student_id']) ?></p>
                        </div>
                        
                      
                        
                        <div class="detail-card">
                            <h3>Phone Number</h3>
                            <p><?= htmlspecialchars($user['Phone_number']) ?></p>
                        </div>
                        
                        <div class="detail-card">
                            <h3>Location</h3>
                            <p><?= htmlspecialchars($user['Location']) ?></p>
                        </div>
                        
                        <div class="detail-card">
                            <h3>Semester</h3>
                            <p><?= htmlspecialchars($user['Semester']) ?></p>
                        </div>
                        
                        <div class="detail-card">
                            <h3>Address</h3>
                            <p><?= htmlspecialchars($user['Address']) ?></p>
                        </div>
                        
                        <div class="detail-card">
                            <h3>Gender</h3>
                            <p><?= htmlspecialchars($user['Gender']) ?></p>
                        </div>
                        
                        <div class="detail-card">
                            <h3>Role</h3>
                            <p>
                                <?= $user['P_flag'] ? "Passenger" : "" ?>
                                <?= $user['C_flag'] ? ($user['P_flag'] ? " & Car Provider" : "Car Provider") : "" ?>
                            </p>
                        </div>
                        <div class="detail-card-full" style="max-width: 500px; margin: 30px auto; padding: 20px; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1); background-color: #f9f9f9;">
                            <h2 style="text-align: center;">Your Ratings</h2>
                            <div style="margin-top: 1rem;">
                                <h3 style="margin-bottom: 0.3rem;">As a Passenger:</h3>
                                <p><?= $avgPassenger !== null ? renderStars($avgPassenger) . " (" . number_format($avgPassenger, 2) . "/5)" : "No ratings yet" ?></p>
                            </div>
                            <div style="margin-top: 1rem;">
                                <h3 style="margin-bottom: 0.3rem;">As a Car Provider:</h3>
                                <p><?= $avgProvider !== null ? renderStars($avgProvider) . " (" . number_format($avgProvider, 2) . "/5)" : "No ratings yet" ?></p>
                            </div>
                        </div>
                        <div class="detail-card-full">
                            <h3>BRAC Mail</h3>
                            <p>
                                <?= htmlspecialchars($user['Brac_mail']) ?>
                                <span class="verification-badge <?= $user['Verification_status'] == 0 ? 'unverified-badge' : '' ?>">
                                    <?= $user['Verification_status'] == 1 ? 'Verified' : 'Unverified' ?>
                                </span>
                            </p>
                        </div>
                        
                        <div class="detail-card" style="grid-column: span 2;">
                            <h3>About Me</h3>
                            <p><?= htmlspecialchars($user['Description']) ?></p>
                        </div>
                    </div>
                    
                    <div class="action-buttons">
                        <a href="edit_profile.php" class="btn btn-primary">
                            <i class="fas fa-edit"></i> Edit Profile
                        </a>
                        <a href="home.php" class="btn btn-light">
                            <i class="fas fa-home"></i> Back to Home
                        </a>
                        <a href="cars.php" class="btn btn-success">
                            <i class="fas fa-car"></i> Car Info
                        </a>
                        
                        <?php if ($user['Verification_status'] == 0): ?>
                            <a href="otp.php" class="btn btn-warning">
                                <i class="fas fa-shield-alt"></i> Verify Account
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Show selected filename
        document.getElementById('profile_image').addEventListener('change', function(e) {
            const fileName = e.target.files[0] ? e.target.files[0].name : 'No file selected';
            document.getElementById('file-name').textContent = fileName;
        });
    </script>
</body>
</html>
