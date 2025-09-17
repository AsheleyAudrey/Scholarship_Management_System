<?php 
include "../Database/db.php";

// Fetch reviews
$reviewQuery = "SELECT * FROM Reviews";
$reviewQueryResult = $conn->query($reviewQuery);
if (!$reviewQueryResult) {
    die("Query failed: " . $conn->error);
}

$reviews = [];
while ($row = $reviewQueryResult->fetch_assoc()) {
    $reviews[] = $row;
}

// Fetch reviewers
$reviewers = [];
$reviewerQuery = "SELECT * FROM Reviewers";
$reviewerResult = $conn->query($reviewerQuery);
if (!$reviewerResult) {
    die("Query failed: " . $conn->error);
}
while ($row = $reviewerResult->fetch_assoc()) {
    $reviewers[$row['reviewer_id']] = $row['name'];
}

// Fetch applications (for linking review → application → student + scholarship)
$applications = [];
$appQuery = "SELECT * FROM Applications";
$appResult = $conn->query($appQuery);
if (!$appResult) {
    die("Query failed: " . $conn->error);
}
while ($row = $appResult->fetch_assoc()) {
    $applications[$row['application_id']] = $row;
}

// Fetch students
$students = [];
$studentQuery = "SELECT * FROM Students";
$studentResult = $conn->query($studentQuery);
if (!$studentResult) {
    die("Query failed: " . $conn->error);
}
while ($row = $studentResult->fetch_assoc()) {
    $students[$row['student_id']] = $row['name'];
}

// Fetch scholarships
$scholarships = [];
$scholarshipQuery = "SELECT * FROM Scholarships";
$scholarshipResult = $conn->query($scholarshipQuery);
if (!$scholarshipResult) {
    die("Query failed: " . $conn->error);
}
while ($row = $scholarshipResult->fetch_assoc()) {
    $scholarships[$row['scholarship_id']] = $row['name'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Review Management</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"/>
</head>
<body>
<?php include 'sidebar.php'; ?>

<div class="container mt-4">
  <h1 class="mb-4">Review Management</h1>
  <div class="table-responsive">
    <table class="table table-hover table-bordered">
      <thead class="table-dark">
        <tr>
          <th>Review ID</th>
          <th>Reviewer</th>
          <th>Student</th>
          <th>Scholarship</th>
          <th>Comments</th>
          <th>Score</th>
          <th>Date</th>
        </tr>
      </thead>
      <tbody>
        <?php if (count($reviews) > 0): ?>
          <?php foreach ($reviews as $review): ?>
            <?php 
              $app = isset($applications[$review['application_id']]) ? $applications[$review['application_id']] : null;
              $studentName = $app && isset($students[$app['student_id']]) ? $students[$app['student_id']] : "Unknown Student";
              $scholarshipName = $app && isset($scholarships[$app['scholarship_id']]) ? $scholarships[$app['scholarship_id']] : "Unknown Scholarship";
              $reviewerName = isset($reviewers[$review['reviewer_id']]) ? $reviewers[$review['reviewer_id']] : "Unknown Reviewer";
            ?>
            <tr>
              <td><?php echo $review['review_id']; ?></td>
              <td><?php echo $reviewerName; ?></td>
              <td><?php echo $studentName; ?></td>
              <td><?php echo $scholarshipName; ?></td>
              <td><?php echo $review['comments']; ?></td>
              <td><?php echo $review['score']; ?></td>
              <td><?php echo $review['date']; ?></td>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr>
            <td colspan="7" class="text-center">No reviews found.</td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
