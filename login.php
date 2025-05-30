<?php
session_start();

if (isset($_SESSION['user_id'])) {
    header("Location: home.php");
    exit();
}

require_once 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['register'])) {
        // Handle registration
        $first_name = $_POST['first_name'] ?? '';
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        
        if ($password !== $confirm_password) {
            $error = "Passwords don't match";
        } else {
            // Store password as plain text
            try {
                $stmt = $pdo->prepare("INSERT INTO users (first_name, email, password) VALUES (?, ?, ?)");
                $stmt->execute([$first_name, $email, $password]);
                $success = "Registration successful! Please login.";
            } catch(PDOException $e) {
                if ($e->getCode() == 23000) { // Duplicate entry error
                    $error = "Email already exists";
                } else {
                    $error = "Registration failed. Please try again.";
                }
            }
        }
    } else {
        // Handle login with plain text password
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        $user_type = $_POST['user_type'] ?? 'customer';
        
        error_log("Login attempt - Email: $email, Selected type: $user_type");
        
        try {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND password = ?");
            $stmt->execute([$email, $password]);
            
            if ($stmt->rowCount() == 1) {
                $user = $stmt->fetch();
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['first_name'];
                $_SESSION['user_type'] = $user['user_type'];
                
                // Debug: Show user_type after login
                echo '<div style="position:fixed;top:10px;left:10px;z-index:9999;background:#fff;padding:10px;border:1px solid #ccc;">Debug: user_type = ' . htmlspecialchars($_SESSION['user_type']) . '</div>';
                
                error_log("Session set - User ID: " . $_SESSION['user_id']);
                error_log("Session set - User type: " . $_SESSION['user_type']);
                
                if ($user_type === 'admin') {
                    if ($user['user_type'] === 'admin') {
                        error_log("Redirecting to admin dashboard");
                        header("Location: admin_dashboard.php");
                        exit();
                    } else {
                        $error = "You do not have admin privileges.";
                    }
                } else {
                    error_log("Redirecting to home page");
                    header("Location: home.php");
                    exit();
                }
            } else {
                error_log("Invalid login attempt for email: $email");
                $error = "Invalid email or password";
            }
        } catch(PDOException $e) {
            error_log("Login error: " . $e->getMessage());
            $error = "Login failed. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Support Ticket System</title>
    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="STStr.svg">
    <link rel="icon" type="image/png" href="STStr.png">
    <link rel="apple-touch-icon" href="STStr.png">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }
        
        body {
            background-color: #f5f7fa;
            margin: 0;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            font-family: Arial, sans-serif;
            position: relative;
            overflow: hidden;
        }
        
        .container {
            display: flex;
            width: 520px;
            height: 500px;
            max-width: 99%;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            z-index: 1;
        }
        
        .left {
            width: 66%;
            height: 100%;
            position: relative;
            overflow: hidden;
        }
        
        .form {
            display: flex;
            flex-direction: column;
            justify-content: center;
            height: 100%;
            width: 100%;
            left: 0;
            backdrop-filter: blur(20px);
            position: relative;
            transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .register-form {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            transform: translateX(100%);
            transition: transform 0.5s cubic-bezier(0.4, 0, 0.2, 1);
            padding-top: 20px;
            opacity: 0;
            visibility: hidden;
        }
        
        .show-register .register-form {
            transform: translateX(0);
            opacity: 1;
            visibility: visible;
        }
        
        .show-register .login-form {
            transform: translateX(-100%);
            opacity: 0;
            visibility: hidden;
        }
        
        .login-form {
            opacity: 1;
            visibility: visible;
            transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .form::before {
            position: absolute;
            content: "";
            width: 40%;
            height: 40%;
            right: 1%;
            z-index: -1;
            background: radial-gradient(
                circle,
                rgb(194, 13, 170) 20%,
                rgb(26, 186, 235) 60%,
                rgb(26, 186, 235) 100%
            );
            filter: blur(70px);
            border-radius: 50%;
        }
        
        .right {
            width: 34%;
            height: 100%;
        }
        
        .img {
            width: 100%;
            height: 100%;
        }
        
        .container::after {
            position: absolute;
            content: "";
            width: 80%;
            height: 80%;
            right: -40%;
            background: rgb(157, 173, 203);
            background: radial-gradient(
                circle,
                rgba(157, 173, 203, 1) 61%,
                rgba(99, 122, 159, 1) 100%
            );
            border-radius: 50%;
            z-index: -1;
        }
        
        .input, button, select {
            background: rgba(253, 253, 253, 0);
            outline: none;
            border: 1px solid rgba(255, 0, 0, 0);
            border-radius: 0.5rem;
            padding: 10px;
            margin: 10px auto;
            width: 80%;
            display: block;
            color: #425981;
            font-weight: 500;
            font-size: 1.1em;
        }
        
        .input-block {
            position: relative;
            opacity: 0;
            transform: translateY(20px);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .form.active .input-block {
            opacity: 1;
            transform: translateY(0);
        }
        
        .input-block:nth-child(1) { transition-delay: 0.1s; }
        .input-block:nth-child(2) { transition-delay: 0.2s; }
        .input-block:nth-child(3) { transition-delay: 0.3s; }
        .input-block:nth-child(4) { transition-delay: 0.4s; }
        .input-block:nth-child(5) { transition-delay: 0.5s; }
        .input-block:nth-child(6) { transition-delay: 0.6s; }
        
        label {
            position: absolute;
            left: 15%;
            top: 37%;
            pointer-events: none;
            color: gray;
        }
        
        .forgot {
            display: block;
            margin: 5px 0 10px 0;
            margin-left: 35px;
            color: #5e7eb6;
            font-size: 0.9em;
        }
        
        .input:focus + label,
        .input:valid + label,
        select:focus + label,
        select:valid + label {
            transform: translateY(-120%) scale(0.9);
            transition: all 0.4s;
        }
        
        button {
            background-color: #5e7eb6;
            color: white;
            font-size: medium;
            box-shadow: 2px 4px 8px rgba(70, 70, 70, 0.178);
            cursor: pointer;
        }
        
        a {
            color: #5e7eb6;
            text-decoration: none;
        }
        
        .input {
            box-shadow: inset 4px 4px 4px rgba(165, 163, 163, 0.315),
                4px 4px 4px rgba(218, 218, 218, 0.13);
        }
        
        .switch-form {
            text-align: center;
            margin-top: 15px;
            font-size: 0.9em;
        }
        
        .success {
            color: #2a9d8f;
            font-size: 14px;
            text-align: center;
            margin-top: 10px;
        }
        
        .error {
            background: #ffeaea;
            color: #d90429;
            border: 1.5px solid #ffb3b3;
            padding: 14px 18px;
            border-radius: 8px;
            font-size: 1.05em;
            margin: 18px auto 0 auto;
            width: 80%;
            box-shadow: 0 2px 8px rgba(217,4,41,0.07);
            display: flex;
            align-items: center;
            gap: 10px;
            animation: shake 0.3s;
        }
        .error i {
            color: #d90429;
            font-size: 1.3em;
        }
        @keyframes shake {
            0% { transform: translateX(0); }
            20% { transform: translateX(-5px); }
            40% { transform: translateX(5px); }
            60% { transform: translateX(-5px); }
            80% { transform: translateX(5px); }
            100% { transform: translateX(0); }
        }
        
        .user-type-container {
            display: flex;
            justify-content: center;
            gap: 2rem;
            margin: 10px 0 0 0;
        }
        .user-type-option {
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .user-type-option input[type="radio"] {
            accent-color: #5e7eb6;
            width: 18px;
            height: 18px;
        }
        .user-type-option label {
            font-size: 1em;
            color: #425981;
            cursor: pointer;
            margin-bottom: 0;
            position: static;
            transform: none;
            pointer-events: auto;
        }
    </style>
</head>
<body>
    <div class="container <?php echo (isset($_POST['register'])) ? 'show-register' : ''; ?>">
        <div class="left">
            <!-- Login Form -->
            <form class="form login-form <?php echo (!isset($_POST['register']) ) ? 'active' : ''; ?>" method="POST">
                <div class="input-block">
                    <input class="input" type="email" id="login-email" name="email" required>
                    <label for="login-email">Email</label>
                </div>
                <div class="input-block">
                    <input class="input" type="password" id="login-password" name="password" required>
                    <label for="login-password">Password</label>
                </div>
                <div class="input-block">
                    <div class="user-type-container">
                        <div class="user-type-option">
                            <input type="radio" id="customer" name="user_type" value="customer" checked>
                            <label for="customer">Customer</label>
                        </div>
                        <div class="user-type-option">
                            <input type="radio" id="admin" name="user_type" value="admin">
                            <label for="admin">Admin</label>
                        </div>
                    </div>
                    <button type="submit">Login</button>
                    <p class="switch-form">Don't have an account? <a href="#" class="switch-btn">Register</a></p>
                </div>
                <?php if (isset($error) && !isset($_POST['register'])): ?>
                    <p class="error"><i class="fas fa-exclamation-circle"></i> <?php echo $error; ?></p>
                <?php endif; ?>
                <?php if (isset($success)): ?>
                    <p class="success"><?php echo $success; ?></p>
                <?php endif; ?>
            </form>

            <!-- Register Form -->
            <form class="form register-form <?php echo (isset($_POST['register']) || isset($error)) ? 'active' : ''; ?>" method="POST">
                <input type="hidden" name="register" value="1">
                <div class="input-block">
                    <input class="input" type="text" id="first_name" name="first_name" required value="<?php echo htmlspecialchars($_POST['first_name'] ?? ''); ?>">
                    <label for="first_name">First Name</label>
                </div>
                <div class="input-block">
                    <input class="input" type="email" id="email" name="email" required value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                    <label for="email">Email</label>
                </div>
                <div class="input-block">
                    <input class="input" type="password" id="password" name="password" required>
                    <label for="password">Password</label>
                </div>
                <div class="input-block">
                    <input class="input" type="password" id="confirm_password" name="confirm_password" required>
                    <label for="confirm_password">Confirm Password</label>
                </div>
                <div class="input-block">
                    <button type="submit">Register</button>
                    <p class="switch-form">Already have an account? <a href="#" class="switch-btn">Login</a></p>
                </div>
                <?php if (isset($error) && isset($_POST['register'])): ?>
                    <p class="error"><?php echo $error; ?></p>
                <?php endif; ?>
                <?php if (isset($success)): ?>
                    <p class="success"><?php echo $success; ?></p>
                <?php endif; ?>
            </form>
        </div>
        <div class="right">
            <div class="img">
                <svg xmlns="http://www.w3.org/2000/svg" width="100%" height="100%" viewBox="0 0 731.67004 550.61784" xmlns:xlink="http://www.w3.org/1999/xlink">
                    <path d="M0,334.13393c0,.66003,.53003,1.19,1.19006,1.19H730.48004c.65997,0,1.19-.52997,1.19-1.19,0-.65997-.53003-1.19-1.19-1.19H1.19006c-.66003,0-1.19006,.53003-1.19006,1.19Z" fill="#3f3d56"></path>
                    <polygon points="466.98463 81.60598 470.81118 130.55703 526.26809 107.39339 494.98463 57.60598 466.98463 81.60598" fill="#a0616a"></polygon>
                    <circle cx="465.32321" cy="55.18079" r="41.33858" fill="#a0616a"></circle>
                    <polygon points="387.98463 440.60598 394.98463 503.39339 345.98463 496.60598 361.98463 438.60598 387.98463 440.60598" fill="#a0616a"></polygon>
                    <polygon points="578.98463 449.60598 585.98463 512.39339 536.98463 505.60598 552.98463 447.60598 578.98463 449.60598" fill="#a0616a"></polygon>
                    <path d="M462.48463,260.10598c-.66897,0-54.14584,2.68515-89.47714,4.46286-16.72275,.84141-29.45202,15.31527-28.15459,32.00884l12.63173,162.5283,36,1,.87795-131,71.12205,4-3-73Z" fill="#2f2e41"></path>
                    <path d="M619.48463,259.10598s9,69,2,76c-7,7-226.5-5.5-226.5-5.5,0,0,48.15354-69.53704,56.82677-71.51852,8.67323-1.98148,146.67323-8.98148,146.67323-8.98148l21,10Z" fill="#2f2e41"></path>
                    <path id="uuid-91047c5b-47d7-4179-8a16-40bd6d529b28-203" d="M335.12666,172.23337c-8.35907-11.69074-9.10267-25.48009-1.66174-30.79863,7.44093-5.31854,20.24665-.15219,28.60713,11.54383,3.40375,4.62627,5.65012,10.00041,6.55111,15.67279l34.79215,49.9814-19.8001,13.70807-35.7745-48.83421c-5.07753-2.68845-9.43721-6.55406-12.71405-11.27326Z" fill="#a0616a"></path>
                    <path d="M464.98463,112.60598l51-21,96,148s-67,15-90,18c-23,3-49-9-49-9l-8-136Z" fill="#6c63ff"></path>
                    <path d="M526.98463,137.60598l-18.5-57.70866,24,18.20866s68,45,68,64c0,19,21,77,21,77,0,0,23.5,19.5,15.5,37.5-8,18,10.5,15.5,12.5,28.5,2,13-28.5,30.5-28.5,30.5,0,0-7.5-73.5-31.5-73.5-24,0-62.5-124.5-62.5-124.5Z" fill="#3f3d56"></path>
                    <path d="M468.56831,111.13035l-25.08368,9.97563s4,70,8,76c4,6,18,38,18,38v10.42913s-28,8.57087-27,13.57087c1,5,66,19,66,19,0,0-13-40-21-53-8-13-18.91632-113.97563-18.91632-113.97563Z" fill="#3f3d56"></path>
                    <path d="M452.48463,121.10598s-29-4-34,30c-5,34-1.82283,38.5-1.82283,38.5l-8.17717,19.5-27-30-26,17s47,76,66,74c19-2,47-57,47-57l-16-92Z" fill="#3f3d56"></path>
                    <path d="M597.32321,270.14478l-14.83858,209.96121-38.5-1.5s-8.5-198.5-8.5-201.5c0-3,4-20,29-21,25-1,32.83858,14.03879,32.83858,14.03879Z" fill="#2f2e41"></path>
                    <path d="M541.48463,484.10598s20-6,23-2c3,4,20,6,20,6l5,49s-14,10-16,12-55,4-56-8c-1-12,14-27,14-27l10-30Z" fill="#2f2e41"></path>
                    <path d="M394.48463,470.10598s6-5,8,9c2,14,9,37-1,40-10,3-110,4-110-5v-9l9-7,18.00394-2.869s34.99606-32.131,38.99606-32.131c4,0,17,13,17,13l20-6Z" fill="#2f2e41"></path>
                    <path d="M505.98463,77.10598s-20-24-28-22-3,5-3,5l-20-22s-16-6-31,13c0,0-9-16,0-25,9-9,12-8,14-13,2-5,16-9,16-9,0,0-.80315-7.19685,3.59843-3.59843s15.3937,3.59843,15.3937,3.59843c0,0,.06299-4,4.53543,0,4.47244,4,9.47244,2,9.47244,2,0,0,0,6.92126,3.5,6.96063,3.5,.03937,9.5-4.96063,10.5-.96063,1,4,8,6,9,18,1,12-4,47-4,47Z" fill="#2f2e41"></path>
                    <g>
                        <path d="M342.99463,178.84874l-114.2362,78.82694c-3.94205,2.72015-9.36214,1.72624-12.08229-2.21581l-32.16176-46.60891c-2.72015-3.94205-1.7259-9.36208,2.21615-12.08223l114.2362-78.82694c3.94205-2.72015,9.36214-1.72624,12.08229,2.21581l32.16176,46.60891c2.72015,3.94205,1.7259,9.36208-2.21615,12.08223Z" fill="#fff"></path>
                        <path d="M312.83914,120.30274l32.16148,46.6085c2.64627,3.83499,1.68408,9.08121-2.15091,11.72749l-56.06388,38.68602c-14.78562-4.04015-28.2774-13.11486-37.66263-26.71596-6.14766-8.9092-9.85314-18.77211-11.26649-28.80885l63.25494-43.6481c3.83499-2.64627,9.08121-1.68408,11.72749,2.15091Z" fill="#e6e6e6"></path>
                        <path d="M223.84012,260.20913c-3.0791,0-6.10938-1.46094-7.9873-4.18066l-32.16211-46.60938c-1.4668-2.12695-2.01758-4.7002-1.5498-7.24805,.4668-2.54785,1.89551-4.75879,4.02246-6.22559l114.23535-78.82715c4.39746-3.03223,10.44043-1.92285,13.47363,2.4707l32.16211,46.60938c1.4668,2.12695,2.01758,4.7002,1.5498,7.24805-.4668,2.54688-1.89551,4.75879-4.02148,6.22559l-114.23633,78.82715c-1.67578,1.15527-3.59082,1.70996-5.48633,1.70996Zm82.04785-142.80176c-1.50391,0-3.02344,.44043-4.35254,1.35742l-114.23633,78.82715c-1.6875,1.16309-2.82031,2.91797-3.19141,4.94043-.37109,2.02148,.06543,4.06445,1.22949,5.75l32.16211,46.60938c2.40625,3.48633,7.20215,4.36816,10.69043,1.96094l114.2373-78.82715c1.68652-1.16309,2.81934-2.91797,3.19043-4.94043,.37109-2.02148-.06543-4.06445-1.22949-5.75l-32.16211-46.60938c-1.48926-2.1582-3.89453-3.31836-6.33789-3.31836Z" fill="#3f3d56"></path>
                        <path d="M224.6666,236.93718c-2.89521,1.9978-3.6253,5.97848-1.6275,8.87369,1.9978,2.89521,5.97848,3.6253,8.87369,1.6275l11.76134-8.11573c2.89521-1.9978,3.6253-5.97848,1.6275-8.87369-1.9978-2.89521-5.97848-3.6253-8.87369-1.6275l-11.76134,8.11573Z" fill="#6c63ff"></path>
                        <path d="M232.63862,171.91114c-4.56802,3.15209-5.71978,9.43286-2.56769,14.00088,3.15209,4.56802,9.43252,5.71972,14.00054,2.56763l18.29546-12.6245c4.56802-3.15209,5.72007-9.43245,2.56797-14.00047-3.15209-4.56802-9.4328-5.72013-14.00082-2.56804l-18.29546,12.6245Z" fill="#6c63ff"></path>
                    </g>
                    <g>
                        <path d="M340.25926,185.80874H201.4659c-4.78947,0-8.68608-3.89636-8.68608-8.68583v-56.62834c0-4.78947,3.89661-8.68583,8.68608-8.68583h138.79336c4.78947,0,8.68608,3.89636,8.68608,8.68583v56.62834c0,4.78947-3.89661,8.68583-8.68608,8.68583Z" fill="#fff"></path>
                        <path d="M348.69017,120.49482v56.62784c0,4.65939-3.77152,8.43091-8.43091,8.43091h-68.11583c-9.87497-11.72273-15.82567-26.8544-15.82567-43.37931,0-10.82439,2.55172-21.04674,7.08876-30.11034h76.85275c4.65939,0,8.43091,3.77152,8.43091,8.43091Z" fill="#e6e6e6"></path>
                        <path d="M340.25907,186.80874H201.4661c-5.34082,0-9.68652-4.34473-9.68652-9.68555v-56.62891c0-5.34082,4.3457-9.68555,9.68652-9.68555h138.79297c5.34082,0,9.68652,4.34473,9.68652,9.68555v56.62891c0,5.34082-4.3457,9.68555-9.68652,9.68555ZM201.4661,112.80874c-4.23828,0-7.68652,3.44727-7.68652,7.68555v56.62891c0,4.23828,3.44824,7.68555,7.68652,7.68555h138.79297c4.23828,0,7.68652-3.44727,7.68652-7.68555v-56.62891c0-4.23828-3.44824-7.68555-7.68652-7.68555H201.4661Z" fill="#3f3d56"></path>
                        <path d="M209.87637,166.41564c-3.51759,0-6.37931,2.86172-6.37931,6.37931s2.86172,6.37931,6.37931,6.37931h14.28966c3.51759,0,6.37931-2.86172,6.37931-6.37931s-2.86172-6.37931-6.37931-6.37931h-14.28966Z" fill="#6c63ff"></path>
                        <path d="M253.36907,117.42253c-5.55,0-10.06511,4.51536-10.06511,10.06536s4.51511,10.06486,10.06511,10.06486h22.22841c5.55,0,10.06511-4.51486,10.06511-10.06486s-4.51511-10.06536-10.06511-10.06536h-22.22841Z" fill="#6c63ff"></path>
                    </g>
                    <g>
                        <path d="M456.25926,381.80874h-138.79336c-4.78947,0-8.68608-3.89636-8.68608-8.68583v-56.62834c0-4.78947,3.89661-8.68583,8.68608-8.68583h138.79336c4.78947,0,8.68608,3.89636,8.68608,8.68583v56.62834c0,4.78947-3.89661,8.68583-8.68608,8.68583Z" fill="#fff"></path>
                        <path d="M464.69017,316.49482v56.62784c0,4.65939-3.77152,8.43091-8.43091,8.43091h-68.11583c-9.87497-11.72273-15.82567-26.8544-15.82567-43.37931,0-10.82439,2.55172-21.04674,7.08876-30.11034h76.85275c4.65939,0,8.43091,3.77152,8.43091,8.43091Z" fill="#e6e6e6"></path>
                        <path d="M456.25907,382.80874h-138.79297c-5.34082,0-9.68652-4.34473-9.68652-9.68555v-56.62891c0-5.34082,4.3457-9.68555,9.68652-9.68555h138.79297c5.34082,0,9.68652,4.34473,9.68652,9.68555v56.62891c0,5.34082-4.3457,9.68555-9.68652,9.68555Zm-138.79297-74c-4.23828,0-7.68652,3.44727-7.68652,7.68555v56.62891c0,4.23828,3.44824,7.68555,7.68652,7.68555h138.79297c4.23828,0,7.68652-3.44727,7.68652-7.68555v-56.62891c0-4.23828-3.44824-7.68555-7.68652-7.68555h-138.79297Z" fill="#3f3d56"></path>
                        <path d="M325.87637,362.41564c-3.51759,0-6.37931,2.86172-6.37931,6.37931s2.86172,6.37931,6.37931,6.37931h14.28966c3.51759,0,6.37931-2.86172,6.37931-6.37931s-2.86172-6.37931-6.37931-6.37931h-14.28966Z" fill="#6c63ff"></path>
                        <path d="M369.36907,313.42253c-5.55,0-10.06511,4.51536-10.06511,10.06536s4.51511,10.06486,10.06511,10.06486h22.22841c5.55,0,10.06511-4.51486,10.06511-10.06486s-4.51511-10.06536-10.06511-10.06536h-22.22841Z" fill="#6c63ff"></path>
                    </g>
                    <path id="uuid-c026fd96-7d81-4b34-bb39-0646c0e08e96-204" d="M465.67391,331.01678c-12.74718,6.63753-26.5046,5.44058-30.72743-2.67249-4.22283-8.11308,2.6878-20.06802,15.44041-26.70621,5.05777-2.72156,10.69376-4.19231,16.43644-4.28916l54.36547-27.44139,10.79681,21.52636-53.36733,28.57487c-3.37375,4.65048-7.81238,8.42516-12.94437,11.00803Z" fill="#a0616a"></path>
                    <path d="M527.48463,97.10598s56-3,68,27c12,30,22,128,22,128l-122,66.37402-21-32.37402,82-64-29-125Z" fill="#3f3d56"></path>
                </svg>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const container = document.querySelector('.container');
            const switchBtns = document.querySelectorAll('.switch-btn');
            const loginForm = document.querySelector('.login-form');
            const registerForm = document.querySelector('.register-form');
            
            function activateForm(form) {
                form.classList.add('active');
                const inputs = form.querySelectorAll('.input-block');
                inputs.forEach((input, index) => {
                    setTimeout(() => {
                        input.style.opacity = '1';
                        input.style.transform = 'translateY(0)';
                    }, 100 * index);
                });
            }

            function deactivateForm(form) {
                form.classList.remove('active');
                const inputs = form.querySelectorAll('.input-block');
                inputs.forEach(input => {
                    input.style.opacity = '0';
                    input.style.transform = 'translateY(20px)';
                });
            }
            
            switchBtns.forEach(btn => {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    container.classList.toggle('show-register');
                    
                    if (container.classList.contains('show-register')) {
                        deactivateForm(loginForm);
                        setTimeout(() => activateForm(registerForm), 300);
                    } else {
                        deactivateForm(registerForm);
                        setTimeout(() => activateForm(loginForm), 300);
                    }
                });
            });
            
            // Activate initial form based on PHP condition
            if (container.classList.contains('show-register')) {
                activateForm(registerForm);
            } else {
                activateForm(loginForm);
            }
        });
    </script>
</body>
</html>