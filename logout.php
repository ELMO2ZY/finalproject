<?php
session_start();

echo '<!DOCTYPE html>
<html>
<head>
    <title>Logging out...</title>
    <link rel="stylesheet" href="theme.css">
    <style>
        .logout-container {
            background: var(--primary-color);
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            color: white;
            opacity: 1;
            transition: opacity 0.5s ease;
        }
        .logout-message {
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="logout-container">
        <div class="logout-message">
            <h1>Logging you out...</h1>
            <p>Please wait while we securely end your session.</p>
        </div>
    </div>
    <script>
        setTimeout(function() {
            document.querySelector(".logout-container").style.opacity = "0";
            setTimeout(function() {
                window.location.href = "login.php";
            }, 500);
        }, 1500);
    </script>
</body>
</html>';

session_destroy();
exit();
?>