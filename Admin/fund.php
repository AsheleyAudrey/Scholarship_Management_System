<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Fund Allocation</title>
  
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

    /* Allocate Funds Button */
    .page-header .btn-allocate {
      background-color: #509CDB; /* Match active item color */
      border: none;
      padding: 10px 20px;
      font-size: 16px;
      color: #ffffff;
    }

    .page-header .btn-allocate:hover {
      background-color: #408CCB;
    }

    /* Fund Balance Section */
    .fund-balance {
      background-color: #ffffff;
      border-radius: 8px;
      padding: 20px;
      box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
      margin-bottom: 20px;
    }

    .fund-balance h3 {
      font-size: 18px;
      font-weight: 600;
      color: #152259; /* Match sidebar color */
      margin-bottom: 15px;
    }

    .fund-balance p {
      font-size: 24px;
      font-weight: 600;
      color: #333;
      margin: 0;
    }

    /* Budget Allocation Section */
    .budget-allocation {
      background-color: #ffffff;
      border-radius: 8px;
      padding: 20px;
      box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
      margin-bottom: 20px;
    }

    .budget-allocation h3 {
      font-size: 18px;
      font-weight: 600;
      color: #152259; /* Match sidebar color */
      margin-bottom: 15px;
    }

    .budget-allocation .progress {
      height: 25px;
      border-radius: 5px;
      margin-bottom: 10px;
    }

    .budget-allocation .progress-bar {
      background-color: #509CDB; /* Match active item color */
    }

    .budget-allocation .progress-label {
      font-size: 14px;
      color: #333;
      margin-bottom: 5px;
    }

    /* Transaction History Table */
    .transaction-history {
      background-color: #ffffff;
      border-radius: 8px;
      padding: 20px;
      box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
      margin-bottom: 20px;
    }

    .transaction-history h3 {
      font-size: 18px;
      font-weight: 600;
      color: #152259; /* Match sidebar color */
      margin-bottom: 15px;
    }

    .transaction-history .table {
      margin-bottom: 0;
    }

    .transaction-history .table th {
      background-color: #152259; /* Match sidebar color */
      color: #ffffff;
    }

    .transaction-history .table td {
      vertical-align: middle;
    }

    .transaction-history .btn-report {
      background-color: #17a2b8; /* Cyan for report */
      border: none;
      font-size: 14px;
      padding: 5px 10px;
    }

    .transaction-history .btn-report:hover {
      background-color: #138496;
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

    .modal-body .form-control,
    .modal-body .form-select {
      border-radius: 5px;
      border: 1px solid #ced4da;
      box-shadow: none;
    }

    .modal-body .form-control:focus,
    .modal-body .form-select:focus {
      border-color: #509CDB; /* Match active item color */
      box-shadow: 0 0 5px rgba(80, 156, 219, 0.3);
    }

    .modal-footer .btn-save {
      background-color: #509CDB; /* Match active item color */
      border: none;
    }

    .modal-footer .btn-save:hover {
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
        <h1>Fund Allocation</h1>
        <button class="btn btn-allocate" data-bs-toggle="modal" data-bs-target="#allocateFundsModal">
          <i class="bi bi-plus-circle me-2"></i> Allocate Funds
        </button>
      </div>

      <!-- Fund Balance -->
      <div class="fund-balance">
        <h3>Total Fund Balance</h3>
        <p id="fundBalance">$50,000</p>
      </div>

      <!-- Budget Allocation -->
      <div class="budget-allocation">
        <h3>Budget Allocation by Scholarship</h3>
        <div id="budgetProgress">
          <div class="progress-label">Merit-Based Scholarship: $10,000 / $15,000</div>
          <div class="progress">
            <div class="progress-bar" role="progressbar" style="width: 66.67%" aria-valuenow="66.67" aria-valuemin="0" aria-valuemax="100">66.67%</div>
          </div>
          <div class="progress-label">Need-Based Scholarship: $8,000 / $10,000</div>
          <div class="progress">
            <div class="progress-bar" role="progressbar" style="width: 80%" aria-valuenow="80" aria-valuemin="0" aria-valuemax="100">80%</div>
          </div>
          <div class="progress-label">STEM Scholarship: $5,000 / $12,000</div>
          <div class="progress">
            <div class="progress-bar" role="progressbar" style="width: 41.67%" aria-valuenow="41.67" aria-valuemin="0" aria-valuemax="100">41.67%</div>
          </div>
        </div>
      </div>

      <!-- Transaction History -->
      <div class="transaction-history">
        <h3>Transaction History</h3>
        <table class="table table-hover">
          <thead>
            <tr>
              <th>Date</th>
              <th>Type</th>
              <th>Scholarship</th>
              <th>Student</th>
              <th>Amount</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody id="transactionTable">
            <tr>
              <td>2025-04-05</td>
              <td>Disbursement</td>
              <td>STEM Scholarship</td>
              <td>Emily Johnson</td>
              <td>$7,000</td>
              <td>
                <button class="btn btn-report" onclick="generateReport(this)">Generate Report</button>
              </td>
            </tr>
            <tr>
              <td>2025-04-01</td>
              <td>Allocation</td>
              <td>Merit-Based Scholarship</td>
              <td>-</td>
              <td>$15,000</td>
              <td>
                <button class="btn btn-report" onclick="generateReport(this)">Generate Report</button>
              </td>
            </tr>
            <tr>
              <td>2025-03-30</td>
              <td>Allocation</td>
              <td>Need-Based Scholarship</td>
              <td>-</td>
              <td>$10,000</td>
              <td>
                <button class="btn btn-report" onclick="generateReport(this)">Generate Report</button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- Allocate Funds Modal -->
  <div class="modal fade" id="allocateFundsModal" tabindex="-1" aria-labelledby="allocateFundsModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="allocateFundsModalLabel">Allocate Funds</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <form id="allocateFundsForm">
            <div class="mb-3">
              <label for="allocationType" class="form-label">Allocation Type</label>
              <select class="form-select" id="allocationType" required>
                <option value="" disabled selected>Select type</option>
                <option value="scholarship">To Scholarship</option>
                <option value="student">To Student</option>
              </select>
            </div>
            <div class="mb-3" id="scholarshipField">
              <label for="scholarship" class="form-label">Scholarship</label>
              <select class="form-select" id="scholarship">
                <option value="Merit-Based Scholarship">Merit-Based Scholarship</option>
                <option value="Need-Based Scholarship">Need-Based Scholarship</option>
                <option value="STEM Scholarship">STEM Scholarship</option>
              </select>
            </div>
            <div class="mb-3" id="studentField" style="display: none;">
              <label for="student" class="form-label">Student</label>
              <select class="form-select" id="student">
                <option value="John Doe">John Doe</option>
                <option value="Jane Smith">Jane Smith</option>
                <option value="Emily Johnson">Emily Johnson</option>
              </select>
            </div>
            <div class="mb-3">
              <label for="amount" class="form-label">Amount</label>
              <input type="number" class="form-control" id="amount" placeholder="$" required>
            </div>
          </form>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          <button type="button" class="btn btn-save" onclick="allocateFunds()">Allocate</button>
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