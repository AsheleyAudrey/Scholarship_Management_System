<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Documents</title>
  
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

    /* Set Required Documents Button */
    .page-header .btn-set {
      background-color: #509CDB; /* Match active item color */
      border: none;
      padding: 10px 20px;
      font-size: 16px;
      color: #ffffff;
    }

    .page-header .btn-set:hover {
      background-color: #408CCB;
    }

    /* Documents Table */
    .documents-table {
      background-color: #ffffff;
      border-radius: 8px;
      padding: 20px;
      box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
      margin-bottom: 20px;
    }

    .documents-table .table {
      margin-bottom: 0;
    }

    .documents-table .table th {
      background-color: #152259; /* Match sidebar color */
      color: #ffffff;
    }

    .documents-table .table td {
      vertical-align: middle;
    }

    .documents-table .table .badge {
      font-size: 12px;
    }

    .documents-table .file-icon {
      font-size: 20px;
      margin-right: 5px;
      color: #509CDB; /* Match active item color */
    }

    .documents-table .btn-upload {
      background-color: #17a2b8; /* Cyan for upload */
      border: none;
      font-size: 14px;
      padding: 5px 10px;
      margin-right: 5px;
    }

    .documents-table .btn-upload:hover {
      background-color: #138496;
    }

    .documents-table .btn-download {
      background-color: #28a745; /* Green for download */
      border: none;
      font-size: 14px;
      padding: 5px 10px;
      margin-right: 5px;
    }

    .documents-table .btn-download:hover {
      background-color: #218838;
    }

    .documents-table .btn-preview {
      background-color: #509CDB; /* Match active item color */
      border: none;
      font-size: 14px;
      padding: 5px 10px;
      margin-right: 5px;
    }

    .documents-table .btn-preview:hover {
      background-color: #408CCB;
    }

    .documents-table .btn-verify {
      font-size: 14px;
      padding: 5px 10px;
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
    .modal-body .form-check-input {
      border-radius: 5px;
      border: 1px solid #ced4da;
      box-shadow: none;
    }

    .modal-body .form-control:focus,
    .modal-body .form-check-input:focus {
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

    /* Preview Modal */
    .modal-body .document-preview {
      width: 100%;
      height: 400px;
      border: 1px solid #ced4da;
      border-radius: 5px;
      display: flex;
      align-items: center;
      justify-content: center;
      background-color: #f8f9fa;
    }

    .modal-body .document-preview img {
      max-width: 100%;
      max-height: 100%;
    }
  </style>
</head>
<body>
  <!-- Main content -->
    <div class="main-content">
      <!-- Header -->
      <div class="page-header">
        <h1>Documents</h1>
        <button class="btn btn-set" data-bs-toggle="modal" data-bs-target="#setRequiredDocsModal">
          <i class="bi bi-gear me-2"></i> Set Required Documents
        </button>
      </div>

      <!-- Documents Table -->
      <div class="documents-table">
        <table class="table table-hover">
          <thead>
            <tr>
              <th>Student ID</th>
              <th>Student Name</th>
              <th>Document Type</th>
              <th>File</th>
              <th>Status</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody id="documentsTable">
            <tr>
              <td>S001</td>
              <td>John Doe</td>
              <td>Transcript</td>
              <td><i class="bi bi-file-earmark-pdf file-icon"></i> transcript.pdf</td>
              <td><span class="badge bg-warning">Pending</span></td>
              <td>
                <button class="btn btn-preview" data-bs-toggle="modal" data-bs-target="#previewModal" onclick="previewDocument(this)">Preview</button>
                <button class="btn btn-download" onclick="downloadDocument(this)">Download</button>
                <button class="btn btn-success btn-verify" onclick="verifyDocument(this)">Verify</button>
              </td>
            </tr>
            <tr>
              <td>S001</td>
              <td>John Doe</td>
              <td>Recommendation Letter</td>
              <td><i class="bi bi-file-earmark-word file-icon"></i> letter.docx</td>
              <td><span class="badge bg-warning">Pending</span></td>
              <td>
                <button class="btn btn-preview" data-bs-toggle="modal" data-bs-target="#previewModal" onclick="previewDocument(this)">Preview</button>
                <button class="btn btn-download" onclick="downloadDocument(this)">Download</button>
                <button class="btn btn-success btn-verify" onclick="verifyDocument(this)">Verify</button>
              </td>
            </tr>
            <tr>
              <td>S002</td>
              <td>Jane Smith</td>
              <td>Transcript</td>
              <td><i class="bi bi-file-earmark-pdf file-icon"></i> transcript.pdf</td>
              <td><span class="badge bg-success">Verified</span></td>
              <td>
                <button class="btn btn-preview" data-bs-toggle="modal" data-bs-target="#previewModal" onclick="previewDocument(this)">Preview</button>
                <button class="btn btn-download" onclick="downloadDocument(this)">Download</button>
              </td>
            </tr>
            <tr>
              <td>S003</td>
              <td>Emily Johnson</td>
              <td>Transcript</td>
              <td>
                <input type="file" class="form-control" onchange="uploadDocument(this)">
              </td>
              <td><span class="badge bg-secondary">Not Uploaded</span></td>
              <td></td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- Set Required Documents Modal -->
  <div class="modal fade" id="setRequiredDocsModal" tabindex="-1" aria-labelledby="setRequiredDocsModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="setRequiredDocsModalLabel">Set Required Documents</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <form id="requiredDocsForm">
            <div class="mb-3 form-check">
              <input type="checkbox" class="form-check-input" id="transcriptRequired" checked>
              <label class="form-check-label" for="transcriptRequired">Transcript</label>
            </div>
            <div class="mb-3 form-check">
              <input type="checkbox" class="form-check-input" id="recommendationLetterRequired">
              <label class="form-check-label" for="recommendationLetterRequired">Recommendation Letter</label>
            </div>
            <div class="mb-3 form-check">
              <input type="checkbox" class="form-check-input" id="financialStatementRequired">
              <label class="form-check-label" for="financialStatementRequired">Financial Statement</label>
            </div>
            <div class="mb-3 form-check">
              <input type="checkbox" class="form-check-input" id="essayRequired">
              <label class="form-check-label" for="essayRequired">Essay</label>
            </div>
          </form>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          <button type="button" class="btn btn-save" onclick="saveRequiredDocs()">Save</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Preview Document Modal -->
  <div class="modal fade" id="previewModal" tabindex="-1" aria-labelledby="previewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="previewModalLabel">Document Preview</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="document-preview" id="documentPreview">
            <p>Preview not available for this file type.</p>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
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