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
    // Get ticket history
    $stmt = $pdo->prepare("
        SELECT th.*, u.first_name
        FROM ticket_history th
        LEFT JOIN users u ON th.user_id = u.id
        WHERE th.ticket_id = ?
        ORDER BY th.created_at DESC
    ");
    $stmt->execute([$ticket_id]);
    $history = $stmt->fetchAll();

    if (empty($history)) {
        echo json_encode(['history' => '<p>No history available.</p>']);
        exit;
    }

    // Format history into HTML
    $html = '<ul class="history-list">';
    foreach ($history as $entry) {
        $date = date('M j, Y g:i A', strtotime($entry['created_at']));
        $html .= '<li class="history-item">';
        $html .= '<div class="history-header">';
        $html .= '<span class="history-date">' . htmlspecialchars($date) . '</span>';
        $html .= '<span class="history-user">' . htmlspecialchars($entry['first_name']) . '</span>';
        $html .= '</div>';
        $html .= '<div class="history-content">' . htmlspecialchars($entry['action']) . '</div>';
        $html .= '</li>';
    }
    $html .= '</ul>';

    echo json_encode(['history' => $html]);

} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Database error']);
    exit;
} 