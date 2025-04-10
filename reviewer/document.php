<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Documents Review</title>
  
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

    /* Application Info */
    .application-info {
      background-color: #ffffff;
      border-radius: 8px;
      padding: 20px;
      box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
      margin-bottom: 20px;
    }

    .application-info p {
      margin: 5px 0;
      font-size: 16px;
      color: #333;
    }

    /* Documents Section */
    .documents-section {
      background-color: #ffffff;
      border-radius: 8px;
      padding: 20px;
      box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
      margin-bottom: 20px;
    }

    .documents-section h3 {
      font-size: 18px;
      font-weight: 600;
      color: #152259; /* Match sidebar color */
      margin-bottom: 15px;
    }

    .document-item {
      border-bottom: 1px solid #dee2e6;
      padding: 15px 0;
    }

    .document-item:last-child {
      border-bottom: none;
    }

    .document-preview {
      display: flex;
      align-items: center;
      gap: 15px;
      margin-bottom: 15px;
    }

    .document-preview img,
    .document-preview .pdf-placeholder {
      width: 100px;
      height: 100px;
      object-fit: cover;
      border-radius: 5px;
      cursor: pointer;
      border: 1px solid #dee2e6;
    }

    .document-preview .pdf-placeholder {
      display: flex;
      align-items: center;
      justify-content: center;
      background-color: #f8f9fa;
      font-size: 14px;
      color: #333;
    }

    .document-preview span {
      font-size: 16px;
      color: #333;
    }

    .verification-section {
      margin-bottom: 15px;
    }

    .verification-section label {
      font-size: 14px;
      margin-right: 15px;
      color: #333;
    }

    .comment-section {
      margin-bottom: 15px;
    }

    .comment-section textarea {
      width: 100%;
      border-radius: 5px;
      border: 1px solid #ced4da;
      box-shadow: none;
      resize: vertical;
    }

    .comment-section textarea:focus {
      border-color: #509CDB; /* Match active item color */
      box-shadow: 0 0 5px rgba(80, 156, 219, 0.3);
    }

    .action-buttons {
      display: flex;
      gap: 10px;
    }

    .btn-verify {
      background-color: #28a745; /* Green for verify */
      border: none;
      font-size: 14px;
      padding: 5px 10px;
    }

    .btn-verify:hover {
      background-color: #218838;
    }

    .btn-flag {
      background-color: #dc3545; /* Red for flag */
      border: none;
      font-size: 14px;
      padding: 5px 10px;
    }

    .btn-flag:hover {
      background-color: #c82333;
    }

    /* Modal Styling */
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

    .modal-body img,
    .modal-body .pdf-placeholder {
      width: 100%;
      max-height: 500px;
      object-fit: contain;
      border: 1px solid #dee2e6;
      border-radius: 5px;
    }

    .modal-body .pdf-placeholder {
      display: flex;
      align-items: center;
      justify-content: center;
      background-color: #f8f9fa;
      font-size: 16px;
      color: #333;
      height: 500px;
    }
  </style>
</head>
<body>
    <!-- Sidebar -->
     <?php  include 'sidebar.php'; ?>

    <!-- Main content -->
    <div class="main-content">
      <!-- Header -->
      <div class="page-header">
        <h1>Documents Review</h1>
      </div>

      <!-- Application Info -->
      <div class="application-info">
        <p><strong>Application ID:</strong> A001</p>
        <p><strong>Student Name:</strong> John Doe</p>
        <p><strong>Scholarship Name:</strong> Merit-Based Scholarship</p>
        <p><strong>Submission Date:</strong> 2025-04-01</p>
      </div>

      <!-- Documents Section -->
      <div class="documents-section">
        <h3>Submitted Documents</h3>
        <div class="document-item">
          <div class="document-preview">
            <img src="https://via.placeholder.com/100" alt="Transcript" data-type="image" data-src="https://via.placeholder.com/800" onclick="openDocumentModal(this)">
            <span>Transcript.jpg</span>
          </div>
          <div class="verification-section">
            <label><input type="radio" name="transcript-verification" value="verified" onchange="updateVerification(this)"> Verified</label>
            <label><input type="radio" name="transcript-verification" value="not-verified" onchange="updateVerification(this)"> Not Verified</label>
          </div>
          <div class="comment-section">
            <textarea rows="3" placeholder="Add comments on document quality or missing info" onblur="saveComment(this)"></textarea>
          </div>
          <div class="action-buttons">
            <button class="btn btn-verify" onclick="saveDocumentReview(this)">Save Review</button>
            <button class="btn btn-flag" onclick="flagDocument(this)">Flag as Suspicious</button>
          </div>
        </div>
        <div class="document-item">
          <div class="document-preview">
            <div class="pdf-placeholder" data-type="pdf" data-src="sample.pdf" onclick="openDocumentModal(this)">PDF Preview</div>
            <span>Recommendation_Letter.pdf</span>
          </div>
          <div class="verification-section">
            <label><input type="radio" name="recommendation-verification" value="verified" onchange="updateVerification(this)"> Verified</label>
            <label><input type="radio" name="recommendation-verification" value="not-verified" onchange="updateVerification(this)"> Not Verified</label>
          </div>
          <div class="comment-section">
            <textarea rows="3" placeholder="Add comments on document quality or missing info" onblur="saveComment(this)"></textarea>
          </div>
          <div class="action-buttons">
            <button class="btn btn-verify" onclick="saveDocumentReview(this)">Save Review</button>
            <button class="btn btn-flag" onclick="flagDocument(this)">Flag as Suspicious</button>
          </div>
        </div>
        <div class="document-item">
          <div class="document-preview">
            <img src="https://via.placeholder.com/100" alt="ID Card" data-type="image" data-src="https://via.placeholder.com/800" onclick="openDocumentModal(this)">
            <span>ID_Card.jpg</span>
          </div>
          <div class="verification-section">
            <label><input type="radio" name="id-verification" value="verified" onchange="updateVerification(this)"> Verified</label>
            <label><input type="radio" name="id-verification" value="not-verified" onchange="updateVerification(this)"> Not Verified</label>
          </div>
          <div class="comment-section">
            <textarea rows="3" placeholder="Add comments on document quality or missing info" onblur="saveComment(this)"></textarea>
          </div>
          <div class="action-buttons">
            <button class="btn btn-verify" onclick="saveDocumentReview(this)">Save Review</button>
            <button class="btn btn-flag" onclick="flagDocument(this)">Flag as Suspicious</button>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Document Modal -->
  <div class="modal fade" id="documentModal" tabindex="-1" aria-labelledby="documentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="documentModalLabel">Document View</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div id="documentContent"></div>
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