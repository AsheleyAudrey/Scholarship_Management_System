<<?php
include "../Database/db.php";
session_start();

// Logged-in reviewer (replace with $_SESSION['user_id'])
$user_id = $_SESSION['user_id'] ?? 4;

// Fetch user data
$userQuery = "SELECT * FROM Users WHERE user_id = ?";
$stmt = $conn->prepare($userQuery);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

$successMessage = "";
$errorMessage = "";

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // PROFILE UPDATE
    if (isset($_POST['updateProfile'])) {
        $name  = $_POST['name'] ?? '';
        $email = $_POST['email'] ?? '';
        $phone = $_POST['phone'] ?? '';

        // Avatar upload
        $avatarUrl = $user['avatar'] ?? 'https://via.placeholder.com/100';
        if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
            $fileTmpPath = $_FILES['avatar']['tmp_name'];
            $fileName    = time() . "_" . basename($_FILES['avatar']['name']);
            $uploadDir   = "../uploads/avatars/";
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
            $dest = $uploadDir . $fileName;
            if (move_uploaded_file($fileTmpPath, $dest)) {
                $avatarUrl = $dest;
            }
        }

        $update = $conn->prepare("UPDATE Users SET name = ?, email = ?, phone = ?, avatar = ? WHERE user_id = ?");
        $update->bind_param("ssssi", $name, $email, $phone, $avatarUrl, $user_id);
        if ($update->execute()) {
            $successMessage = "Profile updated successfully!";
        } else {
            $errorMessage = "Error updating profile: " . $conn->error;
        }
    }

    // PASSWORD UPDATE
    if (isset($_POST['updatePassword'])) {
        $current = $_POST['currentPassword'] ?? '';
        $new     = $_POST['newPassword'] ?? '';
        $confirm = $_POST['confirmPassword'] ?? '';

        if ($new === $confirm) {
            // verify old password
            if (password_verify($current, $user['password'])) {
                $hashed = password_hash($new, PASSWORD_BCRYPT);
                $passUpdate = $conn->prepare("UPDATE Users SET password = ? WHERE user_id = ?");
                $passUpdate->bind_param("si", $hashed, $user_id);
                if ($passUpdate->execute()) {
                    $successMessage = "Password changed successfully!";
                } else {
                    $errorMessage = "Error updating password.";
                }
            } else {
                $errorMessage = "Current password is incorrect!";
            }
        } else {
            $errorMessage = "Passwords do not match!";
        }
    }

    // NOTIFICATION SETTINGS UPDATE
    if (isset($_POST['updateNotifications'])) {
        $newAssign = isset($_POST['newAssignmentEmails']) ? 1 : 0;
        $deadline  = isset($_POST['deadlineReminderEmails']) ? 1 : 0;
        $updates   = isset($_POST['systemUpdateEmails']) ? 1 : 0;

        // Use user-specific settings table
        $check = $conn->prepare("SELECT * FROM ReviewerSettings WHERE user_id = ?");
        $check->bind_param("i", $user_id);
        $check->execute();
        $res = $check->get_result();

        if ($res->num_rows > 0) {
            $update = $conn->prepare("UPDATE ReviewerSettings 
                SET new_assignment = ?, deadline_reminder = ?, system_updates = ? 
                WHERE user_id = ?");
            $update->bind_param("iiii", $newAssign, $deadline, $updates, $user_id);
            $update->execute();
        } else {
            $insert = $conn->prepare("INSERT INTO ReviewerSettings (user_id, new_assignment, deadline_reminder, system_updates) 
                VALUES (?, ?, ?, ?)");
            $insert->bind_param("iiii", $user_id, $newAssign, $deadline, $updates);
            $insert->execute();
        }

        $successMessage = "Notification preferences saved!";
    }

    // Refresh user data
    $stmt = $conn->prepare($userQuery);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
}

// Fetch reviewer-specific notification settings
$settingsQ = $conn->prepare("SELECT * FROM ReviewerSettings WHERE user_id = ?");
$settingsQ->bind_param("i", $user_id);
$settingsQ->execute();
$settings = $settingsQ->get_result()->fetch_assoc() ?? ['new_assignment'=>1,'deadline_reminder'=>1,'system_updates'=>0];
?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Settings and Profile</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"/>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet"/>
  <style>
    /* Your existing CSS here */
    .main-content { background-color: #f8f9fa; min-height: 100vh; padding: 30px; }
    .page-header { display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; }
    .page-header h1 { font-size:24px; font-weight:600; color:#333; }
    .settings-section { background-color:#fff; border-radius:8px; padding:20px; box-shadow:0 2px 5px rgba(0,0,0,0.1); margin-bottom:20px; }
    .nav-tabs .nav-link { color:#333; border:none; border-bottom:2px solid transparent; }
    .nav-tabs .nav-link:hover { border-bottom:2px solid #509CDB; }
    .nav-tabs .nav-link.active { color:#152259; border-bottom:2px solid #509CDB; }
    .tab-content { padding:20px; }
    .form-label { font-size:14px; font-weight:600; color:#333; }
    .form-control, .form-select { border-radius:5px; border:1px solid #ced4da; }
    .form-control:focus, .form-select:focus { border-color:#509CDB; box-shadow:0 0 5px rgba(80,156,219,0.3); }
    .avatar-preview { width:100px; height:100px; border-radius:50%; object-fit:cover; border:1px solid #dee2e6; margin-bottom:15px; }
    .avatar-upload { display:flex; align-items:center; gap:15px; }
    .form-check-input[type="checkbox"] { width:40px; height:20px; border-radius:20px; background-color:#ced4da; border:none; }
    .form-check-input[type="checkbox"]:checked { background-color:#509CDB; }
    .btn-save { background-color:#509CDB; border:none; font-size:14px; padding:10px 20px; }
    .btn-save:hover { background-color:#408CCB; }
  </style>
</head>
<body>
<?php include 'sidebar.php'; ?>

<div class="main-content">
  <div class="page-header">
    <h1>Settings and Profile</h1>
  </div>

  <div class="settings-section">
    <ul class="nav nav-tabs" id="settingsTabs" role="tablist">
      <li class="nav-item" role="presentation">
        <button class="nav-link active" id="profile-tab" data-bs-toggle="tab" data-bs-target="#profile" type="button" role="tab">Profile</button>
      </li>
      <li class="nav-item" role="presentation">
        <button class="nav-link" id="password-tab" data-bs-toggle="tab" data-bs-target="#password" type="button" role="tab">Change Password</button>
      </li>
      <li class="nav-item" role="presentation">
        <button class="nav-link" id="notifications-tab" data-bs-toggle="tab" data-bs-target="#notifications" type="button" role="tab">Notification Preferences</button>
      </li>
    </ul>

    <div class="tab-content" id="settingsTabsContent">
      <!-- Profile Tab -->
      <div class="tab-pane fade show active" id="profile" role="tabpanel">
        <form method="POST" action="update_profile.php" enctype="multipart/form-data">
          <div class="mb-3">
            <label for="profilePicture" class="form-label">Profile Picture</label>
            <div class="avatar-upload">
              <img src="<?= htmlspecialchars($user['avatar'] ?? 'https://via.placeholder.com/100') ?>" class="avatar-preview" id="avatarPreview">
              <input type="file" name="avatar" class="form-control" id="profilePicture" accept="image/*">
            </div>
          </div>
          <div class="mb-3">
            <label class="form-label">Name</label>
            <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($user['name']) ?>" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Phone</label>
            <input type="tel" name="phone" class="form-control" value="<?= htmlspecialchars($user['phone'] ?? '') ?>">
          </div>
          <button type="submit" class="btn btn-save">Save Changes</button>
        </form>
      </div>

      <!-- Change Password Tab -->
      <div class="tab-pane fade" id="password" role="tabpanel">
        <form method="POST" action="update_password.php">
          <div class="mb-3">
            <label class="form-label">Current Password</label>
            <input type="password" name="currentPassword" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label">New Password</label>
            <input type="password" name="newPassword" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Confirm Password</label>
            <input type="password" name="confirmPassword" class="form-control" required>
          </div>
          <button type="submit" class="btn btn-save">Change Password</button>
        </form>
      </div>

      <!-- Notification Preferences Tab -->
      <div class="tab-pane fade" id="notifications" role="tabpanel">
        <form method="POST" action="save_notifications.php">
          <div class="mb-3 form-check form-switch">
            <input class="form-check-input" type="checkbox" name="newAssignmentEmails" <?= ($settings['new_assignment'] ?? 1) ? 'checked' : '' ?>>
            <label class="form-check-label">Email notifications for new application assignments</label>
          </div>
          <div class="mb-3 form-check form-switch">
            <input class="form-check-input" type="checkbox" name="deadlineReminderEmails" <?= ($settings['deadline_reminder'] ?? 1) ? 'checked' : '' ?>>
            <label class="form-check-label">Email notifications for deadline reminders</label>
          </div>
          <div class="mb-3 form-check form-switch">
            <input class="form-check-input" type="checkbox" name="systemUpdateEmails" <?= ($settings['system_updates'] ?? 0) ? 'checked' : '' ?>>
            <label class="form-check-label">Email notifications for system updates</label>
          </div>
          <button type="submit" class="btn btn-save">Save Preferences</button>
        </form>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php $conn->close(); ?>
