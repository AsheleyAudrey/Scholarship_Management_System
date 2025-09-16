<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ob_start();
include "../Database/db.php";

// Check database connection
if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

// Handle AJAX requests
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action'])) {
    ob_clean();
    header('Content-Type: application/json');

    // Fetch Notifications by Status
    if ($_POST['action'] == 'fetch_notifications') {
        $status = $_POST['status']; // 'All', 'Sent', 'Draft', 'Scheduled'
        $page = isset($_POST['page']) ? max(1, intval($_POST['page'])) : 1;
        $limit = 10;
        $offset = ($page - 1) * $limit;

        try {
            // Count total notifications
            $sql = $status == 'All' ?
                "SELECT COUNT(*) AS total FROM Notifications" :
                "SELECT COUNT(*) AS total FROM Notifications WHERE status = ?";
            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                throw new Exception("Prepare count query failed: " . $conn->error);
            }
            if ($status != 'All') $stmt->bind_param("s", $status);
            if (!$stmt->execute()) {
                throw new Exception("Execute count query failed: " . $stmt->error);
            }
            $total = $stmt->get_result()->fetch_assoc()['total'];
            $total_pages = ceil($total / $limit);
            $stmt->close();

            // Fetch notifications
            $sql = $status == 'All' ?
                "SELECT n.notification_id, n.message, n.content, n.status, n.scheduled_date, n.created_date, 
                        COALESCE(CONCAT(s.first_name, ' ', s.last_name), u.username, CONCAT('User ', n.user_id)) AS recipient
                 FROM Notifications n
                 LEFT JOIN Users u ON n.user_id = u.user_id
                 LEFT JOIN Students s ON u.student_id = s.student_id
                 ORDER BY n.created_date DESC
                 LIMIT ? OFFSET ?" :
                "SELECT n.notification_id, n.message, n.content, n.status, n.scheduled_date, n.created_date, 
                        COALESCE(CONCAT(s.first_name, ' ', s.last_name), u.username, CONCAT('User ', n.user_id)) AS recipient
                 FROM Notifications n
                 LEFT JOIN Users u ON n.user_id = u.user_id
                 LEFT JOIN Students s ON u.student_id = s.student_id
                 WHERE n.status = ?
                 ORDER BY n.created_date DESC
                 LIMIT ? OFFSET ?";
            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                throw new Exception("Prepare fetch query failed: " . $conn->error);
            }
            if ($status == 'All') {
                $stmt->bind_param("ii", $limit, $offset);
            } else {
                $stmt->bind_param("sii", $status, $limit, $offset);
            }
            if (!$stmt->execute()) {
                throw new Exception("Execute fetch query failed: " . $stmt->error);
            }
            $result = $stmt->get_result();
            $notifications = [];
            while ($row = $result->fetch_assoc()) {
                $notifications[] = $row;
            }
            $stmt->close();

            echo json_encode([
                'success' => true,
                'notifications' => $notifications,
                'total_pages' => $total_pages,
                'current_page' => $page
            ]);
        } catch (Exception $e) {
            error_log("Fetch notifications error: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
        exit;
    }

    // Send New Notification
    if ($_POST['action'] == 'send_notification') {
        $user_id = intval($_POST['user_id']);
        $subject = trim($_POST['subject']);
        $content = trim($_POST['content']);
        $schedule_date = $_POST['schedule_date'] ? $_POST['schedule_date'] : null;
        $status = 'Unread';

        try {
            $sql = "INSERT INTO Notifications (user_id, message, status, scheduled_date) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);

            if (!$stmt) {
                throw new Exception("Prepare insert query failed: " . $conn->error);
            }
            $stmt->bind_param("isss", $user_id, $content, $status, $schedule_date);
            if ($stmt->execute()) {
                echo json_encode(['success' => true]);
            } else {
                throw new Exception("Insert failed: " . $stmt->error);
            }
            $stmt->close();
        } catch (Exception $e) {
            error_log("Send notification error: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
        exit;
    }

    // Send Now (Draft or Scheduled)
    if ($_POST['action'] == 'send_now') {
        $notification_id = $_POST['notification_id'];
        try {
            $sql = "UPDATE Notifications SET status = 'Sent', scheduled_date = NULL, created_date = NOW() WHERE notification_id = ?";
            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                throw new Exception("Prepare update query failed: " . $conn->error);
            }
            $stmt->bind_param("s", $notification_id);
            if ($stmt->execute()) {
                echo json_encode(['success' => true]);
            } else {
                throw new Exception("Update failed: " . $stmt->error);
            }
            $stmt->close();
        } catch (Exception $e) {
            error_log("Send now error: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
        exit;
    }

    // View Notification Details
    if ($_POST['action'] == 'view_notification') {
        $notification_id = $_POST['notification_id'];
        try {
            $sql = "SELECT n.notification_id, n.message, n.content, n.status, n.scheduled_date, n.created_date,
                           COALESCE(CONCAT(s.first_name, ' ', s.last_name), u.username, CONCAT('User ', n.user_id)) AS recipient
                    FROM Notifications n
                    LEFT JOIN Users u ON n.user_id = u.user_id
                    LEFT JOIN Students s ON u.student_id = s.student_id
                    WHERE n.notification_id = ?";
            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                throw new Exception("Prepare view query failed: " . $conn->error);
            }
            $stmt->bind_param("s", $notification_id);
            if (!$stmt->execute()) {
                throw new Exception("Execute view query failed: " . $stmt->error);
            }
            $result = $stmt->get_result();
            $notification = $result->fetch_assoc();
            $stmt->close();

            if ($notification) {
                echo json_encode(['success' => true, 'notification' => $notification]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Notification not found']);
            }
        } catch (Exception $e) {
            error_log("View notification error: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
        exit;
    }

    echo json_encode(['success' => false, 'message' => 'Invalid action']);
    exit;
}

// Fetch recipients for Send Notification modal
try {
    $sql = "SELECT u.user_id, COALESCE(CONCAT(s.first_name, ' ', s.last_name), u.username, CONCAT('User ', u.user_id)) AS recipient_name
            FROM Users u
            LEFT JOIN Students s ON u.student_id = s.student_id
            WHERE u.role = 'Student'";
    $result = $conn->query($sql);
    if (!$result) {
        throw new Exception("Recipient query failed: " . $conn->error);
    }
    $recipients = [];
    while ($row = $result->fetch_assoc()) {
        $recipients[] = $row;
    }
    error_log("Fetched " . count($recipients) . " recipients for dropdown");
    if (empty($recipients)) {
        error_log("No recipients found in Users table with role='Student'");
    }
} catch (Exception $e) {
    $recipients = [];
    error_log("Error fetching recipients: " . $e->getMessage());
}

// Fetch initial data for each tab
$limit = 10;
$tabs = ['All', 'Sent', 'Draft', 'Scheduled'];
$notifications_data = [];
$total_pages = [];

foreach ($tabs as $status) {
    $page = isset($_GET["page_$status"]) ? max(1, intval($_GET["page_$status"])) : 1;
    $offset = ($page - 1) * $limit;

    try {
        $sql = $status == 'All' ?
            "SELECT COUNT(*) AS total FROM Notifications" :
            "SELECT COUNT(*) AS total FROM Notifications WHERE status = ?";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            throw new Exception("Initial count prepare failed: " . $conn->error);
        }
        if ($status != 'All') $stmt->bind_param("s", $status);
        if (!$stmt->execute()) {
            throw new Exception("Initial count execute failed: " . $stmt->error);
        }
        $total = $stmt->get_result()->fetch_assoc()['total'];
        $total_pages[$status] = ceil($total / $limit);
        $stmt->close();

        $sql = $status == 'All' ?
            "SELECT n.notification_id, n.message, n.content, n.status, n.scheduled_date, n.created_date, 
                    COALESCE(CONCAT(s.first_name, ' ', s.last_name), u.username, CONCAT('User ', n.user_id)) AS recipient
             FROM Notifications n
             LEFT JOIN Users u ON n.user_id = u.user_id
             LEFT JOIN Students s ON u.student_id = s.student_id
             ORDER BY n.created_date DESC
             LIMIT ? OFFSET ?" :
            "SELECT n.notification_id, n.message, n.content, n.status, n.scheduled_date, n.created_date, 
                    COALESCE(CONCAT(s.first_name, ' ', s.last_name), u.username, CONCAT('User ', n.user_id)) AS recipient
             FROM Notifications n
             LEFT JOIN Users u ON n.user_id = u.user_id
             LEFT JOIN Students s ON u.student_id = s.student_id
             WHERE n.status = ?
             ORDER BY n.created_date DESC
             LIMIT ? OFFSET ?";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            throw new Exception("Initial fetch prepare failed: " . $conn->error);
        }
        if ($status == 'All') {
            $stmt->bind_param("ii", $limit, $offset);
        } else {
            $stmt->bind_param("sii", $status, $limit, $offset);
        }
        if (!$stmt->execute()) {
            throw new Exception("Initial fetch execute failed: " . $stmt->error);
        }
        $result = $stmt->get_result();
        $notifications_data[$status] = [];
        while ($row = $result->fetch_assoc()) {
            $notifications_data[$status][] = $row;
        }
        $stmt->close();
    } catch (Exception $e) {
        $notifications_data[$status] = [];
        $total_pages[$status] = 1;
        error_log("Error fetching $status notifications: " . $e->getMessage());
    }
}

ob_end_flush();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Notifications Management</title>
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
        .page-header .btn-send {
            background-color: #509CDB;
            border: none;
            padding: 10px 20px;
            font-size: 16px;
            color: #ffffff;
        }
        .page-header .btn-send:hover {
            background-color: #408CCB;
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
        .notifications-table {
            background-color: #ffffff;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }
        .notifications-table .table {
            margin-bottom: 0;
        }
        .notifications-table .table th {
            background-color: #152259;
            color: #ffffff;
        }
        .notifications-table .table td {
            vertical-align: middle;
        }
        .notifications-table .table .badge {
            font-size: 12px;
        }
        .notifications-table .btn-view {
            background-color: #17a2b8;
            border: none;
            font-size: 14px;
            padding: 5px 10px;
            margin-right: 5px;
        }
        .notifications-table .btn-view:hover {
            background-color: #138496;
        }
        .notifications-table .btn-send-now {
            background-color: #28a745;
            border: none;
            font-size: 14px;
            padding: 5px 10px;
        }
        .notifications-table .btn-send-now:hover {
            background-color: #218838;
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
        .modal-body .form-label {
            font-weight: 500;
            color: #333;
        }
        .modal-body .form-control,
        .modal-body .form-select {
            border-radius: 5px;
            border: 1px solid #ced4da;
            box-shadow: none;
        }
        .modal-body .form-control:focus,
        .modal-body .form-select:focus {
            border-color: #509CDB;
            box-shadow: 0 0 5px rgba(80, 156, 219, 0.3);
        }
        .modal-footer .btn-save {
            background-color: #509CDB;
            border: none;
        }
        .modal-footer .btn-save:hover {
            background-color: #408CCB;
        }
        .modal-footer .btn-save:disabled {
            background-color: #6c757d;
            cursor: not-allowed;
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
   <?php include 'sidebar.php'; ?>


    <!-- Main content -->
    <div class="main-content">
        <div class="page-header">
            <h1>Notifications Management</h1>
            <button class="btn btn-send" data-bs-toggle="modal" data-bs-target="#sendNotificationModal">
                <i class="bi bi-plus-circle me-2"></i> Send Notification
            </button>
        </div>
        <ul class="nav nav-tabs mb-3" id="statusTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="all-tab" data-bs-toggle="tab" data-bs-target="#all" type="button" role="tab" aria-controls="all" aria-selected="true">All</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="sent-tab" data-bs-toggle="tab" data-bs-target="#sent" type="button" role="tab" aria-controls="sent" aria-selected="false">Sent</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="draft-tab" data-bs-toggle="tab" data-bs-target="#draft" type="button" role="tab" aria-controls="draft" aria-selected="false">Draft</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="scheduled-tab" data-bs-toggle="tab" data-bs-target="#scheduled" type="button" role="tab" aria-controls="scheduled" aria-selected="false">Scheduled</button>
            </li>
        </ul>
        <div class="tab-content" id="statusTabContent">
            <?php foreach (['All', 'Sent', 'Draft', 'Scheduled'] as $tab): ?>
                <div class="tab-pane fade <?php echo $tab == 'All' ? 'show active' : ''; ?>" id="<?php echo strtolower($tab); ?>" role="tabpanel" aria-labelledby="<?php echo strtolower($tab); ?>-tab">
                    <div class="notifications-table">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Notification ID</th>
                                    <th>Recipient</th>
                                    <th>Subject</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="<?php echo strtolower($tab); ?>Notifications">
                                <?php foreach ($notifications_data[$tab] as $notification): ?>
                                    <tr data-status="<?php echo htmlspecialchars($notification['status']); ?>">
                                        <td><?php echo htmlspecialchars($notification['notification_id']); ?></td>
                                        <td><?php echo htmlspecialchars($notification['recipient'] ?? 'N/A'); ?></td>
                                        <td><?php echo htmlspecialchars($notification['message']); ?></td>
                                        <td><span class="badge bg-<?php echo $notification['status'] == 'Sent' ? 'success' : ($notification['status'] == 'Draft' ? 'warning' : 'info'); ?>">
                                            <?php echo htmlspecialchars($notification['status']); ?>
                                        </span></td>
                                        <td><?php echo htmlspecialchars($notification['scheduled_date'] ?: $notification['created_date']); ?></td>
                                        <td>
                                            <button class="btn btn-view" data-bs-toggle="modal" data-bs-target="#viewNotificationModal" onclick="viewNotification('<?php echo $notification['notification_id']; ?>')">View</button>
                                            <?php if ($notification['status'] == 'Draft' || $notification['status'] == 'Scheduled'): ?>
                                                <button class="btn btn-send-now" onclick="sendNow('<?php echo $notification['notification_id']; ?>')">Send Now</button>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                <?php if (empty($notifications_data[$tab])): ?>
                                    <tr><td colspan="6">No <?php echo strtolower($tab); ?> notifications found.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                        <nav aria-label="Page navigation">
                            <ul class="pagination" id="pagination<?php echo $tab; ?>">
                                <?php for ($i = 1; $i <= $total_pages[$tab]; $i++): ?>
                                    <li class="page-item <?php echo (isset($_GET["page_$tab"]) && $_GET["page_$tab"] == $i) || ($i == 1 && !isset($_GET["page_$tab"])) ? 'active' : ''; ?>">
                                        <a class="page-link" href="?page_All=<?php echo $tab == 'All' ? $i : $total_pages['All']; ?>&page_Sent=<?php echo $tab == 'Sent' ? $i : $total_pages['Sent']; ?>&page_Draft=<?php echo $tab == 'Draft' ? $i : $total_pages['Draft']; ?>&page_Scheduled=<?php echo $tab == 'Scheduled' ? $i : $total_pages['Scheduled']; ?>" onclick="changePage('<?php echo strtolower($tab); ?>', <?php echo $i; ?>)"><?php echo $i; ?></a>
                                    </li>
                                <?php endfor; ?>
                            </ul>
                        </nav>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <div class="modal fade" id="sendNotificationModal" tabindex="-1" aria-labelledby="sendNotificationModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="sendNotificationModalLabel">Send Notification</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="sendNotificationForm">
                        <div class="mb-3">
                            <label for="recipient" class="form-label">Recipient</label>
                            <select class="form-select" id="recipient" name="user_id" required>
                                <option value="" disabled selected>Select recipient</option>
                                <?php if (empty($recipients)): ?>
                                    <option value="" disabled>No students available</option>
                                <?php else: ?>
                                    <?php foreach ($recipients as $recipient): ?>
                                        <option value="<?php echo htmlspecialchars($recipient['user_id']); ?>">
                                            <?php echo htmlspecialchars($recipient['recipient_name'] ?? 'User ' . $recipient['user_id']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="subject" class="form-label">Subject</label>
                            <input type="text" class="form-control" id="subject" name="subject" required>
                        </div>
                        <div class="mb-3">
                            <label for="message" class="form-label">Message</label>
                            <textarea class="form-control" id="message" name="content" rows="4" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="scheduleDate" class="form-label">Schedule Date (Optional)</label>
                            <input type="datetime-local" class="form-control" id="scheduleDate" name="schedule_date">
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-save" onclick="sendNotification()" <?php echo empty($recipients) ? 'disabled' : ''; ?>>Send</button>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="viewNotificationModal" tabindex="-1" aria-labelledby="viewNotificationModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="viewNotificationModalLabel">Notification Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p><strong>Notification ID:</strong> <span id="viewNotificationId"></span></p>
                    <p><strong>Recipient:</strong> <span id="viewRecipient"></span></p>
                    <p><strong>Subject:</strong> <span id="viewSubject"></span></p>
                    <p><strong>Message:</strong> <span id="viewMessage"></span></p>
                    <p><strong>Status:</strong> <span id="viewStatus"></span></p>
                    <p><strong>Date:</strong> <span id="viewDate"></span></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function changePage(status, page) {
            fetch('', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({ action: 'fetch_notifications', status: status, page: page })
            })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    const tbody = document.getElementById(status + 'Notifications');
                    const pagination = document.getElementById('pagination' + status.charAt(0).toUpperCase() + status.slice(1));
                    tbody.innerHTML = '';

                    if (result.notifications.length === 0) {
                        tbody.innerHTML = `<tr><td colspan="6">No ${status} notifications found.</td></tr>`;
                    } else {
                        result.notifications.forEach(notification => {
                            const row = document.createElement('tr');
                            row.setAttribute('data-status', notification.status);
                            row.innerHTML = `
                                <td>${notification.notification_id}</td>
                                <td>${notification.recipient || 'N/A'}</td>
                                <td>${notification.message}</td>
                                <td><span class="badge bg-${notification.status === 'Sent' ? 'success' : (notification.status === 'Draft' ? 'warning' : 'info')}">${notification.status}</span></td>
                                <td>${notification.scheduled_date || notification.created_date}</td>
                                <td>
                                    <button class="btn btn-view" data-bs-toggle="modal" data-bs-target="#viewNotificationModal" onclick="viewNotification('${notification.notification_id}')">View</button>
                                    ${notification.status === 'Draft' || notification.status === 'Scheduled' ? `<button class="btn btn-send-now" onclick="sendNow('${notification.notification_id}')">Send Now</button>` : ''}
                                </td>
                            `;
                            tbody.appendChild(row);
                        });
                    }

                    pagination.innerHTML = '';
                    for (let i = 1; i <= result.total_pages; i++) {
                        const li = document.createElement('li');
                        li.className = `page-item ${i === result.current_page ? 'active' : ''}`;
                        li.innerHTML = `<a class="page-link" href="?page_All=${status === 'All' ? i : '<?php echo $total_pages['All']; ?>'}&page_Sent=${status === 'Sent' ? i : '<?php echo $total_pages['Sent']; ?>'}&page_Draft=${status === 'Draft' ? i : '<?php echo $total_pages['Draft']; ?>'}&page_Scheduled=${status === 'Scheduled' ? i : '<?php echo $total_pages['Scheduled']; ?>'}" onclick="changePage('${status}', ${i})">${i}</a>`;
                        pagination.appendChild(li);
                    }
                } else {
                    alert(result.message);
                }
            })
            .catch(error => {
                console.error(`Fetch ${status} notifications error:`, error);
                alert('Error: ' + error.message);
            });
            return false;
        }

        function sendNotification() {
            const form = document.getElementById('sendNotificationForm');
            if (!form.checkValidity()) {
                form.reportValidity();
                return;
            }

            const user_id = document.getElementById('recipient').value;
            const subject = document.getElementById('subject').value;
            const content = document.getElementById('message').value;
            const schedule_date = document.getElementById('scheduleDate').value;

            fetch('', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({
                    action: 'send_notification',
                    user_id: user_id,
                    subject: subject,
                    content: content,
                    schedule_date: schedule_date
                })
            })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    alert('Notification saved successfully!');
                    document.getElementById('sendNotificationForm').reset();
                    bootstrap.Modal.getInstance(document.getElementById('sendNotificationModal')).hide();
                    changePage('All', 1);
                    changePage('Draft', 1);
                    changePage('Scheduled', 1);
                } else {
                    alert(result.message);
                }
            })
            .catch(error => {
                console.error('Send notification error:', error);
                alert('Error: ' + error.message);
            });
        }

        function sendNow(notificationId) {
            if (!confirm('Are you sure you want to send this notification now?')) return;

            fetch('', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({ action: 'send_now', notification_id: notificationId })
            })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    alert('Notification sent successfully!');
                    changePage('All', 1);
                    changePage('Sent', 1);
                    changePage('Draft', 1);
                    changePage('Scheduled', 1);
                } else {
                    alert(result.message);
                }
            })
            .catch(error => {
                console.error('Send now error:', error);
                alert('Error: ' + error.message);
            });
        }

        function viewNotification(notificationId) {
            fetch('', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({ action: 'view_notification', notification_id: notificationId })
            })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    document.getElementById('viewNotificationId').textContent = result.notification.notification_id;
                    document.getElementById('viewRecipient').textContent = result.notification.recipient || 'N/A';
                    document.getElementById('viewSubject').textContent = result.notification.message;
                    document.getElementById('viewMessage').textContent = result.notification.content || 'N/A';
                    document.getElementById('viewStatus').textContent = result.notification.status;
                    document.getElementById('viewDate').textContent = result.notification.scheduled_date || result.notification.created_date;
                } else {
                    alert(result.message);
                }
            })
            .catch(error => {
                console.error('View notification error:', error);
                alert('Error: ' + error.message);
            });
        }
    </script>
</body>
</html>