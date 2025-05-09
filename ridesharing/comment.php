<?php
require_once('DBconnect.php');
session_start();
$message = "";
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    unset($_SESSION['message']);
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['receiver_id'], $_POST['comment'], $_POST['f_type'])) {
    $provider_id = $_SESSION['student_id'];
    $receiver_id = $_POST['receiver_id'];
    $comment = $_POST['comment'];
    $f_type = $_POST['f_type'];

    // Check if user is trying to give feedback to themselves
    if ($provider_id == $receiver_id) {
        $message = "❌ You cannot give feedback to yourself.";
    } else {
        // Check if users have shared a completed ride
        $check_ride_sql = "SELECT COUNT(*) as ride_count
                          FROM trips t
                          JOIN selected_passengers sp ON t.Trip_id = sp.Trip_ID
                          WHERE 
                              ((t.Student_id = ? AND sp.Passenger_Student_id = ?)
                              OR
                              (t.Student_id = ? AND sp.Passenger_Student_id = ?))
                              AND t.Is_completed = 1";
        
        $check_stmt = $conn->prepare($check_ride_sql);
        $check_stmt->bind_param("iiii", $provider_id, $receiver_id, $receiver_id, $provider_id);
        $check_stmt->execute();
        $result = $check_stmt->get_result();
        $row = $result->fetch_assoc();
        $check_stmt->close();
        
        if ($row['ride_count'] > 0) {
            // Users have shared a ride, proceed with feedback
            $stmt = $conn->prepare("INSERT INTO feedback (Provider_student_id, Receiver_student_id, F_Comments, F_Type) VALUES (?, ?, ?, ?)");
            if ($stmt) {
                $stmt->bind_param("iiss", $provider_id, $receiver_id, $comment, $f_type);
                if ($stmt->execute()) {
                    $_SESSION['message'] = "✅ $f_type submitted successfully.";
                    header("Location: " . $_SERVER['PHP_SELF']);
                    exit;
                } else {
                    $message = "❌ Failed to submit $f_type.";
                }
                $stmt->close();
            } else {
                $message = "❌ Prepare failed: " . $conn->error;
            }
        } else {
            $message = "❌ You can only give feedback to users you've shared a completed ride with.";
        }
    }
}

$users = [];
$current_user_id = $_SESSION['student_id'];
// Exclude current user from the receiver list
$user_query = $conn->query("SELECT Student_id, Name FROM users WHERE Student_id != '$current_user_id'");
if ($user_query && $user_query->num_rows > 0) {
    while ($user = $user_query->fetch_assoc()) {
        $users[] = $user;
    }
}

$provider_id = $_SESSION['student_id'];
$comments = [];
$sql = "SELECT f.*, 
               u1.Name AS provider_name, 
               u2.Name AS receiver_name 
        FROM feedback f 
        INNER JOIN users u1 ON f.Provider_student_id = u1.Student_id 
        INNER JOIN users u2 ON f.Receiver_student_id = u2.Student_id 
        WHERE u1.Student_id='$provider_id' OR u2.student_id='$provider_id'
        ORDER BY f.Feedback_id DESC";

