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
    $stmt = $pdo->prepare("SELECT t.*, u.email as username 
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
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Poppins', 'Segoe UI', Arial, sans-serif;
            background: #f7fafd;
            min-height: 100vh;
            color: #232e3c;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        .dashboard-container {
            background: #fff;
            padding: 2.5rem;
            border-radius: 20px;
            box-shadow: 0 8px 32px rgba(68,124,245,0.07);
            position: relative;
            overflow: hidden;
            margin-top: 2rem;
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
            border-radius: 20px 20px 0 0;
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
            position: relative;
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
        .header-actions {
            display: flex;
            align-items: center;
            gap: 1.2rem;
        }
        .notif-bell {
            font-size: 1.3rem;
            color: #b0b8c9;
            cursor: pointer;
            position: relative;
            transition: color 0.18s;
        }
        .notif-bell:hover {
            color: #447cf5;
        }
        .notif-badge {
            position: absolute;
            top: -6px; right: -8px;
            background: #ef4444;
            color: #fff;
            font-size: 0.7rem;
            border-radius: 50%;
            padding: 2px 6px;
        }
        .admin-avatar {
            width: 38px; height: 38px;
            background: #5e7eb6;
            color: #fff;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            font-weight: 600;
            box-shadow: 0 2px 8px rgba(68,124,245,0.10);
            margin-left: 0.5rem;
            border: 2px solid #fff;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2.5rem;
        }
        .stat-card {
            background: #fff;
            padding: 1.5rem;
            border-radius: 16px;
            box-shadow: 0 4px 24px rgba(68,124,245,0.08);
            border: none;
            transition: box-shadow 0.2s, transform 0.2s;
            position: relative;
            cursor: pointer;
        }
        .stat-card:hover {
            box-shadow: 0 12px 36px rgba(68,124,245,0.18);
            transform: translateY(-4px) scale(1.025);
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
            background: #f0f4fa;
            color: #5e7eb6;
        }
        .stat-title {
            font-size: 1rem;
            color: #64748b;
            font-weight: 600;
        }
        .stat-value {
            font-size: 2.1rem;
            font-weight: 700;
            color: #232e3c;
        }
        .stat-change {
            font-size: 0.95rem;
            color: #10b981;
            display: flex;
            align-items: center;
            gap: 4px;
            margin-top: 0.5rem;
        }
        .stat-change.negative {
            color: #ef4444;
        }
        .divider {
            height: 1px;
            background: linear-gradient(90deg, #e3e8f0 0%, #fff 100%);
            margin: 2.5rem 0 2rem 0;
            border: none;
        }
        .recent-tickets {
            background: #fff;
            border-radius: 16px;
            padding: 1.5rem;
            box-shadow: 0 4px 24px rgba(68,124,245,0.08);
            border: none;
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
            transition: color 0.2s;
        }
        .view-all:hover {
            color: #447cf5;
        }
        .recent-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            background: transparent;
        }
        .recent-table thead th {
            position: sticky;
            top: 0;
            background: #f7fafd;
            color: #8ca0c9;
            font-size: 0.98rem;
            font-weight: 600;
            border-bottom: 2px solid #e3e8f0;
            z-index: 2;
        }
        .recent-table tbody tr {
            transition: background 0.18s;
        }
        .recent-table tbody tr:nth-child(even) {
            background: #f8fafc;
        }
        .recent-table tbody tr:hover {
            background: #eaf1fb;
        }
        .recent-table td, .recent-table th {
            padding: 0.9rem 0.7rem;
            text-align: left;
        }
        .status-badge {
            display: inline-block;
            padding: 0.25em 0.9em;
            border-radius: 999px;
            font-size: 0.93em;
            font-weight: 600;
            letter-spacing: 0.02em;
            background: #f0f4fa;
            color: #447cf5;
            border: none;
            transition: background 0.18s, color 0.18s;
        }
        .status-badge.open {
            background: #e9f7ef;
            color: #10b981;
        }
        .status-badge.pending {
            background: #fffbe6;
            color: #bfa100;
        }
        .status-badge.resolved {
            background: #e3e8f0;
            color: #5e7eb6;
        }
        .action-icons {
            display: flex;
            gap: 0.6rem;
        }
        .action-icons i {
            cursor: pointer;
            color: #b0b8c9;
            font-size: 1.05rem;
            transition: color 0.18s;
        }
        .action-icons i:hover {
            color: #447cf5;
        }
        @media (max-width: 900px) {
            .dashboard-container { padding: 1.2rem; }
            .stats-grid { grid-template-columns: 1fr; }
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
                <div class="header-actions">
                    <a href="logout.php" class="view-all" style="font-size:1.1rem;"><i class="fas fa-sign-out-alt"></i> Logout</a>
                </div>
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
                <table class="recent-table">
                    <thead>
                        <tr>
                            <th>Subject</th>
                            <th>User</th>
                            <th>Time</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (!empty($recent_tickets)): ?>
                        <?php foreach ($recent_tickets as $ticket): ?>
                            <tr>
                                <td><a href="admin_ticket_detail.php?id=<?php echo $ticket['id']; ?>" target="_blank" style="color:#447cf5;text-decoration:underline;font-weight:600;"><?php echo htmlspecialchars($ticket['subject']); ?></a></td>
                                <td>
                                    <span class="ticket-user">
                                        <i class="fas fa-user"></i>
                                        <?php echo htmlspecialchars($ticket['username']); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="ticket-time">
                                        <i class="far fa-clock"></i>
                                        <?php echo date('M d, Y', strtotime($ticket['created_at'])); ?>
                                    </span>
                                </td>
                                <td><span class="status-badge <?php echo strtolower($ticket['status']); ?>"><?php echo ucfirst($ticket['status']); ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="4" style="text-align:center;color:#b0b8c9;">No tickets found.</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html> 