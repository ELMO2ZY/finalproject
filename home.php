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
    <title>Welcome</title>
    <link rel="stylesheet" href="theme.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="container">
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
</body>
</html>