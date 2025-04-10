<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Settings and Profile</title>
  
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

    /* Accordion Styling */
    .accordion-item {
      border: none;
      margin-bottom: 10px;
      box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    }

    .accordion-button {
      background-color: #ffffff;
      color: #152259; /* Match sidebar color */
      font-weight: 600;
      padding: 15px;
    }

    .accordion-button:not(.collapsed) {
      background-color: #509CDB; /* Match active item color */
      color: #ffffff;
    }

    .accordion-button:focus {
      box-shadow: none;
      border-color: #509CDB;
    }

    .accordion-body {
      background-color: #ffffff;
      padding: 20px;
    }

    /* Form Styling */
    .form-label {
      font-weight: 500;
      color: #333;
    }

    .form-control,
    .form-select {
      border-radius: 5px;
      border: 1px solid #ced4da;
      box-shadow: none;
    }

    .form-control:focus,
    .form-select:focus {
      border-color: #509CDB; /* Match active item color */
      box-shadow: 0 0 5px rgba(80, 156, 219, 0.3);
    }

    /* Toggle Switch Styling */
    .form-check-input[type="checkbox"] {
      width: 40px;
      height: 20px;
      border-radius: 20px;
      background-color: #ced4da;
      border: none;
      cursor: pointer;
    }

    .form-check-input[type="checkbox"]:checked {
      background-color: #509CDB; /* Match active item color */
    }

    .form-check-input[type="checkbox"]:focus {
      box-shadow: 0 0 5px rgba(80, 156, 219, 0.3);
    }

    /* Admin Users Table */
    .admin-users-table {
      margin-top: 20px;
    }

    .admin-users-table .table {
      margin-bottom: 0;
    }

    .admin-users-table .table th {
      background-color: #152259; /* Match sidebar color */
      color: #ffffff;
    }

    .admin-users-table .table td {
      vertical-align: middle;
    }

    .admin-users-table .btn-remove {
      background-color: #dc3545; /* Red for remove */
      border: none;
      font-size: 14px;
      padding: 5px 10px;
    }

    .admin-users-table .btn-remove:hover {
      background-color: #c82333;
    }

    /* Buttons */
    .btn-save {
      background-color: #509CDB; /* Match active item color */
      border: none;
      padding: 8px 15px;
      font-size: 14px;
      color: #ffffff;
    }

    .btn-save:hover {
      background-color: #408CCB;
    }

    .btn-add-admin {
      background-color: #28a745; /* Green for add */
      border: none;
      padding: 8px 15px;
      font-size: 14px;
      color: #ffffff;
    }

    .btn-add-admin:hover {
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
        <h1>Settings and Profile</h1>
      </div>

      <!-- Accordion for Settings Sections -->
      <div class="accordion" id="settingsAccordion">
        <!-- Change Admin Password -->
        <div class="accordion-item">
          <h2 class="accordion-header" id="headingPassword">
            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapsePassword" aria-expanded="true" aria-controls="collapsePassword">
              Change Admin Password
            </button>
          </h2>
          <div id="collapsePassword" class="accordion-collapse collapse show" aria-labelledby="headingPassword" data-bs-parent="#settingsAccordion">
            <div class="accordion-body">
              <form id="changePasswordForm" onsubmit="changePassword(event)">
                <div class="mb-3">
                  <label for="currentPassword" class="form-label">Current Password</label>
                  <input type="password" class="form-control" id="currentPassword" required>
                </div>
                <div class="mb-3">
                  <label for="newPassword" class="form-label">New Password</label>
                  <input type="password" class="form-control" id="newPassword" required minlength="8">
                </div>
                <div class="mb-3">
                  <label for="confirmPassword" class="form-label">Confirm New Password</label>
                  <input type="password" class="form-control" id="confirmPassword" required>
                </div>
                <button type="submit" class="btn btn-save">Change Password</button>
              </form>
            </div>
          </div>
        </div>

        <!-- Manage Admin Users -->
        <div class="accordion-item">
          <h2 class="accordion-header" id="headingAdmins">
            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseAdmins" aria-expanded="false" aria-controls="collapseAdmins">
              Manage Admin Users
            </button>
          </h2>
          <div id="collapseAdmins" class="accordion-collapse collapse" aria-labelledby="headingAdmins" data-bs-parent="#settingsAccordion">
            <div class="accordion-body">
              <form id="addAdminForm" onsubmit="addAdmin(event)" class="mb-4">
                <div class="row">
                  <div class="col-md-4 mb-3">
                    <label for="adminName" class="form-label">Name</label>
                    <input type="text" class="form-control" id="adminName" required>
                  </div>
                  <div class="col-md-4 mb-3">
                    <label for="adminEmail" class="form-label">Email</label>
                    <input type="email" class="form-control" id="adminEmail" required>
                  </div>
                  <div class="col-md-4 mb-3">
                    <label for="adminPassword" class="form-label">Password</label>
                    <input type="password" class="form-control" id="adminPassword" required minlength="8">
                  </div>
                </div>
                <button type="submit" class="btn btn-add-admin">Add Admin</button>
              </form>
              <div class="admin-users-table">
                <table class="table table-hover">
                  <thead>
                    <tr>
                      <th>Name</th>
                      <th>Email</th>
                      <th>Actions</th>
                    </tr>
                  </thead>
                  <tbody id="adminUsersTable">
                    <tr>
                      <td>Admin One</td>
                      <td>admin1@example.com</td>
                      <td>
                        <button class="btn btn-remove" onclick="removeAdmin(this)">Remove</button>
                      </td>
                    </tr>
                    <tr>
                      <td>Admin Two</td>
                      <td>admin2@example.com</td>
                      <td>
                        <button class="btn btn-remove" onclick="removeAdmin(this)">Remove</button>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>

        <!-- Email Templates -->
        <div class="accordion-item">
          <h2 class="accordion-header" id="headingEmailTemplates">
            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseEmailTemplates" aria-expanded="false" aria-controls="collapseEmailTemplates">
              Email Templates
            </button>
          </h2>
          <div id="collapseEmailTemplates" class="accordion-collapse collapse" aria-labelledby="headingEmailTemplates" data-bs-parent="#settingsAccordion">
            <div class="accordion-body">
              <form id="emailTemplatesForm" onsubmit="saveEmailTemplates(event)">
                <div class="mb-3">
                  <label for="applicationApprovedTemplate" class="form-label">Application Approved Template</label>
                  <textarea class="form-control" id="applicationApprovedTemplate" rows="4" required>Dear {StudentName},\n\nCongratulations! Your application for the {ScholarshipName} has been approved.\n\nBest regards,\nScholarship Team</textarea>
                </div>
                <div class="mb-3">
                  <label for="applicationRejectedTemplate" class="form-label">Application Rejected Template</label>
                  <textarea class="form-control" id="applicationRejectedTemplate" rows="4" required>Dear {StudentName},\n\nWe regret to inform you that your application for the {ScholarshipName} has been rejected.\n\nBest regards,\nScholarship Team</textarea>
                </div>
                <div class="mb-3">
                  <label for="documentMissingTemplate" class="form-label">Document Missing Template</label>
                  <textarea class="form-control" id="documentMissingTemplate" rows="4" required>Dear {StudentName},\n\nPlease submit the following missing documents for your {ScholarshipName} application: {MissingDocuments}.\n\nBest regards,\nScholarship Team</textarea>
                </div>
                <button type="submit" class="btn btn-save">Save Templates</button>
              </form>
            </div>
          </div>
        </div>

        <!-- System Settings -->
        <div class="accordion-item">
          <h2 class="accordion-header" id="headingSystemSettings">
            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseSystemSettings" aria-expanded="false" aria-controls="collapseSystemSettings">
              System Settings
            </button>
          </h2>
          <div id="collapseSystemSettings" class="accordion-collapse collapse" aria-labelledby="headingSystemSettings" data-bs-parent="#settingsAccordion">
            <div class="accordion-body">
              <form id="systemSettingsForm" onsubmit="saveSystemSettings(event)">
                <div class="mb-3 form-check form-switch">
                  <input class="form-check-input" type="checkbox" id="emailNotifications" checked>
                  <label class="form-check-label" for="emailNotifications">Enable Email Notifications</label>
                </div>
                <div class="mb-3 form-check form-switch">
                  <input class="form-check-input" type="checkbox" id="autoApproveApplications">
                  <label class="form-check-label" for="autoApproveApplications">Auto-Approve Applications (if criteria met)</label>
                </div>
                <div class="mb-3">
                  <label for="applicationDeadline" class="form-label">Application Deadline</label>
                  <input type="date" class="form-control" id="applicationDeadline" required>
                </div>
                <div class="mb-3">
                  <label for="maxScholarshipAmount" class="form-label">Maximum Scholarship Amount</label>
                  <input type="number" class="form-control" id="maxScholarshipAmount" required value="10000">
                </div>
                <button type="submit" class="btn btn-save">Save Settings</button>
              </form>
            </div>
          </div>
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