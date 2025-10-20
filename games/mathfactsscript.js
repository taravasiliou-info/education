
// Math Facts Practice Game Script

// Variables to keep track of the current operation, score, timer, and interval ID

let currentOperation = '';
let score = 0;
let timeLeft = 30; // Time left in seconds for the game
let timerInterval; // Stores the interval ID for the countdown timer
let currentAnswer = 0; // Stores the correct answer for the current problem

// Function to start the game when user selects an operation and clicks start

function startGame() {
  const operationSelect = document.getElementById('fact-options');
  currentOperation = operationSelect.value;

  if (!currentOperation) {
    alert('Please select an operation.');
    return;
  }

  // Hide start screen and show game screen
  document.getElementById('start-screen').classList.add('hidden');
  document.getElementById('game-screen').classList.remove('hidden');

  // Reset game state
  score = 0;
  timeLeft = 30;
  document.getElementById('score-count').textContent = score;
  document.getElementById('time-left').textContent = timeLeft;

  // Generate first problem
  generateProblem();

  // Start countdown timer
  timerInterval = setInterval(() => {
    timeLeft--;
    document.getElementById('time-left').textContent = timeLeft;

    if (timeLeft <= 0) {
      endGame();
    }
  }, 1000);

  // Prepare input
  const input = document.getElementById('answer');
  input.value = '';
  input.focus();

  // Scroll buttons into view
  document.querySelector('.buttons-grid').scrollIntoView({ behavior: 'smooth', block: 'center' });
}

// Generate a new math problem
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

  const input = document.getElementById('answer');
  input.value = '';
  input.focus();
}

// Check the user's answer
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

// Append a number from button press to the answer input
function appendNumber(number) {
  const input = document.getElementById('answer');
  input.value += number;
  input.focus();
}

// Clear the answer input field
function clearAnswer() {
  const input = document.getElementById('answer');
  input.value = '';
  input.focus();
}

// End the game and show the final screen
function endGame() {
  clearInterval(timerInterval); // Stop timer
  document.getElementById('game-screen').classList.add('hidden');
  document.getElementById('end-screen').classList.remove('hidden');
  document.getElementById('final-score').textContent = score;
}

function submitMathScore(score, operation, maxNumber) {
  fetch('../includes/save_math_score.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({
      score: score,
      operation: operation,
      max_number: maxNumber
    })
  });
}


