<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Apply Now</title>
  
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
      margin-left: 250px;
      padding: 20px;
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

    /* Progress indicator */
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
      background-color: #509CDB; /* Match active item color */
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

    /* Form styling */
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
      color: #152259; /* Match sidebar color */
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
      border-color: #509CDB; /* Match active item color */
      box-shadow: 0 0 5px rgba(80, 156, 219, 0.3);
    }

    .application-form .form-section {
      margin-bottom: 20px;
    }

    .application-form .form-section h3 {
      font-size: 16px;
      font-weight: 600;
      color: #152259; /* Match sidebar color */
      margin-bottom: 15px;
    }

    .application-form .btn-nav {
      background-color: #6c757d; /* Gray for navigation buttons */
      border: none;
      padding: 8px 20px;
      font-size: 14px;
    }

    .application-form .btn-nav:hover {
      background-color: #5a6268;
    }

    .application-form .btn-submit {
      background-color: #509CDB; /* Match active item color */
      border: none;
      padding: 10px 20px;
      font-size: 16px;
      font-weight: 500;
    }

    .application-form .btn-submit:hover {
      background-color: #408CCB; /* Slightly darker shade on hover */
    }

    /* Confirmation popup styling */
    .modal-content {
      border-radius: 8px;
    }

    .modal-header {
      background-color: #152259; /* Match sidebar color */
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
 <!-- Main content -->
    <div class="main-content">
      <!-- Header -->
      <div class="page-header">
        <h1>Apply for a Scholarship</h1>
      </div>

      <!-- Progress Indicator -->
      <div class="progress-indicator">
        <div class="progress-step active">
          <div class="step-circle">1</div>
          <div class="step-label">Scholarship Selection</div>
        </div>
        <div class="progress-step">
          <div class="step-circle">2</div>
          <div class="step-label">Student Details</div>
        </div>
        <div class="progress-step">
          <div class="step-circle">3</div>
          <div class="step-label">Document Upload</div>
        </div>
        <div class="progress-step">
          <div class="step-circle">4</div>
          <div class="step-label">Terms & Submit</div>
        </div>
      </div>

      <!-- Application Form -->
      <div class="application-form">
        <h2>Scholarship Application Form</h2>
        <form id="applicationForm">
          <!-- Step 1: Scholarship Selection -->
          <div class="form-step" id="step1">
            <div class="form-section">
              <h3>Step 1: Select a Scholarship</h3>
              <div class="mb-3">
                <label for="scholarship" class="form-label">Select Scholarship</label>
                <select class="form-select" id="scholarship" required>
                  <option value="" disabled selected>Select a scholarship</option>
                  <option value="merit">Merit-Based Scholarship</option>
                  <option value="need">Need-Based Scholarship</option>
                  <option value="stem">STEM Scholarship</option>
                </select>
              </div>
            </div>
            <div class="text-end">
              <button type="button" class="btn btn-nav" onclick="nextStep(1)">Next</button>
            </div>
          </div>

          <!-- Step 2: Student Details -->
          <div class="form-step" id="step2" style="display: none;">
            <div class="form-section">
              <h3>Step 2: Student Details</h3>
              <div class="row">
                <div class="col-md-6 mb-3">
                  <label for="program" class="form-label">Program of Study</label>
                  <input type="text" class="form-control" id="program" placeholder="e.g., Computer Science" required>
                </div>
                <div class="col-md-6 mb-3">
                  <label for="gpa" class="form-label">Current GPA</label>
                  <input type="number" step="0.01" class="form-control" id="gpa" placeholder="e.g., 3.75" required>
                </div>
              </div>
              <div class="mb-3">
                <label for="achievements" class="form-label">Achievements</label>
                <textarea class="form-control" id="achievements" rows="3" placeholder="List your academic or extracurricular achievements" required></textarea>
              </div>
              <div class="mb-3">
                <label for="motivation" class="form-label">Motivation Statement</label>
                <textarea class="form-control" id="motivation" rows="5" placeholder="Explain why you deserve this scholarship" required></textarea>
              </div>
            </div>
            <div class="d-flex justify-content-between">
              <button type="button" class="btn btn-nav" onclick="prevStep(2)">Previous</button>
              <button type="button" class="btn btn-nav" onclick="nextStep(2)">Next</button>
            </div>
          </div>

          <!-- Step 3: Document Upload -->
          <div class="form-step" id="step3" style="display: none;">
            <div class="form-section">
              <h3>Step 3: Upload Documents</h3>
              <div class="mb-3">
                <label for="transcript" class="form-label">Transcript</label>
                <input type="file" class="form-control" id="transcript" accept=".pdf,.doc,.docx">
              </div>
              <div class="mb-3">
                <label for="recommendation" class="form-label">Recommendation Letter</label>
                <input type="file" class="form-control" id="recommendation" accept=".pdf,.doc,.docx">
              </div>
              <div class="mb-3">
                <label for="financial" class="form-label">Financial Statement (if applicable)</label>
                <input type="file" class="form-control" id="financial" accept=".pdf,.doc,.docx">
              </div>
            </div>
            <div class="d-flex justify-content-between">
              <button type="button" class="btn btn-nav" onclick="prevStep(3)">Previous</button>
              <button type="button" class="btn btn-nav" onclick="nextStep(3)">Next</button>
            </div>
          </div>

          <!-- Step 4: Terms and Submission -->
          <div class="form-step" id="step4" style="display: none;">
            <div class="form-section">
              <h3>Step 4: Terms and Submission</h3>
              <div class="mb-3 form-check">
                <input type="checkbox" class="form-check-input" id="terms" required>
                <label class="form-check-label" for="terms">I agree to the terms and conditions of the scholarship application.</label>
              </div>
            </div>
            <div class="text-center">
              <button type="button" class="btn btn-nav me-2" onclick="prevStep(4)">Previous</button>
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
  </div>

  <!-- Bootstrap JS -->
  <script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
  ></script>
</body>
</html>