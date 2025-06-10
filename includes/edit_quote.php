<?php
require_once '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_edit'])) {
    // Sanitize and assign POST values
    $quote_id = $_POST['quote_id'] ?? null;
    $text = $_POST['text'] ?? '';
    $author = $_POST['author'] ?? '';
    $quote_year = $_POST['quote_year'] ?: null;  // allow NULL for year
    $theme_id = $_POST['theme_id'] ?? null;

    // Validate required fields
    if (!$quote_id || !$text || !$theme_id) {
        // Redirect back with error (optional: improve with error message)
        header("Location: ../dashboard/index.php?error=missing_fields");
        exit;
    }

    // Prepare and execute update statement
    $stmt = $pdo->prepare("
        UPDATE quotes 
        SET text = ?, author = ?, quote_year = ?, theme_id = ? 
        WHERE id = ?
    ");

    $stmt->execute([
        $text,
        $author,
        $quote_year,
        $theme_id,
        $quote_id
    ]);

    // Redirect to index with success message
    header("Location: ../dashboard/index.php?edit=ok");
    exit;
}
