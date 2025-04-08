<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Available Scholarships</title>
  
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

    /* Filters and Search Bar */
    .filters-search {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 20px;
      flex-wrap: wrap;
      gap: 15px;
    }

    .filters {
      display: flex;
      gap: 15px;
      flex-wrap: wrap;
    }

    .filters .form-select {
      width: 200px;
      border-radius: 5px;
      border: 1px solid #ced4da;
      box-shadow: none;
    }

    .filters .form-select:focus {
      border-color: #509CDB; /* Match active item color */
      box-shadow: 0 0 5px rgba(80, 156, 219, 0.3);
    }

    .search-bar {
      max-width: 300px;
    }

    .search-bar .input-group {
      box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
      border-radius: 8px;
    }

    .search-bar .form-control {
      border: none;
      border-radius: 8px 0 0 8px;
    }

    .search-bar .btn {
      background-color: #509CDB; /* Match active item color */
      color: #ffffff;
      border: none;
      border-radius: 0 8px 8px 0;
    }

    /* Scholarship cards */
    .scholarship-card {
      background-color: #ffffff;
      border-radius: 8px;
      padding: 20px;
      box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
      margin-bottom: 20px;
      transition: transform 0.2s;
    }

    .scholarship-card:hover {
      transform: translateY(-5px);
    }

    .scholarship-card h3 {
      font-size: 18px;
      font-weight: 600;
      color: #152259; /* Match sidebar color */
      margin-bottom: 5px;
    }

    .scholarship-card .provider {
      font-size: 14px;
      color: #666;
      margin-bottom: 5px;
    }

    .scholarship-card .amount {
      font-size: 16px;
      font-weight: 600;
      color: #509CDB; /* Match active item color */
      margin-bottom: 5px;
    }

    .scholarship-card .deadline {
      font-size: 14px;
      color: #dc3545; /* Red for urgency */
      margin-bottom: 5px;
    }

    .scholarship-card .description {
      font-size: 14px;
      color: #666;
      margin-bottom: 10px;
    }

    .scholarship-card .btn-view {
      background-color: #6c757d; /* Gray for view details */
      border: none;
      padding: 8px 15px;
      font-size: 14px;
      margin-right: 10px;
    }

    .scholarship-card .btn-view:hover {
      background-color: #5a6268;
    }

    .scholarship-card .btn-apply {
      background-color: #509CDB; /* Match active item color */
      border: none;
      padding: 8px 15px;
      font-size: 14px;
    }

    .scholarship-card .btn-apply:hover {
      background-color: #408CCB; /* Slightly darker shade on hover */
    }
  </style>
</head>
<body>
    <!-- Main content -->
    <div class="main-content">
      <!-- Header -->
      <div class="page-header">
        <h1>Available Scholarships</h1>
      </div>

      <!-- Filters and Search Bar -->
      <div class="filters-search">
        <div class="filters">
          <select class="form-select" id="typeFilter">
            <option value="">All Types</option>
            <option value="merit">Merit-Based</option>
            <option value="need">Need-Based</option>
            <option value="stem">STEM</option>
          </select>
          <select class="form-select" id="locationFilter">
            <option value="">All Locations</option>
            <option value="usa">USA</option>
            <option value="canada">Canada</option>
            <option value="europe">Europe</option>
          </select>
          <select class="form-select" id="deadlineFilter">
            <option value="">All Deadlines</option>
            <option value="upcoming">Upcoming (Next 30 Days)</option>
            <option value="next3months">Next 3 Months</option>
            <option value="later">Later</option>
          </select>
        </div>
        <div class="search-bar">
          <div class="input-group">
            <input type="text" class="form-control" placeholder="Search scholarships...">
            <button class="btn" type="button"><i class="bi bi-search"></i></button>
          </div>
        </div>
      </div>

      <!-- Scholarship Cards -->
      <div class="row">
        <div class="col-md-6 col-lg-4">
          <div class="scholarship-card">
            <h3>Merit-Based Scholarship</h3>
            <div class="provider">University of Excellence</div>
            <div class="amount">$15,000</div>
            <div class="deadline">Deadline: 2025-05-15</div>
            <div class="description">For students with high academic achievement, requiring a GPA above 3.8.</div>
            <div>
              <a href="#" class="btn btn-view">View Details</a>
              <a href="#" class="btn btn-apply">Apply</a>
            </div>
          </div>
        </div>
        <div class="col-md-6 col-lg-4">
          <div class="scholarship-card">
            <h3>Need-Based Scholarship</h3>
            <div class="provider">Community Foundation</div>
            <div class="amount">$10,000</div>
            <div class="deadline">Deadline: 2025-07-01</div>
            <div class="description">For students with demonstrated financial need, family income below $50,000/year.</div>
            <div>
              <a href="#" class="btn btn-view">View Details</a>
              <a href="#" class="btn btn-apply">Apply</a>
            </div>
          </div>
        </div>
        <div class="col-md-6 col-lg-4">
          <div class="scholarship-card">
            <h3>STEM Scholarship</h3>
            <div class="provider">Tech Innovators Inc.</div>
            <div class="amount">$20,000</div>
            <div class="deadline">Deadline: 2025-06-30</div>
            <div class="description">For students pursuing STEM degrees, requiring enrollment in a STEM program and a GPA above 3.5.</div>
            <div>
              <a href="#" class="btn btn-view">View Details</a>
              <a href="#" class="btn btn-apply">Apply</a>
            </div>
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