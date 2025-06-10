<?php
// filter_helpers.php

/**
 * Build dynamic WHERE clause and parameters array for quotes filtering
 *
 * @param array $filters $_GET or any input array with keys:
 *                       'theme' (array of theme IDs),
 *                       'scheduled' ('1' or '0'),
 *                       'search' (string),
 *                       'date_from' (YYYY-MM-DD),
 *                       'date_to' (YYYY-MM-DD)
 * @return array ['where' => string, 'params' => array]
 */
function buildQuoteFilters(array $filters): array {
    $whereClauses = [];
    $params = [];

    // Filter by themes (multi-select)
    if (!empty($filters['theme']) && is_array($filters['theme'])) {
        // Clean theme IDs - only integers allowed
        $themes = array_filter($filters['theme'], fn($t) => ctype_digit(strval($t)));
        if (count($themes) > 0) {
            $placeholders = implode(',', array_fill(0, count($themes), '?'));
            $whereClauses[] = "quotes.theme_id IN ($placeholders)";
            $params = array_merge($params, $themes);
        }
    }

    // Scheduled filter
    if (isset($filters['scheduled'])) {
        if ($filters['scheduled'] === '1') {
            $whereClauses[] = "quotes.scheduled_date IS NOT NULL";
        } elseif ($filters['scheduled'] === '0') {
            $whereClauses[] = "quotes.scheduled_date IS NULL";
        }
    }

    // Search filter (quote text or author)
    if (!empty($filters['search'])) {
        $search = "%{$filters['search']}%";
        $whereClauses[] = "(quotes.text LIKE ? OR quotes.author LIKE ?)";
        $params[] = $search;
        $params[] = $search;
    }

    // Date range filters
    if (!empty($filters['date_from']) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $filters['date_from'])) {
        $whereClauses[] = "quotes.scheduled_date >= ?";
        $params[] = $filters['date_from'];
    }
    if (!empty($filters['date_to']) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $filters['date_to'])) {
        $whereClauses[] = "quotes.scheduled_date <= ?";
        $params[] = $filters['date_to'];
    }

    // Compose WHERE clause
    $where = '';
    if (!empty($whereClauses)) {
        $where = 'WHERE ' . implode(' AND ', $whereClauses);
    }

    return ['where' => $where, 'params' => $params];
}
