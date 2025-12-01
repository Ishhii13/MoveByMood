<?php
// You can later add PHP logic here.
// Example: session_start(); include('db_connect.php');
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>MoveByMood</title>
  <link rel="icon" type="image/png" href="images/favicon.png">

  <!-- Fonts & Icons -->
  <link href="https://fonts.googleapis.com/css2?family=Lilita+One&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

  <!-- CSS -->
  <link rel="stylesheet" href="dash.css">
  <link rel="stylesheet" href="messages.css">
</head>
<body>
  <!-- header -->
  <header class="header">
    <img src="images/real-logo.png" class="logo" alt="Logo">
    <img src="images/words-logo.png" class="words-logo" alt="Words Logo">
  </header>

  <!-- full layout -->
  <div class="layout">
    <!-- mini sidebar -->
    <aside class="mini-sidebar">
      <img src="images/temp-profile.png" class="profile-small" alt="You">
      <nav>
        <button onclick="location.href='dash.php'"><i class="fa-solid fa-house"></i></button>
        <button onclick="location.href='messages.php'"><i class="fa-solid fa-envelope"></i></button>
        <button onclick="location.href='settings.php'"><i class="fa-solid fa-gear"></i></button>
      </nav>
    </aside>

    <!-- chat list -->
    <aside class="chat-list" id="chatList">
      <div class="chat-user" data-user="krizia">
        <img src="images/temp-profile.png" class="chat-pic" alt="Krizia">
        <div>
          <div class="chat-name">Krizia</div>
          <div class="chat-last">Hey — ready for later?</div>
        </div>
      </div>
      <div class="chat-user" data-user="erin">
        <img src="images/temp-profile.png" class="chat-pic" alt="Erin">
        <div>
          <div class="chat-name">Erin</div>
          <div class="chat-last">Workout tomorrow at 6?</div>
        </div>
      </div>
      <div class="chat-user" data-user="louis">
        <img src="images/temp-profile.png" class="chat-pic" alt="Louis">
        <div>
          <div class="chat-name">Louis</div>
          <div class="chat-last">Movie night?</div>
        </div>
      </div>
    </aside>

    <!-- chat area -->
    <main class="chat-area">
      <div class="chat-header" id="chatHeader">
        <img src="images/temp-profile.png" class="chat-header-pic" id="currentPic" alt="">
        <div id="currentName">Krizia</div>
      </div>

      <div class="messages" id="messages"></div>

      <form class="chat-input" id="chatForm">
        <input type="text" id="chatText" placeholder="Type a message..." required>
        <button type="submit"><i class="fa-solid fa-paper-plane"></i></button>
      </form>
    </main>
  </div>

  <!-- JS -->
  <script>
  // ===== messages.js content below =====

  const chatUsers = document.querySelectorAll(".chat-user");
  const chatHeader = document.getElementById("chatHeader");
  const currentName = document.getElementById("currentName");
  const currentPic = document.getElementById("currentPic");
  const messages = document.getElementById("messages");
  const chatForm = document.getElementById("chatForm");
  const chatText = document.getElementById("chatText");

  // sample conversations
  const conversations = {
    krizia: [
      { type: "received", text: "Hey! How’s it going?" },
      { type: "sent", text: "Pretty good, just started a workout!" }
    ],
    erin: [
      { type: "received", text: "Don’t forget our workout tomorrow!" }
    ],
    louis: [
      { type: "received", text: "Movie night later?" }
    ]
  };

  let currentUser = "krizia";

  // render messages
  function renderConversation(user) {
    messages.innerHTML = "";
    (conversations[user] || []).forEach(msg => {
      const div = document.createElement("div");
      div.className = `message ${msg.type}`;
      div.textContent = msg.text;
      messages.appendChild(div);
    });
    messages.scrollTop = messages.scrollHeight;
  }

  // switch user
  chatUsers.forEach(userEl => {
    userEl.addEventListener("click", () => {
      chatUsers.forEach(u => u.classList.remove("active"));
      userEl.classList.add("active");
      currentUser = userEl.dataset.user;

      currentName.textContent = userEl.querySelector(".chat-name").textContent;
      currentPic.src = userEl.querySelector(".chat-pic").src;

      renderConversation(currentUser);
    });
  });

  // send message
  chatForm.addEventListener("submit", e => {
    e.preventDefault();
    const text = chatText.value.trim();
    if (!text) return;

    const msg = { type: "sent", text };
    conversations[currentUser] = conversations[currentUser] || [];
    conversations[currentUser].push(msg);

    renderConversation(currentUser);
    chatText.value = "";
  });

  // init
  document.querySelector(`.chat-user[data-user="${currentUser}"]`).classList.add("active");
  renderConversation(currentUser);

  // ===== end of messages.js =====
  </script>
</body>
</html>