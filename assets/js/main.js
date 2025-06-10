document.addEventListener('DOMContentLoaded', function () {
  console.log("✅ JS loaded");

  // ======== Modal Elements ========
  const scheduleModal = document.getElementById('scheduleModal');
  const modalDateInput = document.getElementById('modalDate');
  const closeScheduleBtn = document.getElementById('closeModal');

  const editModal = document.getElementById('editModal');
  const closeEditBtn = document.getElementById('closeEditModal');

  const addModal = document.getElementById('addModal');
  const openAddBtn = document.getElementById('openAddModal');
  const closeAddBtn = document.getElementById('closeAddModal');

  const importModal = document.getElementById('importModal');
  const openImportBtn = document.getElementById('openImportModal');
  const closeImportBtn = document.getElementById('closeImportModal');
  const importForm = document.getElementById('importForm');
  const importStatus = document.getElementById('importStatus');

  // Filters and key elements
  const themeFilter = document.getElementById('themeFilter');
  const scheduledFilter = document.getElementById('scheduledFilter');
  const searchFilter = document.getElementById('searchFilter');
  const filterForm = document.getElementById('filterForm');
  const quoteTableBody = document.querySelector('.quote-table tbody');
  const quoteSelect = document.getElementById('quote_id');

  // ======== Calendar cell click → open schedule modal ========
  const calendarCells = document.querySelectorAll('td[data-date]');
  calendarCells.forEach(cell => {
    cell.addEventListener('click', function () {
      const selectedDate = this.getAttribute('data-date');
      if (modalDateInput) modalDateInput.value = selectedDate;
      if (scheduleModal) scheduleModal.style.display = 'flex';
    });
  });

  // Close schedule modal
  if (closeScheduleBtn) {
    closeScheduleBtn.addEventListener('click', () => {
      if (scheduleModal) scheduleModal.style.display = 'none';
    });
  }
  window.addEventListener('click', (e) => {
    if (e.target === scheduleModal) scheduleModal.style.display = 'none';
  });

  // ======== Edit modal open/close logic ========
  function bindEditButtons() {
    document.querySelectorAll('.quote-table .editBtn').forEach(btn => {
      btn.addEventListener('click', function (e) {
        e.preventDefault();
        const row = btn.closest('tr');
        if (!row) return;
        const cells = row.querySelectorAll('td');

        const editQuoteId = document.getElementById('editQuoteId');
        const editText = document.getElementById('editText');
        const editAuthor = document.getElementById('editAuthor');
        const editYear = document.getElementById('editYear');
        const editTheme = document.getElementById('editTheme');

        if (editQuoteId) editQuoteId.value = cells[0].textContent.trim();
        if (editText) editText.value = cells[1].textContent.trim().replace(/…$/, '');
        if (editAuthor) editAuthor.value = cells[2].textContent.trim();
        if (editYear) editYear.value = cells[3].textContent.trim();

        if (editTheme) {
          const themeName = cells[4].textContent.trim();
          for (const option of editTheme.options) {
            option.selected = (option.text === themeName);
          }
        }

        if (editModal) editModal.style.display = 'flex';
      });
    });
  }
  bindEditButtons();

  if (closeEditBtn) {
    closeEditBtn.addEventListener('click', () => {
      if (editModal) editModal.style.display = 'none';
    });
  }
  window.addEventListener('click', (e) => {
    if (e.target === editModal) editModal.style.display = 'none';
  });

  // ======== Add modal open/close logic ========
  if (openAddBtn && addModal) {
    openAddBtn.addEventListener('click', () => addModal.style.display = 'flex');
    closeAddBtn.addEventListener('click', () => addModal.style.display = 'none');
    window.addEventListener('click', (e) => {
      if (e.target === addModal) addModal.style.display = 'none';
    });
  }

  // ======== Import modal open/close logic ========
  if (openImportBtn && importModal) {
    openImportBtn.addEventListener('click', () => importModal.style.display = 'flex');
    closeImportBtn.addEventListener('click', () => importModal.style.display = 'none');
    window.addEventListener('click', (e) => {
      if (e.target === importModal) importModal.style.display = 'none';
    });
  }

  // ======== AJAX Import CSV form submission ========
  if (importForm) {
    importForm.addEventListener('submit', function (e) {
      e.preventDefault();

      const formData = new FormData(importForm);
      importStatus.style.display = 'none';

      fetch('../includes/import_handler.php', {
        method: 'POST',
        body: formData
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          importStatus.style.color = 'green';
          importStatus.textContent = `✅ Successfully imported ${data.imported} quotes.`;
          importStatus.style.display = 'block';
          importForm.reset();

          setTimeout(() => {
            importModal.style.display = 'none';
            importStatus.style.display = 'none';
            location.reload();
          }, 1500);
        } else {
          importStatus.style.color = 'red';
          importStatus.textContent = '❌ Import failed. Please check your CSV format.';
          importStatus.style.display = 'block';
        }
      })
      .catch(() => {
        importStatus.style.color = 'red';
        importStatus.textContent = '❌ An error occurred during import.';
        importStatus.style.display = 'block';
      });
    });
  }

  // ======== Delete All Quotes button ========
  const deleteAllBtn = document.getElementById('deleteAllBtn');
  if (deleteAllBtn) {
    deleteAllBtn.addEventListener('click', () => {
      if (confirm('⚠️ Are you absolutely sure you want to DELETE ALL quotes? This action cannot be undone.')) {
        window.location.href = '../includes/delete_all_quotes.php';
      }
    });
  }

  // ======== Clear Filters button ========
  const clearFiltersBtn = document.getElementById('clearFilters');
  if (clearFiltersBtn) {
    clearFiltersBtn.addEventListener('click', () => {
      themeFilter.value = '';
      scheduledFilter.value = '';
      searchFilter.value = '';
      filterForm.submit();
    });
  }

  // ======== Toggle Active status of quotes (AJAX) ========
  document.querySelectorAll('input.toggleActive').forEach(checkbox => {
    checkbox.addEventListener('change', function () {
      const quoteId = this.dataset.id;
      const isActive = this.checked ? 1 : 0;

      fetch('../includes/toggle_active.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `id=${quoteId}&is_active=${isActive}`
      })
      .then(response => response.json())
      .then(data => {
        if (!data.success) {
          alert('⚠️ Could not update active status.');
          this.checked = !isActive; // revert on failure
        }
      })
      .catch(() => {
        alert('⚠️ Error updating active status.');
        this.checked = !isActive; // revert on failure
      });
    });
  });

  // ======== Update Schedule Quotes based on theme selection ========
  async function updateScheduleQuotes(themeId) {
    if (!quoteSelect) return;
    quoteSelect.disabled = true;
    quoteSelect.innerHTML = '<option>Loading quotes…</option>';

    try {
      const response = await fetch(`../includes/fetch_quotes.php?theme_id=${encodeURIComponent(themeId)}`);
      const data = await response.json();

      if (data.success) {
        quoteSelect.innerHTML = '';
        if (data.quotes.length === 0) {
          quoteSelect.innerHTML = '<option value="">No quotes available</option>';
        } else {
for (const quote of data.quotes) {
  const option = document.createElement('option');
  option.value = quote.id;

  const shortText = quote.text.length > 80 ? quote.text.slice(0, 80) + '…' : quote.text;

  if (quote.scheduled_date) {
    option.textContent = `🔒 ${shortText} (Scheduled)`;
    option.disabled = true;
  } else {
    option.textContent = shortText;
  }

  quoteSelect.appendChild(option);
}
        }
      } else {
        console.error('Server error:', data.error);
        quoteSelect.innerHTML = '<option value="">Error loading quotes</option>';
      }
    } catch (error) {
      console.error('Fetch error:', error);
      quoteSelect.innerHTML = '<option value="">Error loading quotes</option>';
    }
    quoteSelect.disabled = false;
  }

  
  // ======== Update quote list based on filters (AJAX) ========
