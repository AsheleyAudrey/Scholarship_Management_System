<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Dashboard</title>
  
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
      color: #152259; /* Match sidebar color */
      margin-bottom: 10px;
    }

    /* Quick stats */
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
      color: #509CDB; /* Match active item color */
      margin-bottom: 10px;
    }

    .stat-card h3 {
      font-size: 16px;
      font-weight: 600;
      color: #152259; /* Match sidebar color */
      margin-bottom: 5px;
    }

    .stat-card p {
      font-size: 24px;
      font-weight: 600;
      color: #333;
      margin: 0;
    }

    /* Recent applications, upcoming deadlines, notifications, and quick links */
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
      color: #152259; /* Match sidebar color */
      margin-bottom: 15px;
    }

    .dashboard-section .table {
      margin-bottom: 0;
    }

    .dashboard-section .table th {
      background-color: #152259; /* Match sidebar color */
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
      color: #dc3545; /* Red for urgency */
    }

    /* Notifications section */
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
      color: #509CDB; /* Match active item color */
    }

    .dashboard-section .notification-item span {
      font-size: 14px;
      color: #333;
    }

    /* Quick links */
    .dashboard-section .quick-links {
      display: flex;
      gap: 15px;
      flex-wrap: wrap;
    }

    .dashboard-section .quick-links .btn {
      background-color: #509CDB; /* Match active item color */
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
  <!-- Main content -->
  <div class="main-content">
    <!-- Header -->
    <div class="page-header">
      <h1>Scholarship Management Dashboard</h1>
    </div>

    <!-- Welcome Message -->
    <div class="welcome-message">
      <h2>Welcome, John Doe!</h2>
      <p>Here’s an overview of your scholarship journey. Stay on top of your applications and deadlines.</p>
    </div>

    <!-- Quick Stats -->
    <div class="quick-stats">
      <div class="stat-card">
        <i class="bi bi-journal-text"></i>
        <h3>Applications Submitted</h3>
        <p>4</p>
      </div>
      <div class="stat-card">
        <i class="bi bi-upload"></i>
        <h3>Pending Documents</h3>
        <p>1</p>
      </div>
      <div class="stat-card">
        <i class="bi bi-bell"></i>
        <h3>Unread Notifications</h3>
        <p>2</p>
      </div>
    </div>

    <!-- Recent Applications -->
    <div class="dashboard-section">
      <h3>Recent Applications</h3>
      <table class="table table-hover">
        <thead>
          <tr>
            <th>Scholarship Name</th>
            <th>Status</th>
            <th>Date Applied</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td>Merit-Based Scholarship</td>
            <td><span class="badge bg-warning">Pending</span></td>
            <td>2025-04-01</td>
          </tr>
          <tr>
            <td>Need-Based Scholarship</td>
            <td><span class="badge bg-primary">Under Review</span></td>
            <td>2025-03-28</td>
          </tr>
          <tr>
            <td>STEM Scholarship</td>
            <td><span class="badge bg-success">Approved</span></td>
            <td>2025-03-25</td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Upcoming Deadlines -->
    <div class="dashboard-section">
      <h3>Upcoming Deadlines</h3>
      <div class="deadline-item">
        <p>STEM Scholarship</p>
        <div class="date">2025-06-30</div>
      </div>
      <div class="deadline-item">
        <p>Need-Based Scholarship</p>
        <div class="date">2025-07-01</div>
      </div>
    </div>

    <!-- Notifications Section -->
    <div class="dashboard-section">
      <h3>Notifications</h3>
      <div class="notification-item">
        <i class="bi bi-bell"></i>
        <span>New application submitted by John Doe.</span>
      </div>
      <div class="notification-item">
        <i class="bi bi-bell"></i>
        <span>Deadline for Merit Scholarship applications is tomorrow.</span>
      </div>
      <div class="notification-item">
        <i class="bi bi-bell"></i>
        <span>Reviewer assigned to application #002.</span>
      </div>
    </div>

    <!-- Quick Links -->
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
  <script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
  ></script>
</body>
</html>