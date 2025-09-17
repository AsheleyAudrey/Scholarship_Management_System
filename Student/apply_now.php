<?php 
include "../Database/db.php";

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Verify database selection
if (!$conn->select_db($dbname)) {
    die("Database not found: $dbname");
}

// Check if necessary tables exist
$tablesToCheck = ['Users', 'Students', 'Scholarships', 'Applications', 'Document', 'Notifications'];
foreach ($tablesToCheck as $table) {
    if ($conn->query("SHOW TABLES LIKE '$table'")->num_rows == 0) {
        die("$table table not found in database: $dbname");
    }
}
session_start();
// Assume logged-in student (Amber David, user_id: 4, student_id: 1)
$user_id = $_SESSION['user_id'];
$studentQuery = "SELECT student_id FROM Students WHERE user_id = ?";
$stmt = $conn->prepare($studentQuery);
if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}
$stmt->bind_param("i", $user_id);
if (!$stmt->execute()) {
    die("Execute failed: " . $stmt->error);
}
$studentResult = $stmt->get_result();
$student = $studentResult->num_rows > 0 ? $studentResult->fetch_assoc() : null;
if (!$student) {
    die("Student not found for user_id: $user_id");
}
$student_id = $student['student_id'];

// Fetch open scholarships for the dropdown
$scholarshipsQuery = "SELECT scholarship_id, name FROM Scholarships WHERE status = 'Open' AND application_end >= CURDATE()";
$scholarshipsResult = $conn->query($scholarshipsQuery);
if (!$scholarshipsResult) {
    die("Query failed: " . $conn->error);
}