async function updateQuoteList() {
  if (!quoteTableBody) return;

  const params = new URLSearchParams({
    theme: themeFilter.value,
    scheduled: scheduledFilter.value,
    search: searchFilter.value.trim(),
  });

  try {
    const response = await fetch('../includes/fetch_filtered_quotes.php?' + params.toString());
    const data = await response.json();

    quoteTableBody.innerHTML = '';
    if (data.success) {
      if (data.quotes.length === 0) {
        quoteTableBody.innerHTML = '<tr><td colspan="7">No quotes found.</td></tr>';
      } else {
        for (const q of data.quotes) {
          const tr = document.createElement('tr');
          if (q.scheduled_date) {
            tr.classList.add('scheduled-quote');
          }
          tr.innerHTML = `
            <td>${q.id}</td>
            <td>${escapeHtml(q.text.length > 80 ? q.text.slice(0, 80) + '…' : q.text)}</td>
            <td>${escapeHtml(q.author)}</td>
            <td>${escapeHtml(q.quote_year)}</td>
            <td>${escapeHtml(q.theme_name)}</td>
            <td>${q.scheduled_date ? q.scheduled_date : 'Not scheduled'}</td>
            <td>
              <a href="#" class="editBtn">✏️</a>
              <a href="../includes/delete_quote.php?id=${q.id}" onclick="return confirm('Delete this quote?')">🗑️</a>
              <a href="#">📅</a>
            </td>
          `;
          quoteTableBody.appendChild(tr);
        }
        bindEditButtons();
      }
    } else {
      quoteTableBody.innerHTML = '<tr><td colspan="7">Error loading quotes.</td></tr>';
    }
  } catch (error) {
    console.error('Fetch error:', error);
    quoteTableBody.innerHTML = '<tr><td colspan="7">Error loading quotes.</td></tr>';
  }
}
// ======== Escape HTML helper ========
  function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
  }

  // ======== Theme filter change ========
  if (themeFilter) {
    themeFilter.addEventListener('change', () => {
      updateScheduleQuotes(themeFilter.value);
      updateQuoteList();
    });
  }

  // ======== Scheduled and Search filters change ========
  if (scheduledFilter) {
    scheduledFilter.addEventListener('change', updateQuoteList);
  }
  if (searchFilter) {
    searchFilter.addEventListener('input', updateQuoteList);
  }

  // ======== Initialize schedule quotes dropdown and quote list on page load ========
  if (themeFilter) {
    updateScheduleQuotes(themeFilter.value);
  }
  updateQuoteList();

  // ======== Theme summary toggle with localStorage ========
  const toggleBtn = document.getElementById('toggleThemeSummary');
  const themeSummaryTable = document.getElementById('themeSummaryTable');
  const toggleText = document.getElementById('toggleThemeSummaryText');

  if (toggleBtn && themeSummaryTable && toggleText) {
    // Restore state from localStorage
    const savedState = localStorage.getItem('themeSummaryExpanded');
    if (savedState === 'true') {
      themeSummaryTable.style.display = 'block';
      toggleBtn.setAttribute('aria-expanded', 'true');
      toggleBtn.textContent = '▼ ';
      toggleBtn.appendChild(toggleText);
      toggleText.textContent = 'click to close';
    } else {
      themeSummaryTable.style.display = 'none';
      toggleBtn.setAttribute('aria-expanded', 'false');
      toggleBtn.textContent = '► ';
      toggleBtn.appendChild(toggleText);
      toggleText.textContent = 'click to open';
    }

    toggleBtn.addEventListener('click', () => {
      const isExpanded = toggleBtn.getAttribute('aria-expanded') === 'true';
      if (isExpanded) {
        themeSummaryTable.style.display = 'none';
        toggleBtn.setAttribute('aria-expanded', 'false');
        toggleBtn.textContent = '► ';
        toggleBtn.appendChild(toggleText);
        toggleText.textContent = 'click to open';
        localStorage.setItem('themeSummaryExpanded', 'false');
      } else {
        themeSummaryTable.style.display = 'block';
        toggleBtn.setAttribute('aria-expanded', 'true');
        toggleBtn.textContent = '▼ ';
        toggleBtn.appendChild(toggleText);
        toggleText.textContent = 'click to close';
        localStorage.setItem('themeSummaryExpanded', 'true');
      }
    });
  }
});
