<?php
session_start();
require_once 'db_connect.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Not authenticated']);
    exit;
}

// Get ticket ID from request
$ticket_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (!$ticket_id) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid ticket ID']);
    exit;
}

try {
    // Get ticket details with user info
    $stmt = $pdo->prepare("
        SELECT t.*, u.first_name, u.email 
        FROM tickets t 
        LEFT JOIN users u ON t.user_id = u.id 
        WHERE t.id = ? AND t.user_id = ?
    ");
    $stmt->execute([$ticket_id, $_SESSION['user_id']]);
    $ticket = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$ticket) {
        http_response_code(404);
        echo json_encode(['error' => 'Ticket not found']);
        exit;
    }

    // Return ticket information
    echo json_encode([
        'status' => $ticket['status'],
        'admin_response' => $ticket['admin_response'] ?? null,
        'updated_at' => $ticket['updated_at'] ?? null
    ]);

} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Database error']);
    exit;
} 