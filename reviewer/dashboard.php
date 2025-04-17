<?php
// Start session for reviewer authentication
session_start();

// Check if reviewer is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Reviewer') {
    header("Location: ../login.php");
    exit();
}

// Database connection with error handling
$servername = "localhost";
$username = "root";
$password = "password"; // Replace with your actual MySQL root password
$dbname = "Scholarship_db"; // Match database name from SQL file
$port = 3306;

$conn = new mysqli($servername, $username, $password, $dbname, $port);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Verify database selection
if (!$conn->select_db($dbname)) {
    die("Database not found: $dbname");
}

// Check if Reviews table exists
$reviewsTableExists = $conn->query("SHOW TABLES LIKE 'Reviews'")->num_rows > 0;

// Get logged-in reviewer info from session
$user_id = $_SESSION['user_id'];
$reviewerQuery = "SELECT r.reviewer_id, u.username
                  FROM ReviewCommittee r
                  JOIN Users u ON r.user_id = u.user_id
                  WHERE r.user_id = ?";
$stmt = $conn->prepare($reviewerQuery);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$reviewerResult = $stmt->get_result();
$reviewer = $reviewerResult->num_rows > 0 ? $reviewerResult->fetch_assoc() : null;
$reviewer_id = $reviewer ? $reviewer['reviewer_id'] : null;
$reviewer_name = $reviewer ? $reviewer['username'] : 'Reviewer';

// Stats Cards
$totalApplicationsQuery = $reviewsTableExists ?
    "SELECT COUNT(*) AS count FROM Reviews WHERE reviewer_id = ?" :
    "SELECT 0 AS count";
$stmt = $conn->prepare($totalApplicationsQuery);
$stmt->bind_param("i", $reviewer_id);
$stmt->execute();
$totalApplications = $stmt->get_result()->fetch_assoc()['count'] ?? 0;

$pendingReviewsQuery = $reviewsTableExists ?
    "SELECT COUNT(*) AS count FROM Reviews WHERE reviewer_id = ? AND score IS NULL" :
    "SELECT 0 AS count";
$stmt = $conn->prepare($pendingReviewsQuery);
$stmt->bind_param("i", $reviewer_id);
$stmt->execute();
$pendingReviews = $stmt->get_result()->fetch_assoc()['count'] ?? 0;

$reviewedApplicationsQuery = $reviewsTableExists ?
    "SELECT COUNT(*) AS count FROM Reviews WHERE reviewer_id = ? AND score IS NOT NULL" :
    "SELECT 0 AS count";
$stmt = $conn->prepare($reviewedApplicationsQuery);
$stmt->bind_param("i", $reviewer_id);
$stmt->execute();
$reviewedApplications = $stmt->get_result()->fetch_assoc()['count'] ?? 0;

// Review Progress
$progressPercentage = $totalApplications > 0 ? round(($reviewedApplications / $totalApplications) * 100) : 0;

// Quick Stats
$averageScoreQuery = $reviewsTableExists ?
    "SELECT AVG(score) AS avg_score FROM Reviews WHERE reviewer_id = ? AND score IS NOT NULL" :
    "SELECT 0 AS avg_score";
$stmt = $conn->prepare($averageScoreQuery);
$stmt->bind_param("i", $reviewer_id);
$stmt->execute();
$averageScore = $stmt->get_result()->fetch_assoc()['avg_score'] ?? 0;
$averageScore = round($averageScore, 1);

// Note: Schema lacks review duration; using placeholder
$averageTime = "45 minutes"; // Replace with actual data if schema is updated

$weeklyReviewsQuery = $reviewsTableExists ?
    "SELECT COUNT(*) AS count FROM Reviews WHERE reviewer_id = ? AND review_date >= CURDATE() - INTERVAL 7 DAY AND score IS NOT NULL" :
    "SELECT 0 AS count";
