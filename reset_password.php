<?php
session_start();
require_once 'db_connect.php';
require_once 'send_email.php';

$token = $_GET['token'] ?? '';
$error = '';
$success = '';
$show_form = true;

if (!$token) {
    $error = 'Invalid or missing token.';
    $show_form = false;
} else {
    // Fetch reset request
    $stmt = $pdo->prepare('SELECT * FROM password_resets WHERE token = ? AND expires_at > NOW()');
    $stmt->execute([$token]);
    $reset = $stmt->fetch();
    if (!$reset) {
        $error = 'This reset link is invalid or has expired.';
        $show_form = false;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $show_form && $reset) {
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    if (empty($new_password) || empty($confirm_password)) {
        $error = 'Please fill in all fields.';
    } elseif ($new_password !== $confirm_password) {
        $error = 'Passwords do not match.';
    } else {
        // Check if new password is different from old
        $stmt = $pdo->prepare('SELECT password, email FROM users WHERE id = ?');
        $stmt->execute([$reset['user_id']]);
        $user = $stmt->fetch();
        if ($user && $user['password'] === $new_password) {
            $error = 'New password must be different from the old password.';
        } else {
            // Update password
            $stmt = $pdo->prepare('UPDATE users SET password = ? WHERE id = ?');
            $stmt->execute([$new_password, $reset['user_id']]);
            // Delete the reset token
            $stmt = $pdo->prepare('DELETE FROM password_resets WHERE token = ?');
            $stmt->execute([$token]);
            // Email the user
            sendPasswordChangedEmail($user['email']);
            $success = 'Your password has been changed successfully! You may now <a href="login.php">login</a>.';
            $show_form = false;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Reset Password</title>
    <link rel="stylesheet" href="theme.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .reset-container { max-width: 400px; margin: 5rem auto; background: #fff; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.08); padding: 2rem; }
        .reset-container h2 { color: #5e7eb6; margin-bottom: 1.5rem; }
        .form-group { margin-bottom: 1.5rem; }
        .form-group label { display: block; margin-bottom: 0.5rem; color: #555; font-weight: 500; }
        .form-group input { width: 100%; padding: 0.75rem; border: 1px solid #ddd; border-radius: 8px; font-size: 1rem; }
        .btn { background: #5e7eb6; color: white; border: none; padding: 0.75rem 1.5rem; border-radius: 8px; font-size: 1rem; cursor: pointer; transition: all 0.3s; }
        .btn:hover { background: #4a6da7; }
        .message { margin-bottom: 1rem; color: #219150; }
        .error { color: #ef4444; margin-bottom: 1rem; }
        .success { color: #219150; margin-bottom: 1rem; }
        .back-link { display:inline-flex;align-items:center;gap:0.5rem;color:#5e7eb6;text-decoration:none;font-weight:500;margin-bottom:1.5rem; }
    </style>
</head>
<body>
    <div class="reset-container">
        <a href="login.php" class="back-link"><i class="fas fa-arrow-left"></i> Back to Login</a>
        <h2>Reset Password</h2>
        <?php if ($error): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="success"><?php echo $success; ?></div>
        <?php endif; ?>
        <?php if ($show_form): ?>
        <form method="POST">
            <div class="form-group">
                <label for="new_password">New Password</label>
                <input type="password" id="new_password" name="new_password" required>
            </div>
            <div class="form-group">
                <label for="confirm_password">Confirm New Password</label>
                <input type="password" id="confirm_password" name="confirm_password" required>
            </div>
            <button type="submit" class="btn">Change Password</button>
        </form>
        <?php endif; ?>
    </div>
</body>
</html> 