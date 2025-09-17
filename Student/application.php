<?php 
session_start();
include "../Database/db.php";

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    die("You must be logged in to view applications.");
}

$user_id = $_SESSION['user_id'];

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

// Fetch submitted applications only (exclude drafts)
$applicationsQuery = "
    SELECT a.application_id, s.name AS scholarship_name, a.status, DATE(a.submission_date) AS date_applied
    FROM Applications a
    JOIN Scholarships s ON a.scholarship_id = s.scholarship_id
    WHERE a.student_id = ? AND a.status != 'Draft'
    ORDER BY a.submission_date DESC
";
$stmt = $conn->prepare($applicationsQuery);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$applicationsResult = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Applications</title>
  
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"/>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet"/>

  <style>
    .main-content { background-color: #f8f9fa; min-height: 100vh; }
    .page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
    .page-header h1 { font-size: 24px; font-weight: 600; color: #333; }
    .table-section { background: #fff; border-radius: 8px; padding: 20px; box-shadow: 0 2px 5px rgba(0,0,0,.1); margin-bottom: 20px; }
    .table-section h3 { font-size: 18px; font-weight: 600; color: #152259; margin-bottom: 15px; }
    .table th { background: #152259; color: #fff; }
    .table td { vertical-align: middle; }
    .table .badge { font-size: 12px; }
    .btn-view { background: #6c757d; border: none; margin-right: 5px; }
    .btn-view:hover { background: #5a6268; }
    .btn-withdraw { background: #dc3545; border: none; }
    .btn-withdraw:hover { background: #c82333; }
  </style>
</head>
<body>
<?php include 'sidebar.php'; ?>

<div class="main-content">
  <div class="page-header">
    <h1>Applications</h1>
  </div>

  <div class="table-section">
    <h3>Submitted Applications</h3>
    <table class="table table-hover">
      <thead>
        <tr>
          <th>Scholarship Name</th>
          <th>Status</th>
          <th>Date Applied</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if ($applicationsResult->num_rows > 0): ?>
          <?php while ($row = $applicationsResult->fetch_assoc()): ?>
            <tr>
              <td><?= htmlspecialchars($row['scholarship_name']) ?></td>
              <td>
                <?php 
                  $statusClass = [
                    "Pending" => "bg-warning",
                    "Under Review" => "bg-primary",
                    "Approved" => "bg-success",
                    "Rejected" => "bg-danger"
                  ][$row['status']] ?? "bg-secondary";
                ?>
                <span class="badge <?= $statusClass ?>"><?= htmlspecialchars($row['status']) ?></span>
              </td>
              <td><?= htmlspecialchars($row['date_applied']) ?></td>
              <td>
                <a href="view_application.php?id=<?= $row['application_id'] ?>" class="btn btn-view"><i class="bi bi-eye"></i> View Details</a>
                <?php if ($row['status'] == "Pending" || $row['status'] == "Under Review"): ?>
                  <a href="withdraw_application.php?id=<?= $row['application_id'] ?>" class="btn btn-withdraw"><i class="bi bi-x-circle"></i> Withdraw</a>
                <?php endif; ?>
              </td>
            </tr>
          <?php endwhile; ?>
        <?php else: ?>
          <tr>
            <td colspan="4" class="text-center">No submitted applications found.</td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
