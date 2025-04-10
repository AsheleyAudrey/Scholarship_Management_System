<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Admin Dashboard</title>
  
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
  <!-- Chart.js -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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

    /* Summary boxes */
    .summary-boxes {
      display: flex;
      gap: 20px;
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
      color: #509CDB; /* Match active item color */
      margin-bottom: 10px;
    }

    .summary-boxes .card h3 {
      font-size: 16px;
      font-weight: 600;
      color: #152259; /* Match sidebar color */
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
      color: #152259; /* Match sidebar color */
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
      color: #509CDB; /* Match active item color */
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
          <p>25</p>
        </div>
        <div class="card">
          <i class="bi bi-journal-text"></i>
          <h3>Pending Applications</h3>
          <p>12</p>
        </div>
        <div class="card">
          <i class="bi bi-people"></i>
          <h3>Active Students</h3>
          <p>150</p>
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
        <div class="activity-item">
          <i class="bi bi-journal-text"></i>
          <span>New application submitted by John Doe for Merit-Based Scholarship.</span>
        </div>
        <div class="activity-item">
          <i class="bi bi-award"></i>
          <span>STEM Scholarship awarded to Jane Smith.</span>
        </div>
        <div class="activity-item">
          <i class="bi bi-person-check"></i>
          <span>New student registered: Emily Johnson.</span>
        </div>
        <div class="activity-item">
          <i class="bi bi-file-earmark-text"></i>
          <span>Document uploaded by John Doe: Transcript.</span>
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