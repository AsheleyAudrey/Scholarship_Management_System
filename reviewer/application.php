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

// Check if necessary tables exist
$tablesToCheck = ['Applications', 'Students', 'Scholarships', 'Reviews', 'ReviewCommittee'];
foreach ($tablesToCheck as $table) {
    if ($conn->query("SHOW TABLES LIKE '$table'")->num_rows == 0) {
        die("$table table not found in database: $dbname");
    }
}

// Assume logged-in reviewer (Richlove Kin, user_id: 2, reviewer_id: 1)
$user_id = 2; // This should ideally come from $_SESSION['user_id']
$reviewerQuery = "SELECT reviewer_id FROM ReviewCommittee WHERE user_id = ?";
$stmt = $conn->prepare($reviewerQuery);
if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}
$stmt->bind_param("i", $user_id);
if (!$stmt->execute()) {
    die("Execute failed: " . $stmt->error);
}
$reviewerResult = $stmt->get_result();
$reviewer = $reviewerResult->num_rows > 0 ? $reviewerResult->fetch_assoc() : null;
if (!$reviewer) {
    die("Reviewer not found for user_id: $user_id");
}
$reviewer_id = $reviewer['reviewer_id'];

// Handle filters, search, and pagination
$scholarshipFilter = isset($_GET['scholarshipFilter']) ? trim($_GET['scholarshipFilter']) : '';
$submissionDateFilter = isset($_GET['submissionDateFilter']) ? trim($_GET['submissionDateFilter']) : '';
$searchQuery = isset($_GET['search']) ? trim($_GET['search']) : '';
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$perPage = 5; // Number of applications per page
$offset = ($page - 1) * $perPage;

// Build the SQL query for applications assigned to the reviewer
$query = "SELECT a.application_id, CONCAT(s.first_name, ' ', s.last_name) AS student_name, 
                 sc.name AS scholarship_name, a.submission_date, 
                 r.decision AS review_status
          FROM Applications a
          JOIN Students s ON a.student_id = s.student_id
          JOIN Scholarships sc ON a.scholarship_id = sc.scholarship_id
          LEFT JOIN Reviews r ON a.application_id = r.application_id AND r.reviewer_id = ?
          WHERE 1=1";

// Add reviewer_id condition (simulating assignment; in a real system, you'd have an assignment table)
$query .= " AND (r.reviewer_id = ? OR r.reviewer_id IS NULL)";

// Apply scholarship filter
if ($scholarshipFilter !== '') {
    $scholarshipFilter = $conn->real_escape_string($scholarshipFilter);
    $query .= " AND sc.name = '$scholarshipFilter'";
}

// Apply submission date filter
if ($submissionDateFilter !== '') {
    $submissionDateFilter = $conn->real_escape_string($submissionDateFilter);
    $query .= " AND DATE(a.submission_date) = '$submissionDateFilter'";
}

// Apply search filter
if ($searchQuery !== '') {
    $searchQuery = $conn->real_escape_string($searchQuery);
    $query .= " AND (a.application_id LIKE '%$searchQuery%' 
                     OR s.first_name LIKE '%$searchQuery%' 
                     OR s.last_name LIKE '%$searchQuery%')";
}

// Get total count for pagination
$countQuery = "SELECT COUNT(*) AS total 
               FROM Applications a
               JOIN Students s ON a.student_id = s.student_id
               JOIN Scholarships sc ON a.scholarship_id = sc.scholarship_id
               LEFT JOIN Reviews r ON a.application_id = r.application_id AND r.reviewer_id = ?
               WHERE 1=1";
if ($scholarshipFilter !== '') {
    $countQuery .= " AND sc.name = '$scholarshipFilter'";
}
if ($submissionDateFilter !== '') {
    $countQuery .= " AND DATE(a.submission_date) = '$submissionDateFilter'";
}
if ($searchQuery !== '') {
    $countQuery .= " AND (a.application_id LIKE '%$searchQuery%' 
                         OR s.first_name LIKE '%$searchQuery%' 
                         OR s.last_name LIKE '%$searchQuery%')";
}
$countStmt = $conn->prepare($countQuery);
$countStmt->bind_param("i", $reviewer_id);
$countStmt->execute();
$totalApplications = $countStmt->get_result()->fetch_assoc()['total'];
$countStmt->close();
$totalPages = ceil($totalApplications / $perPage);

