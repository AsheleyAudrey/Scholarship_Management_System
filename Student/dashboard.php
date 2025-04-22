<?php
// Start session for student authentication
session_start();

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection with error handling
$servername = "localhost";
$username = "root";
$password = "password"; // Replace with your actual MySQL root password
$dbname = "Scholarship_db";
$port = 3306;

$conn = new mysqli($servername, $username, $password, $dbname, $port);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Verify database selection
if (!$conn->select_db($dbname)) {
    die("Database not found: $dbname");
}

// Check if tables exist
$documentsTableExists = $conn->query("SHOW TABLES LIKE 'Documents'")->num_rows > 0;
$scholarshipsTableExists = $conn->query("SHOW TABLES LIKE 'Scholarships'")->num_rows > 0;
if (!$scholarshipsTableExists) {
    die("Scholarships table not found in database: $dbname");
}

// Assume logged-in student (Amber David, user_id: 4, student_id: 1)
$user_id = 4;
$studentQuery = "SELECT student_id, first_name, last_name FROM Students WHERE user_id = ?";
$stmt = $conn->prepare($studentQuery);
if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}
$stmt->bind_param("i", $user_id);
$stmt->execute();
$studentResult = $stmt->get_result();
$student = $studentResult->num_rows > 0 ? $studentResult->fetch_assoc() : null;
$student_id = $student ? $student['student_id'] : null;
$student_name = $student ? $student['first_name'] . ' ' . $student['last_name'] : 'Student';

// Quick Stats
$applicationsSubmittedQuery = "SELECT COUNT(*) AS count FROM Applications WHERE student_id = ?";
$stmt = $conn->prepare($applicationsSubmittedQuery);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$applicationsSubmitted = $stmt->get_result()->fetch_assoc()['count'] ?? 0;

$pendingDocumentsQuery = $documentsTableExists ?
    "SELECT COUNT(DISTINCT a.application_id) AS count
     FROM Applications a
     LEFT JOIN Documents d ON a.application_id = d.application_id
     WHERE a.student_id = ? AND d.document_id IS NULL" :
    "SELECT 0 AS count";
$stmt = $conn->prepare($pendingDocumentsQuery);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$pendingDocuments = $stmt->get_result()->fetch_assoc()['count'] ?? 0;

$unreadNotificationsQuery = "SELECT COUNT(*) AS count FROM Notifications WHERE user_id = ? AND status = 'Unread'";
$stmt = $conn->prepare($unreadNotificationsQuery);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$unreadNotifications = $stmt->get_result()->fetch_assoc()['count'] ?? 0;

// Recent Applications
$recentApplicationsQuery = "SELECT s.name AS scholarship_name, a.status, DATE(a.submission_date) AS date_applied
                           FROM Applications a
                           JOIN Scholarships s ON a.scholarship_id = s.scholarship_id
                           WHERE a.student_id = ?
                           ORDER BY a.submission_date DESC
                           LIMIT 3";
$stmt = $conn->prepare($recentApplicationsQuery);
if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}
$stmt->bind_param("i", $student_id);
if (!$stmt->execute()) {
    die("Execute failed: " . $stmt->error);
}
$recentApplicationsResult = $stmt->get_result();
if (!$recentApplicationsResult) {
    die("Query failed: " . $conn->error);
}

// Upcoming Deadlines
$upcomingDeadlinesQuery = "SELECT name, application_end AS application_deadline
                          FROM Scholarships
                          WHERE status = 'Open' AND application_end >= CURDATE()
                          ORDER BY application_end ASC
                          LIMIT 3";
$upcomingDeadlinesResult = $conn->query($upcomingDeadlinesQuery);
if (!$upcomingDeadlinesResult) {
    die("Query failed: " . $conn->error);
}

