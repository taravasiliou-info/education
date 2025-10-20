<?php
session_start();
require_once '../includes/db.php';

$userId = $_SESSION['user_id'] ?? null;
$isAdmin = false;

if ($userId) {
    $stmt = $pdo->prepare("SELECT is_admin FROM users WHERE user_id = ?");
    $stmt->execute([$userId]);
    $isAdmin = (bool)$stmt->fetchColumn();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: text/html; charset=UTF-8');

    $logFile = __DIR__ . '/score_log.txt';

    function logMessage($msg) {
        global $logFile;
        file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] $msg\n", FILE_APPEND);
    }

    if (!isset($_SESSION['user_id'])) {
        logMessage("ERROR: User not logged in on POST");
        http_response_code(401);
        echo "Not logged in";
        exit;
    }

    $user_id = $_SESSION['user_id'];
    $score = isset($_POST['score']) ? (int)$_POST['score'] : null;
    $max_number = isset($_POST['max_number']) ? (int)$_POST['max_number'] : null;
    $operation = $_POST['operation'] ?? null;

    if ($score === null || $max_number === null || $operation === null || $score < 0) {
        logMessage("ERROR: Invalid input score=$score max_number=$max_number operation=$operation");
        http_response_code(400);
        echo "Invalid input";
        exit;
    }

    try {
        $stmt = $pdo->prepare(
            "INSERT INTO math_facts_scores (user_id, score, max_number, operation, end_time)
             VALUES (:user_id, :score, :max_number, :operation, NOW())"
        );

        $stmt->execute([
            ':user_id' => $user_id,
            ':score' => $score,
            ':max_number' => $max_number,
            ':operation' => $operation,
        ]);

        logMessage("SUCCESS: Saved score $score for user $user_id (operation: $operation, max_number: $max_number)");
        echo "Score saved";
    } catch (PDOException $e) {
        logMessage("ERROR: Database error: " . $e->getMessage());
        http_response_code(500);
        echo "Database error";
    }

    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Math Facts Practice Game</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="../includes/normalize.css" rel="stylesheet">
  <link href="../includes/styles.css" rel="stylesheet">
  <script src="../includes/dropdown.js" defer></script>
  <style>
      #fact-options {
      width: 250px;   /* adjust as needed */
      min-width: 250px;
      padding-right: 2em; /* space for caret */
      }
  </style>
</head>
<body>

<?php include '../includes/header-games.php'; ?>

<main class="d-flex justify-content-center my-4">
  <section style="max-width: 800px; width: 100%;">

    <div class="contentBox">
      <img src="../images/mathfacts.png" alt="Math Facts Game" class="img-fluid d-block mx-auto mb-4">
    </div>

    <h3 class="mb-4">Math Facts Practice</h3>

    <!-- Start Screen -->

<div id="start-screen" class="d-flex flex-column flex-md-row justify-content-center align-items-center gap-2 mb-3">
<select id="fact-options" class="form-select" aria-label="Select math operation">
    <option value="">Please select the operation.</option>
    <option value="add">Addition</option>
    <option value="sub">Subtraction</option>
    <option value="mul">Multiplication</option>
    <option value="div">Division</option>
  </select>
<button onclick="startGame()" class="btn btn-primary">Go</button>
</div>        

        <ul class="instructions mt-3">
          <li>Select a math operation from the dropdown menu.</li>
          <li>Click the "Go" button to begin the game.</li>
          <li>How many problems can you solve in thirty seconds?</li>
        </ul>
      </div>

      <?php if (!isset($_SESSION['user_id'])): ?>
        <p class="alert alert-danger fw-bold">
          If you would like to save your scores and view the leaderboard, please
          <a href="../login.php">log in</a>.
        </p>
      <?php endif; ?>
    </div>

    <!-- Game Screen -->
    <div id="game-screen" class="game-container hidden">
      <div id="timer">Time left: <span id="time-left">30</span>s</div>
      <div id="score">Score: <span id="score-count">0</span></div>
      <div id="problem"></div>
      <input type="number" id="answer" onkeydown="if(event.key==='Enter') checkAnswer()" autofocus>

      <div class="buttons-grid calculator-grid">
        <button onclick="appendNumber(7)">7</button>
        <button onclick="appendNumber(8)">8</button>
        <button onclick="appendNumber(9)">9</button>
        <button onclick="appendNumber(4)">4</button>
        <button onclick="appendNumber(5)">5</button>
        <button onclick="appendNumber(6)">6</button>
        <button onclick="appendNumber(1)">1</button>
        <button onclick="appendNumber(2)">2</button>
        <button onclick="appendNumber(3)">3</button>
        <button onclick="clearAnswer()" class="btn-clear">C</button>
        <button onclick="appendNumber(0)">0</button>
        <button onclick="checkAnswer()" class="btn-submit">⏎</button>
      </div>
    </div>

    <!-- End Screen -->
    <div id="end-screen" class="game-container hidden">
      <h3 class="times-up-text mb-4">Time's up!</h3>
      <p><span class="fw-bold">Your score:</span> <span id="final-score"></span></p>
      <div id="save-result-message" class="text-center"></div>
      <div class="text-center">
        <button onclick="location.reload()" class="mb-4">Play Again</button><br>

      </div>
    </div>

    <!-- Games Home Link -->
    <div class="text-center mt-1">
      <a href="../index.php" class="text-success fw-bold text-decoration-none d-inline-block mb-4 mt-2">
        Games Home</a>
    </div>
    
    <div style="margin-bottom: 20px;"></div>

  </section>
