<?php
// Start PHP session to track logged-in users
session_start();

// Include database connection file
require_once 'includes/db.php';

// Redirect to login page if user is not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Fetch top 10 Math Facts scores with user details
$stmt = $pdo->prepare("
    SELECT u.first_name, u.last_name, m.score, m.max_number, m.operation, m.end_time, m.user_id
    FROM math_facts_scores m
    JOIN users u ON m.user_id = u.user_id
    ORDER BY m.score DESC, m.end_time ASC
    LIMIT 10
");
$stmt->execute();
$topMathScores = $stmt->fetchAll(PDO::FETCH_ASSOC); // Store as associative array

// Fetch top 10 Anagram Hunt scores with user details
$stmt = $pdo->prepare("
    SELECT u.first_name, u.last_name, a.score, a.max_number, a.end_time, a.user_id
    FROM anagram_hunt_scores a
    JOIN users u ON a.user_id = u.user_id
    ORDER BY a.score DESC, a.end_time ASC
    LIMIT 10
");
$stmt->execute();
$topAnagramScores = $stmt->fetchAll(PDO::FETCH_ASSOC); // Store as associative array

// Check if logged-in user is an admin
$isAdmin = false;
$stmt = $pdo->prepare("SELECT is_admin FROM users WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$isAdmin = (bool)$stmt->fetchColumn(); // Convert value to boolean

// Function to render a leaderboard table
function renderLeaderboard($scores, $columns, $title, $cssClass = 'text-success') {
    $currentUserId = $_SESSION['user_id'] ?? null; // Get current user ID if logged in

    // Print leaderboard title
    echo "<h6 class='$cssClass fw-bold'>$title</h6>";

    // Show message if no scores exist
    if (empty($scores)) {
        echo "<p class='text-muted'>No scores yet.</p>";
        return;
    }

    // Start responsive table
    echo "<div class='table-responsive mb-4'>";
    echo "<table class='table table-sm table-bordered table-striped align-middle small'>";
    
    // Table header
    echo "<thead class='table-light'><tr><th>Rank</th>";
    foreach ($columns as $label) {
        echo "<th>$label</th>";
    }
    echo "</tr></thead><tbody>";

    // Loop through scores and print each row
    foreach ($scores as $index => $row) {
        $highlight = ($row['user_id'] == $currentUserId) ? "table-warning" : ""; // Highlight current user's row
        echo "<tr class='$highlight'>";
        
        // Print rank
        echo "<td>" . ($index + 1) . "</td>";

        // Print each column value
        foreach (array_keys($columns) as $key) {
            if ($key === 'end_time') {
                echo "<td>" . date('Y-m-d H:i', strtotime($row[$key])) . "</td>"; // Format date/time
            } elseif ($key === 'operation') {
                echo "<td>" . htmlspecialchars(ucfirst($row[$key])) . "</td>"; // Capitalize operation
            } else {
                echo "<td>" . htmlspecialchars($row[$key]) . "</td>"; // Escape other values
            }
        }
        echo "</tr>";
    }

    // Close table
    echo "</tbody></table></div>";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>

  <!-- Page character encoding -->
  <meta charset="UTF-8">
  
  <!-- Responsive design for mobile devices -->
  <meta name="viewport" content="width=device-width, initial-scale=1">
  
  <!-- Page title -->
  <title>Leaderboards - Play2Learn</title>
  
  <!-- Bootstrap CSS for styling -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  
  <!-- Normalize CSS styles -->
  <link href="includes/normalize.css" rel="stylesheet">

  <!-- Custom CSS styles -->
  <link href="includes/styles.css" rel="stylesheet">
</head>
<body>

<?php include 'includes/header.php'; ?>

<!-- Main content section -->
<main class="container my-4">
  <div class="row justify-content-center">
    <div class="col-lg-8">
      <div class="mt-0">
        
        <!-- Page heading -->
        <h3 class="text-center mb-4">Top Scores Leaderboard</h3>

        <!-- Render Math Facts leaderboard -->
        <?php
        renderLeaderboard($topMathScores, [
            'first_name' => 'Player',
            'operation' => 'Operation',
            'max_number' => 'Max Number',
            'score' => 'Score',
            'end_time' => 'Date'
        ], 'Math Facts Practice', 'text-success');

        // Render Anagram Hunt leaderboard
        renderLeaderboard($topAnagramScores, [
            'first_name' => 'Player',
            'max_number' => 'Word Length',
            'score' => 'Score',
            'end_time' => 'Date'
        ], 'Anagram Hunt', 'text-primary');
        ?>
      </div>
    </div>
  </div>
</main>

<?php include 'includes/footer.php'; ?>

</body>
</html>
