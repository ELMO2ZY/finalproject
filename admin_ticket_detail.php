<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: login.php");
    exit();
}

require_once 'db_connect.php';

// Get ticket ID from URL
$ticket_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$ticket_id) {
    header("Location: admin_tickets.php");
    exit();
}

try {
    // Debug information
    error_log("Fetching ticket ID: " . $ticket_id);
    
    // Fetch ticket details with user information
    $stmt = $pdo->prepare("
        SELECT t.*, u.email as username
        FROM tickets t
        JOIN users u ON t.user_id = u.id
        WHERE t.id = ?
    ");
    $stmt->execute([$ticket_id]);
    $ticket = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$ticket) {
        error_log("No ticket found with ID: " . $ticket_id);
        header("Location: admin_tickets.php");
        exit();
    }

    error_log("Ticket found: " . print_r($ticket, true));

    // Fetch ticket history
    $stmt = $pdo->prepare("
        SELECT th.*, u.email as username
        FROM ticket_history th
        JOIN users u ON th.user_id = u.id
        WHERE th.ticket_id = ?
        ORDER BY th.created_at DESC
    ");
    $stmt->execute([$ticket_id]);
    $history = $stmt->fetchAll(PDO::FETCH_ASSOC);

    error_log("History entries found: " . count($history));

} catch(PDOException $e) {
    error_log("Error in admin ticket detail: " . $e->getMessage());
    $error = "Error fetching ticket details. Please try again later. Error: " . $e->getMessage();
}

function timeAgo($datetime) {
    $now = new DateTime();
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);

    if ($diff->d == 0) {
        if ($diff->h == 0) {
            if ($diff->i == 0) {
                return "just now";
            }
            return $diff->i . " minutes ago";
        }
        return $diff->h . " hours ago";
    }
    if ($diff->d == 1) {
        return "yesterday";
    }
    return $diff->d . " days ago";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ticket Details - Admin Dashboard</title>
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
            max-width: 1000px;
            margin: 0 auto;
        }

        .back-link {
            color: #5e7eb6;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 2rem;
            font-weight: 600;
            transition: all 0.3s ease;
            padding: 0.5rem 1rem;
            border-radius: 8px;
        }

        .back-link:hover {
            transform: translateX(-5px);
            background: rgba(94, 126, 182, 0.08);
        }

        .ticket-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.08);
            overflow: hidden;
            position: relative;
        }

        .ticket-container::before {
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

        .ticket-header {
            padding: 2rem;
            border-bottom: 1px solid #e2e8f0;
        }

        .ticket-title {
            font-size: 1.5rem;
            color: #2d3748;
            margin-bottom: 1rem;
            font-weight: 700;
            letter-spacing: -0.5px;
        }

        .ticket-meta {
            display: flex;
            gap: 2rem;
            color: #64748b;
            font-size: 0.95rem;
        }

        .meta-item {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .meta-item i {
            color: #5e7eb6;
        }

        .ticket-body {
            padding: 2rem;
            border-bottom: 1px solid #e2e8f0;
        }

        .ticket-description {
            color: #4a5568;
            line-height: 1.6;
            font-size: 1rem;
            white-space: pre-wrap;
        }

        .ticket-actions {
            padding: 2rem;
            display: flex;
            gap: 1rem;
            background: #f8fafc;
        }

        .status-btn {
            padding: 0.75rem 1.5rem;
            border-radius: 12px;
            border: none;
            font-size: 0.95rem;
            font-weight: 600;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
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

        .status-btn:hover {
            transform: translateY(-2px);
            filter: brightness(0.95);
        }

        .history-section {
            margin-top: 2rem;
        }

        .history-header {
            padding: 2rem;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .history-header h2 {
            font-size: 1.25rem;
            color: #2d3748;
            font-weight: 600;
        }

        .history-header i {
            color: #5e7eb6;
        }

        .history-item {
            padding: 1.5rem 2rem;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            gap: 1rem;
            align-items: flex-start;
        }

        .history-icon {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: #f1f5f9;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #5e7eb6;
        }

        .history-content {
            flex: 1;
        }

        .history-text {
            color: #4a5568;
            margin-bottom: 0.5rem;
            line-height: 1.5;
        }

        .history-meta {
            color: #94a3b8;
            font-size: 0.9rem;
            display: flex;
            gap: 12px;
        }

        .error {
            background-color: #fee2e2;
            border: 1px solid #ef4444;
            color: #b91c1c;
            padding: 1rem;
            margin-bottom: 1.5rem;
            border-radius: 8px;
            font-size: 0.95rem;
        }

        @media (max-width: 768px) {
            body {
                padding: 1rem;
            }

            .ticket-header, .ticket-body, .ticket-actions, .history-header, .history-item {
                padding: 1.5rem;
            }

            .ticket-meta {
                flex-direction: column;
                gap: 1rem;
            }

            .ticket-actions {
                flex-wrap: wrap;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="admin_tickets.php" class="back-link">
            <i class="fas fa-arrow-left"></i>
            Back to Tickets
        </a>

        <?php if (isset($error)): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <div class="ticket-container">
            <div class="ticket-header">
                <h1 class="ticket-title"><?php echo htmlspecialchars($ticket['subject']); ?></h1>
                <div class="ticket-meta">
                    <div class="meta-item">
                        <i class="fas fa-user"></i>
                        <span><?php echo htmlspecialchars($ticket['username']); ?></span>
                    </div>
                    <div class="meta-item">
                        <i class="far fa-clock"></i>
                        <span>Created <?php echo timeAgo($ticket['created_at']); ?></span>
                    </div>
                    <div class="meta-item">
                        <i class="fas fa-tag"></i>
                        <span>Status: <?php echo ucfirst(htmlspecialchars($ticket['status'])); ?></span>
                    </div>
                </div>
            </div>

            <div class="ticket-body">
                <div class="ticket-description"><?php echo htmlspecialchars($ticket['description']); ?></div>
            </div>

            <div class="ticket-actions">
                <button class="status-btn status-open" onclick="updateStatus(<?php echo $ticket['id']; ?>, 'open')">
                    <i class="fas fa-exclamation-circle"></i>
                    Mark as Open
                </button>
                <button class="status-btn status-pending" onclick="updateStatus(<?php echo $ticket['id']; ?>, 'pending')">
                    <i class="fas fa-clock"></i>
                    Mark as Pending
                </button>
                <button class="status-btn status-resolved" onclick="updateStatus(<?php echo $ticket['id']; ?>, 'resolved')">
                    <i class="fas fa-check-circle"></i>
                    Mark as Resolved
                </button>
            </div>

            <div class="history-section">
                <div class="history-header">
                    <i class="fas fa-history"></i>
                    <h2>Ticket History</h2>
                </div>
                <?php if (empty($history)): ?>
                    <div class="history-item">
                        <div class="history-icon">
                            <i class="fas fa-info"></i>
                        </div>
                        <div class="history-content">
                            <div class="history-text">No history available for this ticket.</div>
                        </div>
                    </div>
                <?php else: ?>
                    <?php foreach ($history as $item): ?>
                        <div class="history-item">
                            <div class="history-icon">
                                <i class="fas fa-<?php 
                                    echo $item['action'] === 'status_change' ? 'exchange-alt' : 
                                        ($item['action'] === 'comment' ? 'comment' : 'pencil-alt'); 
                                ?>"></i>
                            </div>
                            <div class="history-content">
                                <div class="history-text">
                                    <?php 
                                    if ($item['action'] === 'status_change') {
                                        echo "Status changed to " . ucfirst(htmlspecialchars($item['new_value']));
                                    } else {
                                        echo htmlspecialchars($item['description']);
                                    }
                                    ?>
                                </div>
                                <div class="history-meta">
                                    <span><?php echo htmlspecialchars($item['username']); ?></span>
                                    <span><?php echo timeAgo($item['created_at']); ?></span>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        function updateStatus(ticketId, newStatus) {
            if (confirm(`Are you sure you want to change the ticket status to ${newStatus}?`)) {
                window.location.href = `update_ticket_status.php?id=${ticketId}&status=${newStatus}&redirect=admin`;
            }
        }
    </script>
</body>
</html> 
