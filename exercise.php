<?php
// exercise.php
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>MoveByMood</title>
  <link rel="icon" type="image/png" href="images/favicon.png">
  <link rel="stylesheet" href="exercise.css">
</head>
<body>
  <div class="exercise-container">
    <!-- Step 1 -->
    <div class="step" id="step1">
      <h1>Welcome! How do you feel today?</h1>
      <div class="button-group">
        <button onclick="nextStep(2)">Lazy</button>
        <button onclick="nextStep(2)">Tired</button>
        <button onclick="nextStep(2)">Stressed</button>
        <button onclick="nextStep(2)">Relaxed</button>
      </div>
    </div>

    <!-- Step 2 -->
    <div class="step hidden" id="step2">
      <h1>How long do you want to exercise today?</h1>
      <div class="button-group">
        <button onclick="selectTime(5)">5 minutes</button>
        <button onclick="selectTime(10)">10 minutes</button>
        <button onclick="selectTime(30)">30 minutes</button>
        <button onclick="openModal()">Custom</button>
      </div>
    </div>

    <!-- Step 3 -->
    <div class="step hidden" id="step3">
      <h1>Do you have any equipment you want to use?</h1>
      <div class="button-group">
        <button onclick="nextStep(4)">None</button>
        <button onclick="nextStep(4)">Dumbbells</button>
        <button onclick="nextStep(4)">Resistance Bands</button>
        <button onclick="nextStep(4)">Yoga Mat</button>
      </div>
    </div>

    <!-- Step 4 (Workout Page) -->
    <div class="step hidden" id="step4">
      <h1>Your Workout</h1>
      <img src="images/temp-profile.png" alt="Workout" class="workout-img">

      <div class="timer-container">
        <h2 id="timerDisplay">00:00</h2>
        <div class="progress-bar">
          <div class="progress-fill" id="progressFill"></div>
        </div>
        <div class="timer-buttons">
          <button onclick="togglePause()" id="pauseBtn">Pause</button>
          <button onclick="finish()">End Workout</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal -->
  <div id="customModal" class="modal hidden">
    <div class="modal-content">
      <h2>Enter your custom exercise time (minutes)</h2>
      <input type="number" id="customTime" min="1" max="300" placeholder="e.g., 45">
      <div class="modal-buttons">
        <button onclick="saveCustomTime()">Confirm</button>
        <button class="cancel" onclick="closeModal()">Cancel</button>
      </div>
    </div>
  </div>

  <script>
  let selectedTime = 0;
  let totalSeconds = 0;
  let remainingSeconds = 0;
  let timerInterval;
  let isPaused = false;

  function nextStep(stepNumber) {
    document.querySelectorAll('.step').forEach(step => step.classList.add('hidden'));
    document.getElementById(`step${stepNumber}`).classList.remove('hidden');
    
    if (stepNumber === 4) startTimer();
  }

  function selectTime(minutes) {
    selectedTime = minutes;
    nextStep(3);
  }

  function openModal() {
    document.getElementById('customModal').classList.remove('hidden');
  }

  function closeModal() {
    document.getElementById('customModal').classList.add('hidden');
  }

  function saveCustomTime() {
    const time = parseInt(document.getElementById('customTime').value);
    if (!time || time <= 0) {
      alert('Please enter a valid time.');
      return;
    }
    selectedTime = time;
    closeModal();
    nextStep(3);
  }

  // Timer logic
  function startTimer() {
    totalSeconds = selectedTime * 60;
    remainingSeconds = totalSeconds;
    updateTimerDisplay();

    timerInterval = setInterval(() => {
      if (!isPaused && remainingSeconds > 0) {
        remainingSeconds--;
        updateTimerDisplay();
        updateProgress();
      } else if (remainingSeconds <= 0) {
        clearInterval(timerInterval);
        alert('Workout Complete!');
      }
    }, 1000);
  }

  function updateTimerDisplay() {
    const minutes = Math.floor(remainingSeconds / 60);
    const seconds = remainingSeconds % 60;
    document.getElementById('timerDisplay').textContent = 
      `${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
  }

  function updateProgress() {
    const progress = ((totalSeconds - remainingSeconds) / totalSeconds) * 100;
    document.getElementById('progressFill').style.width = `${progress}%`;
  }

  function togglePause() {
    isPaused = !isPaused;
    document.getElementById('pauseBtn').textContent = isPaused ? 'Resume' : 'Pause';
  }

  function finish() {
    clearInterval(timerInterval);
    alert('Workout Ended Early.');
    location.href = 'dash.php'; // Return to dashboard
  }
  </script>
</body>
</html>