// Notifications
$notificationsQuery = "SELECT message
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
  <title>Student Dashboard</title>
  
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <!-- Bootstrap Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />
  <style>
    .main-content {
      background-color: #f8f9fa;
      min-height: 100vh;
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
    .welcome-message {
      background-color: #ffffff;
      border-radius: 8px;
      padding: 20px;
      box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
      margin-bottom: 20px;
    }
    .welcome-message h2 {
      font-size: 20px;
      font-weight: 600;
      color: #152259;
      margin-bottom: 10px;
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
    .dashboard-section .table {
      margin-bottom: 0;
    }
    .dashboard-section .table th {
      background-color: #152259;
      color: #ffffff;
    }
    .dashboard-section .table td {
      vertical-align: middle;
    }
    .dashboard-section .table .badge {
      font-size: 12px;
    }
    .dashboard-section .deadline-item {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 10px 0;
      border-bottom: 1px solid #eee;
    }
    .dashboard-section .deadline-item:last-child {
      border-bottom: none;
    }
    .dashboard-section .deadline-item p {
      margin: 0;
      font-size: 14px;
      color: #333;
    }
    .dashboard-section .deadline-item .date {
      font-size: 14px;
      color: #dc3545;
    }
    .dashboard-section .notification-item {
      display: flex;
      align-items: center;
      padding: 10px 0;
      border-bottom: 1px solid #eee;
    }
    .dashboard-section .notification-item:last-child {
      border-bottom: none;
    }
    .dashboard-section .notification-item i {
      font-size: 20px;
      margin-right: 10px;
      color: #509CDB;
    }
    .dashboard-section .notification-item span {
      font-size: 14px;
      color: #333;
    }
    .dashboard-section .quick-links {
      display: flex;
      gap: 15px;
      flex-wrap: wrap;
    }
    .dashboard-section .quick-links .btn {
      background-color: #509CDB;
      border: none;
      padding: 10px 20px;
      font-size: 14px;
      color: #ffffff;
    }
    .dashboard-section .quick-links .btn:hover {
      background-color: #408CCB;
    }
  </style>
</head>
<body>
  <?php include 'sidebar.php'; ?>
  <div class="main-content">
    <div class="page-header">
      <h1>Scholarship Management Dashboard</h1>
    </div>
    <div class="welcome-message">
      <h2>Welcome, <?php echo htmlspecialchars($student_name); ?>!</h2>
      <p>Here’s an overview of your scholarship journey. Stay on top of your applications and deadlines.</p>
    </div>
    <div class="quick-stats">
      <div class="stat-card">
        <i class="bi bi-journal-text"></i>
        <h3>Applications Submitted</h3>
        <p><?php echo $applicationsSubmitted; ?></p>
      </div>
      <div class="stat-card">
        <i class="bi bi-upload"></i>
        <h3>Pending Documents</h3>
        <p><?php echo $pendingDocuments; ?></p>
      </div>
      <div class="stat-card">
        <i class="bi bi-bell"></i>
        <h3>Unread Notifications</h3>
        <p><?php echo $unreadNotifications; ?></p>
      </div>
    </div>
    <div class="dashboard-section">
      <h3>Recent Applications</h3>
      <?php if ($recentApplicationsResult && $recentApplicationsResult->num_rows > 0): ?>
        <table class="table table-hover">
          <thead>
            <tr>
              <th>Scholarship Name</th>
              <th>Status</th>
              <th>Date Applied</th>
            </tr>
          </thead>
          <tbody>
            <?php while ($row = $recentApplicationsResult->fetch_assoc()): ?>
              <tr>
                <td><?php echo htmlspecialchars($row['scholarship_name']); ?></td>
                <td>
                  <span class="badge <?php
                      $badgeClass = match ($row['status']) {
                          'Submitted' => 'bg-info',
                          'Pending' => 'bg-warning',
                          'Under Review' => 'bg-primary',
                          'Approved' => 'bg-success',
                          'Accepted' => 'bg-success',
                          'Rejected' => 'bg-danger',
                          'Needs More Info' => 'bg-secondary',
                          default => 'bg-secondary'
                      };
                      echo $badgeClass;
                  ?>">
                    <?php echo htmlspecialchars($row['status']); ?>
                  </span>
                </td>
                <td><?php echo htmlspecialchars($row['date_applied']); ?></td>
              </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      <?php else: ?>
        <p>No recent applications found.</p>
      <?php endif; ?>
    </div>
    <div class="dashboard-section">
      <h3>Upcoming Deadlines</h3>
      <?php if ($upcomingDeadlinesResult && $upcomingDeadlinesResult->num_rows > 0): ?>
        <?php while ($row = $upcomingDeadlinesResult->fetch_assoc()): ?>
          <div class="deadline-item">
            <p><?php echo htmlspecialchars($row['name']); ?></p>
            <div class="date"><?php echo htmlspecialchars($row['application_deadline']); ?></div>
          </div>
        <?php endwhile; ?>
      <?php else: ?>
        <p>No upcoming deadlines found.</p>
      <?php endif; ?>
    </div>
    <div class="dashboard-section">
      <h3>Notifications</h3>
      <?php if ($notificationsResult && $notificationsResult->num_rows > 0): ?>
        <?php while ($row = $notificationsResult->fetch_assoc()): ?>
          <div class="notification-item">
            <i class="bi bi-bell"></i>
            <span><?php echo htmlspecialchars($row['message']); ?></span>
          </div>
        <?php endwhile; ?>
      <?php else: ?>
        <p>No unread notifications found.</p>
      <?php endif; ?>
    </div>
    <div class="dashboard-section">
      <h3>Quick Links</h3>
      <div class="quick-links">
        <a href="#" class="btn"><i class="bi bi-pencil-square me-2"></i> Apply Now</a>
        <a href="#" class="btn"><i class="bi bi-upload me-2"></i> Upload Documents</a>
        <a href="#" class="btn"><i class="bi bi-bell me-2"></i> View Notifications</a>
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