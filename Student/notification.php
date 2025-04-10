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

    /* Notifications section */
    .notifications-section {
      background-color: #ffffff;
      border-radius: 8px;
      padding: 20px;
      box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    }

    .notifications-section h3 {
      font-size: 18px;
      font-weight: 600;
      color: #152259; /* Match sidebar color */
      margin-bottom: 15px;
    }

    .notification-item {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 15px;
      border-bottom: 1px solid #eee;
      transition: background-color 0.2s;
    }

    .notification-item:last-child {
      border-bottom: none;
    }

    .notification-item.unread {
      background-color: #f1f8ff; /* Light blue for unread notifications */
      font-weight: 500;
    }

    .notification-content {
      display: flex;
      align-items: center;
      flex: 1;
    }

    .notification-content i {
      font-size: 20px;
      margin-right: 15px;
      color: #509CDB; /* Match active item color */
    }

    .notification-text {
      flex: 1;
    }

    .notification-text p {
      margin: 0;
      font-size: 14px;
      color: #333;
    }

    .notification-text .date {
      font-size: 12px;
      color: #666;
    }

    .notification-actions {
      display: flex;
      gap: 10px;
    }

    .notification-actions .btn {
      font-size: 14px;
      padding: 5px 10px;
    }

    .notification-actions .btn-read {
      background-color: #28a745; /* Green for mark as read */
      border: none;
    }

    .notification-actions .btn-read:hover {
      background-color: #218838;
    }

    .notification-actions .btn-delete {
      background-color: #dc3545; /* Red for delete */
      border: none;
    }

    .notification-actions .btn-delete:hover {
      background-color: #c82333;
    }
  </style>
</head>
<body>

<?php include 'sidebar.php'; ?>
    <!-- Main content -->
    <div class="main-content">
      <!-- Header -->
      <div class="page-header">
        <h1>Notifications</h1>
      </div>

      <!-- Notifications Section -->
      <div class="notifications-section">
        <h3>Notifications</h3>
        <div class="notification-item unread">
          <div class="notification-content">
            <i class="bi bi-bell"></i>
            <div class="notification-text">
              <p>Your application for the Merit-Based Scholarship was reviewed.</p>
              <div class="date">2025-04-07</div>
            </div>
          </div>
          <div class="notification-actions">
            <button class="btn btn-read" onclick="markAsRead(this)">Mark as Read</button>
            <button class="btn btn-delete" onclick="deleteNotification(this)">Delete</button>
          </div>
        </div>
        <div class="notification-item unread">
          <div class="notification-content">
            <i class="bi bi-award"></i>
            <div class="notification-text">
              <p>New scholarship alert: STEM Scholarship is now available!</p>
              <div class="date">2025-04-06</div>
            </div>
          </div>
          <div class="notification-actions">
            <button class="btn btn-read" onclick="markAsRead(this)">Mark as Read</button>
            <button class="btn btn-delete" onclick="deleteNotification(this)">Delete</button>
          </div>
        </div>
        <div class="notification-item">
          <div class="notification-content">
            <i class="bi bi-exclamation-circle"></i>
            <div class="notification-text">
              <p>Missing document: Please upload your Financial Statement for the Need-Based Scholarship application.</p>
              <div class="date">2025-04-05</div>
            </div>
          </div>
          <div class="notification-actions">
            <button class="btn btn-delete" onclick="deleteNotification(this)">Delete</button>
          </div>
        </div>
        <div class="notification-item">
          <div class="notification-content">
            <i class="bi bi-calendar-event"></i>
            <div class="notification-text">
              <p>Upcoming deadline: STEM Scholarship application due on 2025-06-30.</p>
              <div class="date">2025-04-04</div>
            </div>
          </div>
          <div class="notification-actions">
            <button class="btn btn-delete" onclick="deleteNotification(this)">Delete</button>
          </div>
        </div>
        <div class="notification-item">
          <div class="notification-content">
            <i class="bi bi-info-circle"></i>
            <div class="notification-text">
              <p>System message: Scheduled maintenance on 2025-04-10 from 2:00 AM to 4:00 AM.</p>
              <div class="date">2025-04-03</div>
            </div>
          </div>
          <div class="notification-actions">
            <button class="btn btn-delete" onclick="deleteNotification(this)">Delete</button>
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