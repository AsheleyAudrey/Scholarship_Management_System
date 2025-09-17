 <?php 
include "../Database/db.php";

$applicationQuery = "SELECT * FROM Applications";

$applicationQueryResult = $conn->query($applicationQuery);
if (!$applicationQueryResult) {
    die("Query failed: " . $conn->error);
}

$applications = [];
while ($row = $applicationQueryResult->fetch_assoc()) {
    $applications[] = $row;
}

// Applications structure
// {student id, application_id, scholarship_id, date_submitted, status, document_url}

// Students table and Scholaships table
// Get the actual student names and scholarship names
$students = [];
$studentQuery = "SELECT * FROM Students";
$studentQueryResult = $conn->query($studentQuery);
if (!$studentQueryResult) {
    die("Query failed: " . $conn->error);
}
while ($row = $studentQueryResult->fetch_assoc()) {
    $students[$row['student_id']] = $row['first_name'] . ' ' . $row['last_name'];
}
$scholarships = [];
$scholarshipQuery = "SELECT * FROM Scholarships";
$scholarshipQueryResult = $conn->query($scholarshipQuery);
if (!$scholarshipQueryResult) {
    die("Query failed: " . $conn->error);
}
while ($row = $scholarshipQueryResult->fetch_assoc()) {
    $scholarships[$row['scholarship_id']] = $row['name'];
}

// Get reviewers from Users table
$reviewers = [];
$reviewerQuery = "SELECT user_id, username FROM Users WHERE role = 'Reviewer'";
$reviewerQueryResult = $conn->query($reviewerQuery);

if ($reviewerQueryResult) {
    while ($row = $reviewerQueryResult->fetch_assoc()) {
        $reviewers[$row['user_id']] = $row['username'];
    }
}

// log reviewer array
echo "<script>console.log(" . json_encode($reviewers) . ");</script>";


?>

<?php
include "../Database/db.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

  if (isset($_POST['assign_reviewer'])) {
    $application_id = intval($_POST['application_id']);
    $reviewer_id = intval($_POST['reviewer_id']);

    $stmt = $conn->prepare("UPDATE Applications SET assigned_reviewer_id = ? WHERE application_id = ?");
    $stmt->bind_param("ii", $reviewer_id, $application_id);

    echo "<script>console.log('Assigning reviewer', $reviewer_id, 'to application', $application_id);</script>";

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => $stmt->error]);
    }
    $stmt->close();
    exit;
}

    $application_id = intval($_POST['application_id']);
    $action = $_POST['action'];

    if (!in_array($action, ['approve', 'reject'])) {
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
        exit;
    }

    $newStatus = $action === 'approve' ? 'Approved' : 'Rejected';

    $stmt = $conn->prepare("UPDATE Applications SET status = ? WHERE application_id = ?");
    $stmt->bind_param("si", $newStatus, $application_id);

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => $stmt->error]);
    }
    $stmt->close();
}



