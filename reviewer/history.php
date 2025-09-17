<?php
// review_history.php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include "../Database/db.php"; // adjust path if needed
session_start();

// Replace with actual session user id in production
$session_user_id = $_SESSION['user_id'] ?? 4;

// helper for safe output
function h($s){ return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }

$errors = [];
$reviews = [];
$reviewer_id = null;

// 1) Map Users.user_id -> ReviewCommittee.reviewer_id
if (!$conn) {
    $errors[] = "Database connection not available.";
} else {
    $stmt = $conn->prepare("SELECT reviewer_id FROM ReviewCommittee WHERE user_id = ? LIMIT 1");
    if (!$stmt) {
        $errors[] = "Prepare failed (finding reviewer): " . $conn->error;
    } else {
        $stmt->bind_param("i", $session_user_id);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($res && $res->num_rows > 0) {
            $row = $res->fetch_assoc();
            $reviewer_id = (int)$row['reviewer_id'];
        } else {
            $errors[] = "No reviewer profile found for this user. (user_id={$session_user_id})";
        }
        $stmt->close();
    }
}

// 2) If reviewer_id found, fetch reviews
if ($reviewer_id) {
    $sql = "
      SELECT r.review_id,
             r.application_id,
             r.review_date,
             r.score,
             r.comments,
             r.decision,
             a.student_id,
             s.first_name,
             s.last_name,
             sc.scholarship_id,
             sc.name AS scholarship_name
      FROM Reviews r
      JOIN Applications a ON r.application_id = a.application_id
      JOIN Students s ON a.student_id = s.student_id
      JOIN Scholarships sc ON a.scholarship_id = sc.scholarship_id
      WHERE r.reviewer_id = ?
      ORDER BY r.review_date DESC
    ";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        $errors[] = "Prepare failed (fetching reviews): " . $conn->error;
    } else {
        $stmt->bind_param("i", $reviewer_id);
        if (!$stmt->execute()) {
            $errors[] = "Execute failed (fetching reviews): " . $stmt->error;
        } else {
            $res = $stmt->get_result();
            if ($res) {
                while ($r = $res->fetch_assoc()) {
                    // normalize fields
                    $r['student_name'] = trim($r['first_name'] . ' ' . $r['last_name']);
                    $r['score'] = $r['score'] !== null ? (float)$r['score'] : null;
                    $reviews[] = $r;
                }
            }
        }
        $stmt->close();
    }
}

