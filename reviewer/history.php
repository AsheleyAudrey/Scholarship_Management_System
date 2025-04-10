<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Review History</title>
  
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

    /* Filters */
    .filters {
      display: flex;
      gap: 15px;
      margin-bottom: 20px;
      flex-wrap: wrap;
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

    /* Review History Table */
    .review-table {
      background-color: #ffffff;
      border-radius: 8px;
      padding: 20px;
      box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
      margin-bottom: 20px;
    }

    .review-table .table {
      margin-bottom: 0;
    }

    .review-table .table th {
      background-color: #152259; /* Match sidebar color */
      color: #ffffff;
    }

    .review-table .table td {
      vertical-align: middle;
    }

    .review-table .table .btn-expand {
      background-color: #509CDB; /* Match active item color */
      border: none;
      font-size: 14px;
      padding: 5px 10px;
    }

    .review-table .table .btn-expand:hover {
      background-color: #408CCB;
    }

    /* Expandable Review Summary */
    .review-summary {
      background-color: #f8f9fa;
      padding: 15px;
      border-radius: 5px;
      margin-top: 10px;
      display: none;
    }

    .review-summary p {
      margin: 5px 0;
      font-size: 14px;
      color: #333;
    }

    .review-summary .score-breakdown {
      margin-top: 10px;
      padding: 10px;
      background-color: #ffffff;
      border-radius: 5px;
      border: 1px solid #dee2e6;
    }

    .review-summary .score-breakdown p {
      margin: 0;
      padding: 5px 0;
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
        <h1>Review History</h1>
      </div>

      <!-- Filters -->
      <div class="filters">
        <select class="form-select" id="scholarshipFilter" onchange="applyFilters()">
          <option value="">All Scholarships</option>
          <option value="Merit-Based Scholarship">Merit-Based Scholarship</option>
          <option value="Need-Based Scholarship">Need-Based Scholarship</option>
          <option value="STEM Scholarship">STEM Scholarship</option>
        </select>
        <input type="date" class="form-control" id="reviewDateFilter" onchange="applyFilters()" placeholder="Filter by Review Date">
      </div>

      <!-- Review History Table -->
      <div class="review-table">
        <table class="table table-hover">
          <thead>
            <tr>
              <th>Application ID</th>
              <th>Student Name</th>
              <th>Scholarship Name</th>
              <th>Review Date</th>
              <th>Final Score</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody id="reviewTable">
            <tr data-scholarship="Merit-Based Scholarship" data-review-date="2025-04-05">
              <td>A001</td>
              <td>John Doe</td>
              <td>Merit-Based Scholarship</td>
              <td>2025-04-05</td>
              <td>85%</td>
              <td>
                <button class="btn btn-expand" onclick="toggleReviewSummary(this)">View Summary</button>
              </td>
            </tr>
            <tr style="display: none;">
              <td colspan="6">
                <div class="review-summary">
                  <p><strong>Feedback/Comments:</strong> Strong academic record and well-written essay. Good involvement in extracurricular activities.</p>
                  <div class="score-breakdown">
                    <p>Academic Performance: 90%</p>
                    <p>Essay Quality: 80%</p>
                    <p>Extracurricular Activities: 85%</p>
                  </div>
                </div>
              </td>
            </tr>
            <tr data-scholarship="Need-Based Scholarship" data-review-date="2025-04-06">
              <td>A002</td>
              <td>Jane Smith</td>
              <td>Need-Based Scholarship</td>
              <td>2025-04-06</td>
              <td>72%</td>
              <td>
                <button class="btn btn-expand" onclick="toggleReviewSummary(this)">View Summary</button>
              </td>
            </tr>
            <tr style="display: none;">
              <td colspan="6">
                <div class="review-summary">
                  <p><strong>Feedback/Comments:</strong> Academic performance is average. Essay lacks depth. Good extracurricular involvement.</p>
                  <div class="score-breakdown">
                    <p>Academic Performance: 70%</p>
                    <p>Essay Quality: 65%</p>
                    <p>Extracurricular Activities: 80%</p>
                  </div>
                </div>
              </td>
            </tr>
            <tr data-scholarship="STEM Scholarship" data-review-date="2025-04-07">
              <td>A003</td>
              <td>Emily Johnson</td>
              <td>STEM Scholarship</td>
              <td>2025-04-07</td>
              <td>90%</td>
              <td>
                <button class="btn btn-expand" onclick="toggleReviewSummary(this)">View Summary</button>
              </td>
            </tr>
            <tr style="display: none;">
              <td colspan="6">
                <div class="review-summary">
                  <p><strong>Feedback/Comments:</strong> Excellent academic performance and essay. Strong candidate for STEM scholarship.</p>
                  <div class="score-breakdown">
                    <p>Academic Performance: 95%</p>
                    <p>Essay Quality: 90%</p>
                    <p>Extracurricular Activities: 85%</p>
                  </div>
                </div>
              </td>
            </tr>
            <tr data-scholarship="Merit-Based Scholarship" data-review-date="2025-04-08">
              <td>A004</td>
              <td>Michael Brown</td>
              <td>Merit-Based Scholarship</td>
              <td>2025-04-08</td>
              <td>78%</td>
              <td>
                <button class="btn btn-expand" onclick="toggleReviewSummary(this)">View Summary</button>
              </td>
            </tr>
            <tr style="display: none;">
              <td colspan="6">
                <div class="review-summary">
                  <p><strong>Feedback/Comments:</strong> Solid application overall. Awaiting admin approval for final decision.</p>
                  <div class="score-breakdown">
                    <p>Academic Performance: 80%</p>
                    <p>Essay Quality: 75%</p>
                    <p>Extracurricular Activities: 80%</p>
                  </div>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- Bootstrap JS -->
  <script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
  ></script>
</body>
</html>