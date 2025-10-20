<?php
// Start session to access user session data
session_start();

// Include database connection script
require_once 'includes/db.php';

// Redirect to login page if user is not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Get current user's ID from the session
$userId = $_SESSION['user_id'];

// Initialize the isAdmin variable (used in header or for access control)
$isAdmin = false;

// Check if the user is an admin by querying the database
$stmt = $pdo->prepare("SELECT is_admin FROM users WHERE user_id = ?");
$stmt->execute([$userId]);
$isAdmin = (bool)$stmt->fetchColumn();

// Fetch current user details to populate the profile form
$stmt = $pdo->prepare("SELECT first_name, last_name, email, username FROM users WHERE user_id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Initialize an empty message string to store success or error messages
$updateMessage = '';

// Check if the form was submitted using POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Retrieve form data, default to empty string if not set
    $firstName = $_POST['first_name'] ?? '';
    $lastName = $_POST['last_name'] ?? '';
    $email = $_POST['email'] ?? '';
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    // Validate that passwords match if a password was entered
    if ($password && $password !== $confirmPassword) {
        $updateMessage = "Passwords do not match.";
    } else {

        // If password is provided, hash and update it with the profile
        if ($password) {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET first_name=?, last_name=?, email=?, username=?, pass_phrase=? WHERE user_id=?");
            $stmt->execute([$firstName, $lastName, $email, $username, $hashedPassword, $userId]);
        } else {

            // Update profile without changing the password
            $stmt = $pdo->prepare("UPDATE users SET first_name=?, last_name=?, email=?, username=? WHERE user_id=?");
            $stmt->execute([$firstName, $lastName, $email, $username, $userId]);
        }

        // Re-fetch the updated user data to display in the form
        $stmt = $pdo->prepare("SELECT first_name, last_name, email, username FROM users WHERE user_id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Set a success message
        $updateMessage = "Profile information updated successfully.";
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>

  <!-- Meta tags for character encoding and responsiveness -->
  <meta charset="UTF-8" >
  <meta name="viewport" content="width=device-width, initial-scale=1" >

  <!-- Page title -->
  <title>My Profile</title>

  <!-- Bootstrap CSS for styling -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" >
  
  <!-- Normalize CSS to make browsers render all elements more consistently -->
  <link href="includes/normalize.css" rel="stylesheet">

  <!-- Custom site styles -->
  <link href="includes/styles.css" rel="stylesheet" >

  <!-- Custom dropdown script -->
  <script src="includes/dropdown.js" defer></script>

  <!-- Bootstrap JS bundle (includes Popper) -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body>

<!-- Include the header (may contain navigation and user info) -->
<?php include 'includes/header.php'; ?>


<main class="container my-4">
  <div class="row justify-content-center">
    <div class="col-lg-8">

      <!-- Page heading -->
      <h3 class="text-center mb-4">My Profile</h3>

      <!-- Display feedback message if any -->
      <?php if ($updateMessage): ?>
        <div class="alert alert-info text-center"><?= htmlspecialchars($updateMessage) ?></div>
      <?php endif; ?>

      <!-- Profile update form -->
      <form method="post" class="needs-validation" novalidate>
        
        <!-- First Name input -->
        <div class="mb-3">
          <label for="first_name" class="form-label small fw-bold">First Name:</label>
          <input type="text" class="form-control w-100" id="first_name" name="first_name" required 
          value="<?= htmlspecialchars($_POST['first_name'] ?? ($user['first_name'] ?? '')) ?>">
          <div class="invalid-feedback">First name is required.</div>
        </div>

        <!-- Last Name input -->
        <div class="mb-3">
          <label for="last_name" class="form-label small fw-bold">Last Name:</label>
          <input type="text" class="form-control w-100" id="last_name" name="last_name" required 
          value="<?= htmlspecialchars($_POST['last_name'] ?? ($user['last_name'] ?? '')) ?>">
          <div class="invalid-feedback">Last name is required.</div>
        </div>

        <!-- Email input -->
        <div class="mb-3">
          <label for="email" class="form-label small fw-bold">Email:</label>
          <input type="email" class="form-control w-100" id="email" name="email" required 
          value="<?= htmlspecialchars($_POST['email'] ?? ($user['email'] ?? '')) ?>">
          <div class="invalid-feedback">Please enter a valid email address.</div>
        </div>

        <!-- Username input -->
        <div class="mb-3">
          <label for="username" class="form-label small fw-bold">Username:</label>
          <input type="text" class="form-control w-100" id="username" name="username" required 
          value="<?= htmlspecialchars($_POST['username'] ?? ($user['username'] ?? '')) ?>">
          <div class="invalid-feedback">Please enter a valid username.</div>
        </div>

        <!-- Password input (optional) -->
        <div class="mb-3">
          <label for="password" class="form-label small fw-bold">New Password (optional):</label>
          <input type="password" class="form-control w-100" id="password" name="password" minlength="6">
          <div class="invalid-feedback">Password must be at least 6 characters.</div>
        </div>

        <!-- Confirm Password input -->
        <div class="mb-3">
          <label for="confirm_password" class="form-label small fw-bold">Confirm New Password:</label>
          <input type="password" class="form-control w-100" id="confirm_password" name="confirm_password">
          <div class="invalid-feedback">Passwords must match.</div>
        </div>

        <!-- Submit button -->
        <div class="text-center mt-4">
          <button type="submit" class="playButton">Update Profile</button>
        </div>
      </form>
    </div>
  </div>
</main>

<!-- Include the footer -->
<?php include 'includes/footer.php'; ?>

<!-- JavaScript for client-side form validation -->
<script>
  (() => {
    const form = document.querySelector('.needs-validation');

    // On form submit, validate input
    form.addEventListener('submit', event => {
      const password = form.password.value;
      const confirm = form.confirm_password.value;

      // Check for minimum password length
      if (password && password.length < 6) {
        form.password.setCustomValidity('Password must be at least 6 characters');
      } else {
        form.password.setCustomValidity('');
      }

      // Check if passwords match
      if (password && confirm && password !== confirm) {
        form.confirm_password.setCustomValidity('Passwords do not match');
      } else {
        form.confirm_password.setCustomValidity('');
      }

      // Prevent submission if validation fails
      if (!form.checkValidity()) {
        event.preventDefault();
        event.stopPropagation();
      }

      // Add Bootstrap class for validation UI
      form.classList.add('was-validated');
    }, false);
  })();
</script>
</body>
</html>
