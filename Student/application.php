<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Applications</title>
  
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

    /* Table sections */
    .table-section {
      background-color: #ffffff;
      border-radius: 8px;
      padding: 20px;
      box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
      margin-bottom: 20px;
    }

    .table-section h3 {
      font-size: 18px;
      font-weight: 600;
      color: #152259; /* Match sidebar color */
      margin-bottom: 15px;
    }

    .table {
      margin-bottom: 0;
    }

    .table th {
      background-color: #152259; /* Match sidebar color */
      color: #ffffff;
    }

    .table td {
      vertical-align: middle;
    }

    .table .badge {
      font-size: 12px;
    }

    .table .btn {
      font-size: 14px;
      padding: 5px 10px;
    }

    .table .btn-view {
      background-color: #6c757d; /* Gray for view details */
      border: none;
      margin-right: 5px;
    }

    .table .btn-view:hover {
      background-color: #5a6268;
    }

    .table .btn-withdraw {
      background-color: #dc3545; /* Red for withdraw */
      border: none;
    }

    .table .btn-withdraw:hover {
      background-color: #c82333;
    }

    .table .btn-edit {
      background-color: #509CDB; /* Match active item color */
      border: none;
      margin-right: 5px;
    }

    .table .btn-edit:hover {
      background-color: #408CCB;
    }

    .table .btn-duplicate {
      background-color: #17a2b8; /* Cyan for duplicate */
      border: none;
      margin-right: 5px;
    }

    .table .btn-duplicate:hover {
      background-color: #138496;
    }

    .table .btn-export {
      background-color: #28a745; /* Green for export */
      border: none;
    }

    .table .btn-export:hover {
      background-color: #218838;
    }
  </style>
</head>
<body>
    <!-- Main content -->
    <div class="main-content">
      <!-- Header -->
      <div class="page-header">
        <h1>Applications</h1>
      </div>

      <!-- Submitted Applications Section -->
      <div class="table-section">
        <h3>Submitted Applications</h3>
        <table class="table table-hover">
          <thead>
            <tr>
              <th>Scholarship Name</th>
              <th>Status</th>
              <th>Date Applied</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td>Merit-Based Scholarship</td>
              <td><span class="badge bg-warning">Pending</span></td>
              <td>2025-04-01</td>
              <td>
                <a href="#" class="btn btn-view"><i class="bi bi-eye"></i> View Details</a>
                <a href="#" class="btn btn-withdraw"><i class="bi bi-x-circle"></i> Withdraw</a>
              </td>
            </tr>
            <tr>
              <td>Need-Based Scholarship</td>
              <td><span class="badge bg-primary">Under Review</span></td>
              <td>2025-03-28</td>
              <td>
                <a href="#" class="btn btn-view"><i class="bi bi-eye"></i> View Details</a>
                <a href="#" class="btn btn-withdraw"><i class="bi bi-x-circle"></i> Withdraw</a>
              </td>
            </tr>
            <tr>
              <td>STEM Scholarship</td>
              <td><span class="badge bg-success">Approved</span></td>
              <td>2025-03-25</td>
              <td>
                <a href="#" class="btn btn-view"><i class="bi bi-eye"></i> View Details</a>
              </td>
            </tr>
            <tr>
              <td>Community Scholarship</td>
              <td><span class="badge bg-danger">Rejected</span></td>
              <td>2025-03-20</td>
              <td>
                <a href="#" class="btn btn-view"><i class="bi bi-eye"></i> View Details</a>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Draft Applications Section -->
      <div class="table-section">
        <h3>Draft Applications</h3>
        <table class="table table-hover">
          <thead>
            <tr>
              <th>Scholarship Name</th>
              <th>Last Edited</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td>STEM Scholarship</td>
              <td>2025-04-05</td>
              <td>
                <a href="#" class="btn btn-edit"><i class="bi bi-pencil"></i> Edit</a>
                <a href="#" class="btn btn-duplicate"><i class="bi bi-files"></i> Duplicate</a>
                <a href="#" class="btn btn-export"><i class="bi bi-file-earmark-pdf"></i> Export as PDF</a>
              </td>
            </tr>
            <tr>
              <td>Need-Based Scholarship</td>
              <td>2025-04-03</td>
              <td>
                <a href="#" class="btn btn-edit"><i class="bi bi-pencil"></i> Edit</a>
                <a href="#" class="btn btn-duplicate"><i class="bi bi-files"></i> Duplicate</a>
                <a href="#" class="btn btn-export"><i class="bi bi-file-earmark-pdf"></i> Export as PDF</a>
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