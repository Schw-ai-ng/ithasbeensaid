<?php
require_once '../config/db.php';

header('Content-Type: application/json');

$theme_id = isset($_GET['theme_id']) ? trim($_GET['theme_id']) : '';

try {
    if ($theme_id === '') {
        // Get all quotes (with scheduling status)
        $stmt = $pdo->query("SELECT id, text, scheduled_date FROM quotes ORDER BY id DESC");
    } else {
        $stmt = $pdo->prepare("SELECT id, text, scheduled_date FROM quotes WHERE theme_id = ? ORDER BY id DESC");
        $stmt->execute([(int)$theme_id]);
    }

    $quotes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['success' => true, 'quotes' => $quotes]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
