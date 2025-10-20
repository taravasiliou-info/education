<!DOCTYPE html>
<html lang="en">
<head>
  <!-- Character encoding for the page -->
  <meta charset="UTF-8">

  <!-- Responsive viewport for mobile devices -->
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  
  <!-- Bootstrap CSS from CDN -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  
  <!-- Bootstrap JS bundle from CDN -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  
  <!-- Custom JavaScript for quotes -->
  <script src="quotescript.js" defer></script>
  
  <!-- Custom JavaScript for dropdown menus -->
  <script src="includes/dropdown.js" defer></script>

  <!-- Normalize CSS styles -->
  <link href="includes/normalize.css" rel="stylesheet">

  <!-- Custom CSS styles -->
  <link href="includes/styles.css" rel="stylesheet">
  
  <!-- Custom CSS styles -->
  <link href="includes/normalize.css" rel="stylesheet">
  
  <!-- Page title -->
  <title>Home Page - Play2Learn</title>
</head>
<body>

<?php
// Start PHP session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include database connection (PDO object $pdo is expected)
require_once 'includes/db.php';

// Attempt to fetch featured reviews from database
try {
    $stmt = $pdo->prepare("
        SELECT r.review, u.first_name 
        FROM reviews r 
        JOIN users u ON r.user_id = u.user_id 
        WHERE r.featured = 1 
        ORDER BY r.review_id DESC 
        LIMIT 5
    ");
    $stmt->execute();
    $reviews = $stmt->fetchAll(PDO::FETCH_ASSOC); // Store results as associative array
} catch (PDOException $e) {
    $reviews = []; // If query fails, set $reviews as empty array
    // Optionally log error: error_log("Failed to fetch reviews: " . $e->getMessage());
}

// Initialize admin status
$isAdmin = false;

// Check if user is logged in
if (isset($_SESSION['user_id'])) {

    // If admin status not in session, fetch from database
    if (!isset($_SESSION['is_admin'])) {
        $stmt = $pdo->prepare("SELECT is_admin FROM users WHERE user_id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $_SESSION['is_admin'] = (bool)$stmt->fetchColumn(); // Store admin status as boolean
    }
    $isAdmin = $_SESSION['is_admin']; // Set local variable for page logic
}
?>

<?php include 'includes/header.php'; ?>

<!-- Main content -->
<main class="container">

  <!-- Featured reviews heading -->
  <div class="d-flex justify-content-center mt-2 mb-1">
    <div class="d-flex align-items-center gap-3" style="color: black; font-size: 1rem;">
        <h3 class="text-center mb-2 text-success">Featured Reviews: Happy clients say...</h3>
    </div>
  </div>

  <!-- Reviews carousel -->
  <?php if (!empty($reviews)): ?>
    <div id="reviewCarousel" class="carousel slide mb-4" data-bs-ride="carousel" data-bs-interval="10000">
      <div class="carousel-inner">

        <!-- Loop through each review -->
        <?php foreach ($reviews as $index => $rev): ?>
          <div class="carousel-item <?= $index === 0 ? 'active' : '' ?>">
            <div class="d-flex justify-content-center">
              <p class="fs-6 fst-italic text-center w-75">
              “<?= htmlspecialchars($rev['review']) ?>”
                <span class="text-muted"> — <?= htmlspecialchars($rev['first_name']) ?></span>
              </p>
            </div>
          </div>
        <?php endforeach; ?>
      </div>

      <!-- Carousel controls -->
      <button class="carousel-control-prev" type="button" data-bs-target="#reviewCarousel" data-bs-slide="prev">
        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
        <span class="visually-hidden">Previous</span>
      </button>
      <button class="carousel-control-next" type="button" data-bs-target="#reviewCarousel" data-bs-slide="next">
        <span class="carousel-control-next-icon" aria-hidden="true"></span>
        <span class="visually-hidden">Next</span>
      </button>
    </div>
  <?php else: ?>

    <!-- Message when no reviews exist -->
    <p class="text-muted mt-4 text-center">No reviews yet. Be the first to leave one!</p>
  <?php endif; ?>

  <!-- Games section -->
  <section>
    <h3 class="mb-4">Explore Our Games</h3>
    <div class="row">
      
      <!-- Anagram Hunt game box -->
      <div class="col-md-6">
        <div class="gameBox">
          <h3>Anagram Hunt</h3>
          <p>An anagram is a word made by rearranging the letters of another word. The object of the Anagram Hunt game is to find the most anagrams for words of a specified length in a minute.</p>
          <img src="images/anagramhunt.png" alt="Screenshot of Anagram Hunt game" class="img-fluid rounded mb-3">
          <a href="games/anagram-hunt.php" class="playButton">Play Anagram Hunt</a>
        </div>
      </div>

      <!-- Math Facts Practice game box -->
      <div class="col-md-6">
        <div class="gameBox">
          <h3>Math Facts Practice</h3>
          <p>The Math Facts Practice game helps users improve their speed and accuracy with basic arithmetic operations such as addition, subtraction, multiplication, and division.</p>
          <img src="images/mathfacts.png" alt="Screenshot of Math Facts Practice game" class="img-fluid rounded mb-3">
          <a href="games/math-facts.php" class="playButton">Play Math Facts Practice</a>
        </div>
      </div>
    </div>
  </section>
</main>

<?php include 'includes/footer.php'; ?>

</body>
</html>
