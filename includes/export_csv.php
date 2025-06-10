<?php
require_once '../config/db.php';

// Set headers to force download of CSV file
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=quotes_export_' . date('Ymd') . '.csv');

// Open PHP output stream for writing CSV directly to the browser
$output = fopen('php://output', 'w');

// Write CSV header row matching import columns
fputcsv($output, [
    'text',
    'explanation',
    'author',
    'quote_year',
    'language',
    'source',
    'theme_name',
    'is_active',
    'scheduled_date'
]);

// Fetch all quotes joining themes to get theme name
$stmt = $pdo->query("
    SELECT q.text, q.explanation, q.author, q.quote_year, q.language, q.source, t.name AS theme_name, q.is_active, q.scheduled_date
    FROM quotes q
    LEFT JOIN themes t ON q.theme_id = t.id
    ORDER BY q.id ASC
");

// Write each row to CSV output
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    // Ensure nulls or empty values are handled correctly (optional)
    $row = array_map(function($value) {
        return $value === null ? '' : $value;
    }, $row);

    fputcsv($output, $row);
}

fclose($output);
exit;
