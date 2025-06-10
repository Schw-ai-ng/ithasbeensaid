<?php
require_once '../config/db.php';

// Delete all quotes
$pdo->exec("DELETE FROM quotes");

// Redirect back with a message
header("Location: ../dashboard/index.php?delete_all=ok");
exit;
