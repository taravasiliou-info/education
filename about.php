<?php
// Start session if not already active
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include database connection file
require_once 'includes/db.php';

// Initialize variable - check if user is admin
$isAdmin = false;

// If user is logged in, check their admin status
if (isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare("SELECT is_admin FROM users WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $isAdmin = (bool)$stmt->fetchColumn(); // Cast result to boolean
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <!-- Set character encoding -->
  <meta charset="UTF-8" />
  <!-- Make page responsive on all devices -->
  <meta name="viewport" content="width=device-width, initial-scale=1" />

  <!-- Link to Bootstrap CSS (for styling) -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />

  <!-- Normalize styles across browsers -->
  <link href="includes/normalize.css" rel="stylesheet">

  <!-- Link to custom stylesheet -->
  <link href="includes/styles.css" rel="stylesheet" />

  <!-- Link to dropdown script -->
  <script src="includes/dropdown.js" defer></script>

  <!-- Load Bootstrap JavaScript Bundle -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

  <!-- Optional JS to auto-collapse the navbar on click (mobile friendly) -->
  <script>
    document.addEventListener("DOMContentLoaded", function () {
      document.querySelectorAll('.navbar-nav .nav-link').forEach(link => {
        link.addEventListener('click', () => {
          const navbar = document.querySelector('.navbar-collapse');
          if (navbar.classList.contains('show')) {
            new bootstrap.Collapse(navbar).hide();
          }
        });
      });
    });
  </script>

  <!-- Page Title -->
  <title>About - Play2Learn</title>
</head>
<body>

<!-- Include site header -->
<?php include 'includes/header.php'; ?>

<!-- Main Content -->
<main class="container mt-3 mb-5">
  <section class="row justify-content-center">
    <div class="col-12 col-md-10 col-lg-8">

      <!-- Page heading -->
      <h3 class="mb-4">About Us</h3>

      <!-- Description text -->
      <p>
        I created this website as part of a class project, where I was tasked with designing and developing a functional web application. The project focused on using modern web technologies like PHP, MySQL, and Bootstrap, and I integrated features such as user authentication, game tracking, and review submission. This allowed me to not only practice my skills in front-end and back-end development but also to build a dynamic user experience with interactive elements like leaderboards and a review carousel.
      </p>
      <p>
        Throughout the development process, I learned how to handle various aspects of web development, including designing responsive layouts, setting up databases, and managing user data securely. The website is structured to offer engaging games, track player performance, and provide users with the ability to leave feedback on their experience. It was a great opportunity to implement what I learned in class and gain hands-on experience in creating a functional, interactive website.
      </p>
    </div>
  </section>
</main>

<!-- Include site footer -->
<?php include 'includes/footer.php'; ?>

</body>
</html>
