<?php
// Start the PHP session to access session variables
session_start();

// Include database connection script
require_once 'includes/db.php';

// Check if user is logged in by verifying if 'user_id' is set in the session
if (!isset($_SESSION['user_id'])) {

    // If not logged in, deny access
    die("Access denied. You must be logged in.");
}

// Get currently logged-in user's ID
$user_id = $_SESSION['user_id'];

// Handle score deletion if the request method is POST and required data is set
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['score_id'], $_POST['table'])) {

    // Sanitize the score_id and table name
    $score_id = (int) $_POST['score_id'];
    $table = $_POST['table'];

    // Define which tables are allowed to be deleted from
    $allowed_tables = [
        'math_facts_scores' => 'math_facts_scores',
        'anagram_hunt_scores' => 'anagram_hunt_scores'
    ];

    // If the requested table is in the allowed list
    if (array_key_exists($table, $allowed_tables)) {

        // Prepare a secure SQL statement to delete the score belonging to the current user
        $stmt = $pdo->prepare("DELETE FROM {$allowed_tables[$table]} WHERE score_id = ? AND user_id = ?");
        $stmt->execute([$score_id, $user_id]);
    }

    // Redirect back to the page to reflect changes
    header("Location: delete-score.php");
    exit;
}

// Fetch all math facts scores for the current user, most recent first
$mathScores = $pdo->prepare("SELECT * FROM math_facts_scores WHERE user_id = ? ORDER BY end_time DESC");
$mathScores->execute([$user_id]);

// Fetch all anagram hunt scores for the current user, most recent first
$anagramScores = $pdo->prepare("SELECT * FROM anagram_hunt_scores WHERE user_id = ? ORDER BY end_time DESC");
$anagramScores->execute([$user_id]);

// Check if the user is an admin
$isAdmin = false;
$stmt = $pdo->prepare("SELECT is_admin FROM users WHERE user_id = ?");
$stmt->execute([$user_id]);
$isAdmin = (bool)$stmt->fetchColumn();
?>

<!-- HTML Begins -->
<!DOCTYPE html>
<html lang="en">
<head>

  <!-- Page metadata -->
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" >
  <title>My Scores</title>

  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" >

  <!-- Bootstrap JS Bundle -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

  <!-- Custom styles and JS -->
  <link href="includes/styles.css" rel="stylesheet" >

  <!-- Normalize CSS styles -->
  <link href="includes/normalize.css" rel="stylesheet">

  <!-- Droodown script -->
  <script src="includes/dropdown.js" defer></script>

  <!-- Inline styles for smaller table text and tiny button -->
  <style>
    .table-sm td, .table-sm th {
        font-size: 0.8rem;
    }
    .btn-tiny {
        padding: 0.1rem 0.3rem;
        font-size: 0.65rem;
        font-weight: 600;
        line-height: 0.5;
        border-radius: 0.2rem;
    }
  </style>
</head>
<body class="bg-light">

<?php include 'includes/header.php'; ?>

<!-- Main container -->
<div class="container pt-0 pb-0">

  <!-- Page heading -->
  <h3 class="mb-3">Delete Game Scores</h3>

  <!-- Math Facts Score Table -->
  <h6 class="text-success fw-bold">Math Facts Practice</h6>
  <table class="table table-bordered table-sm table-hover bg-white mb-4">
    <thead class="table-dark">
      <tr>
        <th>Score</th>
        <th>Operation</th>
        <th>Max Number</th>
        <th>Time Finished</th>
        <th>Action</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($mathScores as $score): ?>
        <tr>
          <td><?= htmlspecialchars($score['score']) ?></td>
          <td><?= htmlspecialchars($score['operation']) ?></td>
          <td><?= htmlspecialchars($score['max_number']) ?></td>
          <td><?= htmlspecialchars($score['end_time']) ?></td>
          <td>

            <!-- Delete form for each score -->
            <form method="POST" onsubmit="return confirm('Are you sure you want to delete this score?');">
              <input type="hidden" name="score_id" value="<?= $score['score_id'] ?>">
              <input type="hidden" name="table" value="math_facts_scores">
              <button class="btn btn-outline-danger btn-sm btn-tiny">Delete</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>

  <!-- Anagram Hunt Score Table -->
  <h6 class="text-primary fw-bold">Anagram Hunt</h6>
  <table class="table table-bordered table-sm table-hover bg-white">
    <thead class="table-dark">
      <tr>
        <th>Score</th>
        <th>Word Length</th>
        <th>Time Finished</th>
        <th>Action</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($anagramScores as $score): ?>
        <tr>
          <td><?= htmlspecialchars($score['score']) ?></td>
          <td><?= htmlspecialchars($score['max_number']) ?></td>
          <td><?= htmlspecialchars($score['end_time']) ?></td>
          <td>
            
            <!-- Delete form for each score -->
            <form method="POST" onsubmit="return confirm('Are you sure you want to delete this score?');">
              <input type="hidden" name="score_id" value="<?= $score['score_id'] ?>">
              <input type="hidden" name="table" value="anagram_hunt_scores">
              <button class="btn btn-outline-danger btn-sm btn-tiny">Delete</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>

  <!-- Back to home link -->
  <a href="index.php" class="playButton d-inline-block mb-4">← Back to Home</a>
</div>

<?php include 'includes/footer.php'; ?>

</body>
</html>
