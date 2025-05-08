<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: login.php");
    exit();
}

require_once 'db_connect.php';

try {
    // Debug information
    echo "<!-- Debug: Session user_id = " . $_SESSION['user_id'] . " -->";
    echo "<!-- Debug: User type = " . $_SESSION['user_type'] . " -->";
    
    // Get ticket counts for filters
    $stmt = $pdo->prepare("SELECT status, COUNT(*) as count FROM tickets GROUP BY status");
    $stmt->execute();
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

    // Debug information
    echo "<!-- Debug: Status counts = " . json_encode($status_counts) . " -->";

    // Get tickets with user information
    $filter = $_GET['filter'] ?? 'all';
    $query = "SELECT t.*, u.email as username 
              FROM tickets t 
              JOIN users u ON t.user_id = u.id";
    
    if ($filter !== 'all') {
        $query .= " WHERE t.status = ?";
    }
    $query .= " ORDER BY t.created_at DESC";
    
    // Debug information
    echo "<!-- Debug: SQL Query = " . $query . " -->";
    echo "<!-- Debug: Filter = " . $filter . " -->";
    
    $stmt = $pdo->prepare($query);
    if ($filter !== 'all') {
        $stmt->execute([$filter]);
    } else {
        $stmt->execute();
    }
    $tickets = $stmt->fetchAll();
    
    // Debug information
    echo "<!-- Debug: Number of tickets found = " . count($tickets) . " -->";
    
} catch(PDOException $e) {
    error_log("Error in admin tickets: " . $e->getMessage());
    $error = "Error fetching tickets. Please try again later. Error: " . $e->getMessage();
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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Tickets - Admin Dashboard</title>
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
            max-width: 1200px;
            margin: 0 auto;
        }

        .tickets-container {
            background: white;
            padding: 2.5rem;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.08);
            position: relative;
            overflow: hidden;
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
            cursor: pointer;
        }

        .status-badge:hover {
            transform: translateY(-2px);
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

        .ticket-meta {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .ticket-user {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .ticket-user i {
            color: #5e7eb6;
        }

        .created-at {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .created-at i {
            color: #64748b;
        }

        .ticket-actions {
            display: flex;
            gap: 1rem;
            align-items: center;
        }

        .action-btn {
            color: #5e7eb6;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-weight: 600;
            transition: all 0.3s ease;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            background: rgba(94, 126, 182, 0.1);
        }

        .action-btn:hover {
            background: rgba(94, 126, 182, 0.2);
            transform: translateY(-2px);
        }

        .action-btn.delete {
            color: #ef4444;
            background: rgba(239, 68, 68, 0.1);
        }

        .action-btn.delete:hover {
            background: rgba(239, 68, 68, 0.2);
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

            .ticket-meta {
                flex-direction: column;
                align-items: flex-start;
                gap: 8px;
            }
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
    </style>
</head>
<body>
    <div class="container">
        <a href="admin_dashboard.php" class="back-link">
            <i class="fas fa-arrow-left"></i>
            Back to Dashboard
        </a>

        <div class="tickets-container">
            <div class="header">
                <div class="header-left">
                    <i class="fas fa-ticket-alt"></i>
                    <h1>Manage Tickets</h1>
                </div>
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
                    <p>There are no tickets in this category.</p>
                </div>
            <?php else: ?>
                <?php foreach ($tickets as $index => $ticket): ?>
                    <div class="ticket-card" style="--index: <?php echo $index; ?>">
                        <div class="ticket-header">
                            <h2 class="ticket-title"><?php echo htmlspecialchars($ticket['subject']); ?></h2>
                            <span class="status-badge status-<?php echo htmlspecialchars($ticket['status']); ?>" 
                                  onclick="updateStatus(<?php echo $ticket['id']; ?>, '<?php echo $ticket['status']; ?>')">
                                <i class="fas fa-<?php echo $ticket['status'] === 'open' ? 'exclamation-circle' : ($ticket['status'] === 'pending' ? 'clock' : 'check-circle'); ?>"></i>
                                <?php echo ucfirst(htmlspecialchars($ticket['status'])); ?>
                            </span>
                        </div>
                        <div class="ticket-description">
                            <?php echo htmlspecialchars($ticket['description']); ?>
                        </div>
                        <div class="ticket-footer">
                            <div class="ticket-meta">
                                <span class="ticket-user">
                                    <i class="fas fa-user"></i>
                                    <?php echo htmlspecialchars($ticket['username']); ?>
                                </span>
                                <span class="created-at">
                                    <i class="far fa-clock"></i>
                                    Created <?php echo timeAgo($ticket['created_at']); ?>
                                </span>
                            </div>
                            <div class="ticket-actions">
                                <a href="#" class="action-btn delete" onclick="deleteTicket(<?php echo $ticket['id']; ?>)">
                                    <i class="fas fa-trash"></i>
                                    Delete
                                </a>
                                <a href="admin_ticket_detail.php?id=<?php echo $ticket['id']; ?>" class="action-btn">
                                    <i class="fas fa-eye"></i>
                                    View Details
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <script>
        function updateStatus(ticketId, currentStatus) {
            const statuses = ['open', 'pending', 'resolved'];
            const currentIndex = statuses.indexOf(currentStatus);
            const nextStatus = statuses[(currentIndex + 1) % statuses.length];
            
            if (confirm(`Change ticket status to ${nextStatus}?`)) {
                window.location.href = `update_ticket_status.php?id=${ticketId}&status=${nextStatus}`;
            }
        }

        function deleteTicket(ticketId) {
            if (confirm('Are you sure you want to delete this ticket? This action cannot be undone.')) {
                window.location.href = `delete_ticket.php?id=${ticketId}`;
            }
        }
    </script>
</body>
</html> 