$stmt = $conn->prepare($weeklyReviewsQuery);
$stmt->bind_param("i", $reviewer_id);
$stmt->execute();
$weeklyReviews = $stmt->get_result()->fetch_assoc()['count'] ?? 0;

// Recent Activity
$recentActivityQuery = $reviewsTableExists ?
    "SELECT CONCAT('Reviewed application A', LPAD(a.application_id, 3, '0'), ' for ', s.first_name, ' ', s.last_name) AS activity,
            r.review_date
     FROM Reviews r
     JOIN Applications a ON r.application_id = a.application_id
     JOIN Students s ON a.student_id = s.student_id
     WHERE r.reviewer_id = ?
     ORDER BY r.review_date DESC
     LIMIT 3" :
    "SELECT 'No activity' AS activity, NOW() AS review_date";
$stmt = $conn->prepare($recentActivityQuery);
$stmt->bind_param("i", $reviewer_id);
$stmt->execute();
$recentActivityResult = $stmt->get_result();

// Notification Highlights
$notificationsQuery = "SELECT message, 
                             CASE 
                                 WHEN message LIKE 'New application assigned%' THEN 'New'
                                 WHEN message LIKE 'Deadline approaching%' THEN 'Urgent'
                                 ELSE 'Info'
                             END AS badge_type
                      FROM Notifications
                      WHERE user_id = ? AND status = 'Unread'
                      ORDER BY date_created DESC
                      LIMIT 3";
