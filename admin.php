<?php
// Start session to access session variables
session_start();

// Include database connection
require_once 'includes/db.php';

// If user is not logged in, stop execution with message
if (!isset($_SESSION['user_id'])) {
    die("Access denied. You must be logged in.");
}

// Check if the logged-in user is an admin
$stmt = $pdo->prepare("SELECT is_admin FROM users WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$isAdmin = (bool)$stmt->fetchColumn(); // Convert result to boolean

// If user is not an admin, stop execution
if (!$isAdmin) {
    die("Access denied. Admins only.");
}

// If a form was submitted (POST request), handle admin actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? ''; // Get action type (e.g., promote, delete)
    $target_id = (int)($_POST['user_id'] ?? 0); // Target user ID
    $review_id = (int)($_POST['review_id'] ?? 0); // Target review ID (optional)

    // Perform action based on the submitted 'action' type
    switch ($action) {
        case 'promote':
            // Promote user to admin
            $pdo->prepare("UPDATE users SET is_admin = 1 WHERE user_id = ?")->execute([$target_id]);
            break;
        case 'demote':
            // Demote user from admin
            $pdo->prepare("UPDATE users SET is_admin = 0 WHERE user_id = ?")->execute([$target_id]);
            break;
        case 'confirm':
            // Confirm user's registration
            $pdo->prepare("UPDATE users SET registration_confirmed = 1 WHERE user_id = ?")->execute([$target_id]);
            break;
        case 'unconfirm':
            // Unconfirm user's registration
            $pdo->prepare("UPDATE users SET registration_confirmed = 0 WHERE user_id = ?")->execute([$target_id]);
            break;
        case 'delete':
            // Delete user's reviews, game scores, and account
            $pdo->prepare("DELETE FROM reviews WHERE user_id = ?")->execute([$target_id]);
            $pdo->prepare("DELETE FROM math_facts_scores WHERE user_id = ?")->execute([$target_id]);
            $pdo->prepare("DELETE FROM anagram_hunt_scores WHERE user_id = ?")->execute([$target_id]);
            $pdo->prepare("DELETE FROM users WHERE user_id = ?")->execute([$target_id]);
            break;
        case 'confirm_review':
            // Mark review as featured
            if ($review_id > 0) {
                $pdo->prepare("UPDATE reviews SET featured = 1 WHERE review_id = ?")->execute([$review_id]);
            }
            break;
        case 'unconfirm_review':
            // Unmark review as featured
            if ($review_id > 0) {
                $pdo->prepare("UPDATE reviews SET featured = 0 WHERE review_id = ?")->execute([$review_id]);
            }
            break;
        case 'delete_review':
            // Delete review
            if ($review_id > 0) {
                $pdo->prepare("DELETE FROM reviews WHERE review_id = ?")->execute([$review_id]);
            }
            break;
    }

    // Redirect to prevent resubmission on page refresh
    header("Location: admin.php");
    exit;
}

// Fetch all users from the database
$users = $pdo->query("SELECT * FROM users ORDER BY date_registered DESC")->fetchAll(PDO::FETCH_ASSOC);

