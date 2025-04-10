<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Reviewed History</title>
  
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
      margin-left: 250px;
      padding: 20px;
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

    /* Filters and Search */
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

    /* Reviewed History Table */
    .reviewed-table {
      background-color: #ffffff;
      border-radius: 8px;
      padding: 20px;
      box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
      margin-bottom: 20px;
    }

    .reviewed-table .table {
      margin-bottom: 0;
    }

    .reviewed-table .table th {
      background-color: #152259; /* Match sidebar color */
      color: #ffffff;
    }

    .reviewed-table .table td {
      vertical-align: middle;
    }

    .reviewed-table .table .badge {
      font-size: 12px;
    }

    .reviewed-table .btn-view-details {
      background-color: #17a2b8; /* Cyan for view details */
      border: none;
      font-size: 14px;
      padding: 5px 10px;
    }

    .reviewed-table .btn-view-details:hover {
      background-color: #138496;
    }

    /* Pagination */
    .pagination {
      justify-content: center;
    }

    .pagination .page-link {
      color: #509CDB;
    }

    .pagination .page-link:hover {
      background-color: #509CDB;
      color: #ffffff;
    }

    .pagination .page-item.active .page-link {
      background-color: #509CDB;
      border-color: #509CDB;
      color: #ffffff;
    }

    /* Modal Styling */
    .modal-content {
      border-radius: 8px;
    }

    .modal-header {
      background-color: #152259; /* Match sidebar color */
      color: #ffffff;
    }

    .modal-header .btn-close {
      filter: invert(1);
    }

    .modal-body p {
      margin-bottom: 10px;
    }

    .modal-body .score-breakdown {
      background-color: #f8f9fa;
      padding: 15px;
      border-radius: 5px;
    }

    .modal-body .score-breakdown p {
      margin: 5px 0;
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
        <h1>Reviewed History</h1>
      </div>

      <!-- Filters and Search -->
      <div class="filters">
        <select class="form-select" id="scholarshipFilter" onchange="applyFilters()">
          <option value="">All Scholarships</option>
          <option value="Merit-Based Scholarship">Merit-Based Scholarship</option>
          <option value="Need-Based Scholarship">Need-Based Scholarship</option>
          <option value="STEM Scholarship">STEM Scholarship</option>
        </select>
        <input type="date" class="form-control" id="reviewDateFilter" onchange="applyFilters()" placeholder="Filter by Review Date">
        <input type="text" class="form-control" id="searchInput" onkeyup="applyFilters()" placeholder="Search by Applicant ID or Name">
      </div>

      <!-- Reviewed History Table -->
      <div class="reviewed-table">
        <table class="table table-hover">
          <thead>
            <tr>
              <th>Application ID</th>
              <th>Student Name</th>
              <th>Scholarship Name</th>
              <th>Review Date</th>
              <th>Score Given</th>
              <th>Status</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody id="reviewedTable">
            <tr data-scholarship="Merit-Based Scholarship" data-review-date="2025-04-05" data-id="A001" data-name="John Doe">
              <td>A001</td>
              <td>John Doe</td>
              <td>Merit-Based Scholarship</td>
              <td>2025-04-05</td>
              <td>85%</td>
              <td><span class="badge bg-success">Approved</span></td>
              <td>
                <button class="btn btn-view-details" data-bs-toggle="modal" data-bs-target="#reviewDetailsModal" onclick="viewReviewDetails(this)">View Details</button>
              </td>
            </tr>
            <tr data-scholarship="Need-Based Scholarship" data-review-date="2025-04-06" data-id="A002" data-name="Jane Smith">
              <td>A002</td>
              <td>Jane Smith</td>
              <td>Need-Based Scholarship</td>
              <td>2025-04-06</td>
              <td>72%</td>
              <td><span class="badge bg-danger">Rejected</span></td>
              <td>
                <button class="btn btn-view-details" data-bs-toggle="modal" data-bs-target="#reviewDetailsModal" onclick="viewReviewDetails(this)">View Details</button>
              </td>
            </tr>
            <tr data-scholarship="STEM Scholarship" data-review-date="2025-04-07" data-id="A003" data-name="Emily Johnson">
              <td>A003</td>
              <td>Emily Johnson</td>
              <td>STEM Scholarship</td>
              <td>2025-04-07</td>
              <td>90%</td>
              <td><span class="badge bg-success">Approved</span></td>
              <td>
                <button class="btn btn-view-details" data-bs-toggle="modal" data-bs-target="#reviewDetailsModal" onclick="viewReviewDetails(this)">View Details</button>
              </td>
            </tr>
            <tr data-scholarship="Merit-Based Scholarship" data-review-date="2025-04-08" data-id="A004" data-name="Michael Brown">
              <td>A004</td>
              <td>Michael Brown</td>
              <td>Merit-Based Scholarship</td>
              <td>2025-04-08</td>
              <td>78%</td>
              <td><span class="badge bg-warning">Pending Admin Approval</span></td>
              <td>
                <button class="btn btn-view-details" data-bs-toggle="modal" data-bs-target="#reviewDetailsModal" onclick="viewReviewDetails(this)">View Details</button>
              </td>
            </tr>
          </tbody>
        </table>
        <nav aria-label="Page navigation">
          <ul class="pagination" id="pagination">
            <!-- Populated dynamically -->
          </ul>
        </nav>
      </div>
    </div>
  </div>

  <!-- Review Details Modal -->
  <div class="modal fade" id="reviewDetailsModal" tabindex="-1" aria-labelledby="reviewDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="reviewDetailsModalLabel">Review Details</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <p><strong>Application ID:</strong> <span id="modalApplicationId"></span></p>
          <p><strong>Student Name:</strong> <span id="modalStudentName"></span></p>
          <p><strong>Scholarship Name:</strong> <span id="modalScholarshipName"></span></p>
          <p><strong>Review Date:</strong> <span id="modalReviewDate"></span></p>
          <p><strong>Score Given:</strong> <span id="modalScore"></span></p>
          <p><strong>Status:</strong> <span id="modalStatus"></span></p>
          <div class="score-breakdown">
            <p><strong>Score Breakdown:</strong></p>
            <p id="modalAcademic">Academic Performance: <span></span></p>
            <p id="modalEssay">Essay Quality: <span></span></p>
            <p id="modalExtracurricular">Extracurricular Activities: <span></span></p>
          </div>
          <p><strong>Comments:</strong> <span id="modalComments"></span></p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
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