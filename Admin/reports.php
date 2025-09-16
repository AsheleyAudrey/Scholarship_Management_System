<?php
include "../Database/db.php";
session_start();

$applicationsData = [];
$fundData = [];

// Applications vs Approvals
$result = $conn->query("
  SELECT status, COUNT(*) as count
  FROM Applications
  GROUP BY status
");
while ($row = $result->fetch_assoc()) {
  $applicationsData[$row['status']] = $row['count'];
}

// Fund Distribution by Student Program
$result2 = $conn->query("
  SELECT st.program AS program, SUM(sc.amount) AS total
  FROM Applications a
  JOIN Students st ON a.student_id = st.student_id
  JOIN Scholarships sc ON a.scholarship_id = sc.scholarship_id
  WHERE a.status IN ('Approved','Accepted')
  GROUP BY st.program
");
while ($row2 = $result2->fetch_assoc()) {
  $fundData[$row2['program']] = $row2['total'];
}
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
  <div class="page-header">
    <h1>Reports & Analytics</h1>
  </div>

  <!-- Applications vs Approvals Chart -->
  <div class="chart-section">
    <h3>Applications vs Approvals</h3>
    <canvas id="applicationsChart"></canvas>
    <div class="export-buttons">
      <button class="btn btn-export-pdf" onclick="exportChartPDF('applicationsChart','Applications vs Approvals')">Export as PDF</button>
      <button class="btn btn-export-excel" onclick="exportChartExcel(applicationsData,'Applications vs Approvals')">Export as Excel</button>
    </div>
  </div>

  <!-- Fund Distribution Pie Chart -->
  <div class="chart-section">
    <h3>Fund Distribution</h3>
    <canvas id="fundDistributionChart"></canvas>
    <div class="export-buttons">
      <button class="btn btn-export-pdf" onclick="exportChartPDF('fundDistributionChart','Fund Distribution')">Export as PDF</button>
      <button class="btn btn-export-excel" onclick="exportChartExcel(fundData,'Fund Distribution')">Export as Excel</button>
    </div>
  </div>
</div>

<script>
  // PHP → JS data
  const applicationsData = <?php echo json_encode($applicationsData); ?>;
  const fundData = <?php echo json_encode($fundData); ?>;

  // Applications Chart
  new Chart(document.getElementById('applicationsChart'), {
    type: 'bar',
    data: {
      labels: Object.keys(applicationsData),
      datasets: [{
        label: 'Applications',
        data: Object.values(applicationsData),
        backgroundColor: '#509CDB'
      }]
    }
  });

  // Fund Distribution Chart
  new Chart(document.getElementById('fundDistributionChart'), {
    type: 'pie',
    data: {
      labels: Object.keys(fundData),
      datasets: [{
        data: Object.values(fundData),
        backgroundColor: ['#509CDB','#28a745','#ffc107','#dc3545']
      }]
    }
  });

  // Export PDF
  function exportChartPDF(chartId, title) {
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF();
    doc.text(title, 10, 10);
    const canvas = document.getElementById(chartId);
    const imgData = canvas.toDataURL("image/png", 1.0);
    doc.addImage(imgData, 'PNG', 10, 20, 180, 100);
    doc.save(title + ".pdf");
  }

  // Export Excel
  function exportChartExcel(data, title) {
    const ws = XLSX.utils.json_to_sheet(Object.entries(data).map(([k,v]) => ({ Category:k, Value:v })));
    const wb = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(wb, ws, "Report");
    XLSX.writeFile(wb, title + ".xlsx");
  }
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php $conn->close(); ?>
