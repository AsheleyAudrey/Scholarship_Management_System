<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Reports & Analytics</title>
  
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
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
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

    /* Filters Section */
    .filters {
      display: flex;
      gap: 15px;
      margin-bottom: 20px;
    }

    .filters .form-select,
    .filters .form-control {
      border-radius: 5px;
      border: 1px solid #ced4da;
      box-shadow: none;
      max-width: 200px;
    }

    .filters .form-select:focus,
    .filters .form-control:focus {
      border-color: #509CDB; /* Match active item color */
      box-shadow: 0 0 5px rgba(80, 156, 219, 0.3);
    }

    /* Chart Section */
    .chart-section {
      background-color: #ffffff;
      border-radius: 8px;
      padding: 20px;
      box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
      margin-bottom: 20px;
    }

    .chart-section h3 {
      font-size: 18px;
      font-weight: 600;
      color: #152259; /* Match sidebar color */
      margin-bottom: 15px;
    }

    .chart-section canvas {
      max-width: 100%;
      max-height: 400px;
    }

    /* Export Buttons */
    .export-buttons {
      display: flex;
      gap: 10px;
      justify-content: flex-end;
      margin-top: 15px;
    }

    .export-buttons .btn-export-pdf {
      background-color: #dc3545; /* Red for PDF */
      border: none;
      padding: 8px 15px;
      font-size: 14px;
      color: #ffffff;
    }

    .export-buttons .btn-export-pdf:hover {
      background-color: #c82333;
    }

    .export-buttons .btn-export-excel {
      background-color: #28a745; /* Green for Excel */
      border: none;
      padding: 8px 15px;
      font-size: 14px;
      color: #ffffff;
    }

    .export-buttons .btn-export-excel:hover {
      background-color: #218838;
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
        <h1>Reports & Analytics</h1>
      </div>

      <!-- Filters -->
      <div class="filters">
        <select class="form-select" id="programFilter" onchange="updateCharts()">
          <option value="all">All Programs</option>
          <option value="Merit-Based Scholarship">Merit-Based Scholarship</option>
          <option value="Need-Based Scholarship">Need-Based Scholarship</option>
          <option value="STEM Scholarship">STEM Scholarship</option>
        </select>
        <input type="date" class="form-control" id="startDateFilter" onchange="updateCharts()">
        <input type="date" class="form-control" id="endDateFilter" onchange="updateCharts()">
      </div>

      <!-- Applications vs Approvals Chart -->
      <div class="chart-section">
        <h3>Applications vs Approvals</h3>
        <canvas id="applicationsChart"></canvas>
        <div class="export-buttons">
          <button class="btn btn-export-pdf" onclick="exportChartPDF('applicationsChart', 'Applications vs Approvals Report')">Export as PDF</button>
          <button class="btn btn-export-excel" onclick="exportChartExcel('applicationsChart', 'Applications vs Approvals Report')">Export as Excel</button>
        </div>
      </div>

      <!-- Fund Distribution Pie Chart -->
      <div class="chart-section">
        <h3>Fund Distribution</h3>
        <canvas id="fundDistributionChart"></canvas>
        <div class="export-buttons">
          <button class="btn btn-export-pdf" onclick="exportChartPDF('fundDistributionChart', 'Fund Distribution Report')">Export as PDF</button>
          <button class="btn btn-export-excel" onclick="exportChartExcel('fundDistributionChart', 'Fund Distribution Report')">Export as Excel</button>
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