$stmt = $conn->prepare($notificationsQuery);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$notificationsResult = $stmt->get_result();
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
    /* Adjust main content to account for fixed sidebar */
    .main-content {
      background-color: #f8f9fa;
      min-height: 100vh;
    }

    /* Header styling */
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

    /* Stats Cards */
    .stats-card {
      background-color: #ffffff;
      border-radius: 8px;
      padding: 20px;
      box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
      text-align: center;
      transition: transform 0.2s;
    }

    .stats-card:hover {
      transform: translateY(-5px);
    }

    .stats-card h3 {
      font-size: 18px;
      font-weight: 600;
      color: #152259;
      margin-bottom: 10px;
    }

    .stats-card p {
      font-size: 24px;
      font-weight: 600;
      color: #333;
      margin: 0;
    }

    /* Progress Bar Section */
    .progress-section {
      background-color: #ffffff;
      border-radius: 8px;
      padding: 20px;
      box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
      margin-bottom: 20px;
    }

    .progress-section h3 {
      font-size: 18px;
      font-weight: 600;
      color: #152259;
      margin-bottom: 15px;
    }

    .progress-section .progress {
      height: 25px;
      border-radius: 5px;
      margin-bottom: 10px;
    }

    .progress-section .progress-bar {
      background-color: #509CDB;
    }

    .progress-section .progress-label {
      font-size: 14px;
      color: #333;
      margin-bottom: 5px;
    }

    /* Quick Stats Section */
    .quick-stats {
      background-color: #ffffff;
      border-radius: 8px;
      padding: 20px;
      box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
      margin-bottom: 20px;
    }

    .quick-stats h3 {
      font-size: 18px;
      font-weight: 600;
      color: #152259;
      margin-bottom: 15px;
    }

    .quick-stats .stat-item {
      display: flex;
      justify-content: space-between;
      padding: 10px 0;
      border-bottom: 1px solid #dee2e6;
    }

    .quick-stats .stat-item:last-child {
      border-bottom: none;
    }

    .quick-stats .stat-item span {
      font-size: 16px;
      color: #333;
    }

    /* Recent Activity and Notifications */
    .activity-section,
    .notifications-section {
      background-color: #ffffff;
      border-radius: 8px;
      padding: 20px;
      box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
      margin-bottom: 20px;
    }

    .activity-section h3,
    .notifications-section h3 {
      font-size: 18px;
      font-weight: 600;
      color: #152259;
      margin-bottom: 15px;
    }

    .activity-section .list-group-item,
    .notifications-section .list-group-item {
      border: none;
      padding: 10px 0;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .activity-section .list-group-item span,
    .notifications-section .list-group-item span {
      font-size: 14px;
      color: #333;
    }

    .notifications-section .list-group-item .badge {
      font-size: 12px;
    }
  </style>
</head>
<body>
  <!-- Sidebar -->
  <?php include 'sidebar.php'; ?>
  <!-- Main content -->
  <div class="main-content">
    <!-- Header -->
    <div class="page-header">
      <h1>Reviewer Dashboard</h1>
    </div>

    <!-- Stats Cards -->
    <div class="row mb-4">
      <div class="col-md-4">
        <div class="stats-card">
          <h3>Total Applications Assigned</h3>
          <p><?php echo $totalApplications; ?></p>
        </div>
      </div>
      <div class="col-md-4">
        <div class="stats-card">
          <h3>Pending Reviews</h3>
          <p><?php echo $pendingReviews; ?></p>
        </div>
      </div>
      <div class="col-md-4">
        <div class="stats-card">
          <h3>Reviewed Applications</h3>
          <p><?php echo $reviewedApplications; ?></p>
        </div>
      </div>
    </div>

    <!-- Progress Bar Section -->
    <div class="progress-section">
      <h3>Review Progress</h3>
      <div class="progress-label">Review Completion: <?php echo $reviewedApplications; ?> / <?php echo $totalApplications; ?> Applications</div>
      <div class="progress">
        <div class="progress-bar" role="progressbar" style="width: <?php echo $progressPercentage; ?>%" aria-valuenow="<?php echo $progressPercentage; ?>" aria-valuemin="0" aria-valuemax="100"><?php echo $progressPercentage; ?>%</div>
      </div>
    </div>

    <!-- Quick Stats Section -->
    <div class="quick-stats">
      <h3>Quick Stats</h3>
      <div class="stat-item">
        <span>Average Score Given</span>
        <span><?php echo $averageScore > 0 ? $averageScore . '%' : 'N/A'; ?></span>
      </div>
      <div class="stat-item">
        <span>Average Time to Review</span>
        <span><?php echo $averageTime; ?></span>
      </div>
      <div class="stat-item">
        <span>Reviews Completed This Week</span>
        <span><?php echo $weeklyReviews; ?></span>
      </div>
    </div>

    <!-- Recent Activity and Notifications -->
    <div class="row">
      <div class="col-md-6">
        <div class="activity-section">
          <h3>Recent Activity</h3>
          <ul class="list-group">
            <?php if ($recentActivityResult && $recentActivityResult->num_rows > 0): ?>
              <?php while ($row = $recentActivityResult->fetch_assoc()): ?>
                <li class="list-group-item">
                  <span><?php echo htmlspecialchars($row['activity']); ?></span>
                  <span><?php echo htmlspecialchars($row['review_date']); ?></span>
                </li>
              <?php endwhile; ?>
            <?php else: ?>
              <li class="list-group-item">
                <span>No recent activity found.</span>
                <span>-</span>
              </li>
            <?php endif; ?>
          </ul>
        </div>
      </div>
      <div class="col-md-6">
        <div class="notifications-section">
          <h3>Notification Highlights</h3>
          <ul class="list-group">
            <?php if ($notificationsResult && $notificationsResult->num_rows > 0): ?>
              <?php while ($row = $notificationsResult->fetch_assoc()): ?>
                <li class="list-group-item">
                  <span><?php echo htmlspecialchars($row['message']); ?></span>
                  <span class="badge <?php
                    echo $row['badge_type'] == 'New' ? 'bg-primary' :
                         ($row['badge_type'] == 'Urgent' ? 'bg-warning' : 'bg-info');
                  ?>"><?php echo htmlspecialchars($row['badge_type']); ?></span>
                </li>
              <?php endwhile; ?>
            <?php else: ?>
              <li class="list-group-item">
                <span>No unread notifications found.</span>
                <span class="badge bg-secondary">None</span>
              </li>
            <?php endif; ?>
          </ul>
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