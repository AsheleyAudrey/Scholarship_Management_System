<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

include "../Database/db.php"; // adjust path if needed
session_start();

// Example session values (set during login)
$user_role = $_SESSION['role'] ?? 'student'; 
$user_id   = $_SESSION['user_id'] ?? null;

// -------------------------
// Handle "Set Required Docs"
// -------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['required_docs'])) {
    $docs = $_POST['required_docs'];

    $stmt = $conn->prepare("INSERT INTO RequiredDocuments (doc_name, created_at) VALUES (?, NOW())");
    if ($stmt) {
        foreach ($docs as $doc) {
            $stmt->bind_param("s", $doc);
            $stmt->execute();
        }
        $stmt->close();
        $success_message = "Required documents saved successfully!";
    } else {
        $error_message = "Error preparing statement: " . $conn->error;
    }
}

// -------------------------
// Fetch uploaded documents with student info
// -------------------------
$sql = "SELECT d.document_id, d.application_id, d.url, d.type, d.created_at,
               s.student_id, s.first_name, s.last_name
        FROM Document d
        JOIN Applications a ON d.application_id = a.application_id
        JOIN Students s ON a.student_id = s.student_id
        ORDER BY d.created_at DESC";

$result = $conn->query($sql);

// -------------------------
// Fetch Notifications
// -------------------------
$notifications = [];

if ($user_role === 'reviewer') {
    $notif_sql = "SELECT message, created_at 
                  FROM Notifications 
                  WHERE role='reviewer' 
                  ORDER BY created_at DESC LIMIT 5";
    $notif_result = $conn->query($notif_sql);
    if ($notif_result) {
        while ($row = $notif_result->fetch_assoc()) {
            $notifications[] = $row;
        }
    }
} else {
    $notif_sql = "SELECT message, created_at 
                  FROM Notifications 
                  WHERE role='student' AND user_id=? 
                  ORDER BY created_at DESC LIMIT 5";
    $stmt = $conn->prepare($notif_sql);
    if ($stmt) {
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $notif_result = $stmt->get_result();
        while ($row = $notif_result->fetch_assoc()) {
            $notifications[] = $row;
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Documents</title>

  <!-- Bootstrap -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"/>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet"/>

  <style>
    body { background:#f8f9fa; }
    .main-content { min-height:100vh; }
    .page-header { display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; }
    .page-header h1 { font-size:24px; font-weight:600; color:#333; }
    .btn-set { background:#509CDB; border:none; padding:10px 20px; font-size:16px; color:#fff; }
    .btn-set:hover { background:#408CCB; }
    .documents-table { background:#fff; border-radius:8px; padding:20px; box-shadow:0 2px 5px rgba(0,0,0,0.1); }
    .documents-table .table th { background:#152259; color:#fff; }
    .file-icon { font-size:18px; margin-right:5px; color:#509CDB; }
    .notifications { background:#fff; border-radius:8px; padding:15px; box-shadow:0 2px 5px rgba(0,0,0,0.1); margin-bottom:20px; }
  </style>
</head>
<body>
<?php include 'sidebar.php'; ?>

<div class="main-content p-4">

  <!-- Notifications -->
  <div class="notifications mb-4">
    <h5>Notifications</h5>
    <?php if (count($notifications) > 0): ?>
      <ul class="list-group">
        <?php foreach ($notifications as $note): ?>
          <li class="list-group-item d-flex justify-content-between align-items-center">
            <?= htmlspecialchars($note['message']) ?>
            <small class="text-muted"><?= htmlspecialchars($note['created_at']) ?></small>
          </li>
        <?php endforeach; ?>
      </ul>
    <?php else: ?>
      <p class="text-muted">No notifications yet.</p>
    <?php endif; ?>
  </div>

  <!-- Page Header -->
  <div class="page-header">
    <h1>Documents</h1>
    <button class="btn btn-set" data-bs-toggle="modal" data-bs-target="#setRequiredDocsModal">
      <i class="bi bi-gear me-2"></i> Set Required Documents
    </button>
  </div>

  <!-- Documents Table -->
  <div class="documents-table">
    <?php if (isset($success_message)): ?>
      <div class="alert alert-success"><?= $success_message ?></div>
    <?php elseif (isset($error_message)): ?>
      <div class="alert alert-danger"><?= $error_message ?></div>
    <?php endif; ?>

    <table class="table table-hover">
      <thead>
        <tr>
          <th>Student ID</th>
          <th>Student Name</th>
          <th>Document Type</th>
          <th>File</th>
          <th>Uploaded At</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if ($result && $result->num_rows > 0): ?>
          <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
              <td><?= htmlspecialchars($row['student_id']) ?></td>
              <td><?= htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) ?></td>
              <td><?= htmlspecialchars($row['type']) ?></td>
              <td>
                <i class="bi bi-file-earmark-text file-icon"></i>
                <?= htmlspecialchars(basename($row['url'])) ?>
              </td>
              <td><?= htmlspecialchars($row['created_at']) ?></td>
              <td>
                <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#previewModal"
                        onclick="previewDocument('<?= $row['url'] ?>')">Preview</button>
                <a href="<?= $row['url'] ?>" class="btn btn-sm btn-success" download>Download</a>
              </td>
            </tr>
          <?php endwhile; ?>
        <?php else: ?>
          <tr><td colspan="6" class="text-center">No documents uploaded yet.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Preview Modal -->
<div class="modal fade" id="previewModal" tabindex="-1" aria-labelledby="previewModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title" id="previewModalLabel">Document Preview</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <iframe id="previewFrame" src="" width="100%" height="500px" style="border:none;"></iframe>
      </div>
    </div>
  </div>
</div>

<!-- Set Required Documents Modal -->
<div class="modal fade" id="setRequiredDocsModal" tabindex="-1" aria-labelledby="setRequiredDocsModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form method="POST" action="documents.php">
      <div class="modal-content">
        <div class="modal-header bg-primary text-white">
          <h5 class="modal-title" id="setRequiredDocsModalLabel">Set Required Documents</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="form-check">
            <input class="form-check-input" type="checkbox" name="required_docs[]" value="Transcript" id="transcript">
            <label class="form-check-label" for="transcript">Transcript</label>
          </div>
          <div class="form-check">
            <input class="form-check-input" type="checkbox" name="required_docs[]" value="ID Card" id="idcard">
            <label class="form-check-label" for="idcard">ID Card</label>
          </div>
          <div class="form-check">
            <input class="form-check-input" type="checkbox" name="required_docs[]" value="Recommendation Letter" id="recommendation">
            <label class="form-check-label" for="recommendation">Recommendation Letter</label>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary">Save Preferences</button>
        </div>
      </div>
    </form>
  </div>
</div>

<script>
function previewDocument(filePath) {
  document.getElementById("previewFrame").src = filePath;
}
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
$conn->close();
?>
