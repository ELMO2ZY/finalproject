<?php
session_start();
require_once 'db_connect.php';
require_once 'send_email.php';

$email = '';
$step = 1;
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['step']) && $_POST['step'] == 1) {
        $email = trim($_POST['email'] ?? '');
        if (!empty($email)) {
            $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            if ($user) {
                $step = 2;
            } else {
                $message = 'If the email exists, you can reset your password.';
            }
        } else {
            $error = 'Please enter your email.';
        }
    } elseif (isset($_POST['step']) && $_POST['step'] == 2) {
        $email = trim($_POST['email'] ?? '');
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        if (empty($new_password) || empty($confirm_password)) {
            $error = 'Please fill in all fields.';
            $step = 2;
        } elseif ($new_password !== $confirm_password) {
            $error = 'Passwords do not match.';
            $step = 2;
        } else {
            $stmt = $pdo->prepare('SELECT id, password FROM users WHERE email = ?');
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            if ($user) {
                if ($user['password'] === $new_password) {
                    $error = 'New password must be different from the old password.';
                    $step = 2;
                } else {
                    $stmt = $pdo->prepare('UPDATE users SET password = ? WHERE id = ?');
                    $stmt->execute([$new_password, $user['id']]);
                    sendPasswordChangedEmail($email);
                    $message = 'Your password has been changed successfully! You may now <a href="login.php">login</a>.';
                    $step = 3;
                }
            } else {
                $message = 'If the email exists, you can reset your password.';
                $step = 3;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Forgot Password</title>
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
        .back-link { display:inline-flex;align-items:center;gap:0.5rem;color:#5e7eb6;text-decoration:none;font-weight:500;margin-bottom:1.5rem; }
    </style>
</head>
<body>
    <div class="reset-container">
        <a href="login.php" class="back-link"><i class="fas fa-arrow-left"></i> Back to Login</a>
        <h2>Forgot Password</h2>
        <?php if ($error): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <?php if ($message): ?>
            <div class="message"><?php echo $message; ?></div>
        <?php endif; ?>
        <?php if ($step === 1): ?>
        <form method="POST">
            <input type="hidden" name="step" value="1">
            <div class="form-group">
                <label for="email">Enter your email address</label>
                <input type="email" id="email" name="email" required>
            </div>
            <button type="submit" class="btn">Continue</button>
        </form>
        <?php elseif ($step === 2): ?>
        <form method="POST">
            <input type="hidden" name="step" value="2">
            <input type="hidden" name="email" value="<?php echo htmlspecialchars($email); ?>">
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