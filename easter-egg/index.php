<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trivia Challenge</title>
    <link rel="icon" type="image/png" href="https://raw.githubusercontent.com/HirotakaDango/trivia/main/icon.png">
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <meta property="og:url" content="">
    <meta property="og:type" content="website">
    <meta property="og:title" content="Trivia Challenge">
    <meta property="og:description" content="Answer every question!">
    <meta property="og:image" content="https://raw.githubusercontent.com/HirotakaDango/trivia/main/icon.png">
    <script>
      // Inline manifest
      const manifest = {
        "name": "Trivia Challenge",
        "short_name": "Trivia",
        "start_url": ".",
        "display": "standalone",
        "background_color": "#ffffff",
        "theme_color": "#000000",
        "icons": [
          {
            "src": "https://raw.githubusercontent.com/HirotakaDango/trivia/main/icon.png",
            "sizes": "192x192",
            "type": "image/png"
          }
        ]
      };

      const manifestBlob = new Blob([JSON.stringify(manifest)], { type: 'application/json' });
      const manifestURL = URL.createObjectURL(manifestBlob);
      const link = document.createElement('link');
      link.rel = 'manifest';
      link.href = manifestURL;
      document.head.appendChild(link);

      // Inline service worker registration
      if ('serviceWorker' in navigator) {
        const swBlob = new Blob([`
          self.addEventListener('install', function(event) {
            event.waitUntil(
              caches.open('trivia-challenge-v1').then(function(cache) {
                return cache.addAll([
                  '/',
                  '/easter-egg/index.html',
                  '/https://raw.githubusercontent.com/HirotakaDango/trivia/main/icon.png'
                ]);
              })
            );
          });

          self.addEventListener('fetch', function(event) {
            event.respondWith(
              caches.match(event.request).then(function(response) {
                return response || fetch(event.request);
              })
            );
          });
        `], { type: 'application/javascript' });

        const swURL = URL.createObjectURL(swBlob);
        navigator.serviceWorker.register(swURL).then(function(registration) {
          console.log('ServiceWorker registration successful with scope: ', registration.scope);
        }).catch(function(error) {
          console.log('ServiceWorker registration failed: ', error);
        });
      }
    </script>
  </head>
  <body>
    <div class="container my-5">
      <div class="d-flex justify-content-between">
        <div id="health-bar" class="d-none">Health: <span id="health">100</span></div>
        <div id="difficulty" class="d-none">Difficulty: <span id="difficulty-level">easy</span></div>
        <div id="score" class="d-none">Score: <span id="score-value">0</span></div>
      </div>
      <div id="menu" class="mb-3">
        <h1 class="fw-bold mb-4 text-center">Trivia Game</h1>
        <div class="text-center">
          <div class="btn-group-vertical gap-2">
            <button class="btn btn-primary mb-2 rounded" onclick="startGame('easy')">Easy</button>
            <button class="btn btn-warning mb-2 rounded" onclick="startGame('medium')">Medium</button>
            <button class="btn btn-danger mb-2 rounded" onclick="startGame('hard')">Hard</button>
          </div>
        </div>
      </div>
      <div id="welcome-message" class="my-3 text-center">Welcome to the trivia game. Select a difficulty to begin.</div>
      <div id="question" class="my-3 text-center"></div>
      <div id="answer" class="list-group"></div>
      <div id="feedback" class="feedback"></div>
      <div id="next-question-container" class="text-center mt-3 d-none">
        <button id="next-question" class="btn btn-info">Next Question</button>
      </div>
      <div id="back-to-menu-container" class="text-center mt-3 d-none">
        <button id="back-to-menu" class="btn btn-primary">Back to Main Menu</button>
      </div>
    </div>

    <script>
      let correctAnswer = '';
      let questionData = null;
      let score = 0;
      let health = 100;
      let difficulty = 'easy';
      let answerClicked = false;

      function startGame(selectedDifficulty) {
        difficulty = selectedDifficulty;
        document.getElementById('menu').style.display = 'none';
        document.getElementById('welcome-message').style.display = 'none'; // Hide the welcome message
        document.getElementById('health-bar').classList.remove('d-none');
        document.getElementById('difficulty').classList.remove('d-none');
        document.getElementById('score').classList.remove('d-none');
        document.getElementById('difficulty-level').textContent = difficulty;
        fetchTrivia();
      }

      async function fetchTrivia() {
        answerClicked = false; // Reset answer click status for the new question
        document.getElementById('next-question').disabled = true; // Disable the button to prevent multiple clicks
        try {
          const response = await fetch(`https://opentdb.com/api.php?amount=1&difficulty=${difficulty}&type=multiple`);
          const data = await response.json();

          if (data.results.length > 0) {
            questionData = data.results[0];
            const question = questionData.question;
            correctAnswer = questionData.correct_answer;
            const incorrectAnswers = questionData.incorrect_answers;
            const allAnswers = [correctAnswer, ...incorrectAnswers].sort(() => Math.random() - 0.5);

            document.getElementById('question').innerHTML = question;
            document.getElementById('answer').innerHTML = '';
            document.getElementById('feedback').innerHTML = '';
            document.getElementById('next-question-container').classList.add('d-none'); // Hide the Next Question button

            allAnswers.forEach((answer, index) => {
              const answerElement = document.createElement('div');
              answerElement.className = 'list-group-item list-group-item-action text-start border rounded answer-option my-1';
              answerElement.innerHTML = `${String.fromCharCode(65 + index)}. ${answer}`;
              answerElement.onclick = () => checkAnswer(answer);
              document.getElementById('answer').appendChild(answerElement);
            });
          }
        } catch (error) {
          console.error('Error fetching trivia:', error);
        } finally {
          document.getElementById('next-question').disabled = false; // Re-enable the button
        }
      }

      function checkAnswer(selectedAnswer) {
        if (answerClicked) return; // Prevent further clicks
        answerClicked = true;

        const feedbackElement = document.getElementById('feedback');
        if (selectedAnswer === correctAnswer) {
          feedbackElement.innerHTML = `Your answer "${selectedAnswer}" is correct! &#128077;`; // Detailed feedback
          feedbackElement.classList.remove('text-danger');
          feedbackElement.classList.add('text-success');
          score++;
        } else {
          feedbackElement.innerHTML = `You're incorrect! The correct answer was: <strong>${correctAnswer}</strong> &#128078;`; // Detailed feedback for incorrect answer
          feedbackElement.classList.remove('text-success');
          feedbackElement.classList.add('text-danger');
          health -= 5;
          if (health <= 0) {
            endGame();
            return;
          }
        }

        updateScoreAndHealth();
        document.getElementById('next-question-container').classList.remove('d-none'); // Show the Next Question button
      }

      function updateScoreAndHealth() {
        document.getElementById('score-value').textContent = score;
        document.getElementById('health').textContent = health;
      }

      function endGame() {
        document.getElementById('feedback').innerHTML = `Game Over! Your final score is <strong>${score}</strong>. &#128577;`; // Using HTML entity for frowning face emoji
        document.getElementById('question').innerHTML = '';
        document.getElementById('answer').innerHTML = '';
        document.getElementById('next-question-container').classList.add('d-none'); // Hide Next Question button
        document.getElementById('back-to-menu-container').classList.remove('d-none'); // Show Back to Menu button
      }

      function goBackToMenu() {
        document.getElementById('menu').style.display = 'block';
        document.getElementById('welcome-message').style.display = 'block'; // Keep welcome message visible
        document.getElementById('back-to-menu-container').classList.add('d-none'); // Hide Back to Menu button
        document.getElementById('health-bar').classList.add('d-none');
        document.getElementById('difficulty').classList.add('d-none');
        document.getElementById('score').classList.add('d-none');
        document.getElementById('question').innerHTML = '';
        document.getElementById('answer').innerHTML = '';
        document.getElementById('feedback').innerHTML = '';
      }

      document.getElementById('next-question').onclick = function() {
        fetchTrivia(); // Fetch the next question when the button is clicked
      };

      document.getElementById('back-to-menu').onclick = function() {
        goBackToMenu(); // Go back to the main menu
      };

      // Inline service worker script
      if ('serviceWorker' in navigator) {
        window.addEventListener('load', function() {
          navigator.serviceWorker.register('service-worker.js');
        });
      }
    </script>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
  </body>
</html>