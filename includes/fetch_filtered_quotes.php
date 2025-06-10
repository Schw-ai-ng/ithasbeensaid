<?php
require_once '../config/db.php';

header('Content-Type: application/json');

// Allow empty string for "All" filter
$theme = isset($_GET['theme']) ? trim($_GET['theme']) : '';
$scheduled = isset($_GET['scheduled']) ? $_GET['scheduled'] : '';
$search = isset($_GET['search']) ? $_GET['search'] : '';

$query = "SELECT quotes.*, themes.name AS theme_name FROM quotes LEFT JOIN themes ON quotes.theme_id = themes.id WHERE 1=1 ";
$params = [];

if ($theme !== '') {
    $query .= " AND quotes.theme_id = ? ";
    $params[] = (int)$theme;
}

if ($scheduled === '1') {
    $query .= " AND quotes.scheduled_date IS NOT NULL ";
} elseif ($scheduled === '0') {
    $query .= " AND quotes.scheduled_date IS NULL ";
}

if (!empty($search)) {
    $query .= " AND (quotes.text LIKE ? OR quotes.author LIKE ?) ";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$query .= " ORDER BY quotes.scheduled_date DESC, quotes.id DESC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$quotes = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode(['success' => true, 'quotes' => $quotes]);
