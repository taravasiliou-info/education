<?php
session_start();
require_once 'includes/db.php';

$loginError = '';
$registerSuccess = '';
$registerError = '';

// Get redirect URL from GET param or fallback
$redirect = $_GET['redirect'] ?? 'index.php';

// Sanitize and validate redirect URL to avoid open redirect vulnerabilities
function isValidRedirect($url) {
    if (!$url) return false;
    $parsed = parse_url($url);

    // Only allow relative URLs or URLs on the same host
    if (isset($parsed['host']) && $parsed['host'] !== $_SERVER['HTTP_HOST']) {
        return false;
    }
    return true;
}

if (!isValidRedirect($redirect)) {
    $redirect = 'index.php';
}

// If user is already logged in, redirect to the redirect URL
if (isset($_SESSION['user_id'])) {
    header("Location: $redirect");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // LOGIN
    if (isset($_POST['login'])) {
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';

        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['pass_phrase'])) {
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['username'];
            header("Location: $redirect");
            exit;
        } else {
            $loginError = "Invalid username or password.";
        }
    }

    // REGISTRATION
    if (isset($_POST['register'])) {
        $email = $_POST['newEmail'] ?? '';
        $username = $_POST['newUsername'] ?? '';
        $password = $_POST['newPassword'] ?? '';
        $confirmPassword = $_POST['confirmPassword'] ?? '';
        $first_name = $_POST['newFirstName'] ?? '';
        $last_name = $_POST['newLastName'] ?? '';

        if ($password !== $confirmPassword) {
            $registerError = "Passwords do not match.";
        } else {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (email, username, pass_phrase, first_name, last_name) VALUES (?, ?, ?, ?, ?)");

            try {
                $stmt->execute([$email, $username, $hashedPassword, $first_name, $last_name]);
                $registerSuccess = "Registration successful. You can now log in.";
            } catch (PDOException $e) {
                $registerError = "Email is already in use.";
            }
        }
    }
}

?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" >
  <meta name="viewport" content="width=device-width, initial-scale=1.0" >
  <title>Log In / Register</title>

    <!-- Normalize CSS styles -->
    <link href="includes/normalize.css" rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <link href="includes/styles.css" rel="stylesheet">

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script src="includes/loginscript.js" defer></script>

    <script src="includes/dropdown.js" defer></script>

  <style>

    /* Larger checkbox */
    .form-check-input {
      width: 20px;
      height: 20px;
    }

    /* Smaller login/register buttons */
    #loginForm button[type="submit"],
    #registerForm button[type="submit"] {
      padding: 0.25rem 0.5rem;
      font-size: 0.85rem;
      max-width: 200px;
      margin-left: auto;
      margin-right: auto;
    }
  </style>



</head>
<body>

 <!-- Navigation bar at the top -->
 <?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>



<?php if (!isset($isAdmin)) $isAdmin = false; ?>

<!-- Include site header -->
<?php include 'includes/header.php'; ?>

<main class="container my-4">
  <section class="row justify-content-center">
    <div class="col-12 col-md-10 col-lg-8 mb-5">

      <!-- Login Form -->
      <form id="loginForm" method="POST" action="login.php?redirect=<?= htmlspecialchars(urlencode($redirect)) ?>">
        <h3 class="text-center mb-4 fw-bold" style="color: #0b3d91;">Log In</h3>

        <div class="mb-3">
          <label for="username" class="form-label fw-semibold small">Username:</label>
          <input type="text" id="username" name="username" required class="form-control w-100 py-2 px-3" placeholder="Enter your username.">
        </div>

        <div class="mb-3">
          <label for="password" class="form-label fw-semibold small">Password:</label>
          <input type="password" id="password" name="password" required class="form-control w-100 py-2 px-3" placeholder="Enter your password.">
        </div>

        <?php if ($loginError): ?>
          <div class="alert alert-danger text-center"><?= htmlspecialchars($loginError) ?></div>
        <?php endif; ?>

        <div class="text-center">
          <button type="submit" name="login" class="btn btn-success fw-bold w-100 mt-3">Log In</button>
        </div>

        <div class="text-center mt-4 small-text">
          <span>Need an account? </span>
          <a href="#" onclick="displayRegisterForm(event)" class="form-toggle-link">Register</a>
        </div>
      </form>

      <!-- Registration Form -->
      <form id="registerForm" method="POST" action="" style="display:none;">
        <h3 class="text-center mb-4 fw-bold" style="color: #0b3d91;">Register</h3>

        <div class="mb-3">
          <label for="newFirstName" class="form-label fw-semibold small">First Name:</label>
          <input type="text" id="newFirstName" name="newFirstName" required class="form-control w-100 py-2 px-3" placeholder="First name">
        </div>

        <div class="mb-3">
          <label for="newLastName" class="form-label fw-semibold small">Last Name:</label>
          <input type="text" id="newLastName" name="newLastName" required class="form-control w-100 py-2 px-3" placeholder="Last name">
        </div>

        <div class="mb-3">
          <label for="newUsername" class="form-label fw-semibold small">Username:</label>
          <input type="text" id="newUsername" name="newUsername" required class="form-control w-100 py-2 px-3" placeholder="Username">
        </div>

        <div class="mb-3">
          <label for="newEmail" class="form-label fw-semibold small">Email:</label>
          <input type="email" id="newEmail" name="newEmail" required class="form-control w-100 py-2 px-3" placeholder="you@example.com">
        </div>

        <div class="mb-3">
          <label for="newPassword" class="form-label fw-semibold small">Password:</label>
          <input type="password" id="newPassword" name="newPassword" required class="form-control w-100 py-2 px-3" placeholder="Password">
        </div>

        <div class="mb-3">
          <label for="confirmPassword" class="form-label fw-semibold small">Repeat Password:</label>
          <input type="password" id="confirmPassword" name="confirmPassword" required class="form-control w-100 py-2 px-3" placeholder="Repeat password">
        </div>

        <div class="form-check mb-3 d-flex align-items-center justify-content-center">
          <input type="checkbox" class="form-check-input me-2" id="ageCheck" required>
          <label class="form-check-label small fw-semibold mb-0" for="ageCheck">
            I am over 13 years old and like playing games.
          </label>
        </div>

        <?php if ($registerError): ?>
          <div class="alert alert-danger text-center"><?= htmlspecialchars($registerError) ?></div>
        <?php elseif ($registerSuccess): ?>
          <div class="alert alert-success text-center"><?= htmlspecialchars($registerSuccess) ?></div>
        <?php endif; ?>

        <div class="text-center">
          <button type="submit" name="register" class="btn btn-primary fw-bold w-100">Register</button>
        </div>

        <div class="text-center mt-4 small-text">
          <span>Already have an account? </span>
          <a href="#" onclick="displayLoginForm(event)" class="form-toggle-link">Log In</a>
        </div>
      </form>

    </div>
  </section>
</main>

<!-- Include site footer -->
<?php include 'includes/footer.php'; ?>

</body>
</html>
