<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Applications Assigned</title>
  
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

    /* Applications Table */
    .applications-table {
      background-color: #ffffff;
      border-radius: 8px;
      padding: 20px;
      box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
      margin-bottom: 20px;
    }

    .applications-table .table {
      margin-bottom: 0;
    }

    .applications-table .table th {
      background-color: #152259; /* Match sidebar color */
      color: #ffffff;
    }

    .applications-table .table td {
      vertical-align: middle;
    }

    .applications-table .table .badge {
      font-size: 12px;
    }

    .applications-table .btn-start-review {
      background-color: #509CDB; /* Match active item color */
      border: none;
      font-size: 14px;
      padding: 5px 10px;
    }

    .applications-table .btn-start-review:hover {
      background-color: #408CCB;
    }

    .applications-table .btn-start-review:disabled {
      background-color: #ced4da;
      cursor: not-allowed;
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
  </style>
</head>
<body>
    <!-- Sidebar -->
     <?php include 'sidebar.php'; ?>

    <!-- Main content -->
    <div class="main-content">
      <!-- Header -->
      <div class="page-header">
        <h1>Applications Assigned</h1>
      </div>

      <!-- Filters and Search -->
      <div class="filters">
        <select class="form-select" id="scholarshipFilter" onchange="applyFilters()">
          <option value="">All Scholarships</option>
          <option value="Merit-Based Scholarship">Merit-Based Scholarship</option>
          <option value="Need-Based Scholarship">Need-Based Scholarship</option>
          <option value="STEM Scholarship">STEM Scholarship</option>
        </select>
        <input type="date" class="form-control" id="deadlineFilter" onchange="applyFilters()" placeholder="Filter by Deadline">
        <input type="text" class="form-control" id="searchInput" onkeyup="applyFilters()" placeholder="Search by Applicant ID or Name">
      </div>

      <!-- Applications Table -->
      <div class="applications-table">
        <table class="table table-hover">
          <thead>
            <tr>
              <th>Application ID</th>
              <th>Student Name</th>
              <th>Scholarship Name</th>
              <th>Submission Date</th>
              <th>Review Status</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody id="applicationsTable">
            <tr data-scholarship="Merit-Based Scholarship" data-deadline="2025-05-01" data-id="A001" data-name="John Doe">
              <td>A001</td>
              <td>John Doe</td>
              <td>Merit-Based Scholarship</td>
              <td>2025-04-01</td>
              <td><span class="badge bg-secondary">Not Started</span></td>
              <td>
                <button class="btn btn-start-review" onclick="startReview(this)">Start Review</button>
              </td>
            </tr>
            <tr data-scholarship="Need-Based Scholarship" data-deadline="2025-05-15" data-id="A002" data-name="Jane Smith">
              <td>A002</td>
              <td>Jane Smith</td>
              <td>Need-Based Scholarship</td>
              <td>2025-04-02</td>
              <td><span class="badge bg-warning">In Review</span></td>
              <td>
                <button class="btn btn-start-review" onclick="startReview(this)">Continue Review</button>
              </td>
            </tr>
            <tr data-scholarship="STEM Scholarship" data-deadline="2025-05-10" data-id="A003" data-name="Emily Johnson">
              <td>A003</td>
              <td>Emily Johnson</td>
              <td>STEM Scholarship</td>
              <td>2025-04-03</td>
              <td><span class="badge bg-success">Completed</span></td>
              <td>
                <button class="btn btn-start-review" disabled>Review Completed</button>
              </td>
            </tr>
            <tr data-scholarship="Merit-Based Scholarship" data-deadline="2025-05-01" data-id="A004" data-name="Michael Brown">
              <td>A004</td>
              <td>Michael Brown</td>
              <td>Merit-Based Scholarship</td>
              <td>2025-04-04</td>
              <td><span class="badge bg-secondary">Not Started</span></td>
              <td>
                <button class="btn btn-start-review" onclick="startReview(this)">Start Review</button>
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

  <!-- Bootstrap JS -->
  <script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
  ></script>
</body>
</html>