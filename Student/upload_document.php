<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Upload Documents</title>
  
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

    /* Upload form styling */
    .upload-form {
      background-color: #ffffff;
      border-radius: 8px;
      padding: 20px;
      box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
      margin-bottom: 20px;
    }

    .upload-form h3 {
      font-size: 18px;
      font-weight: 600;
      color: #152259; /* Match sidebar color */
      margin-bottom: 15px;
    }

    .upload-form .form-label {
      font-weight: 500;
      color: #333;
    }

    .upload-form .form-control {
      border-radius: 5px;
      border: 1px solid #ced4da;
      box-shadow: none;
    }

    .upload-form .form-control:focus {
      border-color: #509CDB; /* Match active item color */
      box-shadow: 0 0 5px rgba(80, 156, 219, 0.3);
    }

    .upload-form .btn-upload {
      background-color: #509CDB; /* Match active item color */
      border: none;
      padding: 8px 20px;
      font-size: 14px;
    }

    .upload-form .btn-upload:hover {
      background-color: #408CCB; /* Slightly darker shade on hover */
    }

    /* Progress bar styling */
    .progress {
      height: 8px;
      margin-top: 10px;
      border-radius: 5px;
      display: none; /* Hidden by default */
    }

    .progress-bar {
      background-color: #509CDB; /* Match active item color */
    }

    /* Success message styling */
    .success-message {
      display: none; /* Hidden by default */
      color: #28a745; /* Green for success */
      font-size: 14px;
      margin-top: 10px;
    }

    /* Uploaded files table */
    .uploaded-files {
      background-color: #ffffff;
      border-radius: 8px;
      padding: 20px;
      box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    }

    .uploaded-files h3 {
      font-size: 18px;
      font-weight: 600;
      color: #152259; /* Match sidebar color */
      margin-bottom: 15px;
    }

    .uploaded-files .table {
      margin-bottom: 0;
    }

    .uploaded-files .table th {
      background-color: #152259; /* Match sidebar color */
      color: #ffffff;
    }

    .uploaded-files .table td {
      vertical-align: middle;
    }

    .uploaded-files .btn-update {
      background-color: #17a2b8; /* Cyan for update */
      border: none;
      margin-right: 5px;
      font-size: 14px;
      padding: 5px 10px;
    }

    .uploaded-files .btn-update:hover {
      background-color: #138496;
    }

    .uploaded-files .btn-delete {
      background-color: #dc3545; /* Red for delete */
      border: none;
      font-size: 14px;
      padding: 5px 10px;
    }

    .uploaded-files .btn-delete:hover {
      background-color: #c82333;
    }
  </style>
</head>
<body>
    <!-- Main content -->
    <div class="main-content">
      <!-- Header -->
      <div class="page-header">
        <h1>Upload Documents</h1>
      </div>

      <!-- Upload Form -->
      <div class="upload-form">
        <h3>Upload New Document</h3>
        <form id="uploadForm">
          <div class="row">
            <div class="col-md-6 mb-3">
              <label for="documentType" class="form-label">Document Type</label>
              <select class="form-control" id="documentType" required>
                <option value="" disabled selected>Select document type</option>
                <option value="transcript">Transcript</option>
                <option value="id">ID</option>
                <option value="recommendation">Recommendation Letter</option>
                <option value="financial">Financial Statement</option>
                <option value="other">Other</option>
              </select>
            </div>
            <div class="col-md-6 mb-3">
              <label for="documentFile" class="form-label">Select File (PDF, DOC, DOCX, max 5MB)</label>
              <input type="file" class="form-control" id="documentFile" accept=".pdf,.doc,.docx" required>
            </div>
          </div>
          <div class="progress" id="uploadProgress">
            <div class="progress-bar" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
          </div>
          <div class="success-message" id="successMessage">File uploaded successfully!</div>
          <div class="text-end">
            <button type="button" class="btn btn-upload" onclick="uploadFile()">Upload</button>
          </div>
        </form>
      </div>

      <!-- Uploaded Files Table -->
      <div class="uploaded-files">
        <h3>Uploaded Documents</h3>
        <table class="table table-hover">
          <thead>
            <tr>
              <th>Document Type</th>
              <th>File Name</th>
              <th>Size</th>
              <th>Uploaded On</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody id="uploadedFilesTable">
            <tr>
              <td>Transcript</td>
              <td>transcript.pdf</td>
              <td>1.2 MB</td>
              <td>2025-04-01</td>
              <td>
                <button class="btn btn-update" onclick="updateFile(this)">Update</button>
                <button class="btn btn-delete" onclick="deleteFile(this)">Delete</button>
              </td>
            </tr>
            <tr>
              <td>Recommendation Letter</td>
              <td>recommendation.docx</td>
              <td>0.8 MB</td>
              <td>2025-04-02</td>
              <td>
                <button class="btn btn-update" onclick="updateFile(this)">Update</button>
                <button class="btn btn-delete" onclick="deleteFile(this)">Delete</button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- Bootstrap JS -->
  <script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
  ></script>
</body>
</html>