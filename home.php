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
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: #f5f7fa;
            min-height: 100vh;
        }

        /* Top Bar Styles */
        .top-bar {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            height: 60px;
            background: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 2rem;
            z-index: 1000;
        }

        .welcome-text {
            font-size: 2rem;
            color: #5e7eb6;
            font-weight: 700;
            letter-spacing: 0.03em;
            padding-left: 0.5rem;
        }

        .empty-illustration {
            margin-bottom: 1.5rem;
            width: 120px;
            height: 120px;
        }

        .loading-spinner {
            display: flex;
            align-items: center;
            justify-content: center;
            height: 300px;
        }

        .spinner {
            border: 6px solid #e3e8f0;
            border-top: 6px solid #5e7eb6;
            border-radius: 50%;
            width: 48px;
            height: 48px;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .user-profile {
            display: flex;
            align-items: center;
            gap: 1rem;
            cursor: pointer;
            position: relative;
        }

        .user-circle {
            width: 40px;
            height: 40px;
            background: #5e7eb6;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
        }

        .user-dropdown {
            position: absolute;
            top: 100%;
            right: 0;
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            padding: 0.5rem;
            display: none;
            min-width: 150px;
        }

        .user-dropdown.show {
            display: block;
        }

        .dropdown-item {
            padding: 0.75rem 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: #333;
            text-decoration: none;
            transition: background 0.3s;
            border-radius: 4px;
        }

        .dropdown-item:hover {
            background: #f5f7fa;
        }

        /* Sidebar Styles */
        .sidebar {
            position: fixed;
            left: 0;
            top: 60px;
            bottom: 0;
            width: 250px;
            background: white;
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
            padding: 2rem 0;
            z-index: 900;
        }

        .nav-item {
            padding: 1rem 2rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            color: #333;
            text-decoration: none;
            transition: all 0.3s;
        }

        .nav-item:hover, .nav-item.active {
            background: #f5f7fa;
            color: #5e7eb6;
        }

        .nav-item i {
            width: 20px;
            text-align: center;
        }

        /* Main Content Styles */
        .main-content {
            margin-left: 250px;
            margin-top: 60px;
            padding: 2rem;
            min-height: calc(100vh - 60px);
        }

        .tickets-container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 2rem;
            min-height: 400px;
        }

        .no-tickets {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 400px;
            color: #666;
            text-align: center;
        }

        .no-tickets i {
            font-size: 4rem;
            color: #ddd;
            margin-bottom: 1rem;
        }

        .create-ticket-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.4rem;
            background: #5e7eb6;
            color: white;
            padding: 0.5rem 1.5rem;
            border-radius: 8px;
            font-size: 1.15rem;
            line-height: 1.2;
            text-decoration: none;
            transition: all 0.3s;
            margin-top: 1rem;
        }

        .create-ticket-btn:hover {
            background: #4a6da7;
            transform: translateY(-2px);
        }

        .create-ticket-btn i {
            font-size: 1.3rem;
            font-weight: 400;
            color: inherit;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin: 0 0.3rem 0 0;
        }

        /* Success Message Styles */
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

        .tickets-list {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }
        .ticket-link {
            text-decoration: none;
            color: inherit;
            display: block;
            border-radius: 16px;
            transition: box-shadow 0.3s, transform 0.3s;
        }
        .ticket-link:focus {
            outline: 2px solid #5e7eb6;
        }
        .ticket-item {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 4px 24px rgba(94,126,182,0.08), 0 1.5px 4px rgba(94,126,182,0.06);
            padding: 1.5rem 2rem;
            display: flex;
            align-items: flex-start;
            gap: 1.5rem;
            transition: box-shadow 0.3s, transform 0.3s;
            position: relative;
        }
        .ticket-link:hover .ticket-item {
            box-shadow: 0 8px 32px rgba(94,126,182,0.16), 0 3px 8px rgba(94,126,182,0.10);
            transform: translateY(-2px) scale(1.01);
        }
        .ticket-icon {
            font-size: 2.2rem;
            color: #5e7eb6;
            flex-shrink: 0;
            margin-top: 0.2rem;
        }
        .ticket-content {
            flex: 1;
        }
        .ticket-title {
            font-size: 1.3rem;
            font-weight: 700;
            color: #222;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .ticket-desc {
            color: #555;
            margin-bottom: 0.7rem;
        }
        .ticket-meta {
            display: flex;
            align-items: center;
            gap: 1.2rem;
            font-size: 0.98rem;
            color: #888;
        }
        .status-badge {
            display: inline-block;
            padding: 0.3em 1em;
            border-radius: 999px;
            font-size: 0.95em;
            font-weight: 600;
            letter-spacing: 0.03em;
            background: #e9f7ef;
            color: #219150;
            border: 1px solid #b6e2c6;
        }
        .status-badge.closed {
            background: #fdeaea;
            color: #d7263d;
            border: 1px solid #f5b6b6;
        }
        .status-badge.pending {
            background: #fffbe6;
            color: #bfa100;
            border: 1px solid #f5eab6;
        }
        .sort-bar {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        .sort-label {
            font-weight: 500;
            color: #425981;
        }
        .sort-select {
            padding: 0.5rem 1rem;
            border-radius: 6px;
            border: 1px solid #c7d3ee;
            font-size: 1rem;
            color: #425981;
            background: #f5f7fa;
        }
        .modal-overlay {
            display: none;
            position: fixed;
            z-index: 2000;
            left: 0;
            top: 0;
            width: 100vw;
            height: 100vh;
            background: rgba(30, 41, 59, 0.25);
            align-items: center;
            justify-content: center;
        }
        .modal-overlay.active {
            display: flex;
        }
        .modal {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 8px 32px rgba(94,126,182,0.16);
            padding: 2rem 2.5rem;
            min-width: 340px;
            max-width: 95vw;
            max-height: 90vh;
            overflow-y: auto;
            position: relative;
        }
        .modal-close {
            position: absolute;
            top: 1rem;
            right: 1rem;
            background: none;
            border: none;
            font-size: 1.5rem;
            color: #5e7eb6;
            cursor: pointer;
        }
        .modal-title {
            font-size: 1.4rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            color: #222;
        }
        .modal-desc {
            color: #555;
            margin-bottom: 1rem;
        }
        .modal-meta {
            color: #888;
            font-size: 1rem;
            margin-bottom: 1rem;
        }
        .modal-status {
            margin-bottom: 1rem;
        }
        .modal-actions {
            display: flex;
            gap: 1rem;
            margin-top: 1.5rem;
        }
        .modal-btn {
            padding: 0.7rem 1.5rem;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            border: none;
            font-size: 1rem;
            transition: 
                background 0.3s, 
                color 0.3s, 
                box-shadow 0.3s, 
                transform 0.15s cubic-bezier(0.4,0,0.2,1);
            box-shadow: 0 2px 8px rgba(94,126,182,0.08);
            margin-right: 0.5rem;
        }
        .modal-btn:active {
            transform: scale(0.97);
        }
        .modal-btn-delete {
            background: #ef4444;
            color: white;
            box-shadow: 0 4px 16px rgba(239,68,68,0.10);
        }
        .modal-btn-delete:hover {
            background: #dc2626;
            box-shadow: 0 8px 24px rgba(239,68,68,0.18);
        }
        .modal-btn-view {
            background: #5e7eb6;
            color: white;
            box-shadow: 0 4px 16px rgba(94,126,182,0.10);
        }
        .modal-btn-view:hover {
            background: #447cf5;
            box-shadow: 0 8px 24px rgba(94,126,182,0.18);
        }
        .modal-btn-cancel {
            background: #f1f5f9;
            color: #64748b;
        }
        .modal-btn-cancel:hover {
            background: #e2e8f0;
        }
        .toast {
            position: fixed;
            bottom: 2rem;
            right: 2rem;
            background: #5e7eb6;
            color: white;
            padding: 1rem 2rem;
            border-radius: 8px;
            box-shadow: 0 4px 16px rgba(94,126,182,0.15);
            font-size: 1.1rem;
            opacity: 0;
            pointer-events: none;
            z-index: 3000;
            transition: opacity 0.4s, transform 0.4s;
            transform: translateY(30px);
        }
        .toast.show {
            opacity: 1;
            pointer-events: auto;
            transform: translateY(0);
        }
    </style>
</head>
<body>
    <!-- Top Bar -->
    <div class="top-bar">
        <div class="welcome-text">Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?>!</div>
        <div class="user-profile" id="userProfile">
            <div class="user-circle">
                <?php echo strtoupper(substr($_SESSION['user_name'], 0, 1)); ?>
            </div>
            <span><?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
            <div class="user-dropdown" id="userDropdown">
                <a href="logout.php" class="dropdown-item">
                    <i class="fas fa-sign-out-alt"></i>
                    Logout
                </a>
            </div>
        </div>
    </div>

    <!-- Sidebar -->
    <div class="sidebar">
        <a href="home.php" class="nav-item active">
            <i class="fas fa-home"></i>
            Home
        </a>
        <a href="create_ticket.php" class="nav-item">
            <i class="fas fa-plus"></i>
            Create Ticket
        </a>
        <a href="view_tickets.php" class="nav-item">
            <i class="fas fa-ticket-alt"></i>
            View Tickets
        </a>
        <a href="profile.php" class="nav-item">
            <i class="fas fa-user"></i>
            Profile
        </a>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Success Message -->
        <div id="successMessage" class="success-message">
            <i class="fas fa-check-circle"></i> Ticket created successfully!
        </div>

        <!-- Sort Bar -->
        <div class="sort-bar">
            <span class="sort-label">Sort by:</span>
            <select id="sortSelect" class="sort-select">
                <option value="newest">Newest</option>
                <option value="oldest">Oldest</option>
                <option value="status">Status</option>
            </select>
        </div>

        <div class="tickets-container">
            <?php
            require_once 'db_connect.php';
            // Fetch tickets for the current user
            $stmt = $pdo->prepare("SELECT * FROM tickets WHERE user_id = ? ORDER BY created_at DESC");
            $stmt->execute([$_SESSION['user_id']]);
            $tickets = $stmt->fetchAll();
            if (empty($tickets)) {
                echo '<div class="no-tickets">';
                echo '<div class="empty-illustration">';
                // SVG illustration
                echo '<svg width="100%" height="100%" viewBox="0 0 120 120" fill="none" xmlns="http://www.w3.org/2000/svg">';
                echo '<ellipse cx="60" cy="100" rx="40" ry="10" fill="#e3e8f0"/>';
                echo '<rect x="30" y="30" width="60" height="40" rx="8" fill="#5e7eb6"/>';
                echo '<rect x="40" y="40" width="40" height="8" rx="4" fill="#fff"/>';
                echo '<rect x="40" y="52" width="25" height="6" rx="3" fill="#c7d3ee"/>';
                echo '</svg>';
                echo '</div>';
                echo '<h2>No tickets yet</h2>';
                echo '<p>Create your first support ticket to get started</p>';
                echo '<a href="create_ticket.php" class="create-ticket-btn">';
                echo '<i class="fas fa-plus"></i>';
                echo 'Create Ticket';
                echo '</a>';
                echo '</div>';
            } else {
                // Display tickets in a modern card style
                echo '<div class="tickets-list" id="ticketsList">';
                foreach ($tickets as $ticket) {
                    $status = strtolower($ticket['status']);
                    $statusClass = 'status-badge';
                    if ($status === 'closed') $statusClass .= ' closed';
                    if ($status === 'pending') $statusClass .= ' pending';
                    $ticketData = htmlspecialchars(json_encode([
                        'id' => $ticket['id'],
                        'subject' => $ticket['subject'],
                        'description' => $ticket['description'],
                        'status' => $ticket['status'],
                        'created_at' => $ticket['created_at'],
                    ]), ENT_QUOTES, 'UTF-8');
                    echo '<div class="ticket-link" tabindex="0" data-ticket="' . $ticketData . '">';
                    echo '<div class="ticket-item">';
                    echo '<div class="ticket-icon"><i class="fas fa-ticket-alt"></i></div>';
                    echo '<div class="ticket-content">';
                    echo '<div class="ticket-title">' . htmlspecialchars($ticket['subject']) . '</div>';
                    echo '<div class="ticket-desc">' . htmlspecialchars($ticket['description']) . '</div>';
                    echo '<div class="ticket-meta">';
                    echo '<span class="' . $statusClass . '">' . htmlspecialchars(ucfirst($ticket['status'])) . '</span>';
                    echo '<span><i class="far fa-calendar-alt"></i> ' . htmlspecialchars(date('M d, Y', strtotime($ticket['created_at']))) . '</span>';
                    echo '</div>';
                    echo '</div>';
                    echo '</div>';
                    echo '</div>';
                }
                echo '</div>';
            }
            ?>
        </div>

        <!-- Ticket Details Modal -->
        <div class="modal-overlay" id="ticketModal">
            <div class="modal">
                <button class="modal-close" id="closeModalBtn">&times;</button>
                <div class="modal-title" id="modalTitle"></div>
                <div class="modal-desc" id="modalDesc"></div>
                <div class="modal-meta" id="modalMeta"></div>
                <div class="modal-status" id="modalStatus"></div>
                <div class="modal-actions">
                    <button class="modal-btn modal-btn-view" id="viewTicketBtn">View</button>
                    <button class="modal-btn modal-btn-delete" id="deleteTicketBtn">Delete</button>
                </div>
            </div>
        </div>

        <!-- Toast Notification -->
        <div class="toast" id="toast"></div>

        <!-- Confirmation Dialog -->
        <div class="modal-overlay" id="confirmModal">
            <div class="modal">
                <div class="modal-title">Delete Ticket</div>
                <div class="modal-desc">Are you sure you want to delete this ticket? This action cannot be undone.</div>
                <div class="modal-actions">
                    <button class="modal-btn modal-btn-delete" id="confirmDeleteBtn">Delete</button>
                    <button class="modal-btn modal-btn-cancel" id="cancelDeleteBtn">Cancel</button>
                </div>
            </div>
        </div>

    </div>

    <script>
        // User dropdown toggle
        const userProfile = document.getElementById('userProfile');
        const userDropdown = document.getElementById('userDropdown');

        userProfile.addEventListener('click', () => {
            userDropdown.classList.toggle('show');
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', (e) => {
            if (!userProfile.contains(e.target)) {
                userDropdown.classList.remove('show');
            }
        });

        // Success message handling
        if (sessionStorage.getItem('ticketSuccess')) {
            const successMessage = document.getElementById('successMessage');
            successMessage.style.display = 'block';
            sessionStorage.removeItem('ticketSuccess');
            setTimeout(() => {
                successMessage.style.display = 'none';
            }, 3000);
        }

        // Toast Notification
        function showToast(message) {
            const toast = document.getElementById('toast');
            toast.textContent = message;
            toast.classList.add('show');
            setTimeout(() => {
                toast.classList.remove('show');
            }, 3000);
        }

        // Modal Logic
        const ticketModal = document.getElementById('ticketModal');
        const confirmModal = document.getElementById('confirmModal');
        let currentTicketId = null;
        let currentTicketDetailUrl = null;

        function openTicketModal(ticket) {
            document.getElementById('modalTitle').textContent = ticket.subject;
            document.getElementById('modalDesc').textContent = ticket.description;
            document.getElementById('modalMeta').innerHTML = `<i class='far fa-calendar-alt'></i> Created ${new Date(ticket.created_at).toLocaleDateString()}`;
            document.getElementById('modalStatus').innerHTML = `<span class='status-badge'>${ticket.status.charAt(0).toUpperCase() + ticket.status.slice(1)}</span>`;
            ticketModal.classList.add('active');
            currentTicketId = ticket.id;
            currentTicketDetailUrl = `ticket_detail.php?id=${ticket.id}`;
        }

        function closeTicketModal() {
            ticketModal.classList.remove('active');
            currentTicketId = null;
            currentTicketDetailUrl = null;
        }

        document.getElementById('closeModalBtn').onclick = closeTicketModal;

        // Open modal on ticket click
        document.querySelectorAll('.ticket-link').forEach(link => {
            link.addEventListener('click', function() {
                const ticket = JSON.parse(this.getAttribute('data-ticket'));
                openTicketModal(ticket);
            });
            link.addEventListener('keydown', function(e) {
                if (e.key === 'Enter' || e.key === ' ') {
                    const ticket = JSON.parse(this.getAttribute('data-ticket'));
                    openTicketModal(ticket);
                }
            });
        });

        // Delete logic
        document.getElementById('deleteTicketBtn').onclick = function() {
            confirmModal.classList.add('active');
        };
        document.getElementById('cancelDeleteBtn').onclick = function() {
            confirmModal.classList.remove('active');
        };
        document.getElementById('confirmDeleteBtn').onclick = function() {
            if (currentTicketId) {
                // AJAX delete
                fetch(`delete_ticket.php?id=${currentTicketId}`)
                    .then(() => {
                        closeTicketModal();
                        confirmModal.classList.remove('active');
                        showToast('Ticket deleted successfully!');
                        // Remove ticket from DOM
                        document.querySelectorAll('.ticket-link').forEach(link => {
                            const ticket = JSON.parse(link.getAttribute('data-ticket'));
                            if (ticket.id == currentTicketId) {
                                link.remove();
                            }
                        });
                    });
            }
        };

        // View logic
        document.getElementById('viewTicketBtn').onclick = function() {
            if (currentTicketDetailUrl) {
                window.open(currentTicketDetailUrl, '_blank');
            }
        };

        // Sorting logic
        document.getElementById('sortSelect').addEventListener('change', function() {
            const value = this.value;
            const list = document.getElementById('ticketsList');
            const cards = Array.from(list.children);
            cards.sort((a, b) => {
                const ticketA = JSON.parse(a.getAttribute('data-ticket'));
                const ticketB = JSON.parse(b.getAttribute('data-ticket'));
                if (value === 'newest') {
                    return new Date(ticketB.created_at) - new Date(ticketA.created_at);
                } else if (value === 'oldest') {
                    return new Date(ticketA.created_at) - new Date(ticketB.created_at);
                } else if (value === 'status') {
                    return ticketA.status.localeCompare(ticketB.status);
                }
                return 0;
            });
            cards.forEach(card => list.appendChild(card));
        });

        // Close modals with Escape key or click outside
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeTicketModal();
                confirmModal.classList.remove('active');
            }
        });
        ticketModal.addEventListener('click', function(e) {
            if (e.target === ticketModal) closeTicketModal();
        });
        confirmModal.addEventListener('click', function(e) {
            if (e.target === confirmModal) confirmModal.classList.remove('active');
        });
    </script>
</body>
</html>