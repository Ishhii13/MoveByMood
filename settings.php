<?php
// You can later add PHP logic here.
// Example: session_start(); include('db_connect.php');
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>MoveByMood</title>
  <link rel="icon" type="image/png" href="images/favicon.png">

  <!-- Fonts & Icons -->
  <link href="https://fonts.googleapis.com/css2?family=Lilita+One&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

  <!-- CSS -->
  <link rel="stylesheet" href="dash.css">
  <link rel="stylesheet" href="settings.css">
</head>
<body>
  <!-- Header -->
  <header class="header">
    <img src="images/real-logo.png" class="logo" alt="Logo">
    <img src="images/words-logo.png" class="words-logo" alt="Words Logo">
  </header>

  <aside id="sidebar">
    <img src="images/temp-profile.png" class="profile-pic" alt="Profile Picture">
    <h2 class="username">Username</h2>

    <ul class="sidebar-list">
      <li><button onclick="location.href='dash.php'"><i class="fa-solid fa-house"></i> Home</button></li>
      <li><button onclick="location.href='messages.php'"><i class="fa-solid fa-envelope"></i> Messages</button></li>
      <li class="settings"><button onclick="location.href='settings.php'"><i class="fa-solid fa-gear"></i> Settings</button></li>
    </ul>
  </aside>

  <main class="main-container">
    <section class="settings-container">
      <!-- General Section -->
      <section class="settings-section">
        <h2><i class="fa-solid fa-gear"></i> General</h2>

        <div class="setting-item">
          <label>Notifications</label>
          <label class="switch">
            <input type="checkbox" checked>
            <span class="slider"></span>
          </label>
        </div>

        <div class="setting-item">
          <label>Enable Messages</label>
          <label class="switch">
            <input type="checkbox" checked>
            <span class="slider"></span>
          </label>
        </div>

        <div class="setting-item">
          <label>Enable Streaks</label>
          <label class="switch">
            <input type="checkbox" checked>
            <span class="slider"></span>
          </label>
        </div>

        <div class="setting-item font-size-setting">
          <label>Font Size</label>
          <input type="range" min="12" max="24" value="16" class="font-slider">
          <span class="font-preview">Aa</span>
        </div>
      </section>

      <!-- Workout Section -->
      <section class="settings-section">
        <h2><i class="fa-solid fa-dumbbell"></i> Workout</h2>

        <div class="setting-item">
          <label>Text-to-Speech</label>
          <label class="switch">
            <input type="checkbox">
            <span class="slider"></span>
          </label>
        </div>

        <div class="setting-item">
          <label>Show Timer</label>
          <label class="switch">
            <input type="checkbox" checked>
            <span class="slider"></span>
          </label>
        </div>
      </section>
    </section>
  </main>

  <script>
  // ===== settings.js content below =====
  document.addEventListener("DOMContentLoaded", () => {
    const fontSlider = document.querySelector(".font-slider");
    const fontPreview = document.querySelector(".font-preview");

    // Update only the Aa preview when slider changes
    fontSlider.addEventListener("input", () => {
      const fontSize = fontSlider.value + "px";
      fontPreview.style.fontSize = fontSize;
    });
  });
  // ===== end of settings.js =====
  </script>
</body>
</html>