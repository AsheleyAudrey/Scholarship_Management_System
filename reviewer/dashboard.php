<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Reviewer Dashboard</title>
  
  <!-- Bootstrap CSS -->
  <link
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
    rel="stylesheet"
  />
  <!-- Bootstrap Icons -->
  <link
    href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css"
    rel="stylesheet"
  />
  <style>
    /* Adjust main content to account for fixed sidebar */
    .main-content {
      background-color: #f8f9fa; /* Light gray background for main content */
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
      color: #152259; /* Match sidebar color */
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
      color: #152259; /* Match sidebar color */
      margin-bottom: 15px;
    }

    .progress-section .progress {
      height: 25px;
      border-radius: 5px;
      margin-bottom: 10px;
    }

    .progress-section .progress-bar {
      background-color: #509CDB; /* Match active item color */
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
      color: #152259; /* Match sidebar color */
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
      color: #152259; /* Match sidebar color */
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
 <?php  include 'sidebar.php'; ?>
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
            <p id="totalApplications">50</p>
          </div>
        </div>
        <div class="col-md-4">
          <div class="stats-card">
            <h3>Pending Reviews</h3>
            <p id="pendingReviews">20</p>
          </div>
        </div>
        <div class="col-md-4">
          <div class="stats-card">
            <h3>Reviewed Applications</h3>
            <p id="reviewedApplications">30</p>
          </div>
        </div>
      </div>

      <!-- Progress Bar Section -->
      <div class="progress-section">
        <h3>Review Progress</h3>
        <div class="progress-label">Review Completion: 30 / 50 Applications</div>
        <div class="progress">
          <div class="progress-bar" role="progressbar" style="width: 60%" aria-valuenow="60" aria-valuemin="0" aria-valuemax="100">60%</div>
        </div>
      </div>

      <!-- Quick Stats Section -->
      <div class="quick-stats">
        <h3>Quick Stats</h3>
        <div class="stat-item">
          <span>Average Score Given</span>
          <span id="averageScore">82%</span>
        </div>
        <div class="stat-item">
          <span>Average Time to Review</span>
          <span id="averageTime">45 minutes</span>
        </div>
        <div class="stat-item">
          <span>Reviews Completed This Week</span>
          <span id="weeklyReviews">15</span>
        </div>
      </div>

      <!-- Recent Activity and Notifications -->
      <div class="row">
        <div class="col-md-6">
          <div class="activity-section">
            <h3>Recent Activity</h3>
            <ul class="list-group">
              <li class="list-group-item">
                <span>Reviewed application A001 for John Doe</span>
                <span>2025-04-10 09:00</span>
              </li>
              <li class="list-group-item">
                <span>Reviewed application A002 for Jane Smith</span>
                <span>2025-04-09 15:30</span>
              </li>
              <li class="list-group-item">
                <span>Reviewed application A003 for Emily Johnson</span>
                <span>2025-04-09 14:00</span>
              </li>
            </ul>
          </div>
        </div>
        <div class="col-md-6">
          <div class="notifications-section">
            <h3>Notification Highlights</h3>
            <ul class="list-group">
              <li class="list-group-item">
                <span>New application assigned: A004</span>
                <span class="badge bg-primary">New</span>
              </li>
              <li class="list-group-item">
                <span>Deadline approaching for A005 review</span>
                <span class="badge bg-warning">Urgent</span>
              </li>
              <li class="list-group-item">
                <span>Admin updated review criteria</span>
                <span class="badge bg-info">Info</span>
              </li>
            </ul>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Bootstrap JS -->
  <script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
  ></script>
</body>
</html>