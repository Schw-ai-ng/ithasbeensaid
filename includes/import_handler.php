<?php
require_once '../config/db.php';

// Enable error reporting during development (disable in production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Set response type as JSON for AJAX communication
header('Content-Type: application/json');

// Step 1: Check if a CSV file has been uploaded
if (!isset($_FILES['csv_file'])) {
    echo json_encode(['success' => false, 'message' => 'No file uploaded.']);
    exit;
}

$file = $_FILES['csv_file']['tmp_name'];

// Step 2: Validate uploaded file presence and type
if (!is_uploaded_file($file)) {
    echo json_encode(['success' => false, 'message' => 'Invalid uploaded file.']);
    exit;
}

$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime = finfo_file($finfo, $file);
finfo_close($finfo);

// Acceptable MIME types for CSV files
$allowedMimes = ['text/plain', 'text/csv', 'application/vnd.ms-excel'];
if (!in_array($mime, $allowedMimes)) {
    echo json_encode(['success' => false, 'message' => "Invalid file type: $mime"]);
    exit;
}

// Step 3: Open the CSV file for reading
if (($handle = fopen($file, 'r')) === false) {
    echo json_encode(['success' => false, 'message' => 'Could not open uploaded file.']);
    exit;
}

// Step 4: Build a map of theme names (lowercase) to theme IDs for quick lookup
$themeMap = [];
foreach ($pdo->query("SELECT id, name FROM themes") as $row) {
    $themeMap[strtolower(trim($row['name']))] = (int)$row['id'];
}

// Step 5: Read and skip the header row of the CSV file
$header = fgetcsv($handle);

// Step 6: Prepare the SQL statement to insert quotes
$stmt = $pdo->prepare("
    INSERT INTO quotes (text, explanation, author, quote_year, language, source, theme_id, is_active, scheduled_date)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
");

$rowCount = 0;

// Step 7: Process each CSV row
while (($data = fgetcsv($handle, 2000, ',')) !== false) {
    // Skip invalid rows with insufficient columns or empty rows
    if (count($data) < 9 || empty(array_filter($data))) {
        continue;
    }

    // Map CSV columns to variables
    list($text, $explanation, $author, $year, $language, $source, $themeName, $isActive, $scheduledDate) = $data;

    // Step 8: Normalize theme name and lookup its ID
    $themeId = $themeMap[strtolower(trim($themeName))] ?? null;

    // Skip rows where theme name is invalid or not found
    if ($themeId === null) {
        continue;
    }

    // Step 9: Sanitize and validate data before insertion
    $year = is_numeric($year) ? (int)$year : null;
    $isActive = ($isActive == '1') ? 1 : 0;
    $scheduledDate = !empty($scheduledDate) ? $scheduledDate : null;

    // Step 10: Insert the quote row into the database
    try {
        $stmt->execute([
            $text,
            $explanation,
            $author,
            $year,
            $language,
            $source,
            $themeId,
            $isActive,
            $scheduledDate
        ]);
        $rowCount++;
    } catch (PDOException $e) {
        // Optionally log the error, then skip to next row
        continue;
    }
}

fclose($handle);

// Step 11: Return JSON response indicating success and how many rows were imported
echo json_encode(['success' => true, 'imported' => $rowCount]);
exit;
