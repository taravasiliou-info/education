<?php
// Start session to access session variables
session_start();

// Include database connection
require_once 'includes/db.php';

$userId = $_SESSION['user_id'] ?? null;  // ✅ Prevent warning
$isAdmin = false;

if ($userId) {
    $stmt = $pdo->prepare("SELECT is_admin FROM users WHERE user_id = ?");
    $stmt->execute([$userId]);
    $isAdmin = (bool)$stmt->fetchColumn();
}

// Enable full error reporting for debugging (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Import PHPMailer classes into the global namespace
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Include PHPMailer library files
require 'includes/PHPMailer/PHPMailer.php';
require 'includes/PHPMailer/SMTP.php';
require 'includes/PHPMailer/Exception.php';

// Initialize flags for tracking form result
$messageSent = false;
$errorMessage = '';

// Initialize form variables (used for sticky inputs)
$name = $email = $subject = $message = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Sanitize and escape form input values
    $name    = isset($_POST['name']) ? htmlspecialchars(trim($_POST['name'])) : '';
    $email   = isset($_POST['email']) ? trim($_POST['email']) : '';
    $subject = isset($_POST['subject']) ? htmlspecialchars(trim($_POST['subject'])) : '';
    $message = isset($_POST['message']) ? htmlspecialchars(trim($_POST['message'])) : '';

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errorMessage = "Invalid email format.";
    } else {

    // Create a new instance of PHPMailer
    $mail = new PHPMailer(true);

        try {
            // Configure SMTP settings for sending via Gmail
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'play2learnvasiliou@gmail.com'; // Your Gmail address
            $mail->Password   = 'yktgsqgeouysgcyc';              // Your Gmail App Password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            // Set sender and recipient details
            $mail->setFrom($mail->Username, 'Contact Form');
            $mail->addAddress('play2learnvasiliou@gmail.com'); // Receiver's email
            $mail->addReplyTo($email, $name); // User's reply-to info

            // Format email content
            $mail->isHTML(true); // Send as HTML
            $mail->Subject = $subject;
            $mail->Body    = "<strong>Name:</strong> $name<br><strong>Email:</strong> $email<br><strong>Message:</strong><br>" . nl2br($message);
            $mail->AltBody = "Name: $name\nEmail: $email\n\nMessage:\n$message"; // Plain text alternative

            // Send the email
            $mail->send();
            $messageSent = true;

            // Clear the form values after successful send
            $name = $email = $subject = $message = '';

        } catch (Exception $e) {

            // Display error message if sending fails
            $errorMessage = "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>

  <!-- Basic Meta Tags -->
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <!-- Bootstrap CSS from CDN -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

  <!-- Normalize.css to ensure cross-browser consistency -->
  <link href="includes/normalize.css" rel="stylesheet">
  
  <!-- Your custom CSS file -->
  <link href="includes/styles.css" rel="stylesheet">

  <!-- Optional JS for dropdown navigation -->
  <script src="includes/dropdown.js" defer></script>

  <!-- Bootstrap JS bundle from CDN -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

  <title>Contact Us - Play2Learn</title>
</head>
<body>
  
<!-- Include header -->
<?php include 'includes/header.php'; ?>

<!-- Main content area -->
<main class="container mt-3">
  <h3 class="text-center mb-4 fw-bold" style="color: #0b3d91;">Contact Us</h3>

  <!-- Display a success or error message if applicable -->
  <?php if ($messageSent): ?>
    <div class="alert alert-success">Your message has been sent successfully!</div>
  <?php elseif (!empty($errorMessage)): ?>
    <div class="alert alert-danger"><?= $errorMessage ?></div>
  <?php endif; ?>

  <!-- Contact form starts here -->
  <form method="POST" action="contact-us.php" class="mt-4">

    <!-- Name input -->
    <div class="mb-3">
      <label for="name" class="form-label fw-semibold small">Name:</label>
      <input required type="text" class="form-control" name="name" id="name" value="<?= htmlspecialchars($name) ?>">
    </div>

    <!-- Email input -->
    <div class="mb-3">
      <label for="email" class="form-label fw-semibold small">Email:</label>
      <input required type="email" class="form-control" name="email" id="email" value="<?= htmlspecialchars($email) ?>">
    </div>

    <!-- Subject input -->
    <div class="mb-3">
      <label for="subject" class="form-label fw-semibold small">Subject:</label>
      <input required type="text" class="form-control" name="subject" id="subject" value="<?= htmlspecialchars($subject) ?>">
    </div>

    <!-- Message textarea -->
    <div class="mb-3">
      <label for="message" class="form-label fw-semibold small">Message:</label>
      <textarea required class="form-control" name="message" id="message" rows="5"><?= htmlspecialchars($message) ?></textarea>
    </div>

    <!-- Submit button -->
    <div class="text-center mt-4">
      <button type="submit" class="playButton">Send Message</button>
    </div>

    <!-- Extra spacing -->
    <div style="height: 30px;"></div>
  </form>
</main>

<!-- Include site footer -->
<?php include 'includes/footer.php'; ?>

</body>
</html>

