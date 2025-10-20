
<?php if (!isset($isAdmin)) $isAdmin = false; ?>

<!-- Navigation Bar -->
<header>
  <nav class="navbar navbar-expand-lg navbar-light container">
    <!-- Logo linking to homepage -->
    <a class="navbar-brand" href="index.php">
      <img src="images/Play2Learn.svg" alt="Play2Learn Home Logo" height="40" />
    </a>

    <!-- Hamburger button for mobile navigation -->
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
      <span class="navbar-toggler-icon"></span>
    </button>

    <!-- Collapsible navigation links -->
    <div class="collapse navbar-collapse" id="mainNav">
      <ul class="navbar-nav ms-auto">
        <!-- Standard menu links -->
        <li class="nav-item"><a class="nav-link" href="index.php">Home</a></li>

        <!-- Dropdown menu for games -->
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">Games</a>
          <ul class="dropdown-menu bg-white border shadow-sm">
            <li><a class="dropdown-item" href="games/anagram-hunt.php">Anagram Hunt</a></li>
            <li><a class="dropdown-item" href="games/math-facts.php">Math Facts Practice</a></li>
          </ul>
        </li>

        <!-- About Page -->
        <li class="nav-item"><a class="nav-link" href="about.php">About</a></li>

        <?php if (isset($_SESSION['user_id'])): ?>
          <!-- Show these only if user is logged in -->

          <li class="nav-item"><a class="nav-link" href="leaderboard.php">Leaderboard</a></li>

          <!-- Dropdown for account features -->
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle fw-bold" href="#" role="button" data-bs-toggle="dropdown">
              Account
            </a>
            <ul class="dropdown-menu bg-white border shadow-sm">
              <li><a class="dropdown-item" href="profile.php">My Profile</a></li>
              <li><a class="dropdown-item" href="game-history.php">Game History</a></li>
              <li><a class="dropdown-item" href="review.php">Leave A Review</a></li>

              <!-- Admin dashboard link (only if user is admin) -->
              <?php if ($isAdmin): ?>
                <li><a class="dropdown-item text-primary" href="admin.php">Admin Dashboard</a></li>
              <?php endif; ?>

              <li><hr class="dropdown-divider"></li>

              <!-- Logout link -->
              <li>
                <a class="dropdown-item text-danger" href="includes/logout.php?redirect=<?= urlencode($_SERVER['REQUEST_URI']) ?>">Logout</a>
              </li>
            </ul>
          </li>
        <?php else: ?>
          <!-- If not logged in, show login link -->
          <li class="nav-item"><a class="nav-link" href="login.php">Login</a></li>
        <?php endif; ?>
      </ul>
    </div>
  </nav>
</header>