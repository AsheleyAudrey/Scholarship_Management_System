<?php 
include "../Database/db.php";

$conn = new mysqli($servername, $username, $password, $dbname, $port);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Verify database selection
if (!$conn->select_db($dbname)) {
    die("Database not found: $dbname");
}

// Check if necessary tables exist
$tablesToCheck = ['Users', 'ReviewCommittee', 'Reviews', 'Applications', 'Scholarships'];
foreach ($tablesToCheck as $table) {
    if ($conn->query("SHOW TABLES LIKE '$table'")->num_rows == 0) {
        die("$table table not found in database: $dbname");
    }
}
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Reviewer') {
    header("Location: ../login.php");
    exit();
}
$user_id = $_SESSION['user_id'];
// Fetch reviewer details
$reviewerQuery = "SELECT reviewer_id, name FROM ReviewCommittee WHERE user_id = ?";
$stmt = $conn->prepare($reviewerQuery);
if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}
$stmt->bind_param("i", $user_id);
$stmt->execute();
$reviewerResult = $stmt->get_result();
$reviewer = $reviewerResult->num_rows > 0 ? $reviewerResult->fetch_assoc() : null;
if (!$reviewer) {
    die("Reviewer not found for user_id: $user_id");
}
$reviewer_id = $reviewer['reviewer_id'];
$reviewer_name = $reviewer['name'];

// Quick Stats
// Total Applications Assigned
$totalAssignedQuery = "SELECT COUNT(*) AS count FROM Reviews WHERE reviewer_id = ?";
$stmt = $conn->prepare($totalAssignedQuery);
$stmt->bind_param("i", $reviewer_id);
$stmt->execute();
$totalAssigned = $stmt->get_result()->fetch_assoc()['count'] ?? 0;

// Pending Reviews
$pendingReviewsQuery = "SELECT COUNT(*) AS count FROM Reviews WHERE reviewer_id = ? AND decision = 'Pending'";
$stmt = $conn->prepare($pendingReviewsQuery);
$stmt->bind_param("i", $reviewer_id);
$stmt->execute();
$pendingReviews = $stmt->get_result()->fetch_assoc()['count'] ?? 0;

// Reviewed Applications
$reviewedApplicationsQuery = "SELECT COUNT(*) AS count FROM Reviews WHERE reviewer_id = ? AND decision IN ('Approved', 'Rejected', 'Needs More Info')";
$stmt = $conn->prepare($reviewedApplicationsQuery);
$stmt->bind_param("i", $reviewer_id);
$stmt->execute();
$reviewedApplications = $stmt->get_result()->fetch_assoc()['count'] ?? 0;

// Review Progress
$reviewProgress = $totalAssigned > 0 ? ($reviewedApplications / $totalAssigned) * 100 : 0;

// Average Score Given
$averageScoreQuery = "SELECT AVG(score) AS avg_score FROM Reviews WHERE reviewer_id = ? AND score IS NOT NULL";
$stmt = $conn->prepare($averageScoreQuery);
$stmt->bind_param("i", $reviewer_id);
$stmt->execute();
$averageScoreResult = $stmt->get_result()->fetch_assoc();
$averageScore = $averageScoreResult['avg_score'] ? number_format($averageScoreResult['avg_score'], 1) : 'N/A';

// Average Time to Review (in minutes)
// We'll assume the time to review is the difference between review_date and submission_date
$avgTimeQuery = "SELECT AVG(TIMESTAMPDIFF(MINUTE, a.submission_date, r.review_date)) AS avg_time
                 FROM Reviews r
                 JOIN Applications a ON r.application_id = a.application_id
                 WHERE r.reviewer_id = ? AND r.review_date IS NOT NULL";
$stmt = $conn->prepare($avgTimeQuery);
$stmt->bind_param("i", $reviewer_id);
$stmt->execute();
$avgTimeResult = $stmt->get_result()->fetch_assoc();
$averageTime = $avgTimeResult['avg_time'] ? round($avgTimeResult['avg_time']) : 45; // Default to 45 minutes if no data
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Reviewer Dashboard</title>
  
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <!-- Bootstrap Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />
  <style>
    .main-content {
      background-color: #f8f9fa;
      min-height: 100vh;
      padding: 20px;
    }
    .page-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 20px;
    }
    .page-header h1 {
      font-size: 24px;
      font-weight: 600;
      color: #333;
    }
    .quick-stats {
      display: flex;
      gap: 20px;
      margin-bottom: 20px;
      flex-wrap: wrap;
    }
    .stat-card {
      background-color: #ffffff;
      border-radius: 8px;
      padding: 15px;
      box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
      flex: 1;
      min-width: 200px;
      text-align: center;
    }
    .stat-card i {
      font-size: 24px;
      color: #509CDB;
      margin-bottom: 10px;
    }
    .stat-card h3 {
      font-size: 16px;
      font-weight: 600;
      color: #152259;
      margin-bottom: 5px;
    }
    .stat-card p {
      font-size: 24px;
      font-weight: 600;
      color: #333;
      margin: 0;
    }
    .dashboard-section {
      background-color: #ffffff;
      border-radius: 8px;
      padding: 20px;
      box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
      margin-bottom: 20px;
    }
    .dashboard-section h3 {
      font-size: 18px;
      font-weight: 600;
      color: #152259;
      margin-bottom: 15px;
    }
    .progress {
      height: 10px;
      margin-bottom: 10px;
    }
    .quick-stats-text {
      display: flex;
      justify-content: space-between;
      gap: 20px;
      flex-wrap: wrap;
    }
    .quick-stats-text div {
      flex: 1;
      min-width: 200px;
    }
    .quick-stats-text h4 {
      font-size: 16px;
      font-weight: 600;
      color: #152259;
      margin-bottom: 5px;
    }
    .quick-stats-text p {
      font-size: 14px;
      color: #333;
      margin: 0;
    }
  </style>
</head>
<body>
  <?php include 'sidebar.php'; ?>
  <div class="main-content">
    <div class="page-header">
      <h1>Reviewer Dashboard</h1>
    </div>
    <div class="quick-stats">
      <div class="stat-card">
        <i class="bi bi-journal-text"></i>
        <h3>Total Applications Assigned</h3>
        <p><?php echo $totalAssigned; ?></p>
      </div>
      <div class="stat-card">
        <i class="bi bi-hourglass-split"></i>
        <h3>Pending Reviews</h3>
        <p><?php echo $pendingReviews; ?></p>
      </div>
      <div class="stat-card">
        <i class="bi bi-check-circle"></i>
        <h3>Reviewed Applications</h3>
        <p><?php echo $reviewedApplications; ?></p>
      </div>
    </div>
    <div class="dashboard-section">
      <h3>Review Progress</h3>
      <div class="progress">
        <div class="progress-bar bg-success" role="progressbar" style="width: <?php echo $reviewProgress; ?>%" aria-valuenow="<?php echo $reviewProgress; ?>" aria-valuemin="0" aria-valuemax="100"></div>
      </div>
      <p>Review Completion: <?php echo $reviewedApplications; ?> / <?php echo $totalAssigned; ?> Applications</p>
    </div>
    <div class="dashboard-section">
      <h3>Quick Stats</h3>
      <div class="quick-stats-text">
        <div>
          <h4>Average Score Given</h4>
          <p><?php echo $averageScore; ?></p>
        </div>
        <div>
          <h4>Average Time to Review</h4>
          <p><?php echo $averageTime; ?> minutes</p>
        </div>
      </div>
    </div>
  </div>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
// Close database connection
$conn->close();
?>