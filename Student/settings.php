<?php
include "../Database/db.php";
session_start();

// Simulate logged-in user (replace with $_SESSION['user_id'])
$user_id = 4;

// Fetch user data
$userQuery = "SELECT * FROM Users WHERE user_id = ?";
$stmt = $conn->prepare($userQuery);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$userResult = $stmt->get_result();
$user = $userResult->fetch_assoc();

$successMessage = "";
$errorMessage = "";

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstName = $_POST['firstName'] ?? '';
    $lastName  = $_POST['lastName'] ?? '';
    $email     = $_POST['email'] ?? '';
    $phone     = $_POST['phone'] ?? '';
    $language  = $_POST['language'] ?? 'en';

    // Handle profile pic upload
    $profilePicUrl = $user['profile_pic'] ?? 'https://via.placeholder.com/100';
    if (isset($_FILES['profilePic']) && $_FILES['profilePic']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['profilePic']['tmp_name'];
        $fileName    = basename($_FILES['profilePic']['name']);
        $fileType    = $_FILES['profilePic']['type'];

        if (in_array($fileType, ['image/jpeg', 'image/png', 'image/jpg'])) {
            $uploadDir = "../uploads/profile/";
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
            $dest = $uploadDir . time() . "_" . $fileName;
            if (move_uploaded_file($fileTmpPath, $dest)) {
                $profilePicUrl = $dest;
            }
        }
    }

    // Handle password change
    $newPassword = $_POST['newPassword'] ?? '';
    $confirmPassword = $_POST['confirmPassword'] ?? '';
    $passwordSQL = "";
    if (!empty($newPassword)) {
        if ($newPassword === $confirmPassword) {
            $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);
            $passwordSQL = ", password = '$hashedPassword'";
        } else {
            $errorMessage = "Passwords do not match!";
        }
    }

    if (empty($errorMessage)) {
        $updateUser = $conn->prepare("UPDATE Users 
            SET first_name = ?, last_name = ?, email = ?, phone = ?, profile_pic = ?, language = ? $passwordSQL 
            WHERE user_id = ?");
        $updateUser->bind_param("ssssssi", $firstName, $lastName, $email, $phone, $profilePicUrl, $language, $user_id);

        if ($updateUser->execute()) {
            $successMessage = "Settings updated successfully!";
            // Refresh user data
            $stmt = $conn->prepare($userQuery);
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $user = $stmt->get_result()->fetch_assoc();
        } else {
            $errorMessage = "Error updating settings: " . $conn->error;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Settings and Profile</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"/>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet"/>
  <style>
    .main-content { background:#f8f9fa; min-height:100vh; }
    .page-header { display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; }
    .page-header h1 { font-size:24px; font-weight:600; color:#333; }
    .settings-form { background:#fff; border-radius:8px; padding:20px; box-shadow:0 2px 5px rgba(0,0,0,.1); max-width:800px; margin:0 auto; }
    .settings-form h2 { font-size:20px; font-weight:600; color:#152259; margin-bottom:20px; }
    .form-section { margin-bottom:20px; }
    .form-section h3 { font-size:16px; font-weight:600; color:#152259; margin-bottom:15px; }
    .form-label { font-weight:500; color:#333; }
    .form-control,.form-select { border-radius:5px; border:1px solid #ced4da; }
    .form-control:focus,.form-select:focus { border-color:#509CDB; box-shadow:0 0 5px rgba(80,156,219,0.3); }
    .profile-pic-section { display:flex; align-items:center; gap:20px; margin-bottom:20px; }
    .profile-pic { width:100px; height:100px; border-radius:50%; object-fit:cover; border:2px solid #152259; }
    .btn-upload-pic { background:#509CDB; border:none; padding:8px 15px; font-size:14px; }
    .btn-upload-pic:hover { background:#408CCB; }
    .btn-save { background:#509CDB; border:none; padding:10px 20px; font-size:16px; font-weight:500; }
    .btn-save:hover { background:#408CCB; }
    .btn-delete { background:#dc3545; border:none; padding:10px 20px; font-size:16px; font-weight:500; }
    .btn-delete:hover { background:#c82333; }
  </style>
</head>
<body>
  <?php include 'sidebar.php'; ?>
  <div class="main-content">
    <div class="page-header">
      <h1>Settings and Profile</h1>
    </div>

    <?php if ($successMessage): ?>
      <div class="alert alert-success"><?= htmlspecialchars($successMessage) ?></div>
    <?php elseif ($errorMessage): ?>
      <div class="alert alert-danger"><?= htmlspecialchars($errorMessage) ?></div>
    <?php endif; ?>

    <div class="settings-form">
      <h2>Manage Your Account</h2>
      <form method="POST" enctype="multipart/form-data">
        <!-- Personal Information -->
        <div class="form-section">
          <h3>Personal Information</h3>
          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label">First Name</label>
              <input type="text" class="form-control" name="firstName" value="<?= htmlspecialchars($user['first_name'] ?? '') ?>" required>
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">Last Name</label>
              <input type="text" class="form-control" name="lastName" value="<?= htmlspecialchars($user['last_name'] ?? '') ?>" required>
            </div>
          </div>
          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label">Email</label>
              <input type="email" class="form-control" name="email" value="<?= htmlspecialchars($user['email'] ?? '') ?>" required>
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">Phone</label>
              <input type="tel" class="form-control" name="phone" value="<?= htmlspecialchars($user['phone'] ?? '') ?>">
            </div>
          </div>
        </div>

        <!-- Profile Picture -->
        <div class="form-section">
          <h3>Profile Picture</h3>
          <div class="profile-pic-section">
            <img src="<?= htmlspecialchars($user['profile_pic'] ?? 'https://via.placeholder.com/100') ?>" class="profile-pic">
            <div>
              <input type="file" class="form-control mb-2" name="profilePic" accept="image/*">
              <button type="button" class="btn btn-upload-pic" onclick="document.querySelector('input[name=profilePic]').click()">Upload New Picture</button>
            </div>
          </div>
        </div>

        <!-- Change Password -->
        <div class="form-section">
          <h3>Change Password</h3>
          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label">New Password</label>
              <input type="password" class="form-control" name="newPassword">
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">Confirm Password</label>
              <input type="password" class="form-control" name="confirmPassword">
            </div>
          </div>
        </div>

        <!-- Language -->
        <div class="form-section">
          <h3>Language Preference</h3>
          <select class="form-select" name="language">
            <option value="en" <?= ($user['language'] ?? '') === 'en' ? 'selected' : '' ?>>English</option>
            <option value="es" <?= ($user['language'] ?? '') === 'es' ? 'selected' : '' ?>>Spanish</option>
            <option value="fr" <?= ($user['language'] ?? '') === 'fr' ? 'selected' : '' ?>>French</option>
          </select>
        </div>

        <!-- Notification Settings (static for now, can hook to DB later) -->
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

        <!-- Delete Account -->
        <div class="form-section">
          <h3>Delete Account</h3>
          <p class="text-muted mb-3">Deleting your account is permanent and requires admin approval.</p>
          <button type="button" class="btn btn-delete">Request Account Deletion</button>
        </div>

        <!-- Save -->
        <div class="text-center">
          <button type="submit" class="btn btn-save">Save Changes</button>
        </div>
      </form>
    </div>
  </div>
</body>
</html>
<?php $conn->close(); ?>
