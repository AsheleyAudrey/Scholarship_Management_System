<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Notifications Management</title>
  
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

    /* Send Notification Button */
    .page-header .btn-send {
      background-color: #509CDB; /* Match active item color */
      border: none;
      padding: 10px 20px;
      font-size: 16px;
      color: #ffffff;
    }

    .page-header .btn-send:hover {
      background-color: #408CCB;
    }

    /* Tabs for Filtering by Status */
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

    /* Notifications Table */
    .notifications-table {
      background-color: #ffffff;
      border-radius: 8px;
      padding: 20px;
      box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
      margin-bottom: 20px;
    }

    .notifications-table .table {
      margin-bottom: 0;
    }

    .notifications-table .table th {
      background-color: #152259; /* Match sidebar color */
      color: #ffffff;
    }

    .notifications-table .table td {
      vertical-align: middle;
    }

    .notifications-table .table .badge {
      font-size: 12px;
    }

    .notifications-table .btn-view {
      background-color: #17a2b8; /* Cyan for view */
      border: none;
      font-size: 14px;
      padding: 5px 10px;
      margin-right: 5px;
    }

    .notifications-table .btn-view:hover {
      background-color: #138496;
    }

    .notifications-table .btn-send-now {
      background-color: #28a745; /* Green for send */
      border: none;
      font-size: 14px;
      padding: 5px 10px;
    }

    .notifications-table .btn-send-now:hover {
      background-color: #218838;
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


    <!-- Main content -->
    <div class="main-content">
      <!-- Header -->
      <div class="page-header">
        <h1>Notifications Management</h1>
        <button class="btn btn-send" data-bs-toggle="modal" data-bs-target="#sendNotificationModal">
          <i class="bi bi-plus-circle me-2"></i> Send Notification
        </button>
      </div>

      <!-- Tabs for Filtering by Status -->
      <ul class="nav nav-tabs mb-3" id="statusTabs" role="tablist">
        <li class="nav-item" role="presentation">
          <button class="nav-link active" id="all-tab" data-bs-toggle="tab" data-bs-target="#all" type="button" role="tab" aria-controls="all" aria-selected="true">All</button>
        </li>
        <li class="nav-item" role="presentation">
          <button class="nav-link" id="sent-tab" data-bs-toggle="tab" data-bs-target="#sent" type="button" role="tab" aria-controls="sent" aria-selected="false">Sent</button>
        </li>
        <li class="nav-item" role="presentation">
          <button class="nav-link" id="draft-tab" data-bs-toggle="tab" data-bs-target="#draft" type="button" role="tab" aria-controls="draft" aria-selected="false">Draft</button>
        </li>
        <li class="nav-item" role="presentation">
          <button class="nav-link" id="scheduled-tab" data-bs-toggle="tab" data-bs-target="#scheduled" type="button" role="tab" aria-controls="scheduled" aria-selected="false">Scheduled</button>
        </li>
      </ul>

      <!-- Tab Content -->
      <div class="tab-content" id="statusTabContent">
        <!-- All Notifications -->
        <div class="tab-pane fade show active" id="all" role="tabpanel" aria-labelledby="all-tab">
          <div class="notifications-table">
            <table class="table table-hover">
              <thead>
                <tr>
                  <th>Notification ID</th>
                  <th>Recipient</th>
                  <th>Subject</th>
                  <th>Status</th>
                  <th>Date</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody id="allNotifications">
                <tr data-status="Sent">
                  <td>N001</td>
                  <td>John Doe</td>
                  <td>Your Application Has Been Approved</td>
                  <td><span class="badge bg-success">Sent</span></td>
                  <td>2025-04-05</td>
                  <td>
                    <button class="btn btn-view" data-bs-toggle="modal" data-bs-target="#viewNotificationModal" onclick="viewNotification(this)">View</button>
                  </td>
                </tr>
                <tr data-status="Draft">
                  <td>N002</td>
                  <td>Jane Smith</td>
                  <td>Reminder: Submit Missing Documents</td>
                  <td><span class="badge bg-warning">Draft</span></td>
                  <td>-</td>
                  <td>
                    <button class="btn btn-view" data-bs-toggle="modal" data-bs-target="#viewNotificationModal" onclick="viewNotification(this)">View</button>
                    <button class="btn btn-send-now" onclick="sendNow(this)">Send Now</button>
                  </td>
                </tr>
                <tr data-status="Scheduled">
                  <td>N003</td>
                  <td>Emily Johnson</td>
                  <td>Scholarship Award Notification</td>
                  <td><span class="badge bg-info">Scheduled</span></td>
                  <td>2025-04-10</td>
                  <td>
                    <button class="btn btn-view" data-bs-toggle="modal" data-bs-target="#viewNotificationModal" onclick="viewNotification(this)">View</button>
                    <button class="btn btn-send-now" onclick="sendNow(this)">Send Now</button>
                  </td>
                </tr>
              </tbody>
            </table>
            <nav aria-label="Page navigation">
              <ul class="pagination" id="paginationAll">
                <li class="page-item"><a class="page-link" href="#" onclick="changePage('all', 1)">1</a></li>
              </ul>
            </nav>
          </div>
        </div>

        <!-- Sent Notifications -->
        <div class="tab-pane fade" id="sent" role="tabpanel" aria-labelledby="sent-tab">
          <div class="notifications-table">
            <table class="table table-hover">
              <thead>
                <tr>
                  <th>Notification ID</th>
                  <th>Recipient</th>
                  <th>Subject</th>
                  <th>Status</th>
                  <th>Date</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody id="sentNotifications">
                <!-- Populated dynamically -->
              </tbody>
            </table>
            <nav aria-label="Page navigation">
              <ul class="pagination" id="paginationSent">
                <!-- Populated dynamically -->
              </ul>
            </nav>
          </div>
        </div>

        <!-- Draft Notifications -->
        <div class="tab-pane fade" id="draft" role="tabpanel" aria-labelledby="draft-tab">
          <div class="notifications-table">
            <table class="table table-hover">
              <thead>
                <tr>
                  <th>Notification ID</th>
                  <th>Recipient</th>
                  <th>Subject</th>
                  <th>Status</th>
                  <th>Date</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody id="draftNotifications">
                <!-- Populated dynamically -->
              </tbody>
            </table>
            <nav aria-label="Page navigation">
              <ul class="pagination" id="paginationDraft">
                <!-- Populated dynamically -->
              </ul>
            </nav>
          </div>
        </div>

        <!-- Scheduled Notifications -->
        <div class="tab-pane fade" id="scheduled" role="tabpanel" aria-labelledby="scheduled-tab">
          <div class="notifications-table">
            <table class="table table-hover">
              <thead>
                <tr>
                  <th>Notification ID</th>
                  <th>Recipient</th>
                  <th>Subject</th>
                  <th>Status</th>
                  <th>Date</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody id="scheduledNotifications">
                <!-- Populated dynamically -->
              </tbody>
            </table>
            <nav aria-label="Page navigation">
              <ul class="pagination" id="paginationScheduled">
                <!-- Populated dynamically -->
              </ul>
            </nav>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Send Notification Modal -->
  <div class="modal fade" id="sendNotificationModal" tabindex="-1" aria-labelledby="sendNotificationModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="sendNotificationModalLabel">Send Notification</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <form id="sendNotificationForm">
            <div class="mb-3">
              <label for="recipient" class="form-label">Recipient</label>
              <select class="form-select" id="recipient" required>
                <option value="" disabled selected>Select recipient</option>
                <option value="John Doe">John Doe</option>
                <option value="Jane Smith">Jane Smith</option>
                <option value="Emily Johnson">Emily Johnson</option>
              </select>
            </div>
            <div class="mb-3">
              <label for="subject" class="form-label">Subject</label>
              <input type="text" class="form-control" id="subject" required>
            </div>
            <div class="mb-3">
              <label for="message" class="form-label">Message</label>
              <textarea class="form-control" id="message" rows="4" required></textarea>
            </div>
            <div class="mb-3">
              <label for="scheduleDate" class="form-label">Schedule Date (Optional)</label>
              <input type="datetime-local" class="form-control" id="scheduleDate">
            </div>
          </form>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          <button type="button" class="btn btn-save" onclick="sendNotification()">Send</button>
        </div>
      </div>
    </div>
  </div>

  <!-- View Notification Modal -->
  <div class="modal fade" id="viewNotificationModal" tabindex="-1" aria-labelledby="viewNotificationModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="viewNotificationModalLabel">Notification Details</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <p><strong>Notification ID:</strong> <span id="viewNotificationId"></span></p>
          <p><strong>Recipient:</strong> <span id="viewRecipient"></span></p>
          <p><strong>Subject:</strong> <span id="viewSubject"></span></p>
          <p><strong>Message:</strong> <span id="viewMessage"></span></p>
          <p><strong>Status:</strong> <span id="viewStatus"></span></p>
          <p><strong>Date:</strong> <span id="viewDate"></span></p>
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