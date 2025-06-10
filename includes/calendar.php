<?php
$month = isset($_GET['month']) ? (int)$_GET['month'] : date('n');
$year  = isset($_GET['year']) ? (int)$_GET['year'] : date('Y');

$firstDay = mktime(0, 0, 0, $month, 1, $year);
$daysInMonth = date('t', $firstDay);
$startDayOfWeek = date('N', $firstDay);

$prevMonth = $month == 1 ? 12 : $month - 1;
$prevYear  = $month == 1 ? $year - 1 : $year;
$nextMonth = $month == 12 ? 1 : $month + 1;
$nextYear  = $month == 12 ? $year + 1 : $year;

// (Optional) get scheduled dates
$stmt = $pdo->prepare("SELECT scheduled_date FROM quotes WHERE MONTH(scheduled_date) = ? AND YEAR(scheduled_date) = ?");
$stmt->execute([$month, $year]);
$scheduledDates = array_column($stmt->fetchAll(), 'scheduled_date');

// Navigation
echo "<div class='calendar-nav'>";
echo "<a href='?month=$prevMonth&year=$prevYear'>&laquo; Previous</a>";
echo "<strong>" . date('F Y', $firstDay) . "</strong>";
echo "<a href='?month=$nextMonth&year=$nextYear'>Next &raquo;</a>";
echo "</div>";

// Calendar table
echo "<table class='calendar'>";
echo "<tr><th>Mon</th><th>Tue</th><th>Wed</th><th>Thu</th><th>Fri</th><th>Sat</th><th>Sun</th></tr><tr>";

$currentDay = 1;
for ($i = 1; $i < $startDayOfWeek; $i++) {
  echo "<td></td>";
}

for ($day = 1; $day <= $daysInMonth; $day++, $currentDay++) {
  $dateStr = sprintf('%04d-%02d-%02d', $year, $month, $day);
  $isScheduled = in_array($dateStr, $scheduledDates);
  $class = $isScheduled ? 'scheduled' : '';
  echo "<td class='calendar-day $class' data-date='$dateStr'>$day</td>";

  if (($currentDay + $startDayOfWeek - 1) % 7 == 0) {
    echo "</tr><tr>";
  }
}

while (($currentDay + $startDayOfWeek - 1) % 7 != 1) {
  echo "<td></td>";
  $currentDay++;
}

echo "</tr></table>";
?>
