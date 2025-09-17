<?php
include "../Database/db.php";
session_start();

// Ensure user is logged in and is an Admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== "Admin") {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch logged-in admin details
$stmt = $conn->prepare("SELECT * FROM Users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Settings and Profile</title>
  
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"/>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet"/>

  <style>
    .main-content { background-color:#f8f9fa; min-height:100vh; padding:30px; }
    .page-header { display:flex; justify-content:space-between; align-items:center; margin-bottom:25px; }
    .page-header h1 { font-size:24px; font-weight:600; color:#152259; }
    .accordion-item { border:none; margin-bottom:12px; box-shadow:0 2px 5px rgba(0,0,0,0.1); border-radius:8px; overflow:hidden; }
    .accordion-button { background:#fff; color:#152259; font-weight:600; padding:15px; }
    .accordion-button:not(.collapsed) { background:#509CDB; color:#fff; }
    .accordion-body { background:#fff; padding:20px; }
    .form-label { font-weight:500; color:#333; }
    .form-control, .form-select { border-radius:6px; font-size:15px; }
    .btn-save { background:#509CDB; border:none; padding:8px 16px; color:#fff; border-radius:6px; font-size:14px; }
    .btn-save:hover { background:#408CCB; }
    .btn-add-admin { background:#28a745; border:none; padding:8px 15px; font-size:14px; color:#fff; border-radius:6px; }
    .btn-add-admin:hover { background:#218838; }
    .btn-remove { background:#dc3545; border:none; font-size:14px; padding:5px 10px; color:#fff; border-radius:6px; }
    .btn-remove:hover { background:#c82333; }
    .admin-users-table th { background:#152259; color:#fff; }
  </style>
</head>
<body>
  <?php include 'sidebar.php'; ?>

  <div class="main-content">
    <div class="page-header">
      <h1>Settings and Profile</h1>
    </div>

    <div class="accordion" id="settingsAccordion">

      <!-- Change Admin Password -->
      <div class="accordion-item">
        <h2 class="accordion-header" id="headingPassword">
          <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapsePassword">
            Change Admin Password
          </button>
        </h2>
        <div id="collapsePassword" class="accordion-collapse collapse show" data-bs-parent="#settingsAccordion">
          <div class="accordion-body">
            <form method="POST" action="update_password.php">
              <input type="hidden" name="user_id" value="<?php echo $user_id; ?>">
              <div class="mb-3">
                <label class="form-label">Current Password</label>
                <input type="password" name="currentPassword" class="form-control" required>
              </div>
              <div class="mb-3">
                <label class="form-label">New Password</label>
                <input type="password" name="newPassword" class="form-control" minlength="8" required>
              </div>
              <div class="mb-3">
                <label class="form-label">Confirm Password</label>
                <input type="password" name="confirmPassword" class="form-control" required>
              </div>
              <button type="submit" class="btn btn-save">Change Password</button>
            </form>
          </div>
        </div>
      </div>

      <!-- Manage Admin Users -->
      <div class="accordion-item">
        <h2 class="accordion-header" id="headingAdmins">
          <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseAdmins">
            Manage Admin Users
          </button>
        </h2>
        <div id="collapseAdmins" class="accordion-collapse collapse" data-bs-parent="#settingsAccordion">
          <div class="accordion-body">
            <form method="POST" action="add_admin.php" class="mb-4">
              <div class="row">
                <div class="col-md-4 mb-3">
                  <label class="form-label">Name</label>
                  <input type="text" name="adminName" class="form-control" required>
                </div>
                <div class="col-md-4 mb-3">
                  <label class="form-label">Email</label>
                  <input type="email" name="adminEmail" class="form-control" required>
                </div>
                <div class="col-md-4 mb-3">
                  <label class="form-label">Password</label>
                  <input type="password" name="adminPassword" class="form-control" minlength="8" required>
                </div>
              </div>
              <button type="submit" class="btn btn-add-admin">Add Admin</button>
            </form>

            <!-- Dynamic Admin Users List -->
            <div class="admin-users-table">
              <table class="table table-hover">
                <thead>
                  <tr><th>Name</th><th>Email</th><th>Actions</th></tr>
                </thead>
                <tbody>
                  <?php
                  $admins = $conn->query("SELECT user_id, username, email FROM Users WHERE role='Admin'");
                  while ($row = $admins->fetch_assoc()) {
                      echo "<tr>
                              <td>{$row['username']}</td>
                              <td>{$row['email']}</td>
                              <td>
                                <form method='POST' action='remove_admin.php' style='display:inline;'>
                                  <input type='hidden' name='user_id' value='{$row['user_id']}'>
                                  <button class='btn btn-remove' type='submit'>Remove</button>
                                </form>
                              </td>
                            </tr>";
                  }
                  ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>

      <!-- Email Templates -->
      <div class="accordion-item">
        <h2 class="accordion-header" id="headingTemplates">
          <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTemplates">
            Email Templates
          </button>
        </h2>
        <div id="collapseTemplates" class="accordion-collapse collapse" data-bs-parent="#settingsAccordion">
          <div class="accordion-body">
            <form method="POST" action="save_templates.php">
              <div class="mb-3">
                <label class="form-label">Application Approved</label>
                <textarea name="approvedTemplate" rows="4" class="form-control">Dear {StudentName}, your application for {ScholarshipName} has been approved.</textarea>
              </div>
              <div class="mb-3">
                <label class="form-label">Application Rejected</label>
                <textarea name="rejectedTemplate" rows="4" class="form-control">Dear {StudentName}, we regret to inform you that your application for {ScholarshipName} has been rejected.</textarea>
              </div>
              <div class="mb-3">
                <label class="form-label">Missing Documents</label>
                <textarea name="missingTemplate" rows="4" class="form-control">Dear {StudentName}, please provide {MissingDocuments} for {ScholarshipName} application.</textarea>
              </div>
              <button type="submit" class="btn btn-save">Save Templates</button>
            </form>
          </div>
        </div>
      </div>

      <!-- System Settings -->
      <div class="accordion-item">
        <h2 class="accordion-header" id="headingSystem">
          <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseSystem">
            System Settings
          </button>
        </h2>
        <div id="collapseSystem" class="accordion-collapse collapse" data-bs-parent="#settingsAccordion">
          <div class="accordion-body">
            <form method="POST" action="save_system.php">
              <div class="mb-3 form-check form-switch">
                <input class="form-check-input" type="checkbox" name="emailNotifications" checked>
                <label class="form-check-label">Enable Email Notifications</label>
              </div>
              <div class="mb-3 form-check form-switch">
                <input class="form-check-input" type="checkbox" name="autoApprove">
                <label class="form-check-label">Auto-Approve Applications</label>
              </div>
              <div class="mb-3">
                <label class="form-label">Application Deadline</label>
                <input type="date" name="applicationDeadline" class="form-control">
              </div>
              <div class="mb-3">
                <label class="form-label">Max Scholarship Amount</label>
                <input type="number" name="maxScholarshipAmount" class="form-control" value="10000">
              </div>
              <button type="submit" class="btn btn-save">Save Settings</button>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php $conn->close(); ?>