?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Application Management</title>
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

    /* Tabs styling */
    .nav-tabs .nav-link {
      color: #333;
      font-weight: 500;
    }

    .nav-tabs .nav-link.active {
      background-color: #509CDB; /* Match active item color */
      color: #ffffff;
      border-color: #509CDB;
    }

    .nav-tabs .nav-link:hover {
      border-color: #509CDB;
    }

    /* Applications Table */
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
      background-color: #152259; /* Match sidebar color */
      color: #ffffff;
    }

    .applications-table .table td {
      vertical-align: middle;
    }

    .applications-table .table .badge {
      font-size: 12px;
    }

    .applications-table .btn-approve {
      background-color: #28a745; /* Green for approve */
      border: none;
      font-size: 14px;
      padding: 5px 10px;
      margin-right: 5px;
    }

    .applications-table .btn-approve:hover {
      background-color: #218838;
    }

    .applications-table .btn-reject {
      background-color: #dc3545; /* Red for reject */
      border: none;
      font-size: 14px;
      padding: 5px 10px;
      margin-right: 5px;
    }

    .applications-table .btn-reject:hover {
      background-color: #c82333;
    }

    .applications-table .btn-download {
      background-color: #17a2b8; /* Cyan for download */
      border: none;
      font-size: 14px;
      padding: 5px 10px;
      margin-right: 5px;
    }

    .applications-table .btn-download:hover {
      background-color: #138496;
    }

    .applications-table .btn-assign {
      background-color: #509CDB; /* Match active item color */
      border: none;
      font-size: 14px;
      padding: 5px 10px;
    }

    .applications-table .btn-assign:hover {
      background-color: #408CCB;
    }

    /* Modal Form Styling */
    .modal-content {
      border-radius: 8px;
    }

    .modal-header {
      background-color: #152259; /* Match sidebar color */
      color: #ffffff;
    }

    .modal-header .btn-close {
      filter: invert(1);
    }

    .modal-body .form-label {
      font-weight: 500;
      color: #333;
    }

    .modal-body .form-select {
      border-radius: 5px;
      border: 1px solid #ced4da;
      box-shadow: none;
    }

    .modal-body .form-select:focus {
      border-color: #509CDB; /* Match active item color */
      box-shadow: 0 0 5px rgba(80, 156, 219, 0.3);
    }

    .modal-footer .btn-confirm {
      background-color: #509CDB; /* Match active item color */
      border: none;
    }

    .modal-footer .btn-confirm:hover {
      background-color: #408CCB;
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
        <h1>Application Management</h1>
      </div>

      <!-- Tabs for Filtering by Status -->
      <ul class="nav nav-tabs mb-3" id="statusTabs" role="tablist">
        <li class="nav-item" role="presentation">
          <button class="nav-link active" id="all-tab" data-bs-toggle="tab" data-bs-target="#all" type="button" role="tab" aria-controls="all" aria-selected="true">All</button>
        </li>
        <li class="nav-item" role="presentation">
          <button class="nav-link" id="pending-tab" data-bs-toggle="tab" data-bs-target="#pending" type="button" role="tab" aria-controls="pending" aria-selected="false">Pending</button>
        </li>
        <li class="nav-item" role="presentation">
          <button class="nav-link" id="approved-tab" data-bs-toggle="tab" data-bs-target="#approved" type="button" role="tab" aria-controls="approved" aria-selected="false">Approved</button>
        </li>
        <li class="nav-item" role="presentation">
          <button class="nav-link" id="rejected-tab" data-bs-toggle="tab" data-bs-target="#rejected" type="button" role="tab" aria-controls="rejected" aria-selected="false">Rejected</button>
        </li>
      </ul>

      <!-- Tab Content -->
      <div class="tab-content" id="statusTabContent">
        <!-- All Applications -->
        <div class="tab-pane fade show active" id="all" role="tabpanel" aria-labelledby="all-tab">
          <div class="applications-table">
            <table class="table table-hover">
              <thead>
                <tr>
                  <th>Application ID</th>
                  <th>Student Name</th>
                  <th>Scholarship</th>
                  <th>Date Submitted</th>
                  <th>Status</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($applications as $application): ?>
                  <tr data-status="<?php echo $application['status']; ?>">
                    <td><?php echo $application['application_id']; ?></td>
                    <td><?php echo isset($students[$application['student_id']]) ? $students[$application['student_id']] : 'Unknown Student'; ?></td>
                    <td><?php echo isset($scholarships[$application['scholarship_id']]) ? $scholarships[$application['scholarship_id']] : 'Unknown Scholarship'; ?></td>
                    <td><?php echo $application['submission_date']; ?></td>
                    <td>
                      <?php if ($application['status'] == 'Submitted'): ?>
                        <span class="badge bg-warning">Pending</span>
                      <?php elseif ($application['status'] == 'Approved'): ?>
                        <span class="badge bg-success">Approved</span>
                      <?php elseif ($application['status'] == 'Rejected'): ?>
                        <span class="badge bg-danger">Rejected</span>
                      <?php else: ?>
                        <span class="badge bg-secondary"><?php echo $application['status']; ?></span>
                      <?php endif; ?>
                    </td>
                    <td>
                      <?php if ($application['status'] == 'Submitted'): ?>
                        <button class="btn btn-approve" data-bs-toggle="modal" data-bs-target="#approveModal" onclick="setAction(this, 'approve')">Approve</button>
                        <button class="btn btn-reject" data-bs-toggle="modal" data-bs-target="#rejectModal" onclick="setAction(this, 'reject')">Reject</button>
                        <button class="btn btn-download">
                          <a href=<?php echo $application['document_url']; ?> target="_blank" style="color: white; text-decoration: none;">
                            <i class="bi bi-file-pdf"></i>
                          >Download PDF</a>
                        </button>
                        <button class="btn btn-assign" data-bs-toggle="modal" data-bs-target="#assignModal" onclick="setAssign(this)">Assign</button>
                      <?php elseif ($application['status'] == 'Approved'): ?>
                        <button class="btn btn-download" onclick="downloadPDF(this)">Download PDF</button>
                      <?php elseif ($application['status'] == 'Rejected'): ?>
                        <button class="btn btn-download" onclick="downloadPDF(this)">Download PDF</button>
                      <?php else: ?>
                        <button class="btn btn-approve" data-bs-toggle="modal" data-bs-target="#approveModal" onclick="setAction(this, 'approve')">Approve</button>
                        <button class="btn btn-reject" data-bs-toggle="modal" data-bs-target="#rejectModal" onclick="setAction(this, 'reject')">Reject</button>
                        <button class="btn btn-download" onclick="downloadPDF(this)">Download PDF</button>
                      <?php endif; ?>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Pending Applications -->
        <div class="tab-pane fade" id="pending" role="tabpanel" aria-labelledby="pending-tab">
          <div class="applications-table">
            <table class="table table-hover">
              <thead>
                <tr>
                  <th>Application ID</th>
                  <th>Student Name</th>
                  <th>Scholarship</th>
                  <th>Date Submitted</th>
                  <th>Status</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody id="pendingTable">
                <!-- Populated dynamically -->
                <?php foreach ($applications as $application): ?>
                  <?php if ($application['status'] == 'Submitted'): ?>
                    <tr>
                      <td><?php echo $application['application_id']; ?></td>
                      <td><?php echo isset($students[$application['student_id']]) ? $students[$application['student_id']] : 'Unknown Student'; ?></td>
                      <td><?php echo isset($scholarships[$application['scholarship_id']]) ? $scholarships[$application['scholarship_id']] : 'Unknown Scholarship'; ?></td>
                      <td><?php echo $application['submission_date']; ?></td>
                      <td><span class="badge bg-warning">Pending</span></td>
                      <td>
                        <button class="btn btn-approve" data-bs-toggle="modal" data-bs-target="#approveModal" onclick="setAction(this, 'approve')">Approve</button>
                        <button class="btn btn-reject" data-bs-toggle="modal" data-bs-target="#rejectModal" onclick="setAction(this, 'reject')">Reject</button>
                        <button class="btn btn-download">
                          <a href=<?php echo $application['document_url']; ?> target="_blank" style="color: white; text-decoration: none;">
                            <i class="bi bi-file-pdf"></i>
                          Download PDF</a>
                        </button>
                        <button class="btn btn-assign" data-bs-toggle="modal" data-bs-target="#assignModal" onclick="setAssign(this)">Assign</button>
                      </td>
                    </tr> 
                  <?php endif; ?>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Approved Applications -->
        <div class="tab-pane fade" id="approved" role="tabpanel" aria-labelledby="approved-tab">
          <div class="applications-table">
            <table class="table table-hover">
              <thead>
                <tr>
                  <th>Application ID</th>
                  <th>Student Name</th>
                  <th>Scholarship</th>
                  <th>Date Submitted</th>
                  <th>Status</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody id="approvedTable">
                <!-- Populated dynamically -->
                <?php foreach ($applications as $application): ?>
                  <?php if ($application['status'] == 'Approved'): ?>
                    <tr>
                      <td><?php echo $application['application_id']; ?></td>
                      <td><?php echo isset($students[$application['student_id']]) ? $students[$application['student_id']] : 'Unknown Student'; ?></td>
                      <td><?php echo isset($scholarships[$application['scholarship_id']]) ? $scholarships[$application['scholarship_id']] : 'Unknown Scholarship'; ?></td>
                      <td><?php echo $application['submission_date']; ?></td>
                      <td><span class="badge bg-success">Approved</span></td>
                      <td>
                        <button class="btn btn-download" onclick="downloadPDF(this)">Download PDF</button>
                      </td>
                    </tr>
                  <?php endif; ?>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Rejected Applications -->
        <div class="tab-pane fade" id="rejected" role="tabpanel" aria-labelledby="rejected-tab">
          <div class="applications-table">
            <table class="table table-hover">
              <thead>
                <tr>
                  <th>Application ID</th>
                  <th>Student Name</th>
                  <th>Scholarship</th>
                  <th>Date Submitted</th>
                  <th>Status</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody id="rejectedTable">
                <!-- Populated dynamically -->
                <?php foreach ($applications as $application): ?>
                  <?php if ($application['status'] == 'Rejected'): ?>
                    <tr>
                      <td><?php echo $application['application_id']; ?></td>
                      <td><?php echo isset($students[$application['student_id']]) ? $students[$application['student_id']] : 'Unknown Student'; ?></td>
                      <td><?php echo isset($scholarships[$application['scholarship_id']]) ? $scholarships[$application['scholarship_id']] : 'Unknown Scholarship'; ?></td>
                      <td><?php echo $application['submission_date']; ?></td>
                      <td><span class="badge bg-danger">Rejected</span></td>
                      <td>
                        <button class="btn btn-download" onclick="downloadPDF(this)">Download PDF</button>
                      </td>
                    </tr>
                  <?php endif; ?>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Approve Confirmation Modal -->
  <div class="modal fade" id="approveModal" tabindex="-1" aria-labelledby="approveModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="approveModalLabel">Confirm Approval</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          Are you sure you want to approve this application?
          <p><strong>Application ID:</strong> <span id="approveAppId"></span></p>
          <p><strong>Student:</strong> <span id="approveStudent"></span></p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="button" class="btn btn-confirm" onclick="confirmAction('approve')">Confirm</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Reject Confirmation Modal -->
  <div class="modal fade" id="rejectModal" tabindex="-1" aria-labelledby="rejectModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="rejectModalLabel">Confirm Rejection</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          Are you sure you want to reject this application?
          <p><strong>Application ID:</strong> <span id="rejectAppId"></span></p>
          <p><strong>Student:</strong> <span id="rejectStudent"></span></p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="button" class="btn btn-confirm" onclick="confirmAction('reject')">Confirm</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Assign to Review Committee Modal -->
  <div class="modal fade" id="assignModal" tabindex="-1" aria-labelledby="assignModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="assignModalLabel">Assign to Review Committee</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <p><strong>Application ID:</strong> <span id="assignAppId"></span></p>
          <p><strong>Student:</strong> <span id="assignStudent"></span></p>
          <div class="mb-3">
            <label for="reviewer" class="form-label">Select Reviewer</label>
            <select class="form-select" id="reviewer" required>
              <option value="" disabled selected>Select a reviewer</option>
              <?php foreach ($reviewers as $id => $name): ?>
                <option value="<?php echo $id; ?>"><?php echo htmlspecialchars($name); ?></option>
              <?php endforeach; ?>
            </select>

          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="button" class="btn btn-confirm" onclick="assignReviewer()">Assign</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Bootstrap JS -->
  <script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
  ></script>

  <script>
let selectedAppId = null;
let selectedStudent = null;

// When Approve/Reject buttons are clicked
function setAction(button, action) {
  // Get row (closest tr)
  const row = button.closest("tr");
  const appId = row.cells[0].innerText;   // Application ID is first column
  const student = row.cells[1].innerText; // Student Name is second column

  selectedAppId = appId;
  selectedStudent = student;

  if (action === "approve") {
    document.getElementById("approveAppId").innerText = appId;
    document.getElementById("approveStudent").innerText = student;
  } else if (action === "reject") {
    document.getElementById("rejectAppId").innerText = appId;
    document.getElementById("rejectStudent").innerText = student;
  }
}

// Confirm approve/reject
function confirmAction(action) {
  if (!selectedAppId) {
    alert("No application selected!");
    return;
  }

  // Send AJAX request to update status
  fetch("", {
    method: "POST",
    headers: { "Content-Type": "application/x-www-form-urlencoded" },
    body: `application_id=${selectedAppId}&action=${action}`
  })
  .then(res => res.json())
  .then(data => {
    if (data.success) {
      alert("Application " + action + "d successfully!");
      location.reload(); 
    } else {
      alert("Error: " + data.message);
    }
  })
  .catch(err => {
    console.error(err);
  });
}

function setAssign(button) {
  const row = button.closest("tr");
  selectedAppId = row.cells[0].innerText;
  selectedStudent = row.cells[1].innerText;

  document.getElementById("assignAppId").innerText = selectedAppId;
  document.getElementById("assignStudent").innerText = selectedStudent;
}

function assignReviewer() {
  const reviewerId = document.getElementById("reviewer").value;
  if (!selectedAppId || !reviewerId) {
    alert("Please select a reviewer!");
    return;
  }
  fetch("", {
    method: "POST",
    headers: { "Content-Type": "application/x-www-form-urlencoded" },
    body: `application_id=${selectedAppId}&reviewer_id=${reviewerId}&assign_reviewer=1`
  })
  .then(data => {
      alert("Reviewer assigned successfully!");
      location.reload();
  })
  .catch(err => console.error(err));
}

</script>

</body>
</html>
