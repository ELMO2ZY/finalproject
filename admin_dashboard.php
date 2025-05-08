<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: login.php");
    exit();
}

require_once 'db_connect.php';

// Initialize variables with default values
$total_tickets = 0;
$total_users = 0;
$status_counts = [
    'open' => 0,
    'pending' => 0,
    'resolved' => 0
];
$recent_tickets = [];

// Fetch dashboard statistics
try {
    // Get total tickets count
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM tickets");
    $total_tickets = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

    // Get tickets by status
    $stmt = $pdo->prepare("SELECT status, COUNT(*) as count FROM tickets GROUP BY status");
    $stmt->execute();
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $status_counts[$row['status']] = $row['count'];
    }

    // Get recent tickets with user information
    $stmt = $pdo->prepare("SELECT t.*, u.username 
                          FROM tickets t 
                          JOIN users u ON t.user_id = u.id 
                          ORDER BY t.created_at DESC 
                          LIMIT 5");
    $stmt->execute();
    $recent_tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get total users count
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM users");
    $stmt->execute();
    $total_users = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

} catch(PDOException $e) {
    error_log("Error in admin dashboard: " . $e->getMessage());
    $error = "Error fetching dashboard data. Please try again later.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Support Ticket System</title>
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

        .dashboard-container {
            background: white;
            padding: 2.5rem;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.08);
            position: relative;
            overflow: hidden;
            backdrop-filter: blur(10px);
        }

        .dashboard-container::before {
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

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2.5rem;
        }

        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 16px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            border: 1px solid #e2e8f0;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
            border-color: #5e7eb6;
        }

        .stat-header {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 1rem;
        }

        .stat-icon {
            width: 40px;
            height: 40px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
        }

        .stat-icon.tickets {
            background: rgba(94, 126, 182, 0.1);
            color: #5e7eb6;
        }

        .stat-icon.users {
            background: rgba(16, 185, 129, 0.1);
            color: #10b981;
        }

        .stat-icon.open {
            background: rgba(239, 68, 68, 0.1);
            color: #ef4444;
        }

        .stat-icon.pending {
            background: rgba(245, 158, 11, 0.1);
            color: #f59e0b;
        }

        .stat-title {
            font-size: 0.95rem;
            color: #64748b;
            font-weight: 600;
        }

        .stat-value {
            font-size: 2rem;
            color: #2d3748;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .stat-change {
            font-size: 0.9rem;
            color: #10b981;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .stat-change.negative {
            color: #ef4444;
        }

        .recent-tickets {
            background: white;
            border-radius: 16px;
            padding: 1.5rem;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            border: 1px solid #e2e8f0;
        }

        .section-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1.5rem;
        }

        .section-title {
            font-size: 1.25rem;
            color: #2d3748;
            font-weight: 700;
        }

        .view-all {
            color: #5e7eb6;
            text-decoration: none;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: all 0.3s ease;
        }

        .view-all:hover {
            color: #447cf5;
        }

        .ticket-list {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .ticket-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1rem;
            border-radius: 12px;
            background: #f8fafc;
            transition: all 0.3s ease;
        }

        .ticket-item:hover {
            background: #f1f5f9;
            transform: translateX(4px);
        }

        .ticket-info {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .ticket-title {
            font-weight: 600;
            color: #2d3748;
        }

        .ticket-meta {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 0.9rem;
            color: #64748b;
        }

        .ticket-user {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .ticket-user i {
            color: #5e7eb6;
        }

        .ticket-time {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .ticket-time i {
            color: #64748b;
        }

        .ticket-status {
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 6px;
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

        @media (max-width: 768px) {
            body {
                padding: 1rem;
            }

            .dashboard-container {
                padding: 1.5rem;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }

            .header h1 {
                font-size: 1.75rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">

        <div class="dashboard-container">
            <div class="header">
                <div class="header-left">
                    <i class="fas fa-shield-alt"></i>
                    <h1>Admin Dashboard</h1>
                </div>
                <a href="logout.php" class="view-all">
                    <i class="fas fa-sign-out-alt"></i>
                    Logout
                </a>
            </div>

            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-icon tickets">
                            <i class="fas fa-ticket-alt"></i>
                        </div>
                        <span class="stat-title">Total Tickets</span>
                    </div>
                    <div class="stat-value"><?php echo $total_tickets; ?></div>
                    <div class="stat-change">
                        <i class="fas fa-arrow-up"></i>
                        <span>12% from last month</span>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-icon users">
                            <i class="fas fa-users"></i>
                        </div>
                        <span class="stat-title">Total Users</span>
                    </div>
                    <div class="stat-value"><?php echo $total_users; ?></div>
                    <div class="stat-change">
                        <i class="fas fa-arrow-up"></i>
                        <span>5% from last month</span>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-icon open">
                            <i class="fas fa-exclamation-circle"></i>
                        </div>
                        <span class="stat-title">Open Tickets</span>
                    </div>
                    <div class="stat-value"><?php echo $status_counts['open']; ?></div>
                    <div class="stat-change">
                        <i class="fas fa-arrow-up"></i>
                        <span>8% from last week</span>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-icon pending">
                            <i class="fas fa-clock"></i>
                        </div>
                        <span class="stat-title">Pending Tickets</span>
                    </div>
                    <div class="stat-value"><?php echo $status_counts['pending']; ?></div>
                    <div class="stat-change negative">
                        <i class="fas fa-arrow-down"></i>
                        <span>3% from last week</span>
                    </div>
                </div>
            </div>

            <div class="recent-tickets">
                <div class="section-header">
                    <h2 class="section-title">Recent Tickets</h2>
                    <a href="admin_tickets.php" class="view-all">
                        View All
                        <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
                <div class="ticket-list">
                    <?php foreach ($recent_tickets as $ticket): ?>
                        <div class="ticket-item">
                            <div class="ticket-info">
                                <div class="ticket-title"><?php echo htmlspecialchars($ticket['subject']); ?></div>
                                <div class="ticket-meta">
                                    <span class="ticket-user">
                                        <i class="fas fa-user"></i>
                                        <?php echo htmlspecialchars($ticket['username']); ?>
                                    </span>
                                    <span class="ticket-time">
                                        <i class="far fa-clock"></i>
                                        <?php echo date('M d, Y', strtotime($ticket['created_at'])); ?>
                                    </span>
                                </div>
                            </div>
                            <span class="ticket-status status-<?php echo $ticket['status']; ?>">
                                <i class="fas fa-<?php echo $ticket['status'] === 'open' ? 'exclamation-circle' : ($ticket['status'] === 'pending' ? 'clock' : 'check-circle'); ?>"></i>
                                <?php echo ucfirst($ticket['status']); ?>
                            </span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html> 