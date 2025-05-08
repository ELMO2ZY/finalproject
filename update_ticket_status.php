<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

require_once 'db_connect.php';

$ticket_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$new_status = isset($_GET['status']) ? $_GET['status'] : '';
$redirect = isset($_GET['redirect']) ? $_GET['redirect'] : '';

// Validate status
$valid_statuses = ['open', 'pending', 'resolved'];
if (!$ticket_id || !in_array($new_status, $valid_statuses)) {
    header("Location: " . ($redirect === 'admin' ? 'admin_tickets.php' : 'view_tickets.php'));
    exit();
}

try {
    // Start transaction
    $pdo->beginTransaction();

    // Check if user has permission to update this ticket
    if ($_SESSION['user_type'] !== 'admin') {
        $stmt = $pdo->prepare("SELECT user_id FROM tickets WHERE id = ?");
        $stmt->execute([$ticket_id]);
        $ticket = $stmt->fetch();

        if (!$ticket || $ticket['user_id'] !== $_SESSION['user_id']) {
            throw new Exception("You don't have permission to update this ticket.");
        }
    }

    // Get current status
    $stmt = $pdo->prepare("SELECT status FROM tickets WHERE id = ?");
    $stmt->execute([$ticket_id]);
    $current_status = $stmt->fetch()['status'];

    // Only update if status is different
    if ($current_status !== $new_status) {
        // Update ticket status
        $stmt = $pdo->prepare("UPDATE tickets SET status = ? WHERE id = ?");
        $stmt->execute([$new_status, $ticket_id]);

        // Record in history
        $stmt = $pdo->prepare("
            INSERT INTO ticket_history (ticket_id, user_id, action, new_value, description, created_at)
            VALUES (?, ?, 'status_change', ?, ?, NOW())
        ");
        $description = "Status changed from " . ucfirst($current_status) . " to " . ucfirst($new_status);
        $stmt->execute([$ticket_id, $_SESSION['user_id'], $new_status, $description]);
    }

    // Commit transaction
    $pdo->commit();

    // Redirect based on user type
    if ($redirect === 'admin') {
        header("Location: admin_ticket_detail.php?id=" . $ticket_id);
    } else {
        header("Location: ticket_detail.php?id=" . $ticket_id);
    }
    
} catch (Exception $e) {
    // Rollback transaction on error
    $pdo->rollBack();
    error_log("Error updating ticket status: " . $e->getMessage());
    
    // Redirect with error
    $error = urlencode("Error updating ticket status. Please try again later.");
    if ($redirect === 'admin') {
        header("Location: admin_ticket_detail.php?id=" . $ticket_id . "&error=" . $error);
    } else {
        header("Location: ticket_detail.php?id=" . $ticket_id . "&error=" . $error);
    }
}
exit(); 