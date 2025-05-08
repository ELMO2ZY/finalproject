<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home - Support Ticket System</title>
    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="STStr.svg">
    <link rel="icon" type="image/png" href="STStr.png">
    <link rel="apple-touch-icon" href="STStr.png">
    <link rel="stylesheet" href="theme.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Add success message styles */
        .success-message {
            background: #d4edda;
            color: #155724;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 2rem;
            display: none;
            animation: slideIn 0.5s ease;
        }

        @keyframes slideIn {
            from {
                transform: translateY(-20px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        /* Enhanced Button Styles */
        .btn {
            position: relative;
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.1);
            transform: translateY(100%);
            transition: transform 0.3s ease;
        }

        .btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(94, 126, 182, 0.3);
        }

        .btn:hover::before {
            transform: translateY(0);
        }

        .btn i {
            transition: transform 0.3s ease;
        }

        .btn:hover i {
            transform: scale(1.2);
        }

        .btn-logout {
            background: #f8f9fa;
            color: #5e7eb6;
            border: 2px solid #5e7eb6;
        }

        .btn-logout:hover {
            background: #5e7eb6;
            color: white;
        }

        .btn-logout::before {
            background: rgba(94, 126, 182, 0.1);
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Add success message container -->
        <div id="successMessage" class="success-message">
            <i class="fas fa-check-circle"></i> Ticket created successfully!
        </div>
        
        <div class="welcome-container">
            <div class="user-avatar">
                <?php echo strtoupper(substr($_SESSION['user_name'], 0, 1)); ?>
            </div>
            <h1>Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?>!</h1>
            <p>What would you like to do today?</p>
            
            <div class="btn-group">
                <a href="create_ticket.php" class="btn">
                    <i class="fas fa-plus"></i> Create Ticket
                </a>
                <a href="view_tickets.php" class="btn">
                    <i class="fas fa-ticket-alt"></i> View Tickets
                </a>
                <a href="logout.php" class="btn btn-logout">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </div>
    </div>
    
    <script src="animations.js"></script>
    <script>
        // Only keep success message code, remove dark mode JavaScript
        if (sessionStorage.getItem('ticketSuccess')) {
            const successMessage = document.getElementById('successMessage');
            successMessage.style.display = 'block';
            
            // Remove the success flag from session storage
            sessionStorage.removeItem('ticketSuccess');
            
            // Hide success message after 3 seconds
            setTimeout(() => {
                successMessage.style.display = 'none';
            }, 3000);
        }
    </script>
</body>
</html>