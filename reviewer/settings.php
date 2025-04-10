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

    /* Settings Section */
    .settings-section {
      background-color: #ffffff;
      border-radius: 8px;
      padding: 20px;
      box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
      margin-bottom: 20px;
    }

    /* Tabs */
    .nav-tabs .nav-link {
      color: #333;
      border: none;
      border-bottom: 2px solid transparent;
    }

    .nav-tabs .nav-link:hover {
      border-bottom: 2px solid #509CDB;
    }

    .nav-tabs .nav-link.active {
      color: #152259; /* Match sidebar color */
      border-bottom: 2px solid #509CDB; /* Match active item color */
    }

    .tab-content {
      padding: 20px;
    }

    /* Form Styling */
    .settings-section .form-label {
      font-size: 14px;
      font-weight: 600;
      color: #333;
    }

    .settings-section .form-control,
    .settings-section .form-select {
      border-radius: 5px;
      border: 1px solid #ced4da;
      box-shadow: none;
    }

    .settings-section .form-control:focus,
    .settings-section .form-select:focus {
      border-color: #509CDB; /* Match active item color */
      box-shadow: 0 0 5px rgba(80, 156, 219, 0.3);
    }

    /* Avatar Upload */
    .avatar-preview {
      width: 100px;
      height: 100px;
      border-radius: 50%;
      object-fit: cover;
      border: 1px solid #dee2e6;
      margin-bottom: 15px;
    }

    .avatar-upload {
      display: flex;
      align-items: center;
      gap: 15px;
    }

    /* Toggle Switches */
    .form-check-input[type="checkbox"] {
      width: 40px;
      height: 20px;
      border-radius: 20px;
      background-color: #ced4da;
      border: none;
    }

    .form-check-input[type="checkbox"]:checked {
      background-color: #509CDB; /* Match active item color */
    }

    .form-check-input[type="checkbox"]:focus {
      box-shadow: 0 0 5px rgba(80, 156, 219, 0.3);
    }

    .form-check-label {
      font-size: 14px;
      color: #333;
    }

    /* Save Button */
    .btn-save {
      background-color: #509CDB; /* Match active item color */
      border: none;
      font-size: 14px;
      padding: 10px 20px;
    }

    .btn-save:hover {
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
        <h1>Settings and Profile</h1>
      </div>

      <!-- Settings Section -->
      <div class="settings-section">
        <ul class="nav nav-tabs" id="settingsTabs" role="tablist">
          <li class="nav-item" role="presentation">
            <button class="nav-link active" id="profile-tab" data-bs-toggle="tab" data-bs-target="#profile" type="button" role="tab" aria-controls="profile" aria-selected="true">Profile</button>
          </li>
          <li class="nav-item" role="presentation">
            <button class="nav-link" id="password-tab" data-bs-toggle="tab" data-bs-target="#password" type="button" role="tab" aria-controls="password" aria-selected="false">Change Password</button>
          </li>
          <li class="nav-item" role="presentation">
            <button class="nav-link" id="notifications-tab" data-bs-toggle="tab" data-bs-target="#notifications" type="button" role="tab" aria-controls="notifications" aria-selected="false">Notification Preferences</button>
          </li>
        </ul>
        <div class="tab-content" id="settingsTabsContent">
          <!-- Profile Tab -->
          <div class="tab-pane fade show active" id="profile" role="tabpanel" aria-labelledby="profile-tab">
            <form id="profileForm">
              <div class="mb-3">
                <label for="profilePicture" class="form-label">Profile Picture</label>
                <div class="avatar-upload">
                  <img src="https://via.placeholder.com/100" alt="Profile Picture" class="avatar-preview" id="avatarPreview">
                  <input type="file" class="form-control" id="profilePicture" accept="image/*" onchange="updateProfilePicture(this)">
                </div>
              </div>
              <div class="mb-3">
                <label for="name" class="form-label">Name</label>
                <input type="text" class="form-control" id="name" value="John Reviewer" required>
              </div>
              <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" id="email" value="john.reviewer@example.com" required>
              </div>
              <div class="mb-3">
                <label for="phone" class="form-label">Phone Number</label>
                <input type="tel" class="form-control" id="phone" value="+1-555-123-4567">
              </div>
              <button type="submit" class="btn btn-save">Save Changes</button>
            </form>
          </div>
          <!-- Change Password Tab -->
          <div class="tab-pane fade" id="password" role="tabpanel" aria-labelledby="password-tab">
            <form id="passwordForm">
              <div class="mb-3">
                <label for="currentPassword" class="form-label">Current Password</label>
                <input type="password" class="form-control" id="currentPassword" required>
              </div>
              <div class="mb-3">
                <label for="newPassword" class="form-label">New Password</label>
                <input type="password" class="form-control" id="newPassword" required>
              </div>
              <div class="mb-3">
                <label for="confirmPassword" class="form-label">Confirm New Password</label>
                <input type="password" class="form-control" id="confirmPassword" required>
              </div>
              <button type="submit" class="btn btn-save">Change Password</button>
            </form>
          </div>
          <!-- Notification Preferences Tab -->
          <div class="tab-pane fade" id="notifications" role="tabpanel" aria-labelledby="notifications-tab">
            <form id="notificationsForm">
              <div class="mb-3 form-check form-switch">
                <input class="form-check-input" type="checkbox" id="newAssignmentEmails" checked>
                <label class="form-check-label" for="newAssignmentEmails">Email notifications for new application assignments</label>
              </div>
              <div class="mb-3 form-check form-switch">
                <input class="form-check-input" type="checkbox" id="deadlineReminderEmails" checked>
                <label class="form-check-label" for="deadlineReminderEmails">Email notifications for deadline reminders</label>
              </div>
              <div class="mb-3 form-check form-switch">
                <input class="form-check-input" type="checkbox" id="systemUpdateEmails">
                <label class="form-check-label" for="systemUpdateEmails">Email notifications for system updates</label>
              </div>
              <button type="submit" class="btn btn-save">Save Preferences</button>
            </form>
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