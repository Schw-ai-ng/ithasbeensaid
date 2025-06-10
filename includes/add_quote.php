<?php
require_once '../config/db.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_quote'])) {
    // Sanitize and assign POST values
    $text = $_POST['text'] ?? '';
    $author = $_POST['author'] ?? '';
    $quote_year = $_POST['quote_year'] ?: null; // allow NULL for year
    $language = $_POST['language'] ?? null;
    $source = $_POST['source'] ?? null;
    $theme_id = $_POST['theme_id'] ?? null;

    // Validate required fields (text and theme_id)
    if (!$text || !$theme_id) {
        // Redirect back with error (could enhance with error messages)
        header("Location: ../dashboard/index.php?error=missing_fields");
        exit;
    }

    // Prepare and execute insert statement
    $stmt = $pdo->prepare("
        INSERT INTO quotes (text, author, quote_year, language, source, theme_id)
        VALUES (?, ?, ?, ?, ?, ?)
    ");

    $stmt->execute([
        $text,
        $author,
        $quote_year,
        $language,
        $source,
        $theme_id
    ]);

    // Redirect to index with success message
    header("Location: ../dashboard/index.php?add=ok");
    exit;
}
