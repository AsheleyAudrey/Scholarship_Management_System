<div>
    <style>
    /* Custom styles for the sidebar */
    /* Increase specificity to override bg-light */
    div.bg-light.border-end.p-3.sidebar {
      background-color: #152259 !important; /* Deep blue background */
      color: #ffffff;
      width: 250px;
      height: 100vh;
      padding-top: 20px;
      position: fixed;
      top: 0;
      left: 0;
    }

    /* Add a header with a white circle (like the image) */
    .sidebar::before {
      content: '';
      display: block;
      width: 40px;
      height: 40px;
      background-color: #ffffff;
      border-radius: 50%;
      margin: 0 auto 20px;
    }

    /* Add a horizontal line between the circle and the first menu item (Dashboard) */
    .sidebar .nav::before {
      content: '';
      display: block;
      width: 100%; /* Extend to full width */
      height: 1px;
      background-color: #BDBDBD; /* Light gray line */
      margin: 0 auto 10px;
    }

    /* Remove the default h4 "Menu" styling */
    .sidebar h4 {
      display: none; /* Hide the "Menu" title to match the image */
    }

    /* Style the nav links */
    .sidebar .nav-link {
      color: #ffffff;
      padding: 15px 20px;
      display: flex;
      align-items: center;
      font-size: 16px;
    }

    /* Hover effect */
    .sidebar .nav-link:hover {
      background-color: #2a3b5a; /* Slightly lighter blue on hover */
    }

    /* Active state for Dashboard (first item) */
    .sidebar .nav-link:first-child {
      background-color: #509CDB; /* Updated lighter blue for active item */
    }

    /* Style the icons */
    .sidebar .nav-link i {
      margin-right: 10px;
      font-size: 20px;
    }

    /* Add a "NEW" badge to the Features item (last item) */
    .sidebar .nav-link:last-child::after {
      content: 'NEW';
      background-color: #3b82f6;
      color: #ffffff;
      font-size: 12px;
      padding: 2px 8px;
      border-radius: 10px;
      margin-left: 10px;
    }

    /* Adjust main content to account for fixed sidebar */
    .main-content {
      margin-left: 250px;
      padding: 20px;
    }
  </style>
  <div class="d-flex">
    <h4 class="mb-4">Menu</h4>
    <!-- Sidebar -->
    <div class="bg-light border-end p-3 sidebar" style="width: 250px; height: 100vh; overflow-y: auto;">
      
      <div class="nav flex-column">
        <a href="dashboard.php" class="nav-link">
          <i class="bi bi-speedometer2 me-2"></i> Dashboard
        </a>
        <a href="scholarship.php" class="nav-link">
          <i class="bi bi-house"></i> Manage Scholarships
        </a>
        <a href="application.php" class="nav-link">
          <i class="bi bi-person"></i> Application Management
        </a>
        <a href="student.php" class="nav-link">
          <i class="bi bi-people"></i> Student Management
        </a>
        <a href="document.php" class="nav-link">
          <i class="bi bi-file-earmark-text"></i> Document
        </a>
        <a href="fund.php" class="nav-link">
          <i class="bi bi-currency-dollar"></i> Fund Allocation
        </a>
        <a href="reports.php" class="nav-link">
          <i class="bi bi-bar-chart"></i> Reports & Analytics
        </a>
        <a href="detection.php" class="nav-link">
          <i class="bi bi-shield-check"></i> Fraud Detection Logs
        </a>
        <a href="notification.php" class="nav-link">
          <i class="bi bi-bell me-2"></i>System Notifications
        </a>
        <a href="settings.php" class="nav-link">
          <i class="bi bi-person-circle me-2"></i> Settings and Profile
        </a>
        <a href="#" class="nav-link">
          <i class="bi bi-stars me-2"></i> Features
        </a>
      </div>
      <div>
        <a href="/Scholarship_Management_System/login.php" class="btn btn-danger mt-3 w-100">Logout</a>
      </div>
    </div>
  </div>
 