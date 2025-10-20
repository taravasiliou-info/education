
window.loggedInUserId = <?= isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 'null' ?>;

const anagrams = {
  5: [
    ["abets", "baste", "betas", "beast", "beats"],
    ["acres", "cares", "races", "scare"],
    ["alert", "alter", "later"],
    ["angel", "angle", "glean"],
    ["baker", "brake", "break"],
    ["bared", "beard", "bread", "debar"],
    ["dater", "rated", "trade", "tread"],
    ["below", "bowel", "elbow"],
    ["caret", "cater", "crate", "trace", "react"]
  ],
  6: [
    ["arrest", "rarest", "raster", "raters", "starer"],
    ["carets", "caters", "caster", "crates", "reacts", "recast", "traces"],
    ["canter", "nectar", "recant", "trance"],
    ["danger", "gander", "garden", "ranged"],
    ["daters", "trades", "treads", "stared"]
  ],
  7: [
    ["allergy", "gallery", "largely", "regally"],
    ["aspired", "despair", "diapers", "praised"],
    ["claimed", "decimal", "declaim", "medical"],
    ["dearths", "hardest", "hatreds", "threads", "trashed"],
    ["detains", "instead", "sainted", "stained"]
  ],
  8: [
    ["parroted", "predator", "prorated", "teardrop"],
    ["repaints", "painters", "pantries", "pertains"],
    ["restrain", "retrains", "strainer", "terrains", "trainers"],
    ["construe", "counters", "recounts", "trounces"]
  ]
};

window.addEventListener("DOMContentLoaded", () => {
  const { createApp } = Vue;
  createApp({
    data() {
      return {
        gameState: "start",
        wordLength: 5,
        baseWord: "",
        guess: "",
        guessedWords: [],
        allAnagrams: [],
        remainingAnagrams: 0,
        score: 0,
        timeLeft: 60,
        timer: null,
        errorMessage: "",
        user_id: window.loggedInUserId
      };
    },
    methods: {
      startGame() {
        this.errorMessage = "";
        if (!anagrams[this.wordLength]) {
          this.errorMessage = `No anagrams for length ${this.wordLength}. Choose 5–8.`;
          return;
        }
        const set = anagrams[this.wordLength][Math.floor(Math.random()*anagrams[this.wordLength].length)];
        this.baseWord = set[Math.floor(Math.random()*set.length)];
        this.allAnagrams = set.filter(w=>w!==this.baseWord);
        this.remainingAnagrams = this.allAnagrams.length;
        this.guessedWords = [];
        this.score = 0;
        this.guess = "";
        this.timeLeft = 60;
        this.gameState = "play";
        if (this.timer) clearInterval(this.timer);
        this.timer = setInterval(() => {
          if (--this.timeLeft <= 0) this.gameOver();
        }, 1000);
      },
      gameOver() {
        clearInterval(this.timer);
        this.gameState = "gameover";
        this.submitScore();
      },
      submitScore() {
        if (!this.user_id) return;
        const form = document.getElementById('scoreForm');
        form.score.value = this.score;
        form.max_number.value = this.wordLength;
        form.submit();
      },
      checkGuess() {
        const clean = this.guess.trim().toLowerCase();
        if (clean && this.allAnagrams.includes(clean) && !this.guessedWords.includes(clean)) {
          this.guessedWords.push(clean);
          this.score++;
          this.remainingAnagrams--;
        }
        this.guess = "";
      },
      restartGame() {
        clearInterval(this.timer);
        this.gameState = "start";
        this.errorMessage = "";
      }
    }
  }).mount("#anagram-hunt");
});