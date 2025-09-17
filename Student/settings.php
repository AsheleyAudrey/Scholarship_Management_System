<?php
include "../Database/db.php";
session_start();

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch student & user info
$query = "SELECT u.username, u.role, u.approval_status, s.* 
          FROM Users u
          JOIN Students s ON u.user_id = s.user_id
          WHERE u.user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$student = $result->fetch_assoc();

$successMessage = "";
$errorMessage = "";

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstName = $_POST['firstName'] ?? '';
    $lastName  = $_POST['lastName'] ?? '';
    $email     = $_POST['email'] ?? '';
    $phone     = $_POST['phone'] ?? '';
    $dob       = $_POST['dob'] ?? null;
    $program   = $_POST['program'] ?? '';
    $gpa       = $_POST['gpa'] ?? null;

    // Update student info
    $update = $conn->prepare("UPDATE Students 
        SET first_name=?, last_name=?, email=?, phone=?, date_of_birth=?, program=?, gpa=? 
        WHERE user_id=?");
    $update->bind_param("ssssssdi", $firstName, $lastName, $email, $phone, $dob, $program, $gpa, $user_id);

    if ($update->execute()) {
        $successMessage = "Profile updated successfully!";
        // Refresh student data
        $stmt->execute();
        $student = $stmt->get_result()->fetch_assoc();
    } else {
        $errorMessage = "Error updating profile: " . $conn->error;
    }

    // Handle password change
    if (!empty($_POST['newPassword'])) {
        $newPassword = $_POST['newPassword'];
        $confirmPassword = $_POST['confirmPassword'];
        if ($newPassword === $confirmPassword) {
            $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);
            $pwUpdate = $conn->prepare("UPDATE Users SET password=? WHERE user_id=?");
            $pwUpdate->bind_param("si", $hashedPassword, $user_id);
            if ($pwUpdate->execute()) {
                $successMessage .= " Password changed!";
            }
        } else {
            $errorMessage = "Passwords do not match!";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Student Profile & Settings</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php include 'sidebar.php'; ?>
<div class="main-content">
  <h2 class="mb-3">Student Profile & Settings</h2>

  <?php if ($successMessage): ?>
    <div class="alert alert-success"><?= htmlspecialchars($successMessage) ?></div>
  <?php elseif ($errorMessage): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($errorMessage) ?></div>
  <?php endif; ?>

  <form method="POST">
    <!-- Personal Info -->
    <div class="card mb-3">
      <div class="card-header">Personal Information</div>
      <div class="card-body row g-3">
        <div class="col-md-6">
          <label class="form-label">First Name</label>
          <input type="text" name="firstName" class="form-control"
                 value="<?= htmlspecialchars($student['first_name']) ?>" required>
        </div>
        <div class="col-md-6">
          <label class="form-label">Last Name</label>
          <input type="text" name="lastName" class="form-control"
                 value="<?= htmlspecialchars($student['last_name']) ?>" required>
        </div>
        <div class="col-md-6">
          <label class="form-label">Email</label>
          <input type="email" name="email" class="form-control"
                 value="<?= htmlspecialchars($student['email']) ?>" required>
        </div>
        <div class="col-md-6">
          <label class="form-label">Phone</label>
          <input type="text" name="phone" class="form-control"
                 value="<?= htmlspecialchars($student['phone']) ?>">
        </div>
        <div class="col-md-6">
          <label class="form-label">Date of Birth</label>
          <input type="date" name="dob" class="form-control"
                 value="<?= htmlspecialchars($student['date_of_birth']) ?>">
        </div>
      </div>
    </div>

    <!-- Academic Info -->
    <div class="card mb-3">
      <div class="card-header">Academic Information</div>
      <div class="card-body row g-3">
        <div class="col-md-6">
          <label class="form-label">Program</label>
          <input type="text" name="program" class="form-control"
                 value="<?= htmlspecialchars($student['program']) ?>" required>
        </div>
        <div class="col-md-6">
          <label class="form-label">GPA</label>
          <input type="number" step="0.01" min="0" max="4" name="gpa" class="form-control"
                 value="<?= htmlspecialchars($student['gpa']) ?>">
        </div>
      </div>
    </div>

    <!-- Password Change -->
    <div class="card mb-3">
      <div class="card-header">Change Password</div>
      <div class="card-body row g-3">
        <div class="col-md-6">
          <label class="form-label">New Password</label>
          <input type="password" name="newPassword" class="form-control">
        </div>
        <div class="col-md-6">
          <label class="form-label">Confirm Password</label>
          <input type="password" name="confirmPassword" class="form-control">
        </div>
      </div>
    </div>

    <div class="text-center">
      <button type="submit" class="btn btn-primary">Save Changes</button>
    </div>
  </form>
</div>
</body>
</html>
<?php $conn->close(); ?>
