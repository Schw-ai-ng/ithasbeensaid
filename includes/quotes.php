<?php
require_once '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['schedule_quote'])) {
    $quoteId = $_POST['quote_id'] ?? null;
    $date = $_POST['scheduled_date'] ?? null;

    if ($quoteId && $date) {
        $stmt = $pdo->prepare("UPDATE quotes SET scheduled_date = ? WHERE id = ?");
        $stmt->execute([$date, $quoteId]);

        // Redirect back to dashboard with success flag
        header("Location: ../dashboard/index.php?schedule=ok");
        exit;
    } else {
        // Redirect with error (optional)
        header("Location: ../dashboard/index.php?schedule=fail");
        exit;
    }
}
?>
