<?php
ob_start();
include "../Database/db.php";

// Handle AJAX requests
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action'])) {
    ob_clean();
    header('Content-Type: application/json');

    // Add or Edit Scholarship
    if ($_POST['action'] == 'add' || $_POST['action'] == 'edit') {
        $scholarship_id = isset($_POST['scholarship_id']) && !empty($_POST['scholarship_id']) ? intval($_POST['scholarship_id']) : null;
        $name = trim($_POST['name']);
        $amount = floatval($_POST['amount']);
        $gpa = !empty($_POST['gpa']) ? floatval($_POST['gpa']) : null;
        $other_criteria = !empty($_POST['other_criteria']) ? trim($_POST['other_criteria']) : null;
        $application_start = !empty($_POST['application_start']) ? $_POST['application_start'] : null;
        $application_end = !empty($_POST['application_end']) ? $_POST['application_end'] : null;
        $status = !empty($_POST['status']) && in_array($_POST['status'], ['Open', 'Closed', 'Awarded']) ? $_POST['status'] : 'Closed';

        if (empty($name) || empty($amount) || empty($application_end)) {
            echo json_encode(['success' => false, 'message' => 'Name, amount, and application end date are required.']);
            exit;
        }

        try {
            if ($_POST['action'] == 'add') {
                $sql = "INSERT INTO Scholarships (name, amount, gpa, other_criteria, application_start, application_end, status) VALUES (?, ?, ?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("sdsssss", $name, $amount, $gpa, $other_criteria, $application_start, $application_end, $status);
            } else {
                $sql = "UPDATE Scholarships SET name = ?, amount = ?, gpa = ?, other_criteria = ?, application_start = ?, application_end = ?, status = ? WHERE scholarship_id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("sdsssssi", $name, $amount, $gpa, $other_criteria, $application_start, $application_end, $status, $scholarship_id);
            }

            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'scholarship_id' => $_POST['action'] == 'add' ? $conn->insert_id : $scholarship_id]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Database error: ' . $stmt->error]);
            }
            $stmt->close();
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
        exit;
    }

    // Delete Scholarship
    if ($_POST['action'] == 'delete') {
        $scholarship_id = intval($_POST['scholarship_id']);
        try {
            $sql = "DELETE FROM Scholarships WHERE scholarship_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $scholarship_id);

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

    // Toggle Status
    if ($_POST['action'] == 'toggle') {
        $scholarship_id = intval($_POST['scholarship_id']);
        $status = $_POST['status'];
        if (!in_array($status, ['Open', 'Closed', 'Awarded'])) {
            echo json_encode(['success' => false, 'message' => 'Invalid status']);
            exit;
        }
        try {
            $sql = "UPDATE Scholarships SET status = ? WHERE scholarship_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("si", $status, $scholarship_id);

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

    echo json_encode(['success' => false, 'message' => 'Invalid action']);
    exit;
}

// Fetch all scholarships for non-AJAX requests
try {
    $sql = "SELECT * FROM Scholarships";
    $result = $conn->query($sql);
    $scholarships = [];
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $scholarships[] = $row;
        }
    }
} catch (Exception $e) {
    $scholarships = [];
    error_log("Error fetching scholarships: " . $e->getMessage());
}

ob_end_flush();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Manage Scholarships</title>
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
    .scholarships-table {
      background-color: #ffffff;
      border-radius: 8px;
      padding: 20px;
      box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
      margin-bottom: 20px;
    }
    .scholarships-table h3 {
      font-size: 18px;
      font-weight: 600;
      color: #152259;
      margin-bottom: 15px;
    }
    .scholarships-table .table {
      margin-bottom: 0;
    }
    .scholarships-table .table th {
      background-color: #152259;
      color: #ffffff;
    }
    .scholarships-table .table td {
      vertical-align: middle;
    }
    .scholarships-table .table .badge {
      font-size: 12px;
    }
    .scholarships-table .btn-edit {
      background-color: #17a2b8;
      border: none;
      margin-right: 5px;
      font-size: 14px;
      padding: 5px 10px;
    }
    .scholarships-table .btn-edit:hover {
      background-color: #138496;
    }
    .scholarships-table .btn-delete {
      background-color: #dc3545;
      border: none;
      font-size: 14px;
      padding: 5px 10px;
    }
    .scholarships-table .btn-delete:hover {
      background-color: #c82333;
    }
    .scholarships-table .btn-toggle {
      font-size: 14px;
      padding: 5px 10px;
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
  </style>
</head>
<body>
  <?php include 'sidebar.php'; ?>
  <div class="main-content">
    <div class="page-header">
      <h1>Manage Scholarships</h1>
      <button class="btn btn-add" data-bs-toggle="modal" data-bs-target="#addScholarshipModal">
        <i class="bi bi-plus-circle me-2"></i> Add New Scholarship
      </button>
    </div>
    <div class="scholarships-table">
      <h3>All Scholarships</h3>
      <table class="table table-hover">
        <thead>
          <tr>
            <th>Name</th>
            <th>Amount</th>
            <th>Criteria</th>
            <th>Application Window</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody id="scholarshipsTable">
          <?php foreach ($scholarships as $scholarship): ?>
            <?php
              $scholarship_id = isset($scholarship['scholarship_id']) && $scholarship['scholarship_id'] > 0 ? $scholarship['scholarship_id'] : 0;
              $status = isset($scholarship['status']) && in_array($scholarship['status'], ['Open', 'Closed', 'Awarded']) ? $scholarship['status'] : 'Closed';
            ?>
            <tr data-id="<?php echo htmlspecialchars($scholarship_id); ?>">
              <td><?php echo isset($scholarship['name']) ? htmlspecialchars($scholarship['name']) : 'N/A'; ?></td>
              <td>$<?php echo isset($scholarship['amount']) ? number_format($scholarship['amount'], 2) : '0.00'; ?></td>
              <td>
                <?php
                  $criteria = [];
                  if (isset($scholarship['gpa']) && $scholarship['gpa'] !== null) {
                      $criteria[] = "GPA: " . $scholarship['gpa'];
                  }
                  if (isset($scholarship['other_criteria']) && $scholarship['other_criteria'] !== null) {
                      $criteria[] = $scholarship['other_criteria'];
                  }
                  echo htmlspecialchars(!empty($criteria) ? implode(', ', $criteria) : 'None');
                ?>
              </td>
              <td>
                <?php
                  $start = isset($scholarship['application_start']) ? $scholarship['application_start'] : '';
                  $end = isset($scholarship['application_end']) ? $scholarship['application_end'] : '';
                  echo htmlspecialchars($start && $end ? "$start to $end" : ($end ? "Ends $end" : 'N/A'));
                ?>
              </td>
              <td>
                <span class="badge bg-<?php echo $status == 'Open' ? 'success' : ($status == 'Awarded' ? 'info' : 'danger'); ?>">
                  <?php echo htmlspecialchars($status); ?>
                </span>
              </td>
              <td>
                <?php if ($scholarship_id > 0): ?>
                  <button class="btn btn-edit" onclick="editScholarship(<?php echo $scholarship_id; ?>)">Edit</button>
                  <button class="btn btn-delete" onclick="deleteScholarship(<?php echo $scholarship_id; ?>)">Delete</button>
                  <button class="btn btn-<?php echo $status == 'Open' ? 'danger' : 'success'; ?> btn-toggle" onclick="toggleApplicationWindow(<?php echo $scholarship_id; ?>, '<?php echo $status == 'Open' ? 'Closed' : 'Open'; ?>')">
                    <?php echo $status == 'Open' ? 'Close' : 'Open'; ?>
                  </button>
                <?php else: ?>
                  <span>Invalid ID</span>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

  <div class="modal fade" id="addScholarshipModal" tabindex="-1" aria-labelledby="addScholarshipModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="addScholarshipModalLabel">Add New Scholarship</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <form id="scholarshipForm">
            <input type="hidden" id="scholarshipId">
            <div class="mb-3">
              <label for="scholarshipName" class="form-label">Scholarship Name</label>
              <input type="text" class="form-control" id="scholarshipName" required>
            </div>
            <div class="mb-3">
              <label for="scholarshipAmount" class="form-label">Amount</label>
              <input type="number" class="form-control" id="scholarshipAmount" placeholder="$" required>
            </div>
            <div class="mb-3">
              <label for="criteriaGPA" class="form-label">Minimum GPA</label>
              <input type="number" step="0.1" class="form-control" id="criteriaGPA" placeholder="e.g., 3.5">
            </div>
            <div class="mb-3">
              <label for="criteriaOther" class="form-label">Other Criteria</label>
              <select class="form-select" id="criteriaOther">
                <option value="">None</option>
                <option value="Financial Need">Financial Need</option>
                <option value="Leadership">Leadership</option>
                <option value="STEM Major">STEM Major</option>
                <option value="Community Service">Community Service</option>
              </select>
            </div>
            <div class="row">
              <div class="col-md-6 mb-3">
                <label for="applicationStart" class="form-label">Application Start Date</label>
                <input type="date" class="form-control" id="applicationStart">
              </div>
              <div class="col-md-6 mb-3">
                <label for="applicationEnd" class="form-label">Application End Date</label>
                <input type="date" class="form-control" id="applicationEnd" required>
              </div>
            </div>
          </form>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          <button type="button" class="btn btn-save" onclick="saveScholarship()">Save</button>
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    let editMode = false;

    function resetModal() {
      document.getElementById('addScholarshipModalLabel').textContent = 'Add New Scholarship';
      document.getElementById('scholarshipForm').reset();
      document.getElementById('scholarshipId').value = '';
      editMode = false;
    }

    async function editScholarship(scholarship_id) {
      if (!scholarship_id) {
        alert('Invalid scholarship ID');
        return;
      }
      editMode = true;
      const row = document.querySelector(`tr[data-id="${scholarship_id}"]`);
      if (!row) return;

      const cells = row.cells;
      document.getElementById('addScholarshipModalLabel').textContent = 'Edit Scholarship';
      document.getElementById('scholarshipId').value = scholarship_id;
      document.getElementById('scholarshipName').value = cells[0].textContent !== 'N/A' ? cells[0].textContent : '';
      document.getElementById('scholarshipAmount').value = parseFloat(cells[1].textContent.replace('$', '').replace(',', '')) || '';

      const criteria = cells[2].textContent.split(', ').filter(c => c !== 'None');
      const gpaMatch = criteria.find(c => c.startsWith('GPA:'))?.match(/GPA: (\d+\.\d+)/);
      document.getElementById('criteriaGPA').value = gpaMatch ? gpaMatch[1] : '';
      const otherCriteria = criteria.find(c => !c.startsWith('GPA:')) || '';
      document.getElementById('criteriaOther').value = otherCriteria;

      const dates = cells[3].textContent.split(' to ');
      document.getElementById('applicationStart').value = dates[0] !== 'N/A' && dates[0] !== 'Ends' ? dates[0] : '';
      document.getElementById('applicationEnd').value = dates[1] !== 'N/A' ? (dates[1] || dates[0].replace('Ends ', '')) : '';

      const modal = new bootstrap.Modal(document.getElementById('addScholarshipModal'));
      modal.show();
    }

    async function saveScholarship() {
      const scholarship_id = document.getElementById('scholarshipId').value;
      const name = document.getElementById('scholarshipName').value;
      const amount = document.getElementById('scholarshipAmount').value;
      const gpa = document.getElementById('criteriaGPA').value;
      const otherCriteria = document.getElementById('criteriaOther').value;
      const startDate = document.getElementById('applicationStart').value;
      const endDate = document.getElementById('applicationEnd').value;

      if (!name || !amount || !endDate) {
        alert('Please fill in all required fields (Name, Amount, Application End Date).');
        return;
      }

      const today = new Date();
      const start = startDate ? new Date(startDate) : null;
      const end = endDate ? new Date(endDate) : null;
      const status = start && end && today >= start && today <= end ? 'Open' : 'Closed';

      const data = {
        action: editMode ? 'edit' : 'add',
        scholarship_id: scholarship_id,
        name: name,
        amount: amount,
        gpa: gpa,
        other_criteria: otherCriteria,
        application_start: startDate,
        application_end: endDate,
        status: status
      };

      try {
        const response = await fetch('', {
          method: 'POST',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
          body: new URLSearchParams(data)
        });
        const text = await response.text();
        console.log('Raw response:', text);
        const result = JSON.parse(text);

        if (result.success) {
          const tableBody = document.getElementById('scholarshipsTable');
          const criteriaText = [gpa ? `GPA: ${gpa}` : '', otherCriteria].filter(c => c).join(', ') || 'None';
          const applicationWindow = startDate && endDate ? `${startDate} to ${endDate}` : (endDate ? `Ends ${endDate}` : 'N/A');

          if (editMode) {
            const row = document.querySelector(`tr[data-id="${scholarship_id}"]`);
            row.cells[0].textContent = name;
            row.cells[1].textContent = `$${parseFloat(amount).toLocaleString()}`;
            row.cells[2].textContent = criteriaText;
            row.cells[3].textContent = applicationWindow;
            row.cells[4].innerHTML = `<span class="badge bg-${status === 'Open' ? 'success' : (status === 'Awarded' ? 'info' : 'danger')}">${status}</span>`;
            row.cells[5].innerHTML = `
              <button class="btn btn-edit" onclick="editScholarship(${scholarship_id})">Edit</button>
              <button class="btn btn-delete" onclick="deleteScholarship(${scholarship_id})">Delete</button>
              <button class="btn btn-${status === 'Open' ? 'danger' : 'success'} btn-toggle" onclick="toggleApplicationWindow(${scholarship_id}, '${status === 'Open' ? 'Closed' : 'Open'}')">${status === 'Open' ? 'Close' : 'Open'}</button>
            `;
          } else {
            const row = document.createElement('tr');
            row.setAttribute('data-id', result.scholarship_id);
            row.innerHTML = `
              <td>${name}</td>
              <td>$${parseFloat(amount).toLocaleString()}</td>
              <td>${criteriaText}</td>
              <td>${applicationWindow}</td>
              <td><span class="badge bg-${status === 'Open' ? 'success' : (status === 'Awarded' ? 'info' : 'danger')}">${status}</span></td>
              <td>
                <button class="btn btn-edit" onclick="editScholarship(${result.scholarship_id})">Edit</button>
                <button class="btn btn-delete" onclick="deleteScholarship(${result.scholarship_id})">Delete</button>
                <button class="btn btn-${status === 'Open' ? 'danger' : 'success'} btn-toggle" onclick="toggleApplicationWindow(${result.scholarship_id}, '${status === 'Open' ? 'Closed' : 'Open'}')">${status === 'Open' ? 'Close' : 'Open'}</button>
              </td>
            `;
            tableBody.appendChild(row);
          }

          const modal = bootstrap.Modal.getInstance(document.getElementById('addScholarshipModal'));
          modal.hide();
        } else {
          alert(result.message);
        }
      } catch (error) {
        console.error('Save error:', error);
        alert('Error: ' + error.message);
      }
    }

    async function deleteScholarship(scholarship_id) {
      if (!confirm('Are you sure you want to delete this scholarship?')) return;

      try {
        const response = await fetch('', {
          method: 'POST',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
          body: new URLSearchParams({ action: 'delete', scholarship_id: scholarship_id })
        });
        const text = await response.text();
        console.log('Raw response:', text);

        let result;
        try {
            result = JSON.parse(text);
        } catch (e) {
            console.error('JSON parse error:', e);
            alert('Server returned an invalid response. Check the console for details.');
            return;
        }

        if (result.success) {
          document.querySelector(`tr[data-id="${scholarship_id}"]`).remove();
        } else {
          alert(result.message);
        }
      } catch (error) {
        console.error('Delete error:', error);
        alert('Error: ' + error.message);
      }
    }

    async function toggleApplicationWindow(scholarship_id, newStatus) {
      if (!['Open', 'Closed'].includes(newStatus)) {
        alert('Invalid status');
        return;
      }
      try {
        const response = await fetch('', {
          method: 'POST',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
          body: new URLSearchParams({ action: 'toggle', scholarship_id: scholarship_id, status: newStatus })
        });
        const text = await response.text();
        console.log('Raw response:', text);

        let result;
        try {
            result = JSON.parse(text);
        } catch (e) {
            console.error('JSON parse error:', e);
            alert('Server returned an invalid response. Check the console for details.');
            return;
        }

        if (result.success) {
          const row = document.querySelector(`tr[data-id="${scholarship_id}"]`);
          row.cells[4].innerHTML = `<span class="badge bg-${newStatus === 'Open' ? 'success' : 'danger'}">${newStatus}</span>`;
          row.cells[5].innerHTML = `
            <button class="btn btn-edit" onclick="editScholarship(${scholarship_id})">Edit</button>
            <button class="btn btn-delete" onclick="deleteScholarship(${scholarship_id})">Delete</button>
            <button class="btn btn-${newStatus === 'Open' ? 'danger' : 'success'} btn-toggle" onclick="toggleApplicationWindow(${scholarship_id}, '${newStatus === 'Open' ? 'Closed' : 'Open'}')">${newStatus === 'Open' ? 'Close' : 'Open'}</button>
          `;
        } else {
          alert(result.message);
        }
      } catch (error) {
        console.error('Toggle error:', error);
        alert('Error: ' + error.message);
      }
    }

    document.getElementById('addScholarshipModal').addEventListener('hidden.bs.modal', resetModal);
  </script>
</body>
</html>