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

    /* Settings form styling */
    .settings-form {
      background-color: #ffffff;
      border-radius: 8px;
      padding: 20px;
      box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
      max-width: 800px;
      margin: 0 auto;
    }

    .settings-form h2 {
      font-size: 20px;
      font-weight: 600;
      color: #152259; /* Match sidebar color */
      margin-bottom: 20px;
    }

    .settings-form .form-section {
      margin-bottom: 20px;
    }

    .settings-form .form-section h3 {
      font-size: 16px;
      font-weight: 600;
      color: #152259; /* Match sidebar color */
      margin-bottom: 15px;
    }

    .settings-form .form-label {
      font-weight: 500;
      color: #333;
    }

    .settings-form .form-control,
    .settings-form .form-select {
      border-radius: 5px;
      border: 1px solid #ced4da;
      box-shadow: none;
    }

    .settings-form .form-control:focus,
    .settings-form .form-select:focus {
      border-color: #509CDB; /* Match active item color */
      box-shadow: 0 0 5px rgba(80, 156, 219, 0.3);
    }

    .settings-form .profile-pic-section {
      display: flex;
      align-items: center;
      gap: 20px;
      margin-bottom: 20px;
    }

    .settings-form .profile-pic {
      width: 100px;
      height: 100px;
      border-radius: 50%;
      object-fit: cover;
      border: 2px solid #152259; /* Match sidebar color */
    }

    .settings-form .btn-upload-pic {
      background-color: #509CDB; /* Match active item color */
      border: none;
      padding: 8px 15px;
      font-size: 14px;
    }

    .settings-form .btn-upload-pic:hover {
      background-color: #408CCB;
    }

    .settings-form .btn-save {
      background-color: #509CDB; /* Match active item color */
      border: none;
      padding: 10px 20px;
      font-size: 16px;
      font-weight: 500;
    }

    .settings-form .btn-save:hover {
      background-color: #408CCB;
    }

    .settings-form .btn-delete {
      background-color: #dc3545; /* Red for delete */
      border: none;
      padding: 10px 20px;
      font-size: 16px;
      font-weight: 500;
    }

    .settings-form .btn-delete:hover {
      background-color: #c82333;
    }
  </style>
</head>
<body>
    <!-- Main content -->
    <div class="main-content">
      <!-- Header -->
      <div class="page-header">
        <h1>Settings and Profile</h1>
      </div>

      <!-- Settings Form -->
      <div class="settings-form">
        <h2>Manage Your Account</h2>
        <form id="settingsForm">
          <!-- Personal Information Section -->
          <div class="form-section">
            <h3>Personal Information</h3>
            <div class="row">
              <div class="col-md-6 mb-3">
                <label for="firstName" class="form-label">First Name</label>
                <input type="text" class="form-control" id="firstName" value="John" required>
              </div>
              <div class="col-md-6 mb-3">
                <label for="lastName" class="form-label">Last Name</label>
                <input type="text" class="form-control" id="lastName" value="Doe" required>
              </div>
            </div>
            <div class="row">
              <div class="col-md-6 mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" id="email" value="john.doe@example.com" required>
              </div>
              <div class="col-md-6 mb-3">
                <label for="phone" class="form-label">Phone Number</label>
                <input type="tel" class="form-control" id="phone" value="+1-555-123-4567">
              </div>
            </div>
          </div>

          <!-- Profile Picture Section -->
          <div class="form-section">
            <h3>Profile Picture</h3>
            <div class="profile-pic-section">
              <img src="https://via.placeholder.com/100" alt="Profile Picture" class="profile-pic" id="profilePic">
              <div>
                <input type="file" class="form-control mb-2" id="profilePicInput" accept="image/*" style="display: none;">
                <button type="button" class="btn btn-upload-pic" onclick="document.getElementById('profilePicInput').click()">Upload New Picture</button>
              </div>
            </div>
          </div>

          <!-- Change Password Section -->
          <div class="form-section">
            <h3>Change Password</h3>
            <div class="row">
              <div class="col-md-6 mb-3">
                <label for="currentPassword" class="form-label">Current Password</label>
                <input type="password" class="form-control" id="currentPassword">
              </div>
              <div class="col-md-6 mb-3">
                <label for="newPassword" class="form-label">New Password</label>
                <input type="password" class="form-control" id="newPassword">
              </div>
            </div>
            <div class="row">
              <div class="col-md-6 mb-3">
                <label for="confirmPassword" class="form-label">Confirm New Password</label>
                <input type="password" class="form-control" id="confirmPassword">
              </div>
            </div>
          </div>

          <!-- Language Preference Section -->
          <div class="form-section">
            <h3>Language Preference</h3>
            <div class="mb-3">
              <label for="language" class="form-label">Select Language</label>
              <select class="form-select" id="language">
                <option value="en" selected>English</option>
                <option value="es">Spanish</option>
                <option value="fr">French</option>
              </select>
            </div>
          </div>

          <!-- Notification Settings Section -->
          <div class="form-section">
            <h3>Notification Settings</h3>
            <div class="form-check mb-3">
              <input class="form-check-input" type="checkbox" id="emailAlerts" checked>
              <label class="form-check-label" for="emailAlerts">
                Receive email alerts for application updates
              </label>
            </div>
            <div class="form-check mb-3">
              <input class="form-check-input" type="checkbox" id="scholarshipAlerts" checked>
              <label class="form-check-label" for="scholarshipAlerts">
                Receive email alerts for new scholarships
              </label>
            </div>
            <div class="form-check mb-3">
              <input class="form-check-input" type="checkbox" id="deadlineAlerts">
              <label class="form-check-label" for="deadlineAlerts">
                Receive email alerts for upcoming deadlines
              </label>
            </div>
          </div>

          <!-- Delete Account Section -->
          <div class="form-section">
            <h3>Delete Account</h3>
            <p class="text-muted mb-3">Deleting your account is permanent and cannot be undone. This action requires admin approval.</p>
            <button type="button" class="btn btn-delete" onclick="deleteAccount()">Request Account Deletion</button>
          </div>

          <!-- Save Button -->
          <div class="text-center">
            <button type="submit" class="btn btn-save">Save Changes</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Bootstrap JS -->
  <script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
  ></script>
</body>
</html>