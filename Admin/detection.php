<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Fraud Detection Logs</title>
  
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

    /* Tabs for Filtering by Type */
    .nav-tabs .nav-link {
      color: #333;
      font-weight: 500;
    }

    .nav-tabs .nav-link.active {
      background-color: #509CDB; /* Match active item color */
      color: #ffffff;
      border-color: #509CDB;
    }

    .nav-tabs .nav-link:hover {
      border-color: #509CDB;
    }

    /* Flagged Applications and Activity Logs Tables */
    .logs-table {
      background-color: #ffffff;
      border-radius: 8px;
      padding: 20px;
      box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
      margin-bottom: 20px;
    }

    .logs-table .table {
      margin-bottom: 0;
    }

    .logs-table .table th {
      background-color: #152259; /* Match sidebar color */
      color: #ffffff;
    }

    .logs-table .table td {
      vertical-align: middle;
    }

    .logs-table .table .badge {
      font-size: 12px;
    }

    .logs-table .btn-view {
      background-color: #17a2b8; /* Cyan for view */
      border: none;
      font-size: 14px;
      padding: 5px 10px;
      margin-right: 5px;
    }

    .logs-table .btn-view:hover {
      background-color: #138496;
    }

    .logs-table .btn-clear {
      background-color: #dc3545; /* Red for clear */
      border: none;
      font-size: 14px;
      padding: 5px 10px;
    }

    .logs-table .btn-clear:hover {
      background-color: #c82333;
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

    .modal-body .audit-trail {
      background-color: #f8f9fa;
      padding: 15px;
      border-radius: 5px;
      max-height: 300px;
      overflow-y: auto;
    }

    .modal-body .audit-trail p {
      margin: 0;
      padding: 5px 0;
      border-bottom: 1px solid #dee2e6;
    }

    .modal-body .audit-trail p:last-child {
      border-bottom: none;
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
        <h1>Fraud Detection Logs</h1>
      </div>

      <!-- Tabs for Filtering by Type -->
      <ul class="nav nav-tabs mb-3" id="logTabs" role="tablist">
        <li class="nav-item" role="presentation">
          <button class="nav-link active" id="flagged-tab" data-bs-toggle="tab" data-bs-target="#flagged" type="button" role="tab" aria-controls="flagged" aria-selected="true">Flagged Applications</button>
        </li>
        <li class="nav-item" role="presentation">
          <button class="nav-link" id="activity-tab" data-bs-toggle="tab" data-bs-target="#activity" type="button" role="tab" aria-controls="activity" aria-selected="false">User Activity Logs</button>
        </li>
      </ul>

      <!-- Tab Content -->
      <div class="tab-content" id="logTabContent">
        <!-- Flagged Applications -->
        <div class="tab-pane fade show active" id="flagged" role="tabpanel" aria-labelledby="flagged-tab">
          <div class="logs-table">
            <table class="table table-hover">
              <thead>
                <tr>
                  <th>Application ID</th>
                  <th>Student Name</th>
                  <th>Reason</th>
                  <th>Flagged Date</th>
                  <th>Status</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody id="flaggedApplications">
                <tr>
                  <td>A001</td>
                  <td>John Doe</td>
                  <td><span class="badge bg-danger">Duplicate Document Detected</span></td>
                  <td>2025-04-05</td>
                  <td><span class="badge bg-warning">Under Review</span></td>
                  <td>
                    <button class="btn btn-view" data-bs-toggle="modal" data-bs-target="#auditTrailModal" onclick="viewAuditTrail(this)">View Audit Trail</button>
                    <button class="btn btn-clear" onclick="clearFlag(this)">Clear Flag</button>
                  </td>
                </tr>
                <tr>
                  <td>A002</td>
                  <td>Jane Smith</td>
                  <td><span class="badge bg-danger">Multiple Applications</span></td>
                  <td>2025-04-06</td>
                  <td><span class="badge bg-warning">Under Review</span></td>
                  <td>
                    <button class="btn btn-view" data-bs-toggle="modal" data-bs-target="#auditTrailModal" onclick="viewAuditTrail(this)">View Audit Trail</button>
                    <button class="btn btn-clear" onclick="clearFlag(this)">Clear Flag</button>
                  </td>
                </tr>
                <tr>
                  <td>A003</td>
                  <td>Emily Johnson</td>
                  <td><span class="badge bg-danger">Suspicious IP Address</span></td>
                  <td>2025-04-07</td>
                  <td><span class="badge bg-success">Cleared</span></td>
                  <td>
                    <button class="btn btn-view" data-bs-toggle="modal" data-bs-target="#auditTrailModal" onclick="viewAuditTrail(this)">View Audit Trail</button>
                  </td>
                </tr>
              </tbody>
            </table>
            <nav aria-label="Page navigation">
              <ul class="pagination" id="paginationFlagged">
                <li class="page-item"><a class="page-link" href="#" onclick="changePage('flagged', 1)">1</a></li>
              </ul>
            </nav>
          </div>
        </div>

        <!-- User Activity Logs -->
        <div class="tab-pane fade" id="activity" role="tabpanel" aria-labelledby="activity-tab">
          <div class="logs-table">
            <table class="table table-hover">
              <thead>
                <tr>
                  <th>Log ID</th>
                  <th>User</th>
                  <th>Action</th>
                  <th>Details</th>
                  <th>Timestamp</th>
                  <th>IP Address</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody id="activityLogs">
                <tr>
                  <td>L001</td>
                  <td>Admin One</td>
                  <td>Login</td>
                  <td>Successful login</td>
                  <td>2025-04-05 10:00:00</td>
                  <td>192.168.1.1</td>
                  <td>
                    <button class="btn btn-view" data-bs-toggle="modal" data-bs-target="#auditTrailModal" onclick="viewAuditTrail(this)">View Audit Trail</button>
                  </td>
                </tr>
                <tr>
                  <td>L002</td>
                  <td>John Doe</td>
                  <td>Document Upload</td>
                  <td><span class="badge bg-warning">Duplicate document detected</span></td>
                  <td>2025-04-05 10:15:00</td>
                  <td>192.168.1.2</td>
                  <td>
                    <button class="btn btn-view" data-bs-toggle="modal" data-bs-target="#auditTrailModal" onclick="viewAuditTrail(this)">View Audit Trail</button>
                  </td>
                </tr>
                <tr>
                  <td>L003</td>
                  <td>Jane Smith</td>
                  <td>Application Submission</td>
                  <td><span class="badge bg-warning">Multiple submissions from same IP</span></td>
                  <td>2025-04-06 14:30:00</td>
                  <td>192.168.1.2</td>
                  <td>
                    <button class="btn btn-view" data-bs-toggle="modal" data-bs-target="#auditTrailModal" onclick="viewAuditTrail(this)">View Audit Trail</button>
                  </td>
                </tr>
              </tbody>
            </table>
            <nav aria-label="Page navigation">
              <ul class="pagination" id="paginationActivity">
                <li class="page-item"><a class="page-link" href="#" onclick="changePage('activity', 1)">1</a></li>
              </ul>
            </nav>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Audit Trail Modal -->
  <div class="modal fade" id="auditTrailModal" tabindex="-1" aria-labelledby="auditTrailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="auditTrailModalLabel">Audit Trail</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="audit-trail" id="auditTrailContent">
            <!-- Populated dynamically -->
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