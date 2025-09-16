<?php
include "../Database/db.php";
session_start();

// Fetch all uploaded documents with student info
$sql = "SELECT d.document_id, d.application_id, d.url, d.type, d.created_at, d.updated_at,
               s.student_id, s.first_name, s.last_name
        FROM Document d
        JOIN Applications a ON d.application_id = a.application_id
        JOIN Students s ON a.student_id = s.student_id
        ORDER BY d.created_at DESC";

$result = $conn->query($sql);
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
    .main-content { background:#f8f9fa; min-height:100vh; }
    .page-header { display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; }
    .page-header h1 { font-size:24px; font-weight:600; color:#333; }
    .page-header .btn-set { background:#509CDB; border:none; padding:10px 20px; font-size:16px; color:#fff; }
    .page-header .btn-set:hover { background:#408CCB; }
    .documents-table { background:#fff; border-radius:8px; padding:20px; box-shadow:0 2px 5px rgba(0,0,0,0.1); }
    .documents-table .table th { background:#152259; color:#fff; }
    .file-icon { font-size:18px; margin-right:5px; color:#509CDB; }
  </style>
</head>
<body>
<?php include 'sidebar.php'; ?>

<div class="main-content p-4">
  <!-- Header -->
  <div class="page-header">
    <h1>Documents</h1>
  </div>

  <!-- Documents Table -->
  <div class="documents-table">
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

<script>
function previewDocument(filePath) {
  document.getElementById("previewFrame").src = filePath;
}
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php $conn->close(); ?>
