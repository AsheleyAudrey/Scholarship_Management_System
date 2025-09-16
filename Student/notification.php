<?php
include "../Database/db.php";
session_start();

// Simulated logged-in user (replace with $_SESSION['user_id'])
$user_id = 4;

// Handle actions: Mark as Read / Delete
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['mark_read'])) {
        $notif_id = intval($_POST['mark_read']);
        $stmt = $conn->prepare("UPDATE Notifications SET status = 'Read' WHERE notification_id = ? AND user_id = ?");
        $stmt->bind_param("ii", $notif_id, $user_id);
        $stmt->execute();
    }

    if (isset($_POST['delete'])) {
        $notif_id = intval($_POST['delete']);
        $stmt = $conn->prepare("DELETE FROM Notifications WHERE notification_id = ? AND user_id = ?");
        $stmt->bind_param("ii", $notif_id, $user_id);
        $stmt->execute();
    }
}

// Fetch notifications
$stmt = $conn->prepare("SELECT * FROM Notifications WHERE user_id = ? ORDER BY date_created DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$notifications = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Notifications</title>

  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"/>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet"/>

  <style>
    .main-content { background-color:#f8f9fa; min-height:100vh; padding:20px; }
    .page-header { display:flex; justify-content:space-between; align-items:center; margin-bottom:25px; }
    .page-header h1 { font-size:26px; font-weight:600; color:#333; }

    .notifications-section { background:#fff; border-radius:10px; padding:25px; box-shadow:0 2px 8px rgba(0,0,0,.1); max-width:900px; margin:0 auto; }
    .notifications-section h3 { font-size:20px; font-weight:600; color:#152259; margin-bottom:20px; }

    .notification-item { display:flex; justify-content:space-between; align-items:center; padding:20px; border-bottom:1px solid #eee; transition:background-color .2s; }
    .notification-item:last-child { border-bottom:none; }
    .notification-item.unread { background:#f1f8ff; font-weight:500; }

    .notification-content { display:flex; align-items:center; flex:1; }
    .notification-content i { font-size:24px; margin-right:18px; color:#509CDB; }
    .notification-text { flex:1; }
    .notification-text p { margin:0; font-size:15px; color:#333; }
    .notification-text .date { font-size:13px; color:#666; margin-top:4px; }

    .notification-actions { display:flex; gap:12px; }
    .notification-actions button { font-size:15px; padding:6px 12px; border:none; border-radius:4px; }
    .btn-read { background:#28a745; color:#fff; }
    .btn-read:hover { background:#218838; }
    .btn-delete { background:#dc3545; color:#fff; }
    .btn-delete:hover { background:#c82333; }
  </style>
</head>
<body>
  <?php include 'sidebar.php'; ?>
  
  <div class="main-content">
    <div class="page-header">
      <h1>Notifications</h1>
    </div>

    <div class="notifications-section">
      <h3>Your Notifications</h3>
      
      <?php if (empty($notifications)): ?>
        <p class="text-muted">No notifications available.</p>
      <?php else: ?>
        <?php foreach ($notifications as $notif): ?>
          <div class="notification-item <?= $notif['status'] === 'Unread' ? 'unread' : '' ?>">
            <div class="notification-content">
              <i class="bi bi-bell"></i>
              <div class="notification-text">
                <p><?= htmlspecialchars($notif['message']) ?></p>
                <div class="date"><?= htmlspecialchars($notif['date_created']) ?></div>
              </div>
            </div>
            <div class="notification-actions">
              <form method="POST" class="d-inline">
                <?php if ($notif['status'] === 'Unread'): ?>
                  <button type="submit" name="mark_read" value="<?= $notif['notification_id'] ?>" class="btn btn-read">Mark as Read</button>
                <?php endif; ?>
                <button type="submit" name="delete" value="<?= $notif['notification_id'] ?>" class="btn btn-delete">Delete</button>
              </form>
            </div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php $conn->close(); ?>
