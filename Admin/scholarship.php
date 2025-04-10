<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Manage Scholarships</title>
  
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

    /* Add Scholarship Button */
    .page-header .btn-add {
      background-color: #509CDB; /* Match active item color */
      border: none;
      padding: 10px 20px;
      font-size: 16px;
      color: #ffffff;
    }

    .page-header .btn-add:hover {
      background-color: #408CCB;
    }

    /* Scholarships Table */
    .scholarships-table {
      background-color: #ffffff;
      border-radius: 8px;
      padding: 20px;
      box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
      margin-bottom: 20px;
    }

    .scholarships-table h3 {
      font-size: 18px;
      font-weight: 600;
      color: #152259; /* Match sidebar color */
      margin-bottom: 15px;
    }

    .scholarships-table .table {
      margin-bottom: 0;
    }

    .scholarships-table .table th {
      background-color: #152259; /* Match sidebar color */
      color: #ffffff;
    }

    .scholarships-table .table td {
      vertical-align: middle;
    }

    .scholarships-table .table .badge {
      font-size: 12px;
    }

    .scholarships-table .btn-edit {
      background-color: #17a2b8; /* Cyan for edit */
      border: none;
      margin-right: 5px;
      font-size: 14px;
      padding: 5px 10px;
    }

    .scholarships-table .btn-edit:hover {
      background-color: #138496;
    }

    .scholarships-table .btn-delete {
      background-color: #dc3545; /* Red for delete */
      border: none;
      font-size: 14px;
      padding: 5px 10px;
    }

    .scholarships-table .btn-delete:hover {
      background-color: #c82333;
    }

    .scholarships-table .btn-toggle {
      font-size: 14px;
      padding: 5px 10px;
    }

    /* Modal Form Styling */
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

    .modal-body .form-label {
      font-weight: 500;
      color: #333;
    }

    .modal-body .form-control,
    .modal-body .form-select {
      border-radius: 5px;
      border: 1px solid #ced4da;
      box-shadow: none;
    }

    .modal-body .form-control:focus,
    .modal-body .form-select:focus {
      border-color: #509CDB; /* Match active item color */
      box-shadow: 0 0 5px rgba(80, 156, 219, 0.3);
    }

    .modal-footer .btn-save {
      background-color: #509CDB; /* Match active item color */
      border: none;
    }

    .modal-footer .btn-save:hover {
      background-color: #408CCB;
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
        <h1>Manage Scholarships</h1>
        <button class="btn btn-add" data-bs-toggle="modal" data-bs-target="#addScholarshipModal">
          <i class="bi bi-plus-circle me-2"></i> Add New Scholarship
        </button>
      </div>

      <!-- Scholarships Table -->
      <div class="scholarships-table">
        <h3>All Scholarships</h3>
        <table class="table table-hover">
          <thead>
            <tr>
              <th>Name</th>
              <th>Amount</th>
              <th>Criteria</th>
              <th>Application Window</th>
              <th>Status</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody id="scholarshipsTable">
            <tr>
              <td>Merit-Based Scholarship</td>
              <td>$5,000</td>
              <td>GPA: 3.5, Leadership</td>
              <td>2025-01-01 to 2025-06-30</td>
              <td><span class="badge bg-success">Open</span></td>
              <td>
                <button class="btn btn-edit" onclick="editScholarship(this)">Edit</button>
                <button class="btn btn-delete" onclick="deleteScholarship(this)">Delete</button>
                <button class="btn btn-danger btn-toggle" onclick="toggleApplicationWindow(this)">Close</button>
              </td>
            </tr>
            <tr>
              <td>Need-Based Scholarship</td>
              <td>$3,000</td>
              <td>Financial Need, GPA: 3.0</td>
              <td>2025-02-01 to 2025-07-01</td>
              <td><span class="badge bg-success">Open</span></td>
              <td>
                <button class="btn btn-edit" onclick="editScholarship(this)">Edit</button>
                <button class="btn btn-delete" onclick="deleteScholarship(this)">Delete</button>
                <button class="btn btn-danger btn-toggle" onclick="toggleApplicationWindow(this)">Close</button>
              </td>
            </tr>
            <tr>
              <td>STEM Scholarship</td>
              <td>$7,000</td>
              <td>STEM Major, GPA: 3.7</td>
              <td>2025-03-01 to 2025-06-30</td>
              <td><span class="badge bg-danger">Closed</span></td>
              <td>
                <button class="btn btn-edit" onclick="editScholarship(this)">Edit</button>
                <button class="btn btn-delete" onclick="deleteScholarship(this)">Delete</button>
                <button class="btn btn-success btn-toggle" onclick="toggleApplicationWindow(this)">Open</button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- Add/Edit Scholarship Modal -->
  <div class="modal fade" id="addScholarshipModal" tabindex="-1" aria-labelledby="addScholarshipModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="addScholarshipModalLabel">Add New Scholarship</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <form id="scholarshipForm">
            <div class="mb-3">
              <label for="scholarshipName" class="form-label">Scholarship Name</label>
              <input type="text" class="form-control" id="scholarshipName" required>
            </div>
            <div class="mb-3">
              <label for="scholarshipAmount" class="form-label">Amount</label>
              <input type="number" class="form-control" id="scholarshipAmount" placeholder="$" required>
            </div>
            <div class="mb-3">
              <label for="criteriaGPA" class="form-label">Minimum GPA</label>
              <input type="number" step="0.1" class="form-control" id="criteriaGPA" placeholder="e.g., 3.5">
            </div>
            <div class="mb-3">
              <label for="criteriaOther" class="form-label">Other Criteria</label>
              <select class="form-select" id="criteriaOther">
                <option value="">None</option>
                <option value="Financial Need">Financial Need</option>
                <option value="Leadership">Leadership</option>
                <option value="STEM Major">STEM Major</option>
                <option value="Community Service">Community Service</option>
              </select>
            </div>
            <div class="row">
              <div class="col-md-6 mb-3">
                <label for="applicationStart" class="form-label">Application Start Date</label>
                <input type="date" class="form-control" id="applicationStart" required>
              </div>
              <div class="col-md-6 mb-3">
                <label for="applicationEnd" class="form-label">Application End Date</label>
                <input type="date" class="form-control" id="applicationEnd" required>
              </div>
            </div>
          </form>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          <button type="button" class="btn btn-save" onclick="saveScholarship()">Save</button>
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