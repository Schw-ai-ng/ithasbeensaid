<?php
require_once '../config/db.php';

if (isset($_GET['id'])) {
    $id = (int) $_GET['id'];
    $stmt = $pdo->prepare("DELETE FROM quotes WHERE id = ?");
    $stmt->execute([$id]);
}

header("Location: ../dashboard/index.php?delete=ok");
exit;