</main>

<script>
  let currentOperation = '';
  let score = 0;
  let timeLeft = 30;
  let timerInterval;
  let currentAnswer = 0;

  function startGame() {
    const operationSelect = document.getElementById('fact-options');
    currentOperation = operationSelect.value;

    if (!currentOperation) {
      alert('Please select an operation.');
      return;
    }

    document.getElementById('start-screen').classList.add('hidden');
    document.getElementById('game-screen').classList.remove('hidden');

    score = 0;
    timeLeft = 30;
    document.getElementById('score-count').textContent = score;
    document.getElementById('time-left').textContent = timeLeft;

    generateProblem();

    timerInterval = setInterval(() => {
      timeLeft--;
      document.getElementById('time-left').textContent = timeLeft;

      if (timeLeft <= 0) {
        endGame();
      }
    }, 1000);

    document.getElementById('answer').value = '';
    document.getElementById('answer').focus();
    document.querySelector('.buttons-grid').scrollIntoView({ behavior: 'smooth', block: 'center' });
  }

  function generateProblem() {
    const num1 = Math.floor(Math.random() * 10);
    const num2 = Math.floor(Math.random() * 10);
    let problemText;

    switch (currentOperation) {
      case 'add':
        problemText = `${num1} + ${num2}`;
        currentAnswer = num1 + num2;
        break;
      case 'sub':
        let larger = Math.max(num1, num2);
        let smaller = Math.min(num1, num2);
        problemText = `${larger} - ${smaller}`;
        currentAnswer = larger - smaller;
        break;
      case 'mul':
        problemText = `${num1} × ${num2}`;
        currentAnswer = num1 * num2;
        break;
      case 'div':
        const divisor = num2 || 1;
        const dividend = num1 * divisor;
        problemText = `${dividend} ÷ ${divisor}`;
        currentAnswer = dividend / divisor;
        break;
    }

    document.getElementById('problem').textContent = problemText;
    document.getElementById('answer').value = '';
    document.getElementById('answer').focus();
  }

  function checkAnswer() {
    const input = document.getElementById('answer');
    const userAnswer = Number(input.value);

    if (userAnswer === currentAnswer) {
      score++;
      document.getElementById('score-count').textContent = score;
      generateProblem();
    } else {
      input.value = '';
      input.focus();
    }
  }

  function appendNumber(number) {
    const input = document.getElementById('answer');
    input.value += number;
    input.focus();
  }

  function clearAnswer() {
    const input = document.getElementById('answer');
    input.value = '';
    input.focus();
  }

  function endGame() {
    clearInterval(timerInterval);
    document.getElementById('game-screen').classList.add('hidden');
    document.getElementById('end-screen').classList.remove('hidden');
    document.getElementById('final-score').textContent = score;

    submitScore(score, currentOperation, 10);
  }

  function submitScore(score, operation, maxNumber) {
    fetch(window.location.href, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded'
      },
      body: new URLSearchParams({
        score: score,
        operation: operation,
        max_number: maxNumber
      })
    })
    .then(response => response.text())
    .then(result => {
      document.getElementById('save-result-message').innerHTML =
        `<div class="alert alert-success">✅ ${result}</div>`;
    })
    .catch(err => {
      document.getElementById('save-result-message').innerHTML =
        `<div class="alert alert-danger">❌ Failed to save score.</div>`;
      console.error("Score submission failed:", err);
    });
  }
</script>

<?php include '../includes/footer-games.php'; ?>

</body>
</html>
