<?php 
include "../Database/db.php";

$conn = new mysqli($servername, $username, $password, $dbname, $port);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Verify database selection
if (!$conn->select_db($dbname)) {
    die("Database not found: $dbname");
}

// Check if Scholarships table exists
if ($conn->query("SHOW TABLES LIKE 'Scholarships'")->num_rows == 0) {
    die("Scholarships table not found in database: $dbname");
}

// Handle filters and search
$typeFilter = isset($_GET['typeFilter']) ? $_GET['typeFilter'] : '';
$deadlineFilter = isset($_GET['deadlineFilter']) ? $_GET['deadlineFilter'] : '';
$searchQuery = isset($_GET['search']) ? trim($_GET['search']) : '';

// Build the SQL query with filters
$query = "SELECT scholarship_id, name, amount, gpa, other_criteria, application_end
          FROM Scholarships
          WHERE status = 'Open' AND application_end >= CURDATE()";

// Apply type filter
$typeConditions = [];
if ($typeFilter === 'merit') {
    $typeConditions[] = "other_criteria LIKE '%Leadership%'";
} elseif ($typeFilter === 'need') {
    $typeConditions[] = "other_criteria LIKE '%Financial Need%'";
} elseif ($typeFilter === 'stem') {
    $typeConditions[] = "other_criteria LIKE '%STEM%'";
}
if (!empty($typeConditions)) {
    $query .= " AND (" . implode(' OR ', $typeConditions) . ")";
}

// Apply deadline filter
if ($deadlineFilter === 'upcoming') {
    $query .= " AND application_end <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)";
} elseif ($deadlineFilter === 'next3months') {
    $query .= " AND application_end <= DATE_ADD(CURDATE(), INTERVAL 90 DAY)";
} elseif ($deadlineFilter === 'later') {
    $query .= " AND application_end > DATE_ADD(CURDATE(), INTERVAL 90 DAY)";
}

// Apply search filter
if ($searchQuery !== '') {
    $searchQuery = $conn->real_escape_string($searchQuery);
    $query .= " AND name LIKE '%$searchQuery%'";
}

$query .= " ORDER BY application_end ASC";
$result = $conn->query($query);
if (!$result) {
    die("Query failed: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Available Scholarships</title>
  
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <!-- Bootstrap Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />
  <style>
    .main-content {
      background-color: #f8f9fa;
      min-height: 100vh;
    }
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
      border-color: #509CDB;
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
      background-color: #509CDB;
      color: #ffffff;
      border: none;
      border-radius: 0 8px 8px 0;
    }
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
      color: #152259;
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
      color: #509CDB;
      margin-bottom: 5px;
    }
    .scholarship-card .deadline {
      font-size: 14px;
      color: #dc3545;
      margin-bottom: 5px;
    }
    .scholarship-card .description {
      font-size: 14px;
      color: #666;
      margin-bottom: 10px;
    }
    .scholarship-card .btn-view {
      background-color: #6c757d;
      border: none;
      padding: 8px 15px;
      font-size: 14px;
      margin-right: 10px;
    }
    .scholarship-card .btn-view:hover {
      background-color: #5a6268;
    }
    .scholarship-card .btn-apply {
      background-color: #509CDB;
      border: none;
      padding: 8px 15px;
      font-size: 14px;
    }
    .scholarship-card .btn-apply:hover {
      background-color: #408CCB;
    }
  </style>
</head>
<body>
  <?php include 'sidebar.php'; ?>

  <!-- Main content -->
  <div class="main-content">
    <!-- Header -->
    <div class="page-header">
      <h1>Available Scholarships</h1>
    </div>

    <!-- Filters and Search Bar -->
    <div class="filters-search">
      <div class="filters">
        <select class="form-select" id="typeFilter" onchange="applyFilters()">
          <option value="">All Types</option>
          <option value="merit" <?php echo $typeFilter === 'merit' ? 'selected' : ''; ?>>Merit-Based</option>
          <option value="need" <?php echo $typeFilter === 'need' ? 'selected' : ''; ?>>Need-Based</option>
          <option value="stem" <?php echo $typeFilter === 'stem' ? 'selected' : ''; ?>>STEM</option>
        </select>
        <select class="form-select" id="deadlineFilter" onchange="applyFilters()">
          <option value="">All Deadlines</option>
          <option value="upcoming" <?php echo $deadlineFilter === 'upcoming' ? 'selected' : ''; ?>>Upcoming (Next 30 Days)</option>
          <option value="next3months" <?php echo $deadlineFilter === 'next3months' ? 'selected' : ''; ?>>Next 3 Months</option>
          <option value="later" <?php echo $deadlineFilter === 'later' ? 'selected' : ''; ?>>Later</option>
        </select>
      </div>
      <div class="search-bar">
        <div class="input-group">
          <input type="text" class="form-control" id="searchInput" placeholder="Search scholarships..." value="<?php echo htmlspecialchars($searchQuery); ?>">
          <button class="btn" type="button" onclick="applyFilters()"><i class="bi bi-search"></i></button>
        </div>
      </div>
    </div>

    <!-- Scholarship Cards -->
    <div class="row">
      <?php if ($result->num_rows > 0): ?>
        <?php while ($row = $result->fetch_assoc()): ?>
          <div class="col-md-6 col-lg-4">
            <div class="scholarship-card">
              <h3><?php echo htmlspecialchars($row['name']); ?></h3>
              <div class="provider">Scholarship Provider</div>
              <div class="amount">$<?php echo number_format($row['amount'], 2); ?></div>
              <div class="deadline">Deadline: <?php echo htmlspecialchars($row['application_end']); ?></div>
              <div class="description">
                <?php
                $description = [];
                if ($row['gpa']) {
                    $description[] = "Requires a GPA of " . number_format($row['gpa'], 1) . " or higher.";
                }
                if ($row['other_criteria']) {
                    $description[] = htmlspecialchars($row['other_criteria']);
                }
                echo implode(' ', $description);
                ?>
              </div>
              <div>
                <a href="#" class="btn btn-view">View Details</a>
                <a href="apply_now.php?scholarship_id=<?php echo $row['scholarship_id']; ?>" class="btn btn-apply">Apply</a>
              </div>
            </div>
          </div>
        <?php endwhile; ?>
      <?php else: ?>
        <div class="col-12">
          <p>No scholarships found matching your criteria.</p>
        </div>
      <?php endif; ?>
    </div>
  </div>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    function applyFilters() {
      const typeFilter = document.getElementById('typeFilter').value;
      const deadlineFilter = document.getElementById('deadlineFilter').value;
      const searchQuery = document.getElementById('searchInput').value;

      const params = new URLSearchParams();
      if (typeFilter) params.set('typeFilter', typeFilter);
      if (deadlineFilter) params.set('deadlineFilter', deadlineFilter);
      if (searchQuery) params.set('search', searchQuery);

      window.location.href = 'available_scholarship.php?' + params.toString();
    }
  </script>
</body>
</html>

<?php
// Close database connection
$conn->close();
?>