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

// Check if Documents table exists
$documentsTableExists = $conn->query("SHOW TABLES LIKE 'Documents'")->num_rows > 0;

// Assume logged-in admin (user_id: 1, Ju Win)
// In a real system, use $_SESSION['user_id']
$admin_user_id = 1;

// Fetch Summary Boxes data
$scholarshipsAwardedQuery = "SELECT COUNT(*) AS awarded FROM Students WHERE status = 'Scholarship Awarded'";
$pendingApplicationsQuery = "SELECT COUNT(*) AS pending FROM Applications WHERE status IN ('Pending', 'Under Review')";
$activeStudentsQuery = "SELECT COUNT(*) AS active FROM Students WHERE status = 'Active'";

$scholarshipsAwardedResult = $conn->query($scholarshipsAwardedQuery);
$pendingApplicationsResult = $conn->query($pendingApplicationsQuery);
$activeStudentsResult = $conn->query($activeStudentsQuery);

$scholarshipsAwarded = $scholarshipsAwardedResult && $scholarshipsAwardedResult->num_rows > 0 ? $scholarshipsAwardedResult->fetch_assoc()['awarded'] : 0;
$pendingApplications = $pendingApplicationsResult && $pendingApplicationsResult->num_rows > 0 ? $pendingApplicationsResult->fetch_assoc()['pending'] : 0;
$activeStudents = $activeStudentsResult && $activeStudentsResult->num_rows > 0 ? $activeStudentsResult->fetch_assoc()['active'] : 0;

// Fetch Application Trends (last 6 months)
$trendsQuery = "SELECT DATE_FORMAT(submission_date, '%Y-%m') AS month, COUNT(*) AS count
                FROM Applications
                WHERE submission_date >= CURDATE() - INTERVAL 6 MONTH
                GROUP BY month
                ORDER BY month";
$trendsResult = $conn->query($trendsQuery);

$trendLabels = [];
$trendData = [];
if ($trendsResult && $trendsResult->num_rows > 0) {
    while ($row = $trendsResult->fetch_assoc()) {
        $trendLabels[] = $row['month'];
        $trendData[] = $row['count'];
    }
}

// Fetch Application Status
$statusQuery = "SELECT status, COUNT(*) AS count
                FROM Applications
                GROUP BY status";
$statusResult = $conn->query($statusQuery);

$statusLabels = [];
$statusData = [];
if ($statusResult && $statusResult->num_rows > 0) {
    while ($row = $statusResult->fetch_assoc()) {
        $statusLabels[] = $row['status'];
        $statusData[] = $row['count'];
    }
}

// Fetch Recent Activities (union of actions, conditionally include Documents)
$recentActivitiesQuery = "
    (SELECT 'New application submitted' AS action, CONCAT(s.first_name, ' ', s.last_name, ' for ', sc.name) AS details, a.submission_date AS action_date
     FROM Applications a
     JOIN Students s ON a.student_id = s.student_id
     JOIN Scholarships sc ON a.scholarship_id = sc.scholarship_id
     ORDER BY a.submission_date DESC
     LIMIT 2)
    UNION
    (SELECT 'Scholarship awarded' AS action, CONCAT(s.first_name, ' ', s.last_name) AS details, s.enrollment_date AS action_date
     FROM Students s
     WHERE s.status = 'Scholarship Awarded'
     ORDER BY s.enrollment_date DESC
     LIMIT 1)
    UNION
    (SELECT 'New student registered' AS action, CONCAT(s.first_name, ' ', s.last_name) AS details, u.created_at AS action_date
     FROM Students s
     JOIN Users u ON s.user_id = u.user_id
     ORDER BY u.created_at DESC
     LIMIT 1)
";
if ($documentsTableExists) {
    $recentActivitiesQuery .= "
    UNION
    (SELECT 'Document uploaded' AS action, CONCAT(s.first_name, ' ', s.last_name, ': ', d.document_type) AS details, d.uploaded_at AS action_date
     FROM Documents d
     JOIN Applications a ON d.application_id = a.application_id
     JOIN Students s ON a.student_id = s.student_id
     ORDER BY d.uploaded_at DESC
     LIMIT 1)
    ";
}
$recentActivitiesQuery .= " ORDER BY action_date DESC LIMIT 4";