$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $comments[] = $row;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Feedback System</title>
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

        .container {
            max-width: 1000px;
            margin: 0 auto;
            animation: fadeIn 0.5s ease-out;
        }

        .feedback-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .feedback-header h1 {
            font-size: 2.5rem;
            color: var(--primary);
            margin-bottom: 0.5rem;
            font-weight: 700;
        }

        .feedback-header p {
            color: var(--gray);
            font-size: 1.1rem;
        }

        .feedback-form {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            margin-bottom: 3rem;
            transition: transform 0.3s ease;
        }

        .feedback-form:hover {
            transform: translateY(-5px);
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: var(--dark);
        }

        .form-control {
            width: 100%;
            padding: 0.8rem 1rem;
            font-size: 1rem;
            border: 2px solid var(--light-gray);
            border-radius: 10px;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.2);
            outline: none;
        }

        textarea.form-control {
            min-height: 120px;
            resize: vertical;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.8rem 1.5rem;
            font-size: 1rem;
            font-weight: 600;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            border: none;
            gap: 0.5rem;
        }

        .btn-primary {
            background: linear-gradient(to right, var(--primary), var(--secondary));
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(67, 97, 238, 0.3);
        }

        .btn-light {
            background-color: var(--light-gray);
            color: var(--dark);
        }

        .btn-light:hover {
            background-color: #d8dee5;
            transform: translateY(-2px);
        }

        .action-buttons {
            display: flex;
            gap: 1rem;
            margin-top: 1.5rem;
        }

        .message {
            padding: 1rem;
            border-radius: 10px;
            margin: 1rem 0;
            font-weight: 500;
            text-align: center;
            animation: slideDown 0.3s ease-out;
        }

        .message.success {
            background-color: rgba(76, 201, 240, 0.2);
            color: var(--success);
            border-left: 4px solid var(--success);
        }

        .message.error {
            background-color: rgba(247, 37, 133, 0.2);
            color: var(--danger);
            border-left: 4px solid var(--danger);
        }

        .comments-section {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        .section-title {
            font-size: 1.8rem;
            color: var(--primary);
            margin-bottom: 1.5rem;
            text-align: center;
        }

        .comment-list {
            display: grid;
            gap: 1.5rem;
        }

        .comment-card {
            background: var(--light);
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            transition: transform 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .comment-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
        }

        .comment-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
            flex-wrap: wrap;
            gap: 0.5rem;
        }

        .comment-author {
            font-weight: 700;
            color: var(--primary);
            font-size: 1.1rem;
        }

        .comment-type {
            padding: 0.3rem 0.8rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: capitalize;
        }

        .type-compliment {
            background-color: rgba(76, 201, 240, 0.2);
            color: var(--success);
        }

        .type-complaint {
            background-color: rgba(247, 37, 133, 0.2);
            color: var(--danger);
        }

        .comment-arrow {
            color: var(--gray);
            margin: 0 0.5rem;
        }

        .comment-content {
            color: var(--dark);
            line-height: 1.6;
        }

        .empty-state {
            text-align: center;
            padding: 2rem;
            color: var(--gray);
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @media (max-width: 768px) {
            .feedback-header h1 {
                font-size: 2rem;
            }
            
            .feedback-form, .comments-section {
                padding: 1.5rem;
            }
            
            .action-buttons {
                flex-direction: column;
            }
            
            .btn {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <header class="feedback-header">
            <h1>Feedback System</h1>
            <p>Share your thoughts and experiences with other users</p>
        </header>

        <?php if ($message): ?>
            <div class="message <?= strpos($message, '❌') !== false ? 'error' : 'success' ?>">
                <?= $message ?>
            </div>
        <?php endif; ?>

        <section class="feedback-form">
            <h2>Submit New Feedback</h2>
            <form method="post">
                <div class="form-group">
                    <label for="receiver_id"><i class="fas fa-user"></i> Receiver</label>
                    <select name="receiver_id" class="form-control" required>
                        <option value="" disabled selected>Select a user</option>
                        <?php if (!empty($users)): ?>
                            <?php foreach ($users as $user): ?>
                                <option value="<?= $user['Student_id'] ?>">
                                    <?= htmlspecialchars($user['Student_id']) ?> - <?= htmlspecialchars($user['Name']) ?>
                                </option>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <option value="" disabled>No other users available</option>
                        <?php endif; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="f_type"><i class="fas fa-tag"></i> Feedback Type</label>
                    <select name="f_type" class="form-control" required>
                        <option value="compliment">Compliment</option>
                        <option value="complaint">Complaint</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="comment"><i class="fas fa-comment"></i> Your Feedback</label>
                    <textarea name="comment" class="form-control" required></textarea>
                </div>

                <div class="action-buttons">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-paper-plane"></i> Submit Feedback
                    </button>
                    <a href="home.php" class="btn btn-light">
                        <i class="fas fa-home"></i> Back to Home
                    </a>
                </div>
            </form>
        </section>

        <section class="comments-section">
            <h2 class="section-title">Feedback History</h2>
            
            <div class="comment-list">
                <?php if (!empty($comments)): ?>
                    <?php foreach ($comments as $row): ?>
                        <div class="comment-card">
                            <div class="comment-header">
                                <span class="comment-author">
                                    <?= htmlspecialchars($row['provider_name']) ?>
                                    <span class="comment-arrow"><i class="fas fa-arrow-right"></i></span>
                                    <?= htmlspecialchars($row['receiver_name']) ?>
                                </span>
                                <span class="comment-type type-<?= htmlspecialchars($row['F_type']) ?>">
                                    <?= htmlspecialchars($row['F_type']) ?>
                                </span>
                            </div>
                            <div class="comment-content">
                                <?= htmlspecialchars($row['F_comments']) ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-comment-slash" style="font-size: 3rem; margin-bottom: 1rem;"></i>
                        <p>No feedback submitted yet.</p>
                    </div>
                <?php endif; ?>
            </div>
        </section>
    </div>
</body>
</html>