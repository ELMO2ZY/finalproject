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
    <title>Create Ticket</title>
    <link rel="stylesheet" href="theme.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <a href="home.php" class="back-btn">
            <i class="fas fa-arrow-left"></i> Back
        </a>
        <div class="ticket-form">
            <h1>Create New Ticket</h1>
            <div id="successMessage" class="success-message">
                Ticket created successfully!
            </div>
            <form id="ticketForm" action="process_ticket.php" method="POST">
                <div class="input-field">
                    <input type="text" name="subject" id="subject" required>
                    <label for="subject">Subject</label>
                </div>
                <div class="input-field">
                    <textarea name="description" id="description" required></textarea>
                    <label for="description">Description</label>
                </div>
                <div class="input-field">
                    <select name="priority" id="priority">
                        <option value="low">Low Priority</option>
                        <option value="medium" selected>Medium Priority</option>
                        <option value="high">High Priority</option>
                    </select>
                    <label for="priority">Priority</label>
                </div>
                <button type="submit" class="btn">Submit Ticket</button>
            </form>
        </div>
    </div>

    <script src="animations.js"></script>
    <script>
        document.getElementById('ticketForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            setTimeout(() => {
                document.getElementById('successMessage').style.display = 'block';
                this.reset();
                
                setTimeout(() => {
                    document.getElementById('successMessage').style.display = 'none';
                }, 3000);
            }, 1000);
        });
    </script>
</body>
</html>