// Fetch applications with pagination
$query .= " ORDER BY a.submission_date DESC LIMIT ? OFFSET ?";
$stmt = $conn->prepare($query);
if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}
$stmt->bind_param("iiii", $reviewer_id, $reviewer_id, $perPage, $offset);
if (!$stmt->execute()) {
    die("Execute failed: " . $stmt->error);
}
$result = $stmt->get_result();

// Fetch all scholarship names for the filter dropdown
$scholarshipQuery = "SELECT DISTINCT name 
                    FROM Scholarships 
                    WHERE scholarship_id IN (
                        SELECT scholarship_id 
                        FROM Applications
                    )";
$scholarshipResult = $conn->query($scholarshipQuery);
if (!$scholarshipResult) {
    die("Scholarship query failed: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Applications Assigned</title>
  
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
    .filters {
      display: flex;
      gap: 15px;
      margin-bottom: 20px;
      flex-wrap: wrap;
    }
    .filters .form-select,
    .filters .form-control {
      border-radius: 5px;
      border: 1px solid #ced4da;
      box-shadow: none;
      max-width: 200px;
    }
    .filters .form-select:focus,
    .filters .form-control:focus {
      border-color: #509CDB;
      box-shadow: 0 0 5px rgba(80, 156, 219, 0.3);
    }
    .applications-table {
      background-color: #ffffff;
      border-radius: 8px;
      padding: 20px;
      box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
      margin-bottom: 20px;
    }
    .applications-table .table {
      margin-bottom: 0;
    }
    .applications-table .table th {
      background-color: #152259;
      color: #ffffff;
    }
    .applications-table .table td {
      vertical-align: middle;
    }
    .applications-table .table .badge {
      font-size: 12px;
    }
    .applications-table .btn-start-review {
      background-color: #509CDB;
      border: none;
      font-size: 14px;
      padding: 5px 10px;
    }
    .applications-table .btn-start-review:hover {
      background-color: #408CCB;
    }
    .applications-table .btn-start-review:disabled {
      background-color: #ced4da;
      cursor: not-allowed;
    }
    .pagination {
      justify-content: center;
    }
    .pagination .page-link {
      color: #509CDB;
    }
    .pagination .page-link:hover {
      background-color: #509CDB;
      color: #ffffff;
    }
    .pagination .page-item.active .page-link {
      background-color: #509CDB;
      border-color: #509CDB;
      color: #ffffff;
    }
    .modal-header {
      background-color: #152259;
      color: #ffffff;
      border-bottom: none;
    }
    .modal-footer .btn-primary {
      background-color: #509CDB;
      border: none;
    }
    .modal-footer .btn-primary:hover {
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
      <h1>Applications Assigned</h1>
    </div>

    <!-- Filters and Search -->
    <div class="filters">
      <select class="form-select" id="scholarshipFilter" onchange="applyFilters()">
        <option value="">All Scholarships</option>
        <?php while ($row = $scholarshipResult->fetch_assoc()): ?>
          <option value="<?php echo htmlspecialchars($row['name']); ?>" <?php echo $scholarshipFilter === $row['name'] ? 'selected' : ''; ?>>
            <?php echo htmlspecialchars($row['name']); ?>
          </option>
        <?php endwhile; ?>
      </select>
      <input type="date" class="form-control" id="submissionDateFilter" onchange="applyFilters()" value="<?php echo htmlspecialchars($submissionDateFilter); ?>" placeholder="Filter by Submission Date">
      <input type="text" class="form-control" id="searchInput" onkeyup="applyFilters()" placeholder="Search by Applicant ID or Name" value="<?php echo htmlspecialchars($searchQuery); ?>">
    </div>

    <!-- Applications Table -->
    <div class="applications-table">
      <table class="table table-hover">
        <thead>
          <tr>
            <th>Application ID</th>
            <th>Student Name</th>
            <th>Scholarship Name</th>
            <th>Submission Date</th>
            <th>Review Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody id="applicationsTable">
          <?php if ($result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
              <?php
              // Determine review status
              $reviewStatus = 'Not Started';
              $badgeClass = 'bg-secondary';
              $buttonText = 'Start Review';
              $buttonDisabled = false;

              if ($row['review_status']) {
                if ($row['review_status'] === 'Pending') {
                  $reviewStatus = 'In Review';
                  $badgeClass = 'bg-warning';
                  $buttonText = 'Continue Review';
                } elseif (in_array($row['review_status'], ['Approved', 'Rejected', 'Needs More Info'])) {
                  $reviewStatus = 'Completed';
                  $badgeClass = 'bg-success';
                  $buttonText = 'Review Completed';
                  $buttonDisabled = true;
                }
              }
              ?>
              <tr>
                <td><?php echo htmlspecialchars($row['application_id']); ?></td>
                <td><?php echo htmlspecialchars($row['student_name']); ?></td>
                <td><?php echo htmlspecialchars($row['scholarship_name']); ?></td>
                <td><?php echo htmlspecialchars(date('Y-m-d', strtotime($row['submission_date']))); ?></td>
                <td><span class="badge <?php echo $badgeClass; ?>"><?php echo $reviewStatus; ?></span></td>
                <td>
                  <button class="btn btn-start-review" 
                          data-bs-toggle="modal" 
                          data-bs-target="#confirmReviewModal" 
                          data-application-id="<?php echo $row['application_id']; ?>" 
                          data-action="<?php echo $buttonText; ?>" 
                          <?php echo $buttonDisabled ? 'disabled' : ''; ?>>
                    <?php echo $buttonText; ?>
                  </button>
                </td>
              </tr>
            <?php endwhile; ?>
          <?php else: ?>
            <tr>
              <td colspan="6" class="text-center">No applications found.</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
      <nav aria-label="Page navigation">
        <ul class="pagination" id="pagination">
          <?php if ($totalPages > 1): ?>
            <!-- Previous -->
            <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
              <a class="page-link" href="<?php echo buildPaginationUrl($page - 1, $scholarshipFilter, $submissionDateFilter, $searchQuery); ?>">Previous</a>
            </li>
            <!-- Page numbers -->
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
              <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                <a class="page-link" href="<?php echo buildPaginationUrl($i, $scholarshipFilter, $submissionDateFilter, $searchQuery); ?>"><?php echo $i; ?></a>
              </li>
            <?php endfor; ?>
            <!-- Next -->
            <li class="page-item <?php echo $page >= $totalPages ? 'disabled' : ''; ?>">
              <a class="page-link" href="<?php echo buildPaginationUrl($page + 1, $scholarshipFilter, $submissionDateFilter, $searchQuery); ?>">Next</a>
            </li>
          <?php endif; ?>
        </ul>
      </nav>
    </div>

    <!-- Confirmation Modal -->
    <div class="modal fade" id="confirmReviewModal" tabindex="-1" aria-labelledby="confirmReviewModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="confirmReviewModalLabel">Confirm Review Action</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            Are you sure you want to <span id="reviewActionText"></span> for Application ID <span id="reviewApplicationId"></span>?
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <a id="confirmReviewLink" class="btn btn-primary">Confirm</a>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    function applyFilters() {
      const scholarshipFilter = document.getElementById('scholarshipFilter').value;
      const submissionDateFilter = document.getElementById('submissionDateFilter').value;
      const searchQuery = document.getElementById('searchInput').value;

      const params = new URLSearchParams();
      if (scholarshipFilter) params.set('scholarshipFilter', scholarshipFilter);
      if (submissionDateFilter) params.set('submissionDateFilter', submissionDateFilter);
      if (searchQuery) params.set('search', searchQuery);

      window.location.href = 'application.php?' + params.toString();
    }

    // Handle modal population
    document.getElementById('confirmReviewModal').addEventListener('show.bs.modal', function (event) {
      const button = event.relatedTarget; // Button that triggered the modal
      const applicationId = button.getAttribute('data-application-id');
      const action = button.getAttribute('data-action');

      // Update modal content
      document.getElementById('reviewActionText').textContent = action.toLowerCase();
      document.getElementById('reviewApplicationId').textContent = applicationId;
      document.getElementById('confirmReviewLink').setAttribute('href', `review_application.php?application_id=${applicationId}`);
    });
  </script>
</body>
</html>

<?php
// Helper function to build pagination URLs
function buildPaginationUrl($page, $scholarshipFilter, $submissionDateFilter, $searchQuery) {
    $params = new URLSearchParams();
    $params->set('page', $page);
    if ($scholarshipFilter) $params->set('scholarshipFilter', $scholarshipFilter);
    if ($submissionDateFilter) $params->set('submissionDateFilter', $submissionDateFilter);
    if ($searchQuery) $params->set('search', $searchQuery);
    return 'application.php?' . $params->toString();
}

// Close database connection
$conn->close();
?>