<?php
session_start();

echo '<!DOCTYPE html>
<html>
<head>
    <title>Logging out...</title>
    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="STStr.svg">
    <link rel="icon" type="image/png" href="STStr.png">
    <link rel="apple-touch-icon" href="STStr.png">
    <link rel="stylesheet" href="theme.css">
    <style>
        .logout-container {
            background: white;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            color: #333;
            opacity: 1;
            transition: opacity 0.5s ease;
            flex-direction: column;
            gap: 2rem;
        }
        .logout-message {
            text-align: center;
        }
        
        /* 3D Boxes Animation */
        .boxes {
            --size: 32px;
            --duration: 2000ms;
            height: calc(var(--size) * 2);
            width: calc(var(--size) * 3);
            position: relative;
            transform-style: preserve-3d;
            transform-origin: 50% 50%;
            margin-top: calc(var(--size) * 1.5 * -1);
            transform: rotateX(60deg) rotateZ(45deg) rotateY(0deg) translateZ(0px);
        }

        .boxes .box {
            width: var(--size);
            height: var(--size);
            top: 0;
            left: 0;
            position: absolute;
            transform-style: preserve-3d;
        }

        .boxes .box:nth-child(1) {
            transform: translate(100%, 0);
            -webkit-animation: box1 var(--duration) linear infinite;
            animation: box1 var(--duration) linear infinite;
        }

        .boxes .box:nth-child(2) {
            transform: translate(0, 100%);
            -webkit-animation: box2 var(--duration) linear infinite;
            animation: box2 var(--duration) linear infinite;
        }

        .boxes .box:nth-child(3) {
            transform: translate(100%, 100%);
            -webkit-animation: box3 var(--duration) linear infinite;
            animation: box3 var(--duration) linear infinite;
        }
        
        .boxes .box:nth-child(4) {
            transform: translate(200%, 0);
            -webkit-animation: box4 var(--duration) linear infinite;
            animation: box4 var(--duration) linear infinite;
        }

        .boxes .box > div {
            --background: #5C8DF6;
            --top: auto;
            --right: auto;
            --bottom: auto;
            --left: auto;
            --translateZ: calc(var(--size) / 2);
            --rotateY: 0deg;
            --rotateX: 0deg;
            position: absolute;
            width: 100%;
            height: 100%;
            background: var(--background);
            top: var(--top);
            right: var(--right);
            bottom: var(--bottom);
            left: var(--left);
            transform: rotateY(var(--rotateY)) rotateX(var(--rotateX)) translateZ(var(--translateZ));
        }

        .boxes .box > div:nth-child(1) {
            --top: 0;
            --left: 0;
        }
        
        .boxes .box > div:nth-child(2) {
            --background: #145af2;
            --right: 0;
            --rotateY: 90deg;
        }
        
        .boxes .box > div:nth-child(3) {
            --background: #447cf5;
            --rotateX: -90deg;
        }
        
        .boxes .box > div:nth-child(4) {
            --background: #DBE3F4;
            --top: 0;
            --left: 0;
            --translateZ: calc(var(--size) * 3 * -1);
        }

        @keyframes box1 {
            0%, 50% {
                transform: translate(100%, 0);
            }
            100% {
                transform: translate(200%, 0);
            }
        }
        
        @keyframes box2 {
            0% {
                transform: translate(0, 100%);
            }
            50% {
                transform: translate(0, 0);
            }
            100% {
                transform: translate(100%, 0);
            }
        }
        
        @keyframes box3 {
            0%, 50% {
                transform: translate(100%, 100%);
            }
            100% {
                transform: translate(0, 100%);
            }
        }
        
        @keyframes box4 {
            0% {
                transform: translate(200%, 0);
            }
            50% {
                transform: translate(200%, 100%);
            }
            100% {
                transform: translate(100%, 100%);
            }
        }
    </style>
</head>
<body>
    <div class="logout-container">
        <div class="boxes">
            <div class="box">
                <div></div>
                <div></div>
                <div></div>
                <div></div>
            </div>
            <div class="box">
                <div></div>
                <div></div>
                <div></div>
                <div></div>
            </div>
            <div class="box">
                <div></div>
                <div></div>
                <div></div>
                <div></div>
            </div>
            <div class="box">
                <div></div>
                <div></div>
                <div></div>
                <div></div>
            </div>
        </div>
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
        }, 2500);
    </script>
</body>
</html>';

session_destroy();
exit();
?>