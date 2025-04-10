<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Student Management</title>
  
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

    /* Add Student Button */
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

    /* Search Bar */
    .search-bar {
      margin-bottom: 20px;
      max-width: 400px;
    }

    .search-bar .form-control {
      border-radius: 5px;
      border: 1px solid #ced4da;
      box-shadow: none;
    }

    .search-bar .form-control:focus {
      border-color: #509CDB; /* Match active item color */
      box-shadow: 0 0 5px rgba(80, 156, 219, 0.3);
    }

    /* Students Table */
    .students-table {
      background-color: #ffffff;
      border-radius: 8px;
      padding: 20px;
      box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
      margin-bottom: 20px;
    }

    .students-table .table {
      margin-bottom: 0;
    }

    .students-table .table th {
      background-color: #152259; /* Match sidebar color */
      color: #ffffff;
    }

    .students-table .table td {
      vertical-align: middle;
    }

    .students-table .btn-view {
      background-color: #17a2b8; /* Cyan for view */
      border: none;
      font-size: 14px;
      padding: 5px 10px;
      margin-right: 5px;
    }

    .students-table .btn-view:hover {
      background-color: #138496;
    }

    .students-table .btn-edit {
      background-color: #509CDB; /* Match active item color */
      border: none;
      font-size: 14px;
      padding: 5px 10px;
    }

    .students-table .btn-edit:hover {
      background-color: #408CCB;
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

    .modal-body .form-control {
      border-radius: 5px;
      border: 1px solid #ced4da;
      box-shadow: none;
    }

    .modal-body .form-control:focus {
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

    /* Profile Card in Modal */
    .profile-card {
      background-color: #ffffff;
      border-radius: 8px;
      padding: 20px;
      box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
      margin-top: 20px;
    }

    .profile-card h5 {
      font-size: 18px;
      font-weight: 600;
      color: #152259; /* Match sidebar color */
      margin-bottom: 15px;
    }

    .profile-card p {
      margin-bottom: 10px;
      font-size: 14px;
      color: #333;
    }

    .profile-card .history-section {
      margin-top: 20px;
    }

    .profile-card .history-section h6 {
      font-size: 16px;
      font-weight: 600;
      color: #152259;
      margin-bottom: 10px;
    }

    .profile-card .history-section ul {
      list-style: none;
      padding: 0;
    }

    .profile-card .history-section ul li {
      padding: 5px 0;
      font-size: 14px;
      border-bottom: 1px solid #eee;
    }

    .profile-card .history-section ul li:last-child {
      border-bottom: none;
    }
  </style>
</head>
<body>

    <!-- Main content -->
    <div class="main-content">
      <!-- Header -->
      <div class="page-header">
        <h1>Student Management</h1>
        <button class="btn btn-add" data-bs-toggle="modal" data-bs-target="#addStudentModal">
          <i class="bi bi-plus-circle me-2"></i> Add New Student
        </button>
      </div>

      <!-- Search Bar -->
      <div class="search-bar">
        <input type="text" class="form-control" id="searchInput" placeholder="Search by ID or Name" onkeyup="searchStudents()">
      </div>

      <!-- Students Table -->
      <div class="students-table">
        <table class="table table-hover">
          <thead>
            <tr>
              <th>Student ID</th>
              <th>Name</th>
              <th>Email</th>
              <th>Phone</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody id="studentsTable">
            <tr>
              <td>S001</td>
              <td>John Doe</td>
              <td>john.doe@example.com</td>
              <td>+1-555-123-4567</td>
              <td>
                <button class="btn btn-view" data-bs-toggle="modal" data-bs-target="#viewStudentModal" onclick="viewStudent(this)">View</button>
                <button class="btn btn-edit" data-bs-toggle="modal" data-bs-target="#editStudentModal" onclick="editStudent(this)">Edit</button>
              </td>
            </tr>
            <tr>
              <td>S002</td>
              <td>Jane Smith</td>
              <td>jane.smith@example.com</td>
              <td>+1-555-987-6543</td>
              <td>
                <button class="btn btn-view" data-bs-toggle="modal" data-bs-target="#viewStudentModal" onclick="viewStudent(this)">View</button>
                <button class="btn btn-edit" data-bs-toggle="modal" data-bs-target="#editStudentModal" onclick="editStudent(this)">Edit</button>
              </td>
            </tr>
            <tr>
              <td>S003</td>
              <td>Emily Johnson</td>
              <td>emily.johnson@example.com</td>
              <td>+1-555-456-7890</td>
              <td>
                <button class="btn btn-view" data-bs-toggle="modal" data-bs-target="#viewStudentModal" onclick="viewStudent(this)">View</button>
                <button class="btn btn-edit" data-bs-toggle="modal" data-bs-target="#editStudentModal" onclick="editStudent(this)">Edit</button>
              </td>
            </tr>
            <tr>
              <td>S004</td>
              <td>Michael Brown</td>
              <td>michael.brown@example.com</td>
              <td>+1-555-321-6547</td>
              <td>
                <button class="btn btn-view" data-bs-toggle="modal" data-bs-target="#viewStudentModal" onclick="viewStudent(this)">View</button>
                <button class="btn btn-edit" data-bs-toggle="modal" data-bs-target="#editStudentModal" onclick="editStudent(this)">Edit</button>
              </td>
            </tr>
          </tbody>
        </table>

        <!-- Pagination -->
        <nav aria-label="Page navigation">
          <ul class="pagination" id="pagination">
            <li class="page-item"><a class="page-link" href="#" onclick="changePage(1)">1</a></li>
            <li class="page-item"><a class="page-link" href="#" onclick="changePage(2)">2</a></li>
          </ul>
        </nav>
      </div>
    </div>
  </div>

  <!-- Add Student Modal -->
  <div class="modal fade" id="addStudentModal" tabindex="-1" aria-labelledby="addStudentModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="addStudentModalLabel">Add New Student</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <form id="addStudentForm">
            <div class="mb-3">
              <label for="studentId" class="form-label">Student ID</label>
              <input type="text" class="form-control" id="studentId" required>
            </div>
            <div class="mb-3">
              <label for="firstName" class="form-label">First Name</label>
              <input type="text" class="form-control" id="firstName" required>
            </div>
            <div class="mb-3">
              <label for="lastName" class="form-label">Last Name</label>
              <input type="text" class="form-control" id="lastName" required>
            </div>
            <div class="mb-3">
              <label for="email" class="form-label">Email</label>
              <input type="email" class="form-control" id="email" required>
            </div>
            <div class="mb-3">
              <label for="phone" class="form-label">Phone</label>
              <input type="tel" class="form-control" id="phone">
            </div>
          </form>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          <button type="button" class="btn btn-save" onclick="addStudent()">Save</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Edit Student Modal -->
  <div class="modal fade" id="editStudentModal" tabindex="-1" aria-labelledby="editStudentModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="editStudentModalLabel">Edit Student</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <form id="editStudentForm">
            <div class="mb-3">
              <label for="editStudentId" class="form-label">Student ID</label>
              <input type="text" class="form-control" id="editStudentId" readonly>
            </div>
            <div class="mb-3">
              <label for="editFirstName" class="form-label">First Name</label>
              <input type="text" class="form-control" id="editFirstName" required>
            </div>
            <div class="mb-3">
              <label for="editLastName" class="form-label">Last Name</label>
              <input type="text" class="form-control" id="editLastName" required>
            </div>
            <div class="mb-3">
              <label for="editEmail" class="form-label">Email</label>
              <input type="email" class="form-control" id="editEmail" required>
            </div>
            <div class="mb-3">
              <label for="editPhone" class="form-label">Phone</label>
              <input type="tel" class="form-control" id="editPhone">
            </div>
          </form>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          <button type="button" class="btn btn-save" onclick="saveStudent()">Save</button>
        </div>
      </div>
    </div>
  </div>

  <!-- View Student Modal -->
  <div class="modal fade" id="viewStudentModal" tabindex="-1" aria-labelledby="viewStudentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="viewStudentModalLabel">Student Profile</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="profile-card">
            <h5 id="profileName"></h5>
            <p><strong>Student ID:</strong> <span id="profileId"></span></p>
            <p><strong>Email:</strong> <span id="profileEmail"></span></p>
            <p><strong>Phone:</strong> <span id="profilePhone"></span></p>

            <div class="history-section">
              <h6>Application History</h6>
              <ul id="applicationHistory">
                <!-- Populated dynamically -->
              </ul>
            </div>

            <div class="history-section">
              <h6>Scholarship Allocation History</h6>
              <ul id="scholarshipHistory">
                <!-- Populated dynamically -->
              </ul>
            </div>
          </div>
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