$recentActivitiesResult = $conn->query($recentActivitiesQuery);
if (!$recentActivitiesResult) {
    $recentActivitiesError = "Error fetching recent activities: " . $conn->error;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Admin Dashboard</title>
  
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <!-- Bootstrap Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />
  <!-- Chart.js -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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

    /* Welcome message */
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

    /* Summary boxes */
    .summary-boxes {
      display: flex;
      gap: 20px;
      PRESENT
      margin-bottom: 20px;
      flex-wrap: wrap;
    }

    .summary-boxes .card {
      flex: 1;
      min-width: 200px;
      border: none;
      box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
      text-align: center;
      padding: 15px;
    }

    .summary-boxes .card i {
      font-size: 24px;
      color: #509CDB;
      margin-bottom: 10px;
    }

    .summary-boxes .card h3 {
      font-size: 16px;
      font-weight: 600;
      color: #152259;
      margin-bottom: 5px;
    }

    .summary-boxes .card p {
      font-size: 24px;
      font-weight: 600;
      color: #333;
      margin: 0;
    }

    /* Charts and Recent Activities */
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

    .dashboard-section canvas {
      max-width: 100%;
      height: 300px;
    }

    .dashboard-section .activity-item {
      display: flex;
      align-items: center;
      padding: 10px 0;
      border-bottom: 1px solid #eee;
    }

    .dashboard-section .activity-item:last-child {
      border-bottom: none;
    }

    .dashboard-section .activity-item i {
      font-size: 20px;
      margin-right: 10px;
      color: #509CDB;
    }

    .dashboard-section .activity-item span {
      font-size: 14px;
      color: #333;
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
      <h1>Admin Dashboard</h1>
    </div>

    <!-- Welcome Message -->
    <div class="welcome-message">
      <h2>Welcome, Admin!</h2>
      <p>Here’s an overview of the scholarship management system. Monitor applications, scholarships, and student activity.</p>
    </div>

    <!-- Summary Boxes -->
    <div class="summary-boxes">
      <div class="card">
        <i class="bi bi-award"></i>
        <h3>Total Scholarships Awarded</h3>
        <p><?php echo $scholarshipsAwarded; ?></p>
      </div>
      <div class="card">
        <i class="bi bi-journal-text"></i>
        <h3>Pending Applications</h3>
        <p><?php echo $pendingApplications; ?></p>
      </div>
      <div class="card">
        <i class="bi bi-people"></i>
        <h3>Active Students</h3>
        <p><?php echo $activeStudents; ?></p>
      </div>
    </div>

    <!-- Charts -->
    <div class="row">
      <div class="col-md-6">
        <div class="dashboard-section">
          <h3>Application Trends (Line Chart)</h3>
          <canvas id="applicationTrendsLineChart"></canvas>
        </div>
      </div>
      <div class="col-md-6">
        <div class="dashboard-section">
          <h3>Application Status (Bar Chart)</h3>
          <canvas id="applicationStatusBarChart"></canvas>
        </div>
      </div>
    </div>

    <!-- Recent Activities -->
    <div class="dashboard-section">
      <h3>Recent Activities</h3>
      <?php if (isset($recentActivitiesError)): ?>
        <p><?php echo htmlspecialchars($recentActivitiesError); ?></p>
      <?php elseif ($recentActivitiesResult && $recentActivitiesResult->num_rows > 0): ?>
        <?php while ($row = $recentActivitiesResult->fetch_assoc()): ?>
          <div class="activity-item">
            <i class="bi <?php
              echo $row['action'] == 'New application submitted' ? 'bi-journal-text' :
                   ($row['action'] == 'Scholarship awarded' ? 'bi-award' :
                   ($row['action'] == 'New student registered' ? 'bi-person-check' : 'bi-file-earmark-text'));
            ?>"></i>
            <span><?php echo htmlspecialchars($row['action'] . ': ' . $row['details']); ?></span>
          </div>
        <?php endwhile; ?>
      <?php else: ?>
        <p>No recent activities found.</p>
      <?php endif; ?>
    </div>
  </div>

  <!-- Chart.js Scripts -->
  <script>
    // Application Trends Line Chart
    const trendsCtx = document.getElementById('applicationTrendsLineChart').getContext('2d');
    new Chart(trendsCtx, {
      type: 'line',
      data: {
        labels: <?php echo json_encode($trendLabels); ?>,
        datasets: [{
          label: 'Applications Submitted',
          data: <?php echo json_encode($trendData); ?>,
          borderColor: '#509CDB',
          backgroundColor: 'rgba(80, 156, 219, 0.2)',
          fill: true,
          tension: 0.4
        }]
      },
      options: {
        responsive: true,
        scales: {
          y: {
            beginAtZero: true,
            title: { display: true, text: 'Number of Applications' }
          },
          x: {
            title: { display: true, text: 'Month' }
          }
        }
      }
    });

    // Application Status Bar Chart
    const statusCtx = document.getElementById('applicationStatusBarChart').getContext('2d');
    new Chart(statusCtx, {
      type: 'bar',
      data: {
        labels: <?php echo json_encode($statusLabels); ?>,
        datasets: [{
          label: 'Applications by Status',
          data: <?php echo json_encode($statusData); ?>,
          backgroundColor: ['#509CDB', '#FF6B6B', '#4ECDC4', '#45B7D1', '#96CEB4'],
          borderColor: ['#509CDB', '#FF6B6B', '#4ECDC4', '#45B7D1', '#96CEB4'],
          borderWidth: 1
        }]
      },
      options: {
        responsive: true,
        scales: {
          y: {
            beginAtZero: true,
            title: { display: true, text: 'Number of Applications' }
          },
          x: {
            title: { display: true, text: 'Status' }
          }
        }
      }
    });
  </script>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
// Close database connection
$conn->close();
?>