// Fetch all reviews and group them by user_id
$reviewStmt = $pdo->query("SELECT * FROM reviews");
$reviewsByUser = [];
foreach ($reviewStmt as $review) {
    $reviewsByUser[$review['user_id']][] = $review;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>

  <!-- Meta information -->
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Admin Dashboard - Play2Learn</title>

  <!-- Bootstrap CSS for styling -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />

  <!-- Custom CSS styles -->
  <link href="includes/normalize.css" rel="stylesheet">
  <link href="includes/styles.css" rel="stylesheet" />

  <!-- JavaScript for dropdown menu -->
  <script src="includes/dropdown.js" defer></script>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

  <!-- Small font size for tables and fixed width buttons -->
  <style>
    table.table.small,
    table.table.small td,
    table.table.small th {
        font-size: 0.8rem;
    }

    /* Fixed width buttons to unify width */
    .btn-fixed-width {
      min-width: 80px;       
      text-align: center;
      display: inline-block;
      white-space: nowrap;  
    }
  </style>
</head>
<body class="bg-light">

<!-- Include the site header -->
<?php include 'includes/header.php'; ?>

<div class="container pt-0 pb-0">
  <h3>Admin Dashboard</h3>
  <p class="small text-muted">Welcome, Admin. Below is the list of all users and their reviews.</p>

  <div class="table-responsive">

    <!-- User Management Table -->
    <table class="table table-bordered table-hover table-sm small bg-white">
      <thead class="table-dark">
        <tr>
          <th>ID</th>
          <th>Name</th>
          <th>Username</th>
          <th>Email</th>
          <th>Admin?</th>
          <th>Confirmed?</th>
          <th>Registered</th>
          <th>Reviews</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>

        <!-- Loop through users -->
        <?php foreach ($users as $user): ?>
          <tr>
            <td><?= $user['user_id'] ?></td>
            <td><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></td>
            <td><?= htmlspecialchars($user['username']) ?></td>
            <td><?= htmlspecialchars($user['email']) ?></td>
            <td><?= $user['is_admin'] ? 'Yes' : 'No' ?></td>
            <td><?= $user['registration_confirmed'] ? 'Yes' : 'No' ?></td>
            <td><?= $user['date_registered'] ?></td>
            <td>
              <?php if (!empty($reviewsByUser[$user['user_id']])): ?>
                <ul class="list-unstyled mb-0">
                  <?php foreach ($reviewsByUser[$user['user_id']] as $rev): ?>
                    <li>
                      <q><?= htmlspecialchars($rev['review']) ?></q>

                      <!-- Review confirmation form -->
                      <form method="POST" class="d-inline">
                        <input type="hidden" name="review_id" value="<?= $rev['review_id'] ?>">
                        <input type="hidden" name="user_id" value="<?= $user['user_id'] ?>">
                        <?php if ($rev['featured']): ?>
                        <button name="action" value="unconfirm_review" class="btn btn-sm btn-secondary btn-fixed-width" 
                        style="padding: 0.1rem 0.3rem; font-size: 0.6rem; font-weight: normal; background-color: #e2e6ea; color: #000;">
                        Hide from Homepage </button>
                        <?php else: ?>
                        <button name="action" value="confirm_review" class="btn btn-sm btn-secondary btn-fixed-width" 
                        style="padding: 0.1rem 0.3rem; font-size: 0.6rem; font-weight: normal;">
                        Feature on Homepage </button>
                        <?php endif; ?>
                      </form>

                      <!-- Delete review form -->
                      <form method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this review?');">
                        <input type="hidden" name="review_id" value="<?= $rev['review_id'] ?>">
                        <input type="hidden" name="user_id" value="<?= $user['user_id'] ?>">
                        <button name="action" value="delete_review" class="btn btn-sm btn-danger btn-fixed-width" 
                          style="padding: 0.1rem 0.3rem; font-size: 0.6rem; font-weight: normal;">
                          Delete
                        </button>
                      </form>
                    </li>
                  <?php endforeach; ?>
                </ul>
              <?php else: ?>
                <span class="text-muted">No review</span>
              <?php endif; ?>
            </td>
            <td>
              <!-- Promote/Demote Button -->
              <form method="POST" style="display:inline-block;">
                <input type="hidden" name="user_id" value="<?= $user['user_id'] ?>">
                <?php if ($user['is_admin']): ?>
                  <button name="action" value="demote" class="btn btn-primary btn-sm btn-fixed-width" style="padding: 0.2rem 0.5rem; font-size: 0.7rem; font-weight: bold;">Demote</button>
                <?php else: ?>
                  <button name="action" value="promote" class="btn btn-primary btn-sm btn-fixed-width" style="padding: 0.2rem 0.5rem; font-size: 0.7rem; font-weight: bold;">Promote</button>
                <?php endif; ?>
              </form>

              <!-- Delete User Button -->
              <form method="POST" style="display:inline-block;" onsubmit="return confirm('Are you sure you want to delete this user?');">
                <input type="hidden" name="user_id" value="<?= $user['user_id'] ?>">
                <button name="action" value="delete" class="btn btn-danger btn-sm btn-fixed-width" style="padding: 0.2rem 0.5rem; font-size: 0.7rem; font-weight: bold;">Delete</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <!-- Back to Home Button -->
  <div class="mb-3 text-center">
    <a href="index.php" class="playButton">← Back to Home</a>
  </div>
</div>

<!-- Include the site footer -->
<?php include 'includes/footer.php'; ?>

</body>
</html>