// Build distinct scholarship list for the filter UI
$scholarshipOptions = [];
foreach ($reviews as $r) {
    if (!in_array($r['scholarship_name'], $scholarshipOptions, true)) {
        $scholarshipOptions[] = $r['scholarship_name'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width,initial-scale=1"/>
  <title>Review History</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"/>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet"/>
  <style>
    .main-content { background:#f8f9fa; min-height:100vh; padding:22px; }
    .page-header { display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; }
    .page-header h1 { font-size:24px; font-weight:600; color:#152259; }
    .filters { display:flex; gap:12px; margin-bottom:18px; flex-wrap:wrap; }
    .filters .form-select, .filters .form-control { max-width:260px; }
    .review-table { background:#fff; border-radius:10px; padding:18px; box-shadow:0 2px 8px rgba(0,0,0,0.06); }
    .review-table th { background:#152259; color:#fff; }
    .btn-expand { background:#509CDB; color:#fff; border:none; padding:6px 10px; border-radius:6px; }
    .btn-expand:hover { background:#408CCB; }
    .review-summary { background:#f8f9fa; padding:12px; border-radius:8px; margin-top:8px; display:none; }
    .score-breakdown { margin-top:10px; padding:10px; background:#fff; border-radius:6px; border:1px solid #e6e6e6; }
    .muted-centre { text-align:center; color:#777; padding:20px 0; }
    .error-box { margin-bottom:12px; }
  </style>
</head>
<body>
<?php include 'sidebar.php'; ?>

<div class="main-content">
  <div class="page-header">
    <h1>Review History</h1>
    <div class="text-muted">Reviewer ID: <?= $reviewer_id ? h($reviewer_id) : 'N/A' ?></div>
  </div>

  <?php if (!empty($errors)): ?>
    <div class="alert alert-danger error-box">
      <strong>Errors:</strong>
      <ul class="mb-0">
        <?php foreach ($errors as $e): ?>
          <li><?= h($e) ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
  <?php endif; ?>

  <div class="filters mb-2">
    <select id="scholarshipFilter" class="form-select">
      <option value="">All Scholarships</option>
      <?php foreach ($scholarshipOptions as $opt): ?>
        <option value="<?= h(strtolower($opt)) ?>"><?= h($opt) ?></option>
      <?php endforeach; ?>
    </select>

    <input id="reviewDateFilter" type="date" class="form-control" placeholder="Filter by review date"/>

    <input id="searchInput" type="text" class="form-control" placeholder="Search by application ID or student name" style="max-width:360px"/>
  </div>

  <div class="review-table">
    <table class="table table-hover">
      <thead>
        <tr>
          <th>Application ID</th>
          <th>Student Name</th>
          <th>Scholarship Name</th>
          <th>Review Date</th>
          <th>Score</th>
          <th>Decision</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody id="reviewTable">
        <?php if (empty($reviews)): ?>
          <tr><td colspan="7" class="muted-centre">No review history found.</td></tr>
        <?php else: ?>
          <?php foreach ($reviews as $r): 
            $appId = h($r['application_id']);
            $studentName = h($r['student_name']);
            $schName = h($r['scholarship_name']);
            $reviewDate = h($r['review_date']);
            $score = $r['score'] !== null ? h(number_format($r['score'],2)) : '-';
            $decision = h($r['decision'] ?? '-');
            ?>
            <tr data-scholarship="<?= h(strtolower($r['scholarship_name'])) ?>" data-review-date="<?= h($r['review_date']) ?>" data-search="<?= h(strtolower($appId . ' ' . $studentName)) ?>">
              <td><?= $appId ?></td>
              <td><?= $studentName ?></td>
              <td><?= $schName ?></td>
              <td><?= $reviewDate ?></td>
              <td><?= $score ?>%</td>
              <td><?= $decision ?></td>
              <td>
                <button class="btn btn-expand" onclick="toggleSummary(this)">View</button>
              </td>
            </tr>
            <tr class="summary-row" style="display:none;">
              <td colspan="7">
                <div class="review-summary">
                  <p><strong>Comments:</strong> <?= h($r['comments'] ?? 'No comments') ?></p>
                  <div class="score-breakdown">
                    <p><strong>Score (raw):</strong> <?= $score !== '-' ? $score . '%' : '-' ?></p>
                    <!-- If you want to show more breakdown fields, add them to the DB and display here -->
                  </div>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<script>
  // toggle summary row for a given "View" button
  function toggleSummary(btn){
    const tr = btn.closest('tr');
    const next = tr.nextElementSibling;
    if (!next || !next.classList.contains('summary-row')) return;
    if (next.style.display === 'none' || next.style.display === '') {
      next.style.display = 'table-row';
    } else {
      next.style.display = 'none';
    }
  }

  // filter logic
  document.getElementById('scholarshipFilter').addEventListener('change', applyFilters);
  document.getElementById('reviewDateFilter').addEventListener('change', applyFilters);
  document.getElementById('searchInput').addEventListener('input', applyFilters);

  function applyFilters(){
    const scholarship = document.getElementById('scholarshipFilter').value;
    const reviewDate = document.getElementById('reviewDateFilter').value;
    const search = document.getElementById('searchInput').value.trim().toLowerCase();

    const rows = document.querySelectorAll('#reviewTable > tr');
    for (let i = 0; i < rows.length; i += 2) {
      const main = rows[i];
      const summary = rows[i+1];
      if (!main) continue;

      const rowScholarship = main.getAttribute('data-scholarship') || '';
      const rowDate = main.getAttribute('data-review-date') || '';
      const rowSearch = main.getAttribute('data-search') || '';

      let show = true;
      if (scholarship && scholarship !== rowScholarship) show = false;
      if (reviewDate && reviewDate !== rowDate) show = false;
      if (search && rowSearch.indexOf(search) === -1) show = false;

      main.style.display = show ? '' : 'none';
      if (summary) summary.style.display = 'none';
    }
  }
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
// close connection
if ($conn) $conn->close();
?>
