<?php
// reviewer_documents.php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include "../Database/db.php"; // adjust path if necessary
session_start();

function h($s){ return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }

// ---------- configuration / identify reviewer ----------
$session_user_id = $_SESSION['user_id'] ?? 4; // replace with real session in production

// find reviewer_id from ReviewCommittee
$reviewer_id = null;
if ($conn) {
    $stmt = $conn->prepare("SELECT reviewer_id FROM ReviewCommittee WHERE user_id = ? LIMIT 1");
    if ($stmt) {
        $stmt->bind_param("i", $session_user_id);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($res && $row = $res->fetch_assoc()) {
            $reviewer_id = (int)$row['reviewer_id'];
        }
        $stmt->close();
    }
}

// set flash helper
if (!isset($_SESSION['flash'])) $_SESSION['flash'] = null;
function set_flash($msg) { $_SESSION['flash'] = $msg; }
function get_flash() { $m = $_SESSION['flash'] ?? null; $_SESSION['flash'] = null; return $m; }

// ---------- ensure DocumentReviews table exists ----------
if ($conn) {
    $createSql = "
    CREATE TABLE IF NOT EXISTS DocumentReviews (
      id INT AUTO_INCREMENT PRIMARY KEY,
      document_id INT NOT NULL,
      reviewer_id INT NOT NULL,
      status ENUM('Verified','Not Verified') DEFAULT NULL,
      comment TEXT DEFAULT NULL,
      flagged TINYINT(1) DEFAULT 0,
      reviewed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      CONSTRAINT fk_docrev_document FOREIGN KEY (document_id) REFERENCES Document(document_id) ON DELETE CASCADE,
      CONSTRAINT fk_docrev_reviewer FOREIGN KEY (reviewer_id) REFERENCES ReviewCommittee(reviewer_id) ON DELETE CASCADE
    ) ENGINE=InnoDB;
    ";
    $conn->query($createSql);
}

// ---------- handle form actions (save review, flag) ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $conn && $reviewer_id) {
    $action = $_POST['action'] ?? '';
    if ($action === 'save_review') {
        $document_id = intval($_POST['document_id'] ?? 0);
        $status = $_POST['status'] ?? null; // 'Verified' or 'Not Verified'
        $comment = trim($_POST['comment'] ?? '');

        if ($document_id > 0 && ($status === 'Verified' || $status === 'Not Verified')) {
            // check existing
            $stmt = $conn->prepare("SELECT id FROM DocumentReviews WHERE document_id = ? AND reviewer_id = ? LIMIT 1");
            $stmt->bind_param("ii", $document_id, $reviewer_id);
            $stmt->execute();
            $res = $stmt->get_result();
            if ($res && $row = $res->fetch_assoc()) {
                // update
                $stmt2 = $conn->prepare("UPDATE DocumentReviews SET status = ?, comment = ?, flagged = 0, reviewed_at = NOW() WHERE id = ?");
                $stmt2->bind_param("ssi", $status, $comment, $row['id']);
                $stmt2->execute();
                $stmt2->close();
                set_flash("Document review updated.");
            } else {
                // insert
                $stmt2 = $conn->prepare("INSERT INTO DocumentReviews (document_id, reviewer_id, status, comment, flagged) VALUES (?,?,?,?,0)");
                $stmt2->bind_param("iiss", $document_id, $reviewer_id, $status, $comment);
                $stmt2->execute();
                $stmt2->close();
                set_flash("Document review saved.");
            }
            $stmt->close();
        } else {
            set_flash("Invalid input for saving review.");
        }

        header("Location: " . $_SERVER['REQUEST_URI']);
        exit;
    }

    if ($action === 'flag_document') {
        $document_id = intval($_POST['document_id'] ?? 0);
        $comment = trim($_POST['comment'] ?? 'Flagged by reviewer');
        if ($document_id > 0) {
            // fetch application_id for this document
            $stmt = $conn->prepare("SELECT application_id FROM Document WHERE document_id = ? LIMIT 1");
            $stmt->bind_param("i", $document_id);
            $stmt->execute();
            $res = $stmt->get_result();
            $application_id = null;
            if ($res && $row = $res->fetch_assoc()) $application_id = $row['application_id'];
            $stmt->close();

            // record flag in DocumentReviews (flagged=1) and FraudLogs
            if ($application_id !== null) {
                // upsert DocumentReviews flagged
                $stmt = $conn->prepare("SELECT id FROM DocumentReviews WHERE document_id = ? AND reviewer_id = ? LIMIT 1");
                $stmt->bind_param("ii", $document_id, $reviewer_id);
                $stmt->execute();
                $res = $stmt->get_result();
                if ($res && $row = $res->fetch_assoc()) {
                    $stmt2 = $conn->prepare("UPDATE DocumentReviews SET flagged = 1, comment = CONCAT(IFNULL(comment,''), '\n[FLAG] ', ?), reviewed_at = NOW() WHERE id = ?");
                    $stmt2->bind_param("si", $comment, $row['id']);
                    $stmt2->execute();
                    $stmt2->close();
                } else {
                    $stmt2 = $conn->prepare("INSERT INTO DocumentReviews (document_id, reviewer_id, status, comment, flagged) VALUES (?, ?, NULL, ?, 1)");
                    $stmt2->bind_param("iis", $document_id, $reviewer_id, $comment);
                    $stmt2->execute();
                    $stmt2->close();
                }
                $stmt->close();

                // insert into FraudLogs
                $ip = $_SERVER['REMOTE_ADDR'] ?? null;
                $stmt3 = $conn->prepare("INSERT INTO FraudLogs (application_id, user_id, log_type, reason, details, ip_address, flagged_date, status) VALUES (?, ?, 'Flagged Application', 'Flagged Document by Reviewer', ?, ?, NOW(), 'Under Review')");
                $stmt3->bind_param("iiss", $application_id, $session_user_id, $comment, $ip);
                $stmt3->execute();
                $stmt3->close();

                set_flash("Document flagged and logged for review.");
            } else {
                set_flash("Document or application not found.");
            }
        } else {
            set_flash("Invalid document id for flagging.");
        }

        header("Location: " . $_SERVER['REQUEST_URI']);
        exit;
    }
}

