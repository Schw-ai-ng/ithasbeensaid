<?php
require_once '../config/db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;
    $is_active = $_POST['is_active'] ?? null;

    if ($id === null || $is_active === null) {
        echo json_encode(['success' => false, 'message' => 'Missing parameters']);
        exit;
    }

    // Validate inputs
    $id = (int)$id;
    $is_active = ($is_active == 1) ? 1 : 0;

    try {
        $stmt = $pdo->prepare("UPDATE quotes SET is_active = :is_active WHERE id = :id");
        $stmt->execute([':is_active' => $is_active, ':id' => $id]);
        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
