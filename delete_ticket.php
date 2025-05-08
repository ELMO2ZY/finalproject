<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

require_once 'db_connect.php';

// Check if ticket ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: view_tickets.php?error=Invalid ticket ID");
    exit();
}

$ticket_id = $_GET['id'];
$user_id = $_SESSION['user_id'];

try {
    // First verify that the ticket belongs to the user
    $stmt = $pdo->prepare("SELECT id FROM tickets WHERE id = ? AND user_id = ?");
    $stmt->execute([$ticket_id, $user_id]);
    
    if ($stmt->rowCount() === 0) {
        // Ticket doesn't exist or doesn't belong to the user
        header("Location: view_tickets.php?error=You don't have permission to delete this ticket");
        exit();
    }

    // Delete the ticket
    $stmt = $pdo->prepare("DELETE FROM tickets WHERE id = ? AND user_id = ?");
    $stmt->execute([$ticket_id, $user_id]);

    // Redirect back with success message
    header("Location: view_tickets.php?success=Ticket deleted successfully");
    exit();

} catch(PDOException $e) {
    // Log the error and redirect with error message
    error_log("Error deleting ticket: " . $e->getMessage());
    header("Location: view_tickets.php?error=Failed to delete ticket");
    exit();
}
?>