// ---------- fetch applications assigned to this reviewer ----------
$applications = [];
if ($conn && $reviewer_id) {
    // Get distinct applications that have reviews assigned to this reviewer
    $sql = "
      SELECT DISTINCT a.application_id, a.submission_date, s.student_id, s.first_name, s.last_name, sc.name AS scholarship_name
      FROM Applications a
      JOIN Reviews r ON r.application_id = a.application_id
      JOIN Students s ON a.student_id = s.student_id
      LEFT JOIN Scholarships sc ON a.scholarship_id = sc.scholarship_id
      WHERE r.reviewer_id = ?
      ORDER BY a.submission_date DESC
    ";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("i", $reviewer_id);
        $stmt->execute();
        $res = $stmt->get_result();
        while ($row = $res->fetch_assoc()) {
            $row['student_name'] = trim($row['first_name'] . ' ' . $row['last_name']);
            $applications[$row['application_id']] = $row;
        }
        $stmt->close();
    }
}

// ---------- for each application fetch documents and any review info ----------
$application_documents = []; // keyed by application_id => array of docs
if ($conn && !empty($applications)) {
    // prepare document fetch with left join to DocumentReviews for this reviewer
    $docStmt = $conn->prepare("SELECT d.document_id, d.application_id, d.url, d.type, d.created_at, dr.status AS review_status, dr.comment AS review_comment, dr.flagged
        FROM Document d
        LEFT JOIN DocumentReviews dr ON d.document_id = dr.document_id AND dr.reviewer_id = ?
        WHERE d.application_id = ?
        ORDER BY d.created_at DESC");
    if ($docStmt) {
        foreach ($applications as $appId => $app) {
            $docStmt->bind_param("ii", $reviewer_id, $appId);
            $docStmt->execute();
            $res = $docStmt->get_result();
            $docs = [];
            while ($d = $res->fetch_assoc()) $docs[] = $d;
            $application_documents[$appId] = $docs;
        }
        $docStmt->close();
    }
}

// flash message
$flash = get_flash();

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width,initial-scale=1"/>
  <title>Documents Review</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"/>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet"/>
  <style>
    body { background:#f8f9fa; }
    .main-content { min-height:100vh; padding:22px; }
    .page-header { display:flex; justify-content:space-between; align-items:center; margin-bottom:18px; }
    .page-header h1 { color:#152259; font-weight:700; }
    .card-app { margin-bottom:18px; }
    .documents-section { background:#fff; border-radius:8px; padding:16px; box-shadow:0 2px 8px rgba(0,0,0,0.04); }
    .document-item { border-bottom:1px solid #eef1f4; padding:12px 0; display:flex; gap:12px; align-items:flex-start; }
    .doc-thumb { width:96px; height:96px; object-fit:cover; border-radius:6px; border:1px solid #e6e9ec; display:flex; align-items:center; justify-content:center; background:#f8f9fa; }
    .doc-info { flex:1; }
    .doc-actions { min-width:220px; display:flex; flex-direction:column; gap:8px; align-items:flex-end; }
    .btn-verify { background:#28a745; color:#fff; border:none; padding:6px 10px; border-radius:6px; }
    .btn-flag { background:#dc3545; color:#fff; border:none; padding:6px 10px; border-radius:6px; }
    .btn-verify:hover { background:#218838; }
    .btn-flag:hover { background:#c82333; }
    .badge-status { font-weight:600; }
    .muted { color:#6c757d; }
    .form-inline { display:flex; gap:8px; align-items:center; flex-wrap:wrap; }
  </style>
</head>
<body>
<?php include 'sidebar.php'; ?>

<div class="main-content">
  <div class="page-header">
    <h1>Documents Review</h1>
    <div class="text-muted">Reviewer: <?= h($session_user_id) ?> <?= $reviewer_id ? '(ID: ' . h($reviewer_id) . ')' : '' ?></div>
  </div>

  <?php if ($flash): ?>
    <div class="alert alert-success"><?= h($flash) ?></div>
  <?php endif; ?>

  <?php if (!$reviewer_id): ?>
    <div class="alert alert-warning">No reviewer profile found for your user. You must be registered as a reviewer to review documents.</div>
  <?php elseif (empty($applications)): ?>
    <div class="alert alert-info">No applications assigned to you yet.</div>
  <?php else: ?>

    <?php foreach ($applications as $appId => $app): ?>
      <div class="card card-app">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-start">
            <div>
              <h5 class="card-title mb-1">Application: <?= h($appId) ?></h5>
              <div class="muted mb-1">Student: <?= h($app['student_name']) ?> — Scholarship: <?= h($app['scholarship_name'] ?? '-') ?></div>
              <div class="muted">Submitted: <?= h($app['submission_date']) ?></div>
            </div>
            <div>
              <!-- placeholder for actions per application if needed -->
            </div>
          </div>

          <div class="documents-section mt-3">
            <h6 class="mb-3">Submitted Documents</h6>

            <?php
            $docs = $application_documents[$appId] ?? [];
            if (empty($docs)): ?>
              <div class="text-muted">No documents uploaded for this application.</div>
            <?php else: ?>
              <?php foreach ($docs as $d): 
                $ext = strtolower(pathinfo($d['url'], PATHINFO_EXTENSION));
                $isImg = in_array($ext, ['jpg','jpeg','png','gif','webp']);
                $isPdf = $ext === 'pdf';
                $thumb = $isImg ? $d['url'] : null;
                $fileName = basename($d['url']);
                $review_status = $d['review_status'] ?? null;
                $review_comment = $d['review_comment'] ?? '';
                $flagged = (int)($d['flagged'] ?? 0);
                ?>
                <div class="document-item">
                  <div class="doc-thumb" onclick="openPreview('<?= h($d['url']) ?>')">
                    <?php if ($isImg): ?>
                      <img src="<?= h($thumb) ?>" alt="" style="max-width:96px; max-height:96px; border-radius:6px;">
                    <?php elseif ($isPdf): ?>
                      <i class="bi bi-file-earmark-pdf" style="font-size:36px; color:#d25151;"></i>
                    <?php else: ?>
                      <i class="bi bi-file-earmark" style="font-size:32px; color:#6c757d;"></i>
                    <?php endif; ?>
                  </div>

                  <div class="doc-info">
                    <div style="display:flex; justify-content:space-between; gap:12px;">
                      <div>
                        <strong><?= h($fileName) ?></strong>
                        <div class="muted">Type: <?= h($d['type']) ?> — Uploaded: <?= h($d['created_at']) ?></div>
                      </div>
                      <div class="text-end">
                        <?php if ($review_status): ?>
                          <span class="badge badge-status <?= $review_status === 'Verified' ? 'bg-success' : 'bg-warning' ?>"><?= h($review_status) ?></span>
                        <?php endif; ?>
                        <?php if ($flagged): ?>
                          <span class="badge bg-danger">Flagged</span>
                        <?php endif; ?>
                      </div>
                    </div>

                    <div class="mt-2">
                      <form method="POST" class="form-inline">
                        <input type="hidden" name="action" value="save_review">
                        <input type="hidden" name="document_id" value="<?= (int)$d['document_id'] ?>">
                        <div class="form-check form-check-inline">
                          <input class="form-check-input" type="radio" id="v<?= $d['document_id'] ?>1" name="status" value="Verified" <?= $review_status === 'Verified' ? 'checked' : '' ?>>
                          <label class="form-check-label" for="v<?= $d['document_id'] ?>1">Verified</label>
                        </div>
                        <div class="form-check form-check-inline">
                          <input class="form-check-input" type="radio" id="v<?= $d['document_id'] ?>2" name="status" value="Not Verified" <?= $review_status === 'Not Verified' ? 'checked' : '' ?>>
                          <label class="form-check-label" for="v<?= $d['document_id'] ?>2">Not Verified</label>
                        </div>

                        <div style="flex:1; margin-left:8px;">
                          <input type="text" name="comment" class="form-control form-control-sm" placeholder="Add a short comment" value="<?= h($review_comment) ?>">
                        </div>

                        <div style="display:flex; gap:6px; margin-left:8px;">
                          <button type="submit" class="btn btn-verify btn-sm">Save</button>
                      </form>

                      <form method="POST" style="display:inline-block; margin-left:6px;">
                        <input type="hidden" name="action" value="flag_document">
                        <input type="hidden" name="document_id" value="<?= (int)$d['document_id'] ?>">
                        <input type="hidden" name="comment" value="Flagged by reviewer (document: <?= h($fileName) ?>)">
                        <button type="submit" class="btn btn-flag btn-sm" title="Flag as suspicious">Flag</button>
                      </form>
                        </div>
                    </div>

                    <?php if (!empty($review_comment)): ?>
                      <div class="mt-2 muted"><strong>Comment:</strong> <?= h($review_comment) ?></div>
                    <?php endif; ?>
                  </div>

                  <div class="doc-actions">
                    <a href="<?= h($d['url']) ?>" target="_blank" class="btn btn-outline-secondary btn-sm"><i class="bi bi-box-arrow-up-right"></i> Open</a>
                    <a href="<?= h($d['url']) ?>" download class="btn btn-outline-success btn-sm"><i class="bi bi-download"></i> Download</a>
                  </div>
                </div>
              <?php endforeach; ?>
            <?php endif; ?>
          </div>
        </div>
      </div>
    <?php endforeach; ?>

  <?php endif; ?>
</div>

<!-- preview modal -->
<div class="modal fade" id="previewModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg" style="max-width:900px;">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title">Document Preview</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" id="previewBody" style="min-height:400px;">
        <!-- injected -->
      </div>
    </div>
  </div>
</div>

<script>
function openPreview(url){
  const ext = url.split('.').pop().toLowerCase();
  const body = document.getElementById('previewBody');
  body.innerHTML = '';
  if (['jpg','jpeg','png','gif','webp'].includes(ext)){
    const img = document.createElement('img');
    img.src = url;
    img.style.maxWidth = '100%';
    img.style.maxHeight = '80vh';
    body.appendChild(img);
  } else if (ext === 'pdf') {
    const iframe = document.createElement('iframe');
    iframe.src = url;
    iframe.style.width = '100%';
    iframe.style.height = '80vh';
    iframe.style.border = 'none';
    body.appendChild(iframe);
  } else {
    body.innerHTML = '<div class="text-center">Preview not available. <a href="'+url+'" target="_blank">Open in new tab</a></div>';
  }
  var modal = new bootstrap.Modal(document.getElementById('previewModal'));
  modal.show();
}
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
if ($conn) $conn->close();
?>
