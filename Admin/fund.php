<?php
// Use the correct DB connection file
include "../Database/db.php";

// Fetch total funds
$result = $conn->query("SELECT * FROM funds LIMIT 1");
$fund = $result->fetch_assoc();

// Fetch all transactions
$transactions = $conn->query("SELECT t.id, t.date, t.type, s.name AS scholarship_name, t.student, t.amount
                              FROM transactions t
                              LEFT JOIN Scholarships s ON t.scholarship_id = s.scholarship_id
                              ORDER BY t.date DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Reports & Analytics</title>

  <!-- Bootstrap -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"/>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet"/>
  
  <!-- Chart.js -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
  <!-- jsPDF & SheetJS -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>

  <style>
    .main-content { background-color:#f8f9fa; min-height:100vh; }
    .page-header { display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; }
    .page-header h1 { font-size:24px; font-weight:600; color:#333; }
    .chart-section { background:#fff; border-radius:8px; padding:20px; box-shadow:0 2px 5px rgba(0,0,0,0.1); margin-bottom:20px; }
    .chart-section h3 { font-size:18px; font-weight:600; color:#152259; margin-bottom:15px; }
    .chart-section canvas {max-width: 600px;   /* limits width */ max-height: 300px;  /* limits height */margin: 0 auto;     /* center chart */display: block;}
    .export-buttons { display:flex; gap:10px; justify-content:flex-end; margin-top:15px; }
    .btn-export-pdf { background:#dc3545; border:none; padding:8px 15px; font-size:14px; color:#fff; }
    .btn-export-pdf:hover { background:#c82333; }
    .btn-export-excel { background:#28a745; border:none; padding:8px 15px; font-size:14px; color:#fff; }
    .btn-export-excel:hover { background:#218838; }

  </style>
</head>
<body>
<?php include 'sidebar.php'; ?>

  <div class="main-content p-4">
    <h2 class="mb-4">Funds Overview</h2>

    <div class="card shadow-sm p-4 mb-4" style="max-width: 500px;">
      <h5>Total Balance</h5>
      <p class="fs-4 text-success fw-bold">
        $<?= number_format($fund['total_balance'], 2) ?>
      </p>
    </div>

    <h4 class="mb-3">Transactions</h4>
    <table class="table table-bordered table-striped">
      <thead>
        <tr>
          <th>Date</th>
          <th>Type</th>
          <th>Scholarship</th>
          <th>Student</th>
          <th>Amount</th>
        </tr>
      </thead>
      <tbody>
        <?php while ($row = $transactions->fetch_assoc()): ?>
          <tr>
            <td><?= htmlspecialchars($row['date']) ?></td>
            <td><?= htmlspecialchars($row['type']) ?></td>
            <td><?= htmlspecialchars($row['scholarship_name']) ?></td>
            <td><?= htmlspecialchars($row['student']) ?></td>
            <td>$<?= number_format($row['amount'], 2) ?></td>
          </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php $conn->close(); ?>
