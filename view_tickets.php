<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

require_once 'db_connect.php';

// Fetch tickets for the current user
$user_id = $_SESSION['user_id'];
try {
    // Get ticket counts for filters
    $stmt = $pdo->prepare("SELECT status, COUNT(*) as count FROM tickets WHERE user_id = ? GROUP BY status");
    $stmt->execute([$user_id]);
    $status_counts = [
        'all' => 0,
        'open' => 0,
        'pending' => 0,
        'resolved' => 0
    ];
    while ($row = $stmt->fetch()) {
        $status_counts[$row['status']] = $row['count'];
    }
    $status_counts['all'] = array_sum($status_counts);

    // Get tickets
    $filter = $_GET['filter'] ?? 'all';
    $query = "SELECT * FROM tickets WHERE user_id = ?";
    if ($filter !== 'all') {
        $query .= " AND status = ?";
    }
    $query .= " ORDER BY created_at DESC";
    
    $stmt = $pdo->prepare($query);
    if ($filter !== 'all') {
        $stmt->execute([$user_id, $filter]);
    } else {
        $stmt->execute([$user_id]);
    }
    $tickets = $stmt->fetchAll();
} catch(PDOException $e) {
    $error = "Error fetching tickets: " . $e->getMessage();
}

function timeAgo($datetime) {
    $now = new DateTime();
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);

    if ($diff->d == 0) {
        if ($diff->h == 0) {
            return "just now";
        }
        return $diff->h . " hours ago";
    }
    if ($diff->d == 1) {
        return "1 day ago";
    }
    return $diff->d . " days ago";
}

function maskAccountNumber($accountNumber) {
    // Keep first 4 and last 4 digits visible, mask the rest
    $length = strlen($accountNumber);
    if ($length <= 8) {
        return $accountNumber; // Don't mask if too short
    }
    $firstFour = substr($accountNumber, 0, 4);
    $lastFour = substr($accountNumber, -4);
    $masked = str_repeat('*', $length - 8);
    return $firstFour . $masked . $lastFour;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Tickets - Support Ticket System</title>
    <link rel="icon" type="image/svg+xml" href="STStr.svg">
    <link rel="icon" type="image/png" href="STStr.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
        }

        body {
            background: linear-gradient(135deg, #f6f9fc 0%, #edf2f7 100%);
            min-height: 100vh;
            padding: 2rem;
        }

        .container {
            max-width: 900px;
            margin: 0 auto;
        }

        .tickets-container {
            background: white;
            padding: 2.5rem;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.08);
            position: relative;
            overflow: hidden;
            backdrop-filter: blur(10px);
        }

        .tickets-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 6px;
            background: linear-gradient(90deg, #5e7eb6, #447cf5, #5e7eb6);
            background-size: 200% 100%;
            animation: gradientMove 3s ease infinite;
        }

        @keyframes gradientMove {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        .back-link {
            color: #5e7eb6;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 2.5rem;
            font-weight: 600;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            padding: 0.5rem 1rem;
            border-radius: 8px;
        }

        .back-link:hover {
            transform: translateX(-5px);
            background: rgba(94, 126, 182, 0.08);
        }

        .header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 15px;
            margin-bottom: 2.5rem;
        }

        .header-left {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .header h1 {
            font-size: 2rem;
            color: #2d3748;
            font-weight: 700;
            letter-spacing: -0.5px;
        }

        .header i {
            font-size: 2rem;
            color: #5e7eb6;
            filter: drop-shadow(0 2px 4px rgba(94, 126, 182, 0.2));
        }

        .header-create-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            background: white;
            color: #5e7eb6;
            padding: 0.75rem 1.5rem;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            border: 2px solid #e2e8f0;
        }

        .header-create-btn i {
            font-size: 1.2rem;
            color: #5e7eb6;
            filter: none;
        }

        .header-create-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
            border-color: #5e7eb6;
        }

        .filters {
            display: flex;
            gap: 1rem;
            margin-bottom: 2.5rem;
            flex-wrap: wrap;
        }

        .filter-btn {
            padding: 0.8rem 1.5rem;
            border-radius: 12px;
            border: 2px solid transparent;
            background: #f8fafc;
            color: #64748b;
            cursor: pointer;
            text-decoration: none;
            font-size: 0.95rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .filter-btn:hover {
            background: #f1f5f9;
            color: #5e7eb6;
            transform: translateY(-2px);
        }

        .filter-btn.active {
            background: #5e7eb6;
            color: white;
            box-shadow: 0 4px 12px rgba(94, 126, 182, 0.25);
        }

        .count {
            background: rgba(255, 255, 255, 0.2);
            padding: 2px 10px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 700;
        }

        .filter-btn:not(.active) .count {
            background: #e2e8f0;
            color: #64748b;
        }

        .ticket-card {
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            padding: 1.75rem;
            margin-bottom: 1.25rem;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.02);
        }

        .ticket-card:hover {
            transform: translateY(-4px) scale(1.01);
            box-shadow: 0 12px 24px rgba(0, 0, 0, 0.06);
            border-color: #5e7eb6;
        }

        .ticket-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 1.25rem;
        }

        .ticket-title {
            font-size: 1.2rem;
            font-weight: 700;
            color: #2d3748;
            margin: 0;
            letter-spacing: -0.3px;
        }

        .status-badge {
            padding: 0.5rem 1.25rem;
            border-radius: 25px;
            font-size: 0.9rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
        }

        .status-badge i {
            font-size: 0.9rem;
        }

        .status-open {
            background: #fff8e1;
            color: #b45309;
        }

        .status-pending {
            background: #fff4e6;
            color: #c2410c;
        }

        .status-resolved {
            background: #ecfdf5;
            color: #047857;
        }

        .ticket-description {
            color: #64748b;
            font-size: 1rem;
            margin-bottom: 1.5rem;
            line-height: 1.6;
        }

        .ticket-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 0.95rem;
            color: #64748b;
        }

        .ticket-actions {
            display: flex;
            gap: 1rem;
            align-items: center;
        }

        .delete-btn {
            color: #ef4444;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-weight: 600;
            transition: all 0.3s ease;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            background: rgba(239, 68, 68, 0.1);
        }

        .delete-btn:hover {
            background: rgba(239, 68, 68, 0.2);
            transform: translateY(-2px);
        }

        .delete-btn i {
            font-size: 0.9rem;
            transition: transform 0.3s ease;
        }

        .delete-btn:hover i {
            transform: scale(1.1);
        }

        .created-at {
            display: flex;
            align-items: center;
            gap: 8px;
            font-weight: 500;
        }

        .created-at i {
            font-size: 1rem;
            color: #5e7eb6;
        }

        .view-details {
            color: #5e7eb6;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
            padding: 0.5rem 1rem;
            border-radius: 8px;
        }

        .view-details:hover {
            color: #447cf5;
            background: rgba(94, 126, 182, 0.08);
        }

        .view-details i {
            font-size: 0.9rem;
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .view-details:hover i {
            transform: translateX(4px);
        }

        .no-tickets {
            text-align: center;
            padding: 4rem 2rem;
            color: #64748b;
        }

        .no-tickets i {
            font-size: 3.5rem;
            color: #5e7eb6;
            margin-bottom: 1.5rem;
            filter: drop-shadow(0 4px 6px rgba(94, 126, 182, 0.2));
        }

        .no-tickets h2 {
            font-size: 1.75rem;
            color: #2d3748;
            margin-bottom: 1rem;
            font-weight: 700;
            letter-spacing: -0.5px;
        }

        .no-tickets p {
            font-size: 1.1rem;
            margin-bottom: 2rem;
            color: #64748b;
        }

        .create-ticket-btn {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            background: linear-gradient(135deg, #5e7eb6, #447cf5);
            color: white;
            padding: 1rem 2rem;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 4px 12px rgba(94, 126, 182, 0.25);
        }

        .create-ticket-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(94, 126, 182, 0.35);
        }

        @keyframes slideIn {
            from {
                transform: translateY(30px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .ticket-card {
            animation: slideIn 0.6s cubic-bezier(0.4, 0, 0.2, 1) forwards;
            animation-delay: calc(var(--index) * 0.1s);
            opacity: 0;
        }

        @media (max-width: 768px) {
            body {
                padding: 1rem;
            }

            .tickets-container {
                padding: 1.5rem;
            }

            .header h1 {
                font-size: 1.75rem;
            }

            .filters {
                gap: 0.75rem;
            }

            .filter-btn {
                padding: 0.6rem 1rem;
                font-size: 0.9rem;
            }
        }

        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(4px);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }

        .modal {
            background: white;
            padding: 2rem;
            border-radius: 16px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            max-width: 400px;
            width: 90%;
            text-align: center;
            position: relative;
            animation: modalSlideIn 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        @keyframes modalSlideIn {
            from {
                transform: translateY(-20px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .modal-icon {
            font-size: 3rem;
            color: #ef4444;
            margin-bottom: 1rem;
        }

        .modal-title {
            font-size: 1.5rem;
            color: #2d3748;
            margin-bottom: 1rem;
            font-weight: 700;
        }

        .modal-message {
            color: #64748b;
            margin-bottom: 2rem;
            line-height: 1.5;
        }

        .modal-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
        }

        .modal-btn {
            padding: 0.75rem 1.5rem;
            border-radius: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border: none;
            font-size: 1rem;
        }

        .modal-btn-cancel {
            background: #f1f5f9;
            color: #64748b;
        }

        .modal-btn-cancel:hover {
            background: #e2e8f0;
            transform: translateY(-2px);
        }

        .modal-btn-delete {
            background: #ef4444;
            color: white;
        }

        .modal-btn-delete:hover {
            background: #dc2626;
            transform: translateY(-2px);
        }

        .account-info {
            display: flex;
            align-items: center;
            gap: 8px;
            margin: 0.5rem 0;
            color: #64748b;
            font-size: 0.95rem;
        }

        .account-info i {
            color: #5e7eb6;
        }

        .account-number {
            font-family: monospace;
            letter-spacing: 1px;
            font-weight: 500;
        }
    </style>
</head>
<body>
    <div class="modal-overlay" id="deleteModal">
        <div class="modal">
            <i class="fas fa-exclamation-triangle modal-icon"></i>
            <h2 class="modal-title">Delete Ticket</h2>
            <p class="modal-message">Are you sure you want to delete this ticket? This action cannot be undone.</p>
            <div class="modal-buttons">
                <button class="modal-btn modal-btn-cancel" onclick="closeModal()">Cancel</button>
                <button class="modal-btn modal-btn-delete" id="confirmDelete">Delete</button>
            </div>
        </div>
    </div>

    <div class="container">
        <div class="tickets-container">
            <a href="home.php" class="back-link">
                <i class="fas fa-arrow-left"></i>
                Back to Home
            </a>

            <div class="header">
                <div class="header-left">
                    <i class="fas fa-ticket-alt"></i>
                    <h1>My Tickets</h1>
                </div>
                <a href="create_ticket.php" class="header-create-btn">
                    <i class="fas fa-plus"></i>
                    <span>Create Ticket</span>
                </a>
            </div>

            <div class="filters">
                <a href="?filter=all" class="filter-btn <?php echo (!isset($_GET['filter']) || $_GET['filter'] === 'all') ? 'active' : ''; ?>">
                    <i class="fas fa-list-ul"></i>
                    All <span class="count"><?php echo $status_counts['all']; ?></span>
                </a>
                <a href="?filter=open" class="filter-btn <?php echo (isset($_GET['filter']) && $_GET['filter'] === 'open') ? 'active' : ''; ?>">
                    <i class="fas fa-exclamation-circle"></i>
                    Open <span class="count"><?php echo $status_counts['open']; ?></span>
                </a>
                <a href="?filter=pending" class="filter-btn <?php echo (isset($_GET['filter']) && $_GET['filter'] === 'pending') ? 'active' : ''; ?>">
                    <i class="fas fa-clock"></i>
                    Pending <span class="count"><?php echo $status_counts['pending']; ?></span>
                </a>
                <a href="?filter=resolved" class="filter-btn <?php echo (isset($_GET['filter']) && $_GET['filter'] === 'resolved') ? 'active' : ''; ?>">
                    <i class="fas fa-check-circle"></i>
                    Resolved <span class="count"><?php echo $status_counts['resolved']; ?></span>
                </a>
            </div>

            <?php if (isset($error)): ?>
                <div class="error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <?php if (empty($tickets)): ?>
                <div class="no-tickets">
                    <i class="fas fa-ticket-alt"></i>
                    <h2>No tickets found</h2>
                    <p>You haven't created any tickets in this category yet.</p>
                </div>
            <?php else: ?>
                <?php foreach ($tickets as $index => $ticket): ?>
                    <div class="ticket-card" style="--index: <?php echo $index; ?>">
                        <div class="ticket-header">
                            <h2 class="ticket-title"><?php echo htmlspecialchars($ticket['subject']); ?></h2>
                            <span class="status-badge status-<?php echo htmlspecialchars($ticket['status']); ?>">
                                <i class="fas fa-<?php echo $ticket['status'] === 'open' ? 'exclamation-circle' : ($ticket['status'] === 'pending' ? 'clock' : 'check-circle'); ?>"></i>
                                <?php echo ucfirst(htmlspecialchars($ticket['status'])); ?>
                            </span>
                        </div>
                        <?php if (!empty($ticket['account_number'])): ?>
                            <div class="account-info">
                                <i class="fas fa-credit-card"></i>
                                <span class="account-number"><?php echo maskAccountNumber($ticket['account_number']); ?></span>
                            </div>
                        <?php endif; ?>
                        <div class="ticket-description">
                            <?php 
                            $description = htmlspecialchars($ticket['description']);
                            echo strlen($description) > 150 ? substr($description, 0, 147) . '...' : $description;
                            ?>
                        </div>
                        <div class="ticket-footer">
                            <span class="created-at">
                                <i class="far fa-clock"></i>
                                Created <?php echo timeAgo($ticket['created_at']); ?>
                            </span>
                            <div class="ticket-actions">
                                <a href="#" class="delete-btn" onclick="showDeleteModal(<?php echo $ticket['id']; ?>)">
                                    <i class="fas fa-trash"></i>
                                    Delete
                                </a>
                                <a href="ticket_detail.php?id=<?php echo $ticket['id']; ?>" class="view-details">
                                    View Details
                                    <i class="fas fa-arrow-right"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <script>
        let currentTicketId = null;

        function showDeleteModal(ticketId) {
            currentTicketId = ticketId;
            document.getElementById('deleteModal').style.display = 'flex';
        }

        function closeModal() {
            document.getElementById('deleteModal').style.display = 'none';
            currentTicketId = null;
        }

        document.getElementById('confirmDelete').addEventListener('click', function() {
            if (currentTicketId) {
                window.location.href = `delete_ticket.php?id=${currentTicketId}`;
            }
        });

        // Close modal when clicking outside
        document.getElementById('deleteModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal();
            }
        });

        // Close modal with Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeModal();
            }
        });
    </script>
</body>
</html> 