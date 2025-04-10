<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Notifications</title>
  
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

    .page-header .btn-mark-all {
      background-color: #509CDB; /* Match active item color */
      border: none;
      font-size: 14px;
      padding: 5px 15px;
    }

    .page-header .btn-mark-all:hover {
      background-color: #408CCB;
    }

    /* Notifications Section */
    .notifications-section {
      background-color: #ffffff;
      border-radius: 8px;
      padding: 20px;
      box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
      margin-bottom: 20px;
    }

    .notifications-section .list-group-item {
      border: none;
      padding: 15px 0;
      display: flex;
      justify-content: space-between;
      align-items: center;
      border-bottom: 1px solid #dee2e6;
      transition: background-color 0.2s;
    }

    .notifications-section .list-group-item:last-child {
      border-bottom: none;
    }

    .notifications-section .list-group-item.unread {
      background-color: #e9f5ff; /* Light blue for unread notifications */
    }

    .notifications-section .list-group-item .notification-content {
      display: flex;
      align-items: center;
      gap: 15px;
    }

    .notifications-section .list-group-item i {
      font-size: 20px;
      color: #509CDB; /* Match active item color */
    }

    .notifications-section .list-group-item .notification-text {
      font-size: 16px;
      color: #333;
    }

    .notifications-section .list-group-item .notification-timestamp {
      font-size: 14px;
      color: #666;
    }

    .notifications-section .list-group-item .btn-mark-read {
      background-color: #28a745; /* Green for mark as read */
      border: none;
      font-size: 14px;
      padding: 5px 10px;
    }

    .notifications-section .list-group-item .btn-mark-read:hover {
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
        <h1>Notifications</h1>
        <button class="btn btn-mark-all" onclick="markAllAsRead()">Mark All as Read</button>
      </div>

      <!-- Notifications Section -->
      <div class="notifications-section">
        <ul class="list-group" id="notificationList">
          <li class="list-group-item unread" data-id="1">
            <div class="notification-content">
              <i class="bi bi-file-earmark-plus"></i>
              <span class="notification-text">New application assigned: A005</span>
            </div>
            <div class="d-flex align-items-center gap-2">
              <span class="notification-timestamp">2025-04-10 09:00</span>
              <button class="btn btn-mark-read" onclick="markAsRead(this)">Mark as Read</button>
            </div>
          </li>
          <li class="list-group-item unread" data-id="2">
            <div class="notification-content">
              <i class="bi bi-exclamation-triangle"></i>
              <span class="notification-text">Deadline approaching for A004 review: Due 2025-04-12</span>
            </div>
            <div class="d-flex align-items-center gap-2">
              <span class="notification-timestamp">2025-04-09 15:30</span>
              <button class="btn btn-mark-read" onclick="markAsRead(this)">Mark as Read</button>
            </div>
          </li>
          <li class="list-group-item unread" data-id="3">
            <div class="notification-content">
              <i class="bi bi-info-circle"></i>
              <span class="notification-text">System Update: Review rubric updated by admin</span>
            </div>
            <div class="d-flex align-items-center gap-2">
              <span class="notification-timestamp">2025-04-09 10:00</span>
              <button class="btn btn-mark-read" onclick="markAsRead(this)">Mark as Read</button>
            </div>
          </li>
          <li class="list-group-item" data-id="4">
            <div class="notification-content">
              <i class="bi bi-file-earmark-plus"></i>
              <span class="notification-text">New application assigned: A003</span>
            </div>
            <div class="d-flex align-items-center gap-2">
              <span class="notification-timestamp">2025-04-08 14:00</span>
            </div>
          </li>
        </ul>
      </div>
    </div>
  </div>

  <!-- Bootstrap JS -->
  <script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
  ></script>
</body>
</html>