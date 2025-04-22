<?php
ob_start();
include "../Database/db.php";

// Handle AJAX requests
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action'])) {
    ob_clean();
    header('Content-Type: application/json');

    // Add Student
    if ($_POST['action'] == 'add') {
        $username = trim($_POST['username']);
        $password = trim($_POST['password']);
        $first_name = trim($_POST['first_name']);
        $last_name = trim($_POST['last_name']);
        $email = trim($_POST['email']);
        $phone = !empty($_POST['phone']) ? trim($_POST['phone']) : null;
        $date_of_birth = !empty($_POST['date_of_birth']) ? trim($_POST['date_of_birth']) : null;
        $enrollment_date = !empty($_POST['enrollment_date']) ? trim($_POST['enrollment_date']) : date('Y-m-d');
        $program = trim($_POST['program']);
        $gpa = !empty($_POST['gpa']) ? floatval($_POST['gpa']) : null;

        if (empty($username) || empty($password) || empty($first_name) || empty($last_name) || empty($email) || empty($program)) {
            echo json_encode(['success' => false, 'message' => 'Username, password, first name, last name, email, and program are required.']);
            exit;
        }

        if ($gpa !== null && ($gpa < 0 || $gpa > 4.0)) {
            echo json_encode(['success' => false, 'message' => 'GPA must be between 0.0 and 4.0.']);
            exit;
        }

        try {
            // Check if username or email already exists
            $sql = "SELECT user_id FROM Users WHERE username = ? UNION SELECT user_id FROM Students WHERE email = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ss", $username, $email);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                echo json_encode(['success' => false, 'message' => 'Username or email already exists.']);
                exit;
            }
            $stmt->close();

            // Insert into Users table
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $role = 'Student';
            $sql = "INSERT INTO Users (username, password, role, approval_status) VALUES (?, ?, ?, 'Approved')";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sss", $username, $hashed_password, $role);
            $stmt->execute();
            $user_id = $conn->insert_id;
            $stmt->close();

            // Insert into Students table
            $sql = "INSERT INTO Students (user_id, first_name, last_name, email, phone, date_of_birth, enrollment_date, program, gpa) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("isssssssd", $user_id, $first_name, $last_name, $email, $phone, $date_of_birth, $enrollment_date, $program, $gpa);
            if ($stmt->execute()) {
                $student_id = $conn->insert_id;
                echo json_encode(['success' => true, 'student_id' => $student_id]);
            } else {
                // Rollback user creation
                $delete_sql = "DELETE FROM Users WHERE user_id = ?";
                $delete_stmt = $conn->prepare($delete_sql);
                $delete_stmt->bind_param("i", $user_id);
                $delete_stmt->execute();
                echo json_encode(['success' => false, 'message' => 'Database error: ' . $stmt->error]);
            }
            $stmt->close();
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
        exit;
    }

    // Edit Student
    if ($_POST['action'] == 'edit') {
        $student_id = trim($_POST['student_id']);
        $first_name = trim($_POST['first_name']);
        $last_name = trim($_POST['last_name']);
        $email = trim($_POST['email']);
        $phone = !empty($_POST['phone']) ? trim($_POST['phone']) : null;

        if (empty($student_id) || empty($first_name) || empty($last_name) || empty($email)) {
            echo json_encode(['success' => false, 'message' => 'Student ID, first name, last name, and email are required.']);
            exit;
        }

        try {
            $sql = "UPDATE Students SET first_name = ?, last_name = ?, email = ?, phone = ? WHERE student_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssss", $first_name, $last_name, $email, $phone, $student_id);

            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'student_id' => $student_id]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Database error: ' . $stmt->error]);
            }
            $stmt->close();
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
        exit;
    }

    // View Student
    if ($_POST['action'] == 'view') {
        $student_id = trim($_POST['student_id']);
        try {
            // Fetch student details
            $sql = "SELECT s.student_id, s.first_name, s.last_name, s.email, s.phone, s.program, s.gpa, s.status 
                    FROM Students s WHERE s.student_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $student_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $student = $result->fetch_assoc();
            $stmt->close();

            if (!$student) {
                echo json_encode(['success' => false, 'message' => 'Student not found']);
                exit;
            }

            // Fetch application history
            $sql = "SELECT a.application_id, a.submission_date, a.status, sc.name AS scholarship_name 
                    FROM Applications a 
                    JOIN Scholarships sc ON a.scholarship_id = sc.scholarship_id 
                    WHERE a.student_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $student_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $applications = [];
            while ($row = $result->fetch_assoc()) {
                $applications[] = $row;
            }
            $stmt->close();

            // Fetch scholarship allocation history (based on student status)
            $scholarship_history = [];
            if ($student['status'] == 'Scholarship Awarded') {
                $sql = "SELECT sc.name, a.submission_date 
                        FROM Applications a 
                        JOIN Scholarships sc ON a.scholarship_id = sc.scholarship_id 
                        WHERE a.student_id = ? AND a.status = 'Approved'";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("s", $student_id);
                $stmt->execute();
                $result = $stmt->get_result();
                while ($row = $result->fetch_assoc()) {
                    $scholarship_history[] = $row;
                }
                $stmt->close();
            }

            echo json_encode([
                'success' => true,
                'student' => $student,
                'applications' => $applications,
                'scholarship_history' => $scholarship_history
            ]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
        exit;
    }

    echo json_encode(['success' => false, 'message' => 'Invalid action']);
    exit;
}

// Fetch all students for display
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$limit = 10; // Students per page
$offset = ($page - 1) * $limit;

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$search_query = $search ? "WHERE s.student_id LIKE ? OR s.first_name LIKE ? OR s.last_name LIKE ? OR s.email LIKE ?" : '';

try {
    // Count total students for pagination
    $sql = "SELECT COUNT(*) AS total FROM Students s $search_query";
    $stmt = $conn->prepare($sql);
    if ($search) {
        $search_param = "%$search%";
        $stmt->bind_param("ssss", $search_param, $search_param, $search_param, $search_param);
    }
    $stmt->execute();
    $total = $stmt->get_result()->fetch_assoc()['total'];
    $total_pages = ceil($total / $limit);
    $stmt->close();

    // Fetch students
    $sql = "SELECT s.student_id, s.first_name, s.last_name, s.email, s.phone 
            FROM Students s 
            $search_query 
            ORDER BY s.student_id 
            LIMIT ? OFFSET ?";
    $stmt = $conn->prepare($sql);
    if ($search) {
        $search_param = "%$search%";
        $stmt->bind_param("ssssii", $search_param, $search_param, $search_param, $search_param, $limit, $offset);
    } else {
        $stmt->bind_param("ii", $limit, $offset);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $students = [];
    while ($row = $result->fetch_assoc()) {
        $students[] = $row;
    }
    $stmt->close();
} catch (Exception $e) {
    $students = [];
    $total_pages = 1;
    error_log("Error fetching students: " . $e->getMessage());
}
ob_end_flush();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Student Management</title>
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
        .page-header .btn-add {
            background-color: #509CDB;
            border: none;
            padding: 10px 20px;
            font-size: 16px;
            color: #ffffff;
        }
        .page-header .btn-add:hover {
            background-color: #408CCB;
        }
        .search-bar {
            margin-bottom: 20px;
            max-width: 400px;
        }
        .search-bar .form-control {
            border-radius: 5px;
            border: 1px solid #ced4da;
            box-shadow: none;
        }
        .search-bar .form-control:focus {
            border-color: #509CDB;
            box-shadow: 0 0 5px rgba(80, 156, 219, 0.3);
        }
        .students-table {
            background-color: #ffffff;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }
        .students-table .table {
            margin-bottom: 0;
        }
        .students-table .table th {
            background-color: #152259;
            color: #ffffff;
        }
        .students-table .table td {
            vertical-align: middle;
        }
        .students-table .btn-view {
            background-color: #17a2b8;
            border: none;
            font-size: 14px;
            padding: 5px 10px;
            margin-right: 5px;
        }
        .students-table .btn-view:hover {
            background-color: #138496;
        }
        .students-table .btn-edit {
            background-color: #509CDB;
            border: none;
            font-size: 14px;
            padding: 5px 10px;
        }
        .students-table .btn-edit:hover {
            background-color: #408CCB;
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
        .modal-body .form-control {
            border-radius: 5px;
            border: 1px solid #ced4da;
            box-shadow: none;
        }
        .modal-body .form-control:focus {
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
        .profile-card {
            background-color: #ffffff;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            margin-top: 20px;
        }
        .profile-card h5 {
            font-size: 18px;
            font-weight: 600;
            color: #152259;
            margin-bottom: 15px;
        }
        .profile-card p {
            margin-bottom: 10px;
            font-size: 14px;
            color: #333;
        }
        .profile-card .history-section {
            margin-top: 20px;
        }
        .profile-card .history-section h6 {
            font-size: 16px;
            font-weight: 600;
            color: #152259;
            margin-bottom: 10px;
        }
        .profile-card .history-section ul {
            list-style: none;
            padding: 0;
        }
        .profile-card .history-section ul li {
            padding: 5px 0;
            font-size: 14px;
            border-bottom: 1px solid #eee;
        }
        .profile-card .history-section ul li:last-child {
            border-bottom: none;
        }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    <div class="main-content">
        <div class="page-header">
            <h1>Student Management</h1>
            <button class="btn btn-add" data-bs-toggle="modal" data-bs-target="#addStudentModal">
                <i class="bi bi-plus-circle me-2"></i> Add New Student
            </button>
        </div>

        <div class="search-bar">
            <input type="text" class="form-control" id="searchInput" placeholder="Search by ID or Name" onkeyup="searchStudents()">
        </div>

        <div class="students-table">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Student ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="studentsTable">
                    <?php foreach ($students as $student): ?>
                        <tr data-id="<?php echo htmlspecialchars($student['student_id']); ?>">
                            <td><?php echo htmlspecialchars($student['student_id']); ?></td>
                            <td><?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></td>
                            <td><?php echo htmlspecialchars($student['email']); ?></td>
                            <td><?php echo htmlspecialchars($student['phone'] ?: 'N/A'); ?></td>
                            <td>
                                <button class="btn btn-view" onclick="viewStudent('<?php echo $student['student_id']; ?>')">View</button>
                                <button class="btn btn-edit" onclick="editStudent('<?php echo $student['student_id']; ?>')">Edit</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <nav aria-label="Page navigation">
                <ul class="pagination" id="pagination">
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <li class="page-item <?php echo $page == $i ? 'active' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>" onclick="changePage(<?php echo $i; ?>)"><?php echo $i; ?></a>
                        </li>
                    <?php endfor; ?>
                </ul>
            </nav>
        </div>
    </div>

    <!-- Add Student Modal -->
    <div class="modal fade" id="addStudentModal" tabindex="-1" aria-labelledby="addStudentModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addStudentModalLabel">Add New Student</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="addStudentForm">
                        <div class="mb-3">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" class="form-control" id="username" required>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" required>
                        </div>
                        <div class="mb-3">
                            <label for="firstName" class="form-label">First Name</label>
                            <input type="text" class="form-control" id="firstName" required>
                        </div>
                        <div class="mb-3">
                            <label for="lastName" class="form-label">Last Name</label>
                            <input type="text" class="form-control" id="lastName" required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" required>
                        </div>
                        <div class="mb-3">
                            <label for="phone" class="form-label">Phone</label>
                            <input type="tel" class="form-control" id="phone">
                        </div>
                        <div class="mb-3">
                            <label for="dateOfBirth" class="form-label">Date of Birth</label>
                            <input type="date" class="form-control" id="dateOfBirth">
                        </div>
                        <div class="mb-3">
                            <label for="enrollmentDate" class="form-label">Enrollment Date</label>
                            <input type="date" class="form-control" id="enrollmentDate" value="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="program" class="form-label">Program</label>
                            <input type="text" class="form-control" id="program" required>
                        </div>
                        <div class="mb-3">
                            <label for="gpa" class="form-label">GPA</label>
                            <input type="number" step="0.01" min="0" max="4.0" class="form-control" id="gpa">
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-save" onclick="addStudent()">Save</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Student Modal -->
    <div class="modal fade" id="editStudentModal" tabindex="-1" aria-labelledby="editStudentModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editStudentModalLabel">Edit Student</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="editStudentForm">
                        <div class="mb-3">
                            <label for="editStudentId" class="form-label">Student ID</label>
                            <input type="text" class="form-control" id="editStudentId" readonly>
                        </div>
                        <div class="mb-3">
                            <label for="editFirstName" class="form-label">First Name</label>
                            <input type="text" class="form-control" id="editFirstName" required>
                        </div>
                        <div class="mb-3">
                            <label for="editLastName" class="form-label">Last Name</label>
                            <input type="text" class="form-control" id="editLastName" required>
                        </div>
                        <div class="mb-3">
                            <label for="editEmail" class="form-label">Email</label>
                            <input type="email" class="form-control" id="editEmail" required>
                        </div>
                        <div class="mb-3">
                            <label for="editPhone" class="form-label">Phone</label>
                            <input type="tel" class="form-control" id="editPhone">
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-save" onclick="saveStudent()">Save</button>
                </div>
            </div>
        </div>
    </div>

    <!-- View Student Modal -->
    <div class="modal fade" id="viewStudentModal" tabindex="-1" aria-labelledby="viewStudentModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="viewStudentModalLabel">Student Profile</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="profile-card">
                        <h5 id="profileName"></h5>
                        <p><strong>Student ID:</strong> <span id="profileId"></span></p>
                        <p><strong>Email:</strong> <span id="profileEmail"></span></p>
                        <p><strong>Phone:</strong> <span id="profilePhone"></span></p>
                        <p><strong>Program:</strong> <span id="profileProgram"></span></p>
                        <p><strong>GPA:</strong> <span id="profileGPA"></span></p>
                        <p><strong>Status:</strong> <span id="profileStatus"></span></p>
                        <div class="history-section">
                            <h6>Application History</h6>
                            <ul id="applicationHistory"></ul>
                        </div>
                        <div class="history-section">
                            <h6>Scholarship Allocation History</h6>
                            <ul id="scholarshipHistory"></ul>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function searchStudents() {
            const searchValue = document.getElementById('searchInput').value;
            window.location.href = `?search=${encodeURIComponent(searchValue)}&page=1`;
        }

        function changePage(page) {
            const searchValue = document.getElementById('searchInput').value;
            window.location.href = `?search=${encodeURIComponent(searchValue)}&page=${page}`;
            return false;
        }

        async function addStudent() {
            const username = document.getElementById('username').value;
            const password = document.getElementById('password').value;
            const firstName = document.getElementById('firstName').value;
            const lastName = document.getElementById('lastName').value;
            const email = document.getElementById('email').value;
            const phone = document.getElementById('phone').value;
            const dateOfBirth = document.getElementById('dateOfBirth').value;
            const enrollmentDate = document.getElementById('enrollmentDate').value;
            const program = document.getElementById('program').value;
            const gpa = document.getElementById('gpa').value;

            if (!username || !password || !firstName || !lastName || !email || !enrollmentDate || !program) {
                alert('Please fill in all required fields.');
                return;
            }

            const data = {
                action: 'add',
                username,
                password,
                first_name: firstName,
                last_name: lastName,
                email,
                phone,
                date_of_birth: dateOfBirth,
                enrollment_date: enrollmentDate,
                program,
                gpa
            };

            try {
                const response = await fetch('student.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: new URLSearchParams(data)
                });
                const text = await response.text();
                console.log('Raw response:', text);
                const result = JSON.parse(text);

                if (result.success) {
                    const studentId = result.student_id;
                    const tableBody = document.getElementById('studentsTable');
                    const row = document.createElement('tr');
                    row.setAttribute('data-id', studentId);
                    row.innerHTML = `
                        <td>${studentId}</td>
                        <td>${firstName} ${lastName}</td>
                        <td>${email}</td>
                        <td>${phone || 'N/A'}</td>
                        <td>
                            <button class="btn btn-view" onclick="viewStudent('${studentId}')">View</button>
                            <button class="btn btn-edit" onclick="editStudent('${studentId}')">Edit</button>
                        </td>
                    `;
                    tableBody.appendChild(row);

                    const modal = bootstrap.Modal.getInstance(document.getElementById('addStudentModal'));
                    modal.hide();
                    document.getElementById('addStudentForm').reset();
                    alert('Student added successfully!');
                } else {
                    alert(`Error: ${result.message}`);
                }
            } catch (error) {
                console.error('Add student error:', error);
                alert(`Error: ${error.message}`);
            }
        }

        async function editStudent(studentId) {
            const row = document.querySelector(`tr[data-id="${studentId}"]`);
            if (!row) return;

            const cells = row.cells;
            document.getElementById('editStudentId').value = studentId;
            document.getElementById('editFirstName').value = cells[1].textContent.split(' ')[0];
            document.getElementById('editLastName').value = cells[1].textContent.split(' ')[1] || '';
            document.getElementById('editEmail').value = cells[2].textContent;
            document.getElementById('editPhone').value = cells[3].textContent !== 'N/A' ? cells[3].textContent : '';

            const modal = new bootstrap.Modal(document.getElementById('editStudentModal'));
            modal.show();
        }

        async function saveStudent() {
            const studentId = document.getElementById('editStudentId').value;
            const firstName = document.getElementById('editFirstName').value;
            const lastName = document.getElementById('editLastName').value;
            const email = document.getElementById('editEmail').value;
            const phone = document.getElementById('editPhone').value;

            if (!studentId || !firstName || !lastName || !email) {
                alert('Please fill in all required fields.');
                return;
            }

            const data = {
                action: 'edit',
                student_id: studentId,
                first_name: firstName,
                last_name: lastName,
                email,
                phone
            };

            try {
                const response = await fetch('student.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: new URLSearchParams(data)
                });
                const text = await response.text();
                console.log('Raw response:', text);
                const result = JSON.parse(text);

                if (result.success) {
                    const row = document.querySelector(`tr[data-id="${studentId}"]`);
                    row.cells[1].textContent = `${firstName} ${lastName}`;
                    row.cells[2].textContent = email;
                    row.cells[3].textContent = phone || 'N/A';

                    const modal = bootstrap.Modal.getInstance(document.getElementById('editStudentModal'));
                    modal.hide();
                    alert('Student updated successfully!');
                } else {
                    alert(`Error: ${result.message}`);
                }
            } catch (error) {
                console.error('Edit student error:', error);
                alert(`Error: ${error.message}`);
            }
        }

        async function viewStudent(studentId) {
            try {
                const response = await fetch('student.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: new URLSearchParams({ action: 'view', student_id: studentId })
                });
                const text = await response.text();
                console.log('Raw response:', text);
                const result = JSON.parse(text);

                if (result.success) {
                    const student = result.student;
                    document.getElementById('profileName').textContent = `${student.first_name} ${student.last_name}`;
                    document.getElementById('profileId').textContent = student.student_id;
                    document.getElementById('profileEmail').textContent = student.email;
                    document.getElementById('profilePhone').textContent = student.phone || 'N/A';
                    document.getElementById('profileProgram').textContent = student.program || 'N/A';
                    document.getElementById('profileGPA').textContent = student.gpa || 'N/A';
                    document.getElementById('profileStatus').textContent = student.status || 'N/A';

                    const appHistory = document.getElementById('applicationHistory');
                    appHistory.innerHTML = result.applications.length ? result.applications.map(app => 
                        `<li>Applied to ${app.scholarship_name} on ${app.submission_date} (Status: ${app.status})</li>`
                    ).join('') : '<li>No applications found</li>';

                    const schHistory = document.getElementById('scholarshipHistory');
                    schHistory.innerHTML = result.scholarship_history.length ? result.scholarship_history.map(sch => 
                        `<li>Awarded ${sch.name} on ${sch.submission_date}</li>`
                    ).join('') : '<li>No scholarships awarded</li>';

                    const modal = new bootstrap.Modal(document.getElementById('viewStudentModal'));
                    modal.show();
                } else {
                    alert(`Error: ${result.message}`);
                }
            } catch (error) {
                console.error('View student error:', error);
                alert(`Error: ${error.message}`);
            }
        }

        document.getElementById('addStudentModal').addEventListener('hidden.bs.modal', () => {
            document.getElementById('addStudentForm').reset();
        });

        document.getElementById('editStudentModal').addEventListener('hidden.bs.modal', () => {
            document.getElementById('editStudentForm').reset();
        });
    </script>
</body>
</html>