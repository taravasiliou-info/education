<?php

// Start the session to manage user authentication
session_start();

// Include the database connection file to establish DB connection
require_once '../includes/db.php';

$userId = $_SESSION['user_id'] ?? null;
$isAdmin = false;

if ($userId) {
    $stmt = $pdo->prepare("SELECT is_admin FROM users WHERE user_id = ?");
    $stmt->execute([$userId]);
    $isAdmin = (bool)$stmt->fetchColumn();
}

// Set HTTP Content-Type header only if this is a POST request (to specify encoding)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: text/html; charset=UTF-8');
}

// Define the path for a log file to store submission logs
$logFile = __DIR__ . '/score_log.txt';

// Function to write log messages with timestamps to the log file
function logMessage($msg) {
    global $logFile;
    file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] $msg\n", FILE_APPEND);
}

// Handle form submission for saving game score to the database
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Double check user login status before processing POST data
    if (!isset($_SESSION['user_id'])) {
        logMessage("ERROR: User not logged in on POST");
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    }

    // Retrieve user ID and sanitize incoming POST data for score and max_number
    $user_id = $_SESSION['user_id'];
    $score = isset($_POST['score']) ? (int)$_POST['score'] : null;
    $max_number = isset($_POST['max_number']) ? (int)$_POST['max_number'] : null;

    // Validate that score and max_number are within acceptable ranges
    if ($score === null || $max_number === null || $score < 0 || $max_number < 5 || $max_number > 8) {
        logMessage("ERROR: Invalid input score=$score max_number=$max_number");
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    }

    // Attempt to insert the submitted score into the anagram_hunt_scores database table
    try {
        $stmt = $pdo->prepare(
            "INSERT INTO anagram_hunt_scores (user_id, score, max_number, end_time)
             VALUES (:user_id, :score, :max_number, NOW())"
        );
        $stmt->execute([
            ':user_id' => $user_id,
            ':score' => $score,
            ':max_number' => $max_number,
        ]);

        // Log success and set a session flag to show success message on reload
        logMessage("SUCCESS: Saved score $score for user $user_id (word length: $max_number)");
        $_SESSION['score_saved'] = true;

        // Redirect to the same page to clear POST data and prevent form resubmission
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;

    } catch (PDOException $e) {
        // Log any database errors and redirect back to the same page
        $error = 'Database error: ' . $e->getMessage();
        logMessage("ERROR: $error");
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    }
}
?>

<html>
  
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Anagram Hunt Game</title>
  
  <!-- Include Bootstrap CSS for styling -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  
  <!-- Include Normalize CSS for consistent styling across browsers -->
  <link href="../includes/normalize.css" rel="stylesheet">
  
  <!-- Custom styles for the game -->
  <link href="../includes/styles.css" rel="stylesheet">

  <!-- Dropdown menu script for UI interactions, loaded deferred -->
  <script src="../includes/dropdown.js" defer></script>

  <!-- Pass PHP session user ID to JavaScript global variable -->
  <script>
    window.loggedInUserId = <?= isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 'null' ?>;
  </script>

  <!-- Load anagrams data JavaScript file -->
  <script src="anagrams.js"></script>

  <!-- Load Vue.js framework for reactive UI -->
  <script src="https://cdn.jsdelivr.net/npm/vue@3.4.15/dist/vue.global.prod.js"></script>

  <!-- Vue application logic -->
  <script>
    window.addEventListener("DOMContentLoaded", () => {
      const { createApp } = Vue;
      createApp({
        data() {
          return {
            gameState: "start",        // current game phase: start, play, gameover
            wordLength: 5,             // selected word length for the game
            baseWord: "",              // the base word to find anagrams for
            guess: "",                 // current user guess input
            guessedWords: [],          // list of correctly guessed anagrams
            allAnagrams: [],           // all valid anagrams for current base word
            remainingAnagrams: 0,      // number of anagrams still to find
            score: 0,                  // current score
            timeLeft: 60,              // countdown timer in seconds
            timer: null,               // reference to the interval timer
            errorMessage: "",          // any error messages to display to user
            user_id: window.loggedInUserId  // user id passed from PHP session
          };
        },
        methods: {

          // Start a new game with the selected word length
          startGame() {
            this.errorMessage = "";
            if (!anagrams[this.wordLength]) {
              this.errorMessage = `No anagrams for length ${this.wordLength}. Choose 4–8.`;
              return;
            }

            // Pick a random set of anagrams and select a base word
            const set = anagrams[this.wordLength][Math.floor(Math.random() * anagrams[this.wordLength].length)];
            this.baseWord = set[Math.floor(Math.random() * set.length)];
            this.allAnagrams = set.filter(w => w !== this.baseWord);
            this.remainingAnagrams = this.allAnagrams.length;
            this.guessedWords = [];
            this.score = 0;
            this.guess = "";
            this.timeLeft = 60;
            this.gameState = "play";
            
            // Clear existing timer if any, and start new countdown timer
            if (this.timer) clearInterval(this.timer);
            this.timer = setInterval(() => {
              if (--this.timeLeft <= 0) this.gameOver();
            }, 1000);
          },

          // End the game when time runs out
          gameOver() {
            clearInterval(this.timer);
            this.gameState = "gameover";
            this.submitScore();
          },

          // Submit the score via hidden form to server for saving
          submitScore() {
            if (!this.user_id) return;  // only submit if user is logged in
            const form = document.getElementById('scoreForm');
            form.score.value = this.score;
            form.max_number.value = this.wordLength;
            form.submit();
          },

          // Check if user's guess is a valid new anagram
          checkGuess() {
            const clean = this.guess.trim().toLowerCase();
            if (clean && this.allAnagrams.includes(clean) && !this.guessedWords.includes(clean)) {
              this.guessedWords.push(clean);
              this.score++;
              this.remainingAnagrams--;
            }
            this.guess = "";
          },

          // Restart the game and reset to initial state
          restartGame() {
            clearInterval(this.timer);
            this.gameState = "start";
            this.errorMessage = "";
          }
        }
      }).mount("#anagram-hunt");
    });
  </script>
</head>


<body>

<?php include '../includes/header-games.php'; ?>

<main id="anagram-hunt" class="d-flex justify-content-center my-4">
  <div style="max-width: 800px; width: 100%;">
  
    <!-- Show success message if score was saved in previous submission -->
    <?php if (!empty($_SESSION['score_saved'])): ?>
      <div class="alert alert-success text-center" role="alert">
        ✅ Your score was saved successfully!
      </div>
      <?php unset($_SESSION['score_saved']); ?>
      <script>

        // Hide success message after 4 seconds
        setTimeout(() => {
          document.querySelector('.alert-success')?.style.display = 'none';
        }, 4000);
      </script>
    <?php endif; ?>

    <div class="contentBox">

      <!-- Display game logo -->
      <img src="../images/anagramhunt.png" alt="Anagram Hunt" class="img-fluid d-block mx-auto mb-4">
    </div>

    <h3 class="mb-4">Anagram Hunt</h3>

    <!-- Explanation about what an anagram is -->
    <div class="contentBox">
      <h4 style="font-size: 1rem; font-weight: bold; color: green;">What is an Anagram?</h4>
      <p style="text-align: left;">An anagram is a word made by rearranging the letters of another word. For example, post, pots, opts, tops, and spot are all anagrams of stop. The object of the Anagram Hunt game is to find the most anagrams for words of a specified length in a minute.</p>
    </div>

    <!-- Instructions on how to play the game -->
    <div class="contentBox">
      <h4 style="font-size: 1rem; font-weight: bold; color: black;">How to Play</h4>
      <ul>
        <li>Choose word length.</li>
        <li>Press Play!</li>
        <li>How many anagrams can you find in a minute?</li>
      </ul>

      <!-- Show alert if user is not logged in -->
      <?php if (!isset($_SESSION['user_id'])): ?>
        <p class="alert alert-danger fw-bold"> If you would like to save your scores and view the leaderboard, please
          <a href="../login.php" >log in</a>. </p>
      <?php endif; ?>
    </div>

    <!-- Vue conditional: show start screen with word length input -->
    <div v-if="gameState === 'start'">
      <div class="text-center">
        <h3>Select Word Length</h3>
        <input type="number" v-model.number="wordLength" min="4" max="8"
               class="form-control mb-3 mx-auto" style="max-width: 200px;">
        <button @click="startGame" class="btn btn-primary"><strong>Play!</strong></button>
        <div v-if="errorMessage" class="alert alert-danger mt-3">{{ errorMessage }}</div>
      </div>
    </div>

    <!-- Vue conditional: show gameplay UI -->
    <div v-else-if="gameState === 'play'">
      <h3 class="mb-3">Find anagrams for: <strong>{{ baseWord }}</strong></h3>
      <p>⏱️ {{ timeLeft }} sec | Score: {{ score }} | Remaining: {{ remainingAnagrams }}</p>
      <input type="text" v-model="guess" @keyup.enter="checkGuess"
             class="form-control mb-3 mx-auto" placeholder="Enter anagram..."
             style="max-width: 300px;">
<ul class="list-group mb-3 mx-auto" style="max-width: 250px;">
  <li v-for="(word,i) in guessedWords" :key="i" class="list-group-item">{{ word }}</li>
</ul>
    </div>

    <!-- Vue conditional: show game over screen -->
    <div v-else-if="gameState === 'gameover'" class="text-center">
      <h4 class="text-danger mb-4 fs-6">⏳ Game Over!</h4>
      <p>Your score: {{ score }}</p>
      <button class="btn btn-success btn-sm fw-bold mb-2" @click="restartGame">Play Again</button>
    </div>
  </div>
</main>

<!-- Link to Games Home page styled as green bold text -->
<div class="text-center mt-1">
    <a href="../index.php" class="fw-bold text-success" style="font-size: .9rem; text-decoration: none; margin-bottom: 40px;">
        Games Home
    </a>
</div>

<div style="margin-bottom: 20px;"></div>  <!-- Spacer -->

<?php include '../includes/footer-games.php'; ?>

<!-- Hidden form used by Vue to submit scores to the server -->
<form id="scoreForm" method="POST" style="display:none;">
  <input type="hidden" name="score" />
  <input type="hidden" name="max_number" />
</form>

</body>
</html>