// Handle form submission
$successMessage = '';
$errorMessage = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate scholarship selection
    $scholarship_id = isset($_POST['scholarship']) ? intval($_POST['scholarship']) : 0;
    if ($scholarship_id <= 0) {
        $errorMessage = "Please select a valid scholarship.";
    } else {
        // Replace this part in your code:
        $files = ['transcript', 'recommendation', 'financial'];
        $uploadedFiles = [];
        $docTypes = ['Transcript', 'Recommendation Letter', 'Financial Statement'];

        // Upload Function to your server
        function uploadToServer($fileTmpPath, $fileName, $fileType) {
            $url = "https://3.255.226.198.sslip.io/image/upload";

            // The body attribute must be "image"
            $cfile = new CURLFile($fileTmpPath, $fileType, $fileName);
            $postData = ["image" => $cfile];

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            $result = curl_exec($ch);
            $error  = curl_error($ch);
            curl_close($ch);

            if ($error) {
                return false;
            }

            // Response contains "imageUrl" instead of "url"
            $response = json_decode($result, true);
            return $response['imageUrl'] ?? false;
        }


        foreach ($files as $index => $fileKey) {
            if (isset($_FILES[$fileKey]) && $_FILES[$fileKey]['error'] === UPLOAD_ERR_OK) {
                $file = $_FILES[$fileKey];
                $fileType = $file['type'];
                $fileName = basename($file['name']);

                // Validate file type
                if (!in_array($fileType, [
                    'application/pdf',
                    'application/msword',
                    'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
                ])) {
                    $errorMessage = "Invalid file type for {$docTypes[$index]}. Only PDF, DOC, and DOCX are allowed.";
                    break;
                }

                // Upload to your custom server
                $url = uploadToServer($file['tmp_name'], $fileName, $fileType);
                if (!$url) {
                    $errorMessage = "Failed to upload {$docTypes[$index]} to server.";
                    break;
                }

                // Save uploaded file URL
                $uploadedFiles[] = ['url' => $url, 'type' => $docTypes[$index]];
            }
        }


        // If no error, proceed to save data into database
        if (empty($errorMessage)) {
            // Start a transaction
            $conn->begin_transaction();

            try {
                // Extract uploaded file URLs
                $transcriptUrl = null;
                $recommendationUrl = null;
                $financialUrl = null;

                foreach ($uploadedFiles as $file) {
                    if ($file['type'] === 'Transcript') {
                        $transcriptUrl = $file['url'];
                    } elseif ($file['type'] === 'Recommendation Letter') {
                        $recommendationUrl = $file['url'];
                    } elseif ($file['type'] === 'Financial Statement') {
                        $financialUrl = $file['url'];
                    }
                }

                if (!$transcriptUrl) {
                    throw new Exception("Transcript is required.");
                }

                // Insert application into Applications table
                $applicationStmt = $conn->prepare("
                    INSERT INTO Applications 
                        (student_id, scholarship_id, submission_date, document_url, finantial_statement_url, recommendation_letter_url) 
                    VALUES (?, ?, NOW(), ?, ?, ?)
                ");
                if (!$applicationStmt) {
                    throw new Exception("Prepare failed for Applications insert: " . $conn->error);
                }

                $applicationStmt->bind_param("iisss", $student_id, $scholarship_id, $transcriptUrl, $financialUrl, $recommendationUrl);

                if (!$applicationStmt->execute()) {
                    throw new Exception("Failed to insert application: " . $applicationStmt->error);
                }
                $applicationStmt->close();

                // Commit the transaction
                $conn->commit();
                $successMessage = "Application submitted successfully!";
            } catch (Exception $e) {
                // Rollback the transaction on error
                $conn->rollback();
                $errorMessage = "Error submitting application: " . $e->getMessage();
            }
        }

    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Apply Now</title>
  
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
    .progress-indicator {
      display: flex;
      justify-content: space-between;
      max-width: 800px;
      margin: 0 auto 20px;
    }
    .progress-step {
      flex: 1;
      text-align: center;
      position: relative;
    }
    .progress-step .step-circle {
      width: 30px;
      height: 30px;
      background-color: #ced4da;
      color: #ffffff;
      border-radius: 50%;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      font-size: 14px;
      margin-bottom: 5px;
    }
    .progress-step.active .step-circle {
      background-color: #509CDB;
    }
    .progress-step .step-label {
      font-size: 12px;
      color: #666;
    }
    .progress-step.active .step-label {
      color: #509CDB;
      font-weight: 600;
    }
    .progress-step:not(:last-child)::after {
      content: '';
      position: absolute;
      top: 14px;
      left: 50%;
      width: 100%;
      height: 2px;
      background-color: #ced4da;
      z-index: -1;
    }
    .progress-step.active:not(:last-child)::after {
      background-color: #509CDB;
    }
    .application-form {
      background-color: #ffffff;
      border-radius: 8px;
      padding: 20px;
      box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
      max-width: 800px;
      margin: 0 auto;
    }
    .application-form h2 {
      font-size: 20px;
      font-weight: 600;
      color: #152259;
      margin-bottom: 20px;
    }
    .application-form .form-label {
      font-weight: 500;
      color: #333;
    }
    .application-form .form-control,
    .application-form .form-select {
      border-radius: 5px;
      border: 1px solid #ced4da;
      box-shadow: none;
    }
    .application-form .form-control:focus,
    .application-form .form-select:focus {
      border-color: #509CDB;
      box-shadow: 0 0 5px rgba(80, 156, 219, 0.3);
    }
    .application-form .form-section {
      margin-bottom: 20px;
    }
    .application-form .form-section h3 {
      font-size: 16px;
      font-weight: 600;
      color: #152259;
      margin-bottom: 15px;
    }
    .application-form .btn-nav {
      background-color: #6c757d;
      border: none;
      padding: 8px 20px;
      font-size: 14px;
    }
    .application-form .btn-nav:hover {
      background-color: #5a6268;
    }
    .application-form .btn-submit {
      background-color: #509CDB;
      border: none;
      padding: 10px 20px;
      font-size: 16px;
      font-weight: 500;
    }
    .application-form .btn-submit:hover {
      background-color: #408CCB;
    }
    .modal-content {
      border-radius: 8px;
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
      <h1>Apply for a Scholarship</h1>
    </div>

    <!-- Display success or error message -->
    <?php if ($successMessage): ?>
      <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?php echo htmlspecialchars($successMessage); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    <?php elseif ($errorMessage): ?>
      <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?php echo htmlspecialchars($errorMessage); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    <?php endif; ?>

    <!-- Progress Indicator -->
    <div class="progress-indicator">
      <div class="progress-step active">
        <div class="step-circle">1</div>
        <div class="step-label">Scholarship Selection</div>
      </div>
      <div class="progress-step">
        <div class="step-circle">2</div>
        <div class="step-label">Document Upload</div>
      </div>
      <div class="progress-step">
        <div class="step-circle">3</div>
        <div class="step-label">Terms & Submit</div>
      </div>
    </div>

    <!-- Application Form -->
    <div class="application-form">
      <h2>Scholarship Application Form</h2>
      <form id="applicationForm" method="POST" enctype="multipart/form-data">
        <!-- Step 1: Scholarship Selection -->
        <div class="form-step" id="step1">
          <div class="form-section">
            <h3>Step 1: Select a Scholarship</h3>
            <div class="mb-3">
              <label for="scholarship" class="form-label">Select Scholarship</label>
              <select class="form-select" id="scholarship" name="scholarship" required>
                <option value="" disabled selected>Select a scholarship</option>
                <?php while ($row = $scholarshipsResult->fetch_assoc()): ?>
                  <option value="<?php echo $row['scholarship_id']; ?>">
                    <?php echo htmlspecialchars($row['name']); ?>
                  </option>
                <?php endwhile; ?>
              </select>
            </div>
          </div>
          <div class="text-end">
            <button type="button" class="btn btn-nav" onclick="nextStep(1)">Next</button>
          </div>
        </div>

        <!-- Step 2: Document Upload (previously Step 3) -->
        <div class="form-step" id="step2" style="display: none;">
          <div class="form-section">
            <h3>Step 2: Upload Documents</h3>
            <div class="mb-3">
              <label for="transcript" class="form-label">Transcript</label>
              <input type="file" class="form-control" id="transcript" name="transcript" accept=".pdf,.doc,.docx">
            </div>
            <div class="mb-3">
              <label for="recommendation" class="form-label">Recommendation Letter</label>
              <input type="file" class="form-control" id="recommendation" name="recommendation" accept=".pdf,.doc,.docx">
            </div>
            <div class="mb-3">
              <label for="financial" class="form-label">Financial Statement (if applicable)</label>
              <input type="file" class="form-control" id="financial" name="financial" accept=".pdf,.doc,.docx">
            </div>
          </div>
          <div class="d-flex justify-content-between">
            <button type="button" class="btn btn-nav" onclick="prevStep(2)">Previous</button>
            <button type="button" class="btn btn-nav" onclick="nextStep(2)">Next</button>
          </div>
        </div>

        <!-- Step 3: Terms and Submission (previously Step 4) -->
        <div class="form-step" id="step3" style="display: none;">
          <div class="form-section">
            <h3>Step 3: Terms and Submission</h3>
            <div class="mb-3 form-check">
              <input type="checkbox" class="form-check-input" id="terms" name="terms" required>
              <label class="form-check-label" for="terms">I agree to the terms and conditions of the scholarship application.</label>
            </div>
          </div>
          <div class="text-center">
            <button type="button" class="btn btn-nav me-2" onclick="prevStep(3)">Previous</button>
            <button type="button" class="btn btn-submit" data-bs-toggle="modal" data-bs-target="#confirmModal">Submit Application</button>
          </div>
        </div>
      </form>
    </div>

    <!-- Confirmation Modal -->
    <div class="modal fade" id="confirmModal" tabindex="-1" aria-labelledby="confirmModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="confirmModalLabel">Confirm Submission</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            Are you sure you want to submit your scholarship application? Please ensure all details are correct.
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="button" class="btn btn-primary" onclick="document.getElementById('applicationForm').submit()">Confirm</button>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    function nextStep(currentStep) {
      document.getElementById('step' + currentStep).style.display = 'none';
      const nextStep = currentStep + 1;
      document.getElementById('step' + nextStep).style.display = 'block';

      // Update progress indicator
      document.querySelector('.progress-step:nth-child(' + currentStep + ')').classList.remove('active');
      document.querySelector('.progress-step:nth-child(' + nextStep + ')').classList.add('active');
    }

    function prevStep(currentStep) {
      document.getElementById('step' + currentStep).style.display = 'none';
      const prevStep = currentStep - 1;
      document.getElementById('step' + prevStep).style.display = 'block';

      // Update progress indicator
      document.querySelector('.progress-step:nth-child(' + currentStep + ')').classList.remove('active');
      document.querySelector('.progress-step:nth-child(' + prevStep + ')').classList.add('active');
    }
  </script>

  <!-- Loader Modal -->
<div class="modal fade" id="loadingModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content text-center p-4">
      <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
        <span class="visually-hidden">Loading...</span>
      </div>
      <p class="mt-3 mb-0">Uploading files... Please wait.</p>
    </div>
  </div>
</div>

<script>
  // Show loader when submitting
  document.getElementById('applicationForm').addEventListener('submit', function() {
    var loaderModal = new bootstrap.Modal(document.getElementById('loadingModal'), {
      backdrop: 'static',
      keyboard: false
    });
    loaderModal.show();
  });
</script>

</body>
</html>

<?php
// Close database connection
$conn->close();
?>
