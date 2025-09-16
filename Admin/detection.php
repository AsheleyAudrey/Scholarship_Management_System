<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ob_start();
include "../Database/db.php";

// Check database connection
if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

// Create FraudLogs table if it doesn't exist
$sql = "CREATE TABLE IF NOT EXISTS FraudLogs (
    log_id INT PRIMARY KEY AUTO_INCREMENT,
    application_id INT DEFAULT NULL,
    user_id INT DEFAULT NULL,
    log_type ENUM('Flagged Application', 'User Activity') NOT NULL,
    reason VARCHAR(255) NOT NULL,
    details TEXT DEFAULT NULL,
    ip_address VARCHAR(45) DEFAULT NULL,
    flagged_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('Under Review', 'Cleared') DEFAULT 'Under Review',
    FOREIGN KEY (application_id) REFERENCES Applications(application_id) ON DELETE SET NULL,
    FOREIGN KEY (user_id) REFERENCES Users(user_id) ON DELETE SET NULL
)";
if (!$conn->query($sql)) {
    error_log("Failed to create FraudLogs table: " . $conn->error);
}

// Handle AJAX requests
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action'])) {
    ob_clean();
    header('Content-Type: application/json');

    // Fetch Flagged Applications
    if ($_POST['action'] == 'fetch_flagged') {
        $page = isset($_POST['page']) ? max(1, intval($_POST['page'])) : 1;
        $limit = 10;
        $offset = ($page - 1) * $limit;

        try {
            $sql = "SELECT COUNT(*) AS total FROM FraudLogs f WHERE f.log_type = 'Flagged Application'";
            $stmt = $conn->prepare($sql);
            $stmt->execute();
            $total = $stmt->get_result()->fetch_assoc()['total'];
            $total_pages = ceil($total / $limit);
            $stmt->close();

            $sql = "SELECT f.log_id, f.application_id, s.first_name, s.last_name, f.reason, f.flagged_date, f.status
                    FROM FraudLogs f
                    LEFT JOIN Applications a ON f.application_id = a.application_id
                    LEFT JOIN Students s ON a.student_id = s.student_id
                    WHERE f.log_type = 'Flagged Application'
                    ORDER BY f.flagged_date DESC
                    LIMIT ? OFFSET ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ii", $limit, $offset);
            $stmt->execute();
            $result = $stmt->get_result();
            $flagged = [];
            while ($row = $result->fetch_assoc()) {
                $flagged[] = $row;
            }
            $stmt->close();

            echo json_encode([
                'success' => true,
                'flagged' => $flagged,
                'total_pages' => $total_pages,
                'current_page' => $page
            ]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
        exit;
    }

    // Fetch User Activity Logs
    if ($_POST['action'] == 'fetch_activity') {
        $page = isset($_POST['page']) ? max(1, intval($_POST['page'])) : 1;
        $limit = 10;
        $offset = ($page - 1) * $limit;

        try {
            $sql = "SELECT COUNT(*) AS total FROM FraudLogs f WHERE f.log_type = 'User Activity'";
            $stmt = $conn->prepare($sql);
            $stmt->execute();
            $total = $stmt->get_result()->fetch_assoc()['total'];
            $total_pages = ceil($total / $limit);
            $stmt->close();

            $sql = "SELECT f.log_id, u.username, f.reason, f.details, f.ip_address, f.flagged_date
                    FROM FraudLogs f
                    LEFT JOIN Users u ON f.user_id = u.user_id
                    WHERE f.log_type = 'User Activity'
                    ORDER BY f.flagged_date DESC
                    LIMIT ? OFFSET ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ii", $limit, $offset);
            $stmt->execute();
            $result = $stmt->get_result();
            $activity = [];
            while ($row = $result->fetch_assoc()) {
                $activity[] = $row;
            }
            $stmt->close();

            echo json_encode([
                'success' => true,
                'activity' => $activity,
                'total_pages' => $total_pages,
                'current_page' => $page
            ]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
        exit;
    }

    // Clear Flag
    if ($_POST['action'] == 'clear_flag') {
        $log_id = intval($_POST['log_id']);
        try {
            $sql = "UPDATE FraudLogs SET status = 'Cleared' WHERE log_id = ? AND log_type = 'Flagged Application'";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $log_id);
            if ($stmt->execute()) {
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Database error: ' . $stmt->error]);
            }
            $stmt->close();
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
        exit;
    }

    // View Audit Trail
    if ($_POST['action'] == 'view_audit') {
        $log_id = intval($_POST['log_id']);
        try {
            $sql = "SELECT f.log_id, f.log_type, f.reason, f.details, f.ip_address, f.flagged_date, f.status,
                           a.application_id, s.first_name, s.last_name, sc.name AS scholarship_name, a.document_url,
                           u.username
                    FROM FraudLogs f
                    LEFT JOIN Applications a ON f.application_id = a.application_id
                    LEFT JOIN Students s ON a.student_id = s.student_id
                    LEFT JOIN Scholarships sc ON a.scholarship_id = sc.scholarship_id
                    LEFT JOIN Users u ON f.user_id = u.user_id
                    WHERE f.log_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $log_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $log = $result->fetch_assoc();
            $stmt->close();

            if (!$log) {
                echo json_encode(['success' => false, 'message' => 'Log not found']);
                exit;
            }

            $audit_trail = [];
            if ($log['log_type'] == 'Flagged Application') {
                $audit_trail[] = "Log ID: {$log['log_id']}";
                $audit_trail[] = "Application ID: " . ($log['application_id'] ?? 'N/A');
                $audit_trail[] = "Student: " . ($log['first_name'] && $log['last_name'] ? "{$log['first_name']} {$log['last_name']}" : 'N/A');
                $audit_trail[] = "Scholarship: " . ($log['scholarship_name'] ?? 'N/A');
                $audit_trail[] = "Reason: {$log['reason']}";
                $audit_trail[] = "Details: " . ($log['details'] ?? 'N/A');
                $audit_trail[] = "Document URL: " . ($log['document_url'] ?? 'N/A');
                $audit_trail[] = "IP Address: " . ($log['ip_address'] ?? 'N/A');
                $audit_trail[] = "Flagged Date: {$log['flagged_date']}";
                $audit_trail[] = "Status: {$log['status']}";
            } else {
                $audit_trail[] = "Log ID: {$log['log_id']}";
                $audit_trail[] = "User: " . ($log['username'] ?? 'N/A');
                $audit_trail[] = "Action: {$log['reason']}";
                $audit_trail[] = "Details: " . ($log['details'] ?? 'N/A');
                $audit_trail[] = "IP Address: " . ($log['ip_address'] ?? 'N/A');
                $audit_trail[] = "Timestamp: {$log['flagged_date']}";
            }

            echo json_encode([
                'success' => true,
                'audit_trail' => $audit_trail
            ]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
        exit;
    }

    echo json_encode(['success' => false, 'message' => 'Invalid action']);
    exit;
}

// Fetch initial data for Flagged Applications
$page_flagged = isset($_GET['page_flagged']) ? max(1, intval($_GET['page_flagged'])) : 1;
$limit = 10;
$offset_flagged = ($page_flagged - 1) * $limit;

try {
    $sql = "SELECT COUNT(*) AS total FROM FraudLogs f WHERE f.log_type = 'Flagged Application'";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $total_flagged = $stmt->get_result()->fetch_assoc()['total'];
    $total_pages_flagged = ceil($total_flagged / $limit);
    $stmt->close();

    $sql = "SELECT f.log_id, f.application_id, s.first_name, s.last_name, f.reason, f.flagged_date, f.status
            FROM FraudLogs f
            LEFT JOIN Applications a ON f.application_id = a.application_id
            LEFT JOIN Students s ON a.student_id = s.student_id
            WHERE f.log_type = 'Flagged Application'
            ORDER BY f.flagged_date DESC
            LIMIT ? OFFSET ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $limit, $offset_flagged);
    $stmt->execute();
    $result = $stmt->get_result();
    $flagged_applications = [];
    while ($row = $result->fetch_assoc()) {
        $flagged_applications[] = $row;
    }
    $stmt->close();
} catch (Exception $e) {
    $flagged_applications = [];
    $total_pages_flagged = 1;
    error_log("Error fetching flagged applications: " . $e->getMessage());
}

// Fetch initial data for User Activity Logs
$page_activity = isset($_GET['page_activity']) ? max(1, intval($_GET['page_activity'])) : 1;
$offset_activity = ($page_activity - 1) * $limit;

try {
    $sql = "SELECT COUNT(*) AS total FROM FraudLogs f WHERE f.log_type = 'User Activity'";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $total_activity = $stmt->get_result()->fetch_assoc()['total'];
    $total_pages_activity = ceil($total_activity / $limit);
    $stmt->close();

    $sql = "SELECT f.log_id, u.username, f.reason, f.details, f.ip_address, f.flagged_date
            FROM FraudLogs f
            LEFT JOIN Users u ON f.user_id = u.user_id
            WHERE f.log_type = 'User Activity'
            ORDER BY f.flagged_date DESC
            LIMIT ? OFFSET ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $limit, $offset_activity);
    $stmt->execute();
    $result = $stmt->get_result();
    $activity_logs = [];
    while ($row = $result->fetch_assoc()) {
        $activity_logs[] = $row;
    }
    $stmt->close();
} catch (Exception $e) {
    $activity_logs = [];
    $total_pages_activity = 1;
    error_log("Error fetching activity logs: " . $e->getMessage());
}

ob_end_flush();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Fraud Detection Logs</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />
    <style>
        .main-content {
            background-color: #f8f9fa;
            min-height: 100vh;
            padding: 20px;
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
        .nav-tabs .nav-link {
            color: #333;
            font-weight: 500;
        }
        .nav-tabs .nav-link.active {
            background-color: #509CDB;
            color: #ffffff;
            border-color: #509CDB;
        }
        .nav-tabs .nav-link:hover {
            border-color: #509CDB;
        }
        .logs-table {
            background-color: #ffffff;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }
        .logs-table .table {
            margin-bottom: 0;
        }
        .logs-table .table th {
            background-color: #152259;
            color: #ffffff;
        }
        .logs-table .table td {
            vertical-align: middle;
        }
        .logs-table .table .badge {
            font-size: 12px;
        }
        .logs-table .btn-view {
            background-color: #17a2b8;
            border: none;
            font-size: 14px;
            padding: 5px 10px;
            margin-right: 5px;
        }
        .logs-table .btn-view:hover {
            background-color: #138496;
        }
        .logs-table .btn-clear {
            background-color: #dc3545;
            border: none;
            font-size: 14px;
            padding: 5px 10px;
        }
        .logs-table .btn-clear:hover {
            background-color: #c82333;
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
        .modal-content {
            border-radius: 8px;
        }
        .modal-header {
            background-color: #152259;
            color: #ffffff;
        }
        .modal-header .btn-close {
            filter: invert(1);
        }
        .modal-body .audit-trail {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            max-height: 300px;
            overflow-y: auto;
        }
        .modal-body .audit-trail p {
            margin: 0;
            padding: 5px 0;
            border-bottom: 1px solid #dee2e6;
        }
        .modal-body .audit-trail p:last-child {
            border-bottom: none;
        }
    </style>
</head>
<body>
   <!-- Sidebar -->
   <?php include 'sidebar.php'; ?>


    <!-- Main content -->
    <div class="main-content">
        <div class="page-header">
            <h1>Fraud Detection Logs</h1>
        </div>
        <ul class="nav nav-tabs mb-3" id="logTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="flagged-tab" data-bs-toggle="tab" data-bs-target="#flagged" type="button" role="tab" aria-controls="flagged" aria-selected="true">Flagged Applications</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="activity-tab" data-bs-toggle="tab" data-bs-target="#activity" type="button" role="tab" aria-controls="activity" aria-selected="false">User Activity Logs</button>
            </li>
        </ul>
        <div class="tab-content" id="logTabContent">
            <div class="tab-pane fade show active" id="flagged" role="tabpanel" aria-labelledby="flagged-tab">
                <div class="logs-table">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Application ID</th>
                                <th>Student Name</th>
                                <th>Reason</th>
                                <th>Flagged Date</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="flaggedApplications">
                            <?php foreach ($flagged_applications as $log): ?>
                                <tr data-id="<?php echo htmlspecialchars($log['log_id']); ?>">
                                    <td><?php echo htmlspecialchars($log['application_id'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars(($log['first_name'] && $log['last_name']) ? $log['first_name'] . ' ' . $log['last_name'] : 'N/A'); ?></td>
                                    <td><span class="badge bg-danger"><?php echo htmlspecialchars($log['reason']); ?></span></td>
                                    <td><?php echo htmlspecialchars($log['flagged_date']); ?></td>
                                    <td><span class="badge bg-<?php echo $log['status'] == 'Under Review' ? 'warning' : 'success'; ?>"><?php echo htmlspecialchars($log['status']); ?></span></td>
                                    <td>
                                        <button class="btn btn-view" data-bs-toggle="modal" data-bs-target="#auditTrailModal" onclick="viewAuditTrail(<?php echo $log['log_id']; ?>)">View Audit Trail</button>
                                        <?php if ($log['status'] == 'Under Review'): ?>
                                            <button class="btn btn-clear" onclick="clearFlag(<?php echo $log['log_id']; ?>)">Clear Flag</button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if (empty($flagged_applications)): ?>
                                <tr><td colspan="6">No flagged applications found.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                    <nav aria-label="Page navigation">
                        <ul class="pagination" id="paginationFlagged">
                            <?php for ($i = 1; $i <= $total_pages_flagged; $i++): ?>
                                <li class="page-item <?php echo $page_flagged == $i ? 'active' : ''; ?>">
                                    <a class="page-link" href="?page_flagged=<?php echo $i; ?>&page_activity=<?php echo $page_activity; ?>" onclick="changePage('flagged', <?php echo $i; ?>)"><?php echo $i; ?></a>
                                </li>
                            <?php endfor; ?>
                        </ul>
                    </nav>
                </div>
            </div>
            <div class="tab-pane fade" id="activity" role="tabpanel" aria-labelledby="activity-tab">
                <div class="logs-table">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Log ID</th>
                                <th>User</th>
                                <th>Action</th>
                                <th>Details</th>
                                <th>Timestamp</th>
                                <th>IP Address</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="activityLogs">
                            <?php foreach ($activity_logs as $log): ?>
                                <tr data-id="<?php echo htmlspecialchars($log['log_id']); ?>">
                                    <td><?php echo htmlspecialchars($log['log_id']); ?></td>
                                    <td><?php echo htmlspecialchars($log['username'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($log['reason']); ?></td>
                                    <td><span class="badge bg-<?php echo strpos($log['details'], 'detected') !== false ? 'warning' : 'info'; ?>"><?php echo htmlspecialchars($log['details'] ?: 'N/A'); ?></span></td>
                                    <td><?php echo htmlspecialchars($log['flagged_date']); ?></td>
                                    <td><?php echo htmlspecialchars($log['ip_address'] ?: 'N/A'); ?></td>
                                    <td>
                                        <button class="btn btn-view" data-bs-toggle="modal" data-bs-target="#auditTrailModal" onclick="viewAuditTrail(<?php echo $log['log_id']; ?>)">View Audit Trail</button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if (empty($activity_logs)): ?>
                                <tr><td colspan="7">No activity logs found.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                    <nav aria-label="Page navigation">
                        <ul class="pagination" id="paginationActivity">
                            <?php for ($i = 1; $i <= $total_pages_activity; $i++): ?>
                                <li class="page-item <?php echo $page_activity == $i ? 'active' : ''; ?>">
                                    <a class="page-link" href="?page_flagged=<?php echo $page_flagged; ?>&page_activity=<?php echo $i; ?>" onclick="changePage('activity', <?php echo $i; ?>)"><?php echo $i; ?></a>
                                </li>
                            <?php endfor; ?>
                        </ul>
                    </nav>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="auditTrailModal" tabindex="-1" aria-labelledby="auditTrailModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="auditTrailModalLabel">Audit Trail</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="audit-trail" id="auditTrailContent"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function changePage(type, page) {
            fetch('', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({ action: type === 'flagged' ? 'fetch_flagged' : 'fetch_activity', page: page })
            })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    const tbody = document.getElementById(type === 'flagged' ? 'flaggedApplications' : 'activityLogs');
                    const pagination = document.getElementById(`pagination${type.charAt(0).toUpperCase() + type.slice(1)}`);
                    tbody.innerHTML = '';

                    if (type === 'flagged') {
                        if (result.flagged.length === 0) {
                            tbody.innerHTML = '<tr><td colspan="6">No flagged applications found.</td></tr>';
                        } else {
                            result.flagged.forEach(log => {
                                const row = document.createElement('tr');
                                row.setAttribute('data-id', log.log_id);
                                row.innerHTML = `
                                    <td>${log.application_id || 'N/A'}</td>
                                    <td>${log.first_name && log.last_name ? log.first_name + ' ' + log.last_name : 'N/A'}</td>
                                    <td><span class="badge bg-danger">${log.reason}</span></td>
                                    <td>${log.flagged_date}</td>
                                    <td><span class="badge bg-${log.status === 'Under Review' ? 'warning' : 'success'}">${log.status}</span></td>
                                    <td>
                                        <button class="btn btn-view" data-bs-toggle="modal" data-bs-target="#auditTrailModal" onclick="viewAuditTrail(${log.log_id})">View Audit Trail</button>
                                        ${log.status === 'Under Review' ? `<button class="btn btn-clear" onclick="clearFlag(${log.log_id})">Clear Flag</button>` : ''}
                                    </td>
                                `;
                                tbody.appendChild(row);
                            });
                        }
                    } else {
                        if (result.activity.length === 0) {
                            tbody.innerHTML = '<tr><td colspan="7">No activity logs found.</td></tr>';
                        } else {
                            result.activity.forEach(log => {
                                const row = document.createElement('tr');
                                row.setAttribute('data-id', log.log_id);
                                row.innerHTML = `
                                    <td>${log.log_id}</td>
                                    <td>${log.username || 'N/A'}</td>
                                    <td>${log.reason}</td>
                                    <td><span class="badge bg-${log.details && log.details.includes('detected') ? 'warning' : 'info'}">${log.details || 'N/A'}</span></td>
                                    <td>${log.flagged_date}</td>
                                    <td>${log.ip_address || 'N/A'}</td>
                                    <td>
                                        <button class="btn btn-view" data-bs-toggle="modal" data-bs-target="#auditTrailModal" onclick="viewAuditTrail(${log.log_id})">View Audit Trail</button>
                                    </td>
                                `;
                                tbody.appendChild(row);
                            });
                        }
                    }

                    pagination.innerHTML = '';
                    for (let i = 1; i <= result.total_pages; i++) {
                        const li = document.createElement('li');
                        li.className = `page-item ${i === result.current_page ? 'active' : ''}`;
                        li.innerHTML = `<a class="page-link" href="?page_flagged=${type === 'flagged' ? i : '<?php echo $page_flagged; ?>'}&page_activity=${type === 'activity' ? i : '<?php echo $page_activity; ?>'}" onclick="changePage('${type}', ${i})">${i}</a>`;
                        pagination.appendChild(li);
                    }
                } else {
                    alert(result.message);
                }
            })
            .catch(error => {
                console.error(`${type} fetch error:`, error);
                alert('Error: ' + error.message);
            });
            return false;
        }

        function viewAuditTrail(logId) {
            fetch('', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({ action: 'view_audit', log_id: logId })
            })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    const content = document.getElementById('auditTrailContent');
                    content.innerHTML = result.audit_trail.map(item => `<p>${item}</p>`).join('');
                } else {
                    alert(result.message);
                }
            })
            .catch(error => {
                console.error('Audit trail error:', error);
                alert('Error: ' + error.message);
            });
        }

        function clearFlag(logId) {
            if (!confirm('Are you sure you want to clear this flag?')) return;

            fetch('', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({ action: 'clear_flag', log_id: logId })
            })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    const row = document.querySelector(`#flaggedApplications tr[data-id="${logId}"]`);
                    row.cells[4].innerHTML = `<span class="badge bg-success">Cleared</span>`;
                    row.cells[5].innerHTML = `<button class="btn btn-view" data-bs-toggle="modal" data-bs-target="#auditTrailModal" onclick="viewAuditTrail(${logId})">View Audit Trail</button>`;
                } else {
                    alert(result.message);
                }
            })
            .catch(error => {
                console.error('Clear flag error:', error);
                alert('Error: ' + error.message);
            });
        }
    </script>
</body>
</html>