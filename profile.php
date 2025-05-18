<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

require_once 'db_connect.php';

// Fetch user data
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

// Handle profile update
$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $first_name = $_POST['first_name'] ?? '';
    $email = $_POST['email'] ?? '';
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    try {
        // Update basic info
        if (!empty($first_name) && !empty($email)) {
            $stmt = $pdo->prepare("UPDATE users SET first_name = ?, email = ? WHERE id = ?");
            $stmt->execute([$first_name, $email, $_SESSION['user_id']]);
            $_SESSION['user_name'] = $first_name;
            $success_message = "Profile updated successfully!";
        }

        // Update password if provided
        if (!empty($current_password) && !empty($new_password)) {
            if ($new_password === $confirm_password) {
                // Verify current password
                $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
                $stmt->execute([$_SESSION['user_id']]);
                $current_hash = $stmt->fetchColumn();

                if ($current_password === $current_hash) {
                    $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
                    $stmt->execute([$new_password, $_SESSION['user_id']]);
                    $success_message = "Profile and password updated successfully!";
                } else {
                    $error_message = "Current password is incorrect";
                }
            } else {
                $error_message = "New passwords don't match";
            }
        }
    } catch(PDOException $e) {
        $error_message = "Update failed. Please try again.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - Support Ticket System</title>
    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="STStr.svg">
    <link rel="icon" type="image/png" href="STStr.png">
    <link rel="apple-touch-icon" href="STStr.png">
    <link rel="stylesheet" href="theme.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: #f5f7fa;
            min-height: 100vh;
        }

        /* Top Bar Styles */
        .top-bar {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            height: 60px;
            background: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 2rem;
            z-index: 1000;
        }

        .user-profile {
            display: flex;
            align-items: center;
            gap: 1rem;
            cursor: pointer;
            position: relative;
        }

        .user-circle {
            width: 40px;
            height: 40px;
            background: #5e7eb6;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
        }

        .user-dropdown {
            position: absolute;
            top: 100%;
            right: 0;
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            padding: 0.5rem;
            display: none;
            min-width: 150px;
        }

        .user-dropdown.show {
            display: block;
        }

        .dropdown-item {
            padding: 0.75rem 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: #333;
            text-decoration: none;
            transition: background 0.3s;
            border-radius: 4px;
        }

        .dropdown-item:hover {
            background: #f5f7fa;
        }

        /* Main Content Styles */
        .main-content {
            margin-left: 0;
            margin-top: 60px;
            padding: 2rem;
            min-height: calc(100vh - 60px);
        }

        .back-button {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            color: #5e7eb6;
            text-decoration: none;
            font-weight: 500;
            margin-bottom: 1.5rem;
            transition: all 0.3s;
        }

        .back-button:hover {
            color: #4a6da7;
            transform: translateX(-5px);
        }

        .back-button i {
            font-size: 1.2rem;
        }

        .profile-container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 2rem;
            max-width: 800px;
            margin: 0 auto;
        }

        .profile-header {
            display: flex;
            align-items: center;
            gap: 2rem;
            margin-bottom: 2rem;
            padding-bottom: 2rem;
            border-bottom: 1px solid #eee;
        }

        .profile-avatar {
            width: 100px;
            height: 100px;
            background: #5e7eb6;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 2.5rem;
            font-weight: 600;
        }

        .profile-info h1 {
            font-size: 1.8rem;
            color: #333;
            margin-bottom: 0.5rem;
        }

        .profile-info p {
            color: #666;
        }

        .form-section {
            margin-bottom: 2rem;
        }

        .form-section h2 {
            font-size: 1.4rem;
            color: #333;
            margin-bottom: 1.5rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: #555;
            font-weight: 500;
        }

        .form-group input {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.3s;
        }

        .form-group input:focus {
            outline: none;
            border-color: #5e7eb6;
        }

        .btn {
            background: #5e7eb6;
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s;
        }

        .btn:hover {
            background: #4a6da7;
            transform: translateY(-2px);
        }

        .message {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
        }

        .success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
    </style>
</head>
<body>
    <!-- Top Bar -->
    <div class="top-bar">
        <div class="logo">
            <img src="STStr.svg" alt="Logo" height="40">
        </div>
        <div class="user-profile" id="userProfile">
            <div class="user-circle">
                <?php echo strtoupper(substr($_SESSION['user_name'], 0, 1)); ?>
            </div>
            <span><?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
            <div class="user-dropdown" id="userDropdown">
                <a href="logout.php" class="dropdown-item">
                    <i class="fas fa-sign-out-alt"></i>
                    Logout
                </a>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <a href="home.php" class="back-button">
            <i class="fas fa-arrow-left"></i>
            Back to Dashboard
        </a>
        <div class="profile-container">
            <?php if ($success_message): ?>
                <div class="message success">
                    <?php echo htmlspecialchars($success_message); ?>
                </div>
            <?php endif; ?>

            <?php if ($error_message): ?>
                <div class="message error">
                    <?php echo htmlspecialchars($error_message); ?>
                </div>
            <?php endif; ?>

            <div class="profile-header">
                <div class="profile-avatar">
                    <?php echo strtoupper(substr($user['first_name'], 0, 1)); ?>
                </div>
                <div class="profile-info">
                    <h1><?php echo htmlspecialchars($user['first_name']); ?></h1>
                    <p><?php echo htmlspecialchars($user['email']); ?></p>
                </div>
            </div>

            <form method="POST" action="">
                <div class="form-section">
                    <h2>Basic Information</h2>
                    <div class="form-group">
                        <label for="first_name">First Name</label>
                        <input type="text" id="first_name" name="first_name" value="<?php echo htmlspecialchars($user['first_name']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" readonly style="background:#f1f5f9; color:#888; cursor:not-allowed;">
                    </div>
                </div>

                <div class="form-section">
                    <h2>Change Password</h2>
                    <div class="form-group">
                        <label for="current_password">Current Password</label>
                        <input type="password" id="current_password" name="current_password">
                    </div>
                    <div class="form-group">
                        <label for="new_password">New Password</label>
                        <input type="password" id="new_password" name="new_password">
                    </div>
                    <div class="form-group">
                        <label for="confirm_password">Confirm New Password</label>
                        <input type="password" id="confirm_password" name="confirm_password">
                    </div>
                </div>

                <button type="submit" class="btn">Save Changes</button>
            </form>
        </div>
    </div>

    <script>
        // User dropdown toggle
        const userProfile = document.getElementById('userProfile');
        const userDropdown = document.getElementById('userDropdown');

        userProfile.addEventListener('click', () => {
            userDropdown.classList.toggle('show');
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', (e) => {
            if (!userProfile.contains(e.target)) {
                userDropdown.classList.remove('show');
            }
        });
    </script>
</body>
</html> 