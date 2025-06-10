<?php
require_once '../config/db.php';

// Fetch theme counts for the summary with quote counts per theme
$themeCountsStmt = $pdo->query("
    SELECT t.id, t.name, COUNT(q.id) AS quote_count
    FROM themes t
    LEFT JOIN quotes q ON q.theme_id = t.id
    GROUP BY t.id, t.name
    ORDER BY quote_count DESC
");

// Prepare themes for select dropdowns used multiple times
$themesStmt = $pdo->query("SELECT id, name FROM themes ORDER BY name");
$allThemes = $themesStmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch quotes for listing
// (Apply filtering if needed here later)
$quotesStmt = $pdo->query("
    SELECT quotes.*, themes.name AS theme_name
    FROM quotes
    LEFT JOIN themes ON quotes.theme_id = themes.id
    ORDER BY quotes.scheduled_date DESC, quotes.id DESC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Quote Dashboard</title>
  <link rel="stylesheet" href="../assets/css/style.css" />
</head>
<body>
  <h1>Quote Dashboard</h1>

  <section class="theme-summary">
    <h2>
      Theme Summary
      <button id="toggleThemeSummary" aria-expanded="false" aria-controls="themeSummaryTable" style="margin-left: 10px; font-size: 0.8rem;">
        ► <span id="toggleThemeSummaryText">click to open</span>
      </button>
    </h2>
    <div id="themeSummaryTable" style="display: none;">
      <table>
        <thead>
          <tr>
            <th>Theme</th>
            <th>Number of Quotes</th>
          </tr>
        </thead>
        <tbody>
          <?php while ($row = $themeCountsStmt->fetch()): ?>
            <tr>
              <td><?= htmlspecialchars($row['name']) ?></td>
              <td><?= $row['quote_count'] ?></td>
            </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </section>

  <!-- Top Action Buttons -->
  <div class="top-actions">
    <button id="openAddModal">➕ Add Quote</button>
    <button id="openImportModal" class="button-link">⬆️ Import CSV</button>
    <a href="../includes/export_csv.php" class="button-link">⬇️ Export CSV</a>
    <a href="../includes/template.csv" download class="button-link">📄 Download Template</a>
    <button id="deleteAllBtn">🗑️ Delete All Quotes</button>
  </div>

  <!-- Toast Messages -->
  <?php if (isset($_GET['schedule']) && $_GET['schedule'] === 'ok'): ?>
    <div class="toast">✅ Quote scheduled successfully.</div>
  <?php endif; ?>
  <?php if (isset($_GET['edit']) && $_GET['edit'] === 'ok'): ?>
    <div class="toast">✏️ Quote updated.</div>
  <?php endif; ?>
  <?php if (isset($_GET['delete']) && $_GET['delete'] === 'ok'): ?>
    <div class="toast">🗑️ Quote deleted.</div>
  <?php endif; ?>
  <?php if (isset($_GET['delete_all']) && $_GET['delete_all'] === 'ok'): ?>
    <div class="toast" style="background:#b33; color:#fff;">🗑️ All quotes deleted.</div>
  <?php endif; ?>

  <!-- Calendar -->
  <?php include '../includes/calendar.php'; ?>

  <!-- Schedule Quote Modal -->
  <div id="scheduleModal" class="modal" style="display: none;">
    <div class="modal-content">
      <span class="close" id="closeModal">&times;</span>
      <h3>Schedule a Quote</h3>
      <form action="../includes/quotes.php" method="POST">
        <input type="hidden" name="scheduled_date" id="modalDate" />
        <label for="quote_id">Choose a Quote:</label>
        <select name="quote_id" id="quote_id" required>
          <?php
          // List only unscheduled quotes for scheduling
          $stmt = $pdo->query("SELECT id, text FROM quotes WHERE scheduled_date IS NULL ORDER BY id DESC LIMIT 100");
          while ($row = $stmt->fetch()):
          ?>
            <option value="<?= $row['id'] ?>">
              <?= htmlspecialchars(substr($row['text'], 0, 80)) ?>…
            </option>
          <?php endwhile; ?>
        </select>
        <br /><br />
        <button type="submit" name="schedule_quote">Assign Quote to Date</button>
      </form>
    </div>
  </div>

  <!-- Edit Quote Modal -->
  <div id="editModal" class="modal" style="display: none;">
    <div class="modal-content">
      <span class="close" id="closeEditModal">&times;</span>
      <h3>Edit Quote</h3>
      <form action="../includes/edit_quote.php" method="POST">
        <input type="hidden" name="quote_id" id="editQuoteId" />
        <label for="editText">Quote Text:</label>
        <textarea name="text" id="editText" rows="4" required></textarea>
        <label for="editAuthor">Author:</label>
        <input type="text" name="author" id="editAuthor" required />
        <label for="editYear">Year:</label>
        <input type="number" name="quote_year" id="editYear" />
        <label>Theme:</label>
        <select name="theme_id" id="editTheme" required>
          <option value="">Select theme</option>
          <?php foreach ($allThemes as $theme): ?>
            <option value="<?= htmlspecialchars($theme['id']) ?>"><?= htmlspecialchars($theme['name']) ?></option>
          <?php endforeach; ?>
        </select>
        <br /><br />
        <button type="submit" name="save_edit">Save Changes</button>
      </form>
    </div>
  </div>

  <!-- Add Quote Modal -->
  <div id="addModal" class="modal" style="display: none;">
    <div class="modal-content">
      <span class="close" id="closeAddModal">&times;</span>
      <h3>Add New Quote</h3>
      <form action="../includes/add_quote.php" method="POST">
        <label>Quote:</label>
        <textarea name="text" required></textarea>
        <label>Author:</label>
        <input type="text" name="author" />
        <label>Year:</label>
        <input type="number" name="quote_year" />
        <label>Language:</label>
        <input type="text" name="language" />
        <label>Source:</label>
        <input type="text" name="source" />
        <label>Theme:</label>
        <select name="theme_id" required>
          <option value="">Select theme</option>
          <?php foreach ($allThemes as $theme): ?>
            <option value="<?= htmlspecialchars($theme['id']) ?>"><?= htmlspecialchars($theme['name']) ?></option>
          <?php endforeach; ?>
        </select>
        <br /><br />
        <button type="submit" name="add_quote">Add Quote</button>
      </form>
    </div>
  </div>

  <!-- Import CSV Modal -->
  <div id="importModal" class="modal" style="display: none;">
    <div class="modal-content">
      <span class="close" id="closeImportModal">&times;</span>
      <h3>Import Quotes from CSV</h3>
      <form id="importForm" enctype="multipart/form-data">
        <label>Select CSV file:</label>
        <input type="file" name="csv_file" accept=".csv" required />
        <br /><br />
        <button type="submit">Import CSV</button>
      </form>
      <div id="importStatus" style="margin-top:10px; color: green; display:none;"></div>
    </div>
  </div>

  <form id="filterForm" method="GET" style="margin-bottom: 20px;">
    <label for="themeFilter">Theme:</label>
    <select name="theme" id="themeFilter">
      <option value="">All</option>
      <?php foreach ($allThemes as $theme): ?>
        <option value="<?= htmlspecialchars($theme['id']) ?>" <?= (isset($_GET['theme']) && $_GET['theme'] == $theme['id']) ? 'selected' : '' ?>>
          <?= htmlspecialchars($theme['name']) ?>
        </option>
      <?php endforeach; ?>
    </select>

    <label for="scheduledFilter">Scheduled:</label>
    <select name="scheduled" id="scheduledFilter">
      <option value="">All</option>
      <option value="1" <?= (isset($_GET['scheduled']) && $_GET['scheduled'] === '1') ? 'selected' : '' ?>>Scheduled</option>
      <option value="0" <?= (isset($_GET['scheduled']) && $_GET['scheduled'] === '0') ? 'selected' : '' ?>>Not Scheduled</option>
    </select>

    <label for="searchFilter">Search:</label>
    <input type="text" name="search" id="searchFilter" value="<?= htmlspecialchars($_GET['search'] ?? '') ?>" placeholder="Quote text or author" />

    <button type="submit">Filter</button>
    <button type="button" id="clearFilters">Clear</button>
  </form>

  <!-- Quote Table -->
  <h2>All Quotes</h2>
  <table class="quote-table">
    <thead>
      <tr>
        <th>ID</th>
        <th>Quote</th>
        <th>Author</th>
        <th>Year</th>
        <th>Theme</th>
        <th>Scheduled</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php while ($quote = $quotesStmt->fetch()): ?>
        <tr>
          <td><?= $quote['id'] ?></td>
          <td><?= htmlspecialchars(mb_strimwidth($quote['text'], 0, 80, '…')) ?></td>
          <td><?= htmlspecialchars($quote['author']) ?></td>
          <td><?= $quote['quote_year'] ?></td>
          <td><?= htmlspecialchars($quote['theme_name'] ?? '—') ?></td>
          <td><?= $quote['scheduled_date'] ?? 'Not scheduled' ?></td>
          <td>
            <a href="#" class="editBtn">✏️</a>
            <a href="../includes/delete_quote.php?id=<?= $quote['id'] ?>" onclick="return confirm('Delete this quote?')">🗑️</a>
            <a href="#">📅</a>
          </td>
        </tr>
      <?php endwhile; ?>
    </tbody>
  </table>

  <script src="../assets/js/main.js" defer></script>
</body>
</html>
