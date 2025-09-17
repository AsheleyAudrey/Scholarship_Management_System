<?php
session_start();
include "../Database/db.php";

// Check login
if (!isset($_SESSION['user_id'])) {
    die("You must be logged in to view application details.");
}

$user_id = $_SESSION['user_id'];
$app_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($app_id <= 0) {
    die("Invalid application ID.");
}

// log user id


// Connect
$conn = new mysqli($servername, $username, $password, $dbname, $port);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get student_id
$studentQuery = "SELECT student_id FROM Students WHERE user_id = ?";
$stmt = $conn->prepare($studentQuery);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    die("No student found for this user.");
}
$student_id = $result->fetch_assoc()['student_id'];
// Fetch application details (make sure it belongs to the student)
$query = "
    SELECT a.application_id, s.name AS scholarship_name, a.status, 
           a.submission_date, a.document_url
    FROM Applications a
    JOIN Scholarships s ON a.scholarship_id = s.scholarship_id
    WHERE a.application_id = ? AND a.student_id = ?
";
$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $app_id, $student_id);
$stmt->execute();
$appResult = $stmt->get_result();

if ($appResult->num_rows === 0) {
    die("Application not found or access denied.");
}

$application = $appResult->fetch_assoc();

echo "<script>console.log('User ID: ' + " . json_encode($application) . ");</script>";
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Application Details</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"/>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet"/>
  <style>
    .main-content { background-color: #f8f9fa; min-height: 100vh; padding: 20px; }
    .card { margin-bottom: 20px; }
    .card-header { background: #152259; color: #fff; font-weight: 600; }
  </style>
</head>
<body>
<?php include 'sidebar.php'; ?>

<div class="main-content">
  <div class="card">
    <div class="card-header">
      Application Details
    </div>
    <div class="card-body">
      <h5 class="card-title"><?= htmlspecialchars($application['scholarship_name']) ?></h5>
      <p><strong>Status:</strong> 
        <?php 
          $statusClass = [
            "Pending" => "badge bg-warning",
            "Under Review" => "badge bg-primary",
            "Approved" => "badge bg-success",
            "Rejected" => "badge bg-danger"
          ][$application['status']] ?? "badge bg-secondary";
        ?>
        <span class="<?= $statusClass ?>"><?= htmlspecialchars($application['status']) ?></span>
      </p>
      <p><strong>Date Applied:</strong> <?= htmlspecialchars($application['submission_date']) ?></p>
      <?php if (!empty($application['document_url'])): ?>
        <p><strong>Submitted Document:</strong> 
          <a href="<?= htmlspecialchars($application['document_url']) ?>" target="_blank" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-file-earmark-text"></i> View Document
          </a>
        </p>
      <?php else: ?>
        <p><strong>Submitted Document:</strong> None</p>
      <?php endif; ?>

      <div class="mt-3">
        <a href="applications.php" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Back to Applications</a>
        <?php if ($application['status'] == "Pending" || $application['status'] == "Under Review"): ?>
          <a href="withdraw_application.php?id=<?= $application['application_id'] ?>" class="btn btn-danger">
            <i class="bi bi-x-circle"></i> Withdraw
          </a>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
