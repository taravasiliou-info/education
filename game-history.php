<?php
// Start session to keep track of the logged-in user
session_start();

// Include database connection settings
require_once 'includes/db.php';

// Redirect login page if the user is not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Get logged-in user's ID from the session
$userId = $_SESSION['user_id'];

// Fetch user's profile information from database
$stmt = $pdo->prepare("SELECT first_name, last_name, email, username FROM users WHERE user_id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Initialize message for profile updates
$updateMessage = '';

// Handle profile update form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Get form data or default to empty strings
    $firstName = $_POST['first_name'] ?? '';
    $lastName = $_POST['last_name'] ?? '';
    $email = $_POST['email'] ?? '';
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    // Check if passwords match
    if ($password && $password !== $confirmPassword) {
        $updateMessage = "Passwords do not match.";
    } else {

        // Update password if provided
        if ($password) {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET first_name=?, last_name=?, email=?, username=?, pass_phrase=? WHERE user_id=?");
            $stmt->execute([$firstName, $lastName, $email, $username, $hashedPassword, $userId]);
        } else {

            // Update profile info without changing password
            $stmt = $pdo->prepare("UPDATE users SET first_name=?, last_name=?, email=?, username=? WHERE user_id=?");
            $stmt->execute([$firstName, $lastName, $email, $username, $userId]);
        }

        // Refresh user info after update
        $stmt = $pdo->prepare("SELECT first_name, last_name, email, username FROM users WHERE user_id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Set a success message
        $updateMessage = "Profile information updated successfully.";
    }
}

// Fetch math facts game history for the logged-in user
$stmt = $pdo->prepare("SELECT score, max_number, operation, end_time FROM math_facts_scores WHERE user_id = ? ORDER BY end_time DESC");
$stmt->execute([$userId]);
$mathFactsScores = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch anagram hunt game history for the logged-in user
$stmt = $pdo->prepare("SELECT score, max_number, end_time FROM anagram_hunt_scores WHERE user_id = ? ORDER BY end_time DESC");
$stmt->execute([$userId]);
$anagramScores = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>

  <!-- Character encoding -->
  <meta charset="UTF-8" >

  <!-- Responsive design -->
  <meta name="viewport" content="width=device-width, initial-scale=1" >

  <!-- Page title -->
  <title>Game History - Play2Learn</title>

  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" >
 
  <!-- Normalize CSS styles -->
  <link href="includes/normalize.css" rel="stylesheet">
 
  <!-- Custom CSS -->
  <link href="includes/styles.css" rel="stylesheet" >

  <!-- Custom JavaScript for dropdown menus -->
  <script src="includes/dropdown.js" defer></script>

  <!-- Bootstrap JS Bundle (includes Popper.js) -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body>

<header>
<?php

// Check if the logged-in user is an admin
$isAdmin = false;
if (isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare("SELECT is_admin FROM users WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $isAdmin = (bool)$stmt->fetchColumn();
}
?>

<?php include 'includes/header.php'; ?>

<main class="container my-4">
  <div class="row justify-content-center">
    <div class="col-lg-8">

      <div class="mt-0">

        <!-- Page header -->
        <h3 class="text-center mb-3">Game History</h3>

        <!-- Math Facts Practice section -->
        <h6 class="text-success fw-bold">Math Facts Practice</h6>
        <?php if (count($mathFactsScores) > 0): ?>

          <!-- Table of Math Facts scores -->
          <div class="table-responsive mb-4">
            <table class="table table-sm table-bordered table-striped align-middle small">
              <thead class="table-light">
                <tr>
                  <th scope="col">Date</th>
                  <th scope="col">Operation</th>
                  <th scope="col">Max Number</th>
                  <th scope="col">Score</th>
                </tr>
              </thead>
              <tbody>
                <?php

                // Loop through all math facts scores and display each one
                foreach ($mathFactsScores as $row): ?>
                  <tr>

                    <!-- Display formatted date of the game -->
                    <td><?= date('Y-m-d H:i', strtotime($row['end_time'])) ?></td>

                    <!-- Display operation name with first letter capitalized -->
                    <td><?= htmlspecialchars(ucfirst($row['operation'])) ?></td>

                    <!-- Display the maximum number used in the game -->
                    <td><?= htmlspecialchars($row['max_number']) ?></td>

                    <!-- Display user's score -->
                    <td><?= htmlspecialchars($row['score']) ?></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php else: ?>

          <!-- Message if no math facts games have been played -->
          <p class="text-muted">No math facts games played yet.</p>
        <?php endif; ?>

        <!-- Anagram Hunt section -->
        <h6 class="text-primary fw-bold">Anagram Hunt</h6>
        <?php if (count($anagramScores) > 0): ?>

          <!-- Table of Anagram Hunt scores -->
          <div class="table-responsive mb-4">
            <table class="table table-sm table-bordered table-striped align-middle small">
              <thead class="table-light">
                <tr>
                  <th scope="col">Date</th>
                  <th scope="col">Word Length</th>
                  <th scope="col">Score</th>
                </tr>
              </thead>
              <tbody>
                <?php

                // Loop through all anagram hunt scores
                foreach ($anagramScores as $row): ?>
                  <tr>

                    <!-- Display formatted date of game -->
                    <td><?= date('Y-m-d H:i', strtotime($row['end_time'])) ?></td>

                    <!-- Display maximum word length attempted -->
                    <td><?= htmlspecialchars($row['max_number']) ?></td>

                    <!-- Display user's score -->
                    <td><?= htmlspecialchars($row['score']) ?></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php else: ?>
          
          <!-- Message if no anagram hunt games have been played -->
          <p class="text-muted">No anagram hunt games played yet.</p>
        <?php endif; ?>
      </div>

    </div>
  </div>

  <!-- Button to delete all game scores -->
  <div class="text-center mt-0 mb-2">
    <a href="delete-score.php" class="btn btn-outline-danger btn-sm">Delete Game Scores</a>
  </div>
</main>

<?php include 'includes/footer.php'; ?>

<!-- Client-side form validation script -->
<script>
(() => {
  const form = document.querySelector('.needs-validation');

  form.addEventListener('submit', event => {
    const password = form.password.value;
    const confirm = form.confirm_password.value;

    // Check password length
    if (password && password.length < 6) {
      form.password.setCustomValidity('Password must be at least 6 characters');
    } else {
      form.password.setCustomValidity('');
    }

    // Check password confirmation
    if (password && confirm && password !== confirm) {
      form.confirm_password.setCustomValidity('Passwords do not match');
    } else {
      form.confirm_password.setCustomValidity('');
    }

    // Prevent form submission if invalid
    if (!form.checkValidity()) {
      event.preventDefault();
      event.stopPropagation();
    }

    // Add Bootstrap validation classes
    form.classList.add('was-validated');
  }, false);
})();
</script>
</body>
</html>
