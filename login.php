<?php
// login.php
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>MoveByMood</title>
  <link rel="icon" type="image/png" href="images/favicon.png">
  <style>
    body {
      margin: 0;
      height: 100vh;
      display: flex;
      font-family: Arial, sans-serif;
      background: linear-gradient(180deg, #f7f7f7, #e9e9e9);
    }

    .container {
      margin: auto;
      width: 360px;
    }

    .box {
      padding: 28px;
      background: white;
      box-shadow: 0 4px 14px rgba(0,0,0,0.08);
      border-radius: 12px;
    }

    h2 { text-align:center; margin-top:0; }

    input { width:100%; padding:10px; margin:8px 0; border-radius:6px; border:1px solid #ccc; font-size:16px; }
    button { width:100%; padding:12px; border-radius:8px; border:none; font-size:16px; cursor:pointer; background:#4a90e2; color:#fff; }
    button.secondary { background:#fff; color:#4a90e2; border:1px solid #4a90e2; margin-top:8px; }

    .small { font-size:13px; color:#666; text-align:center; margin-top:12px; }

    .modal-backdrop { position:fixed; inset:0; display:none; background:rgba(0,0,0,0.4); align-items:center; justify-content:center; z-index:50; }
    .modal { width:90%; max-width:480px; background:#fff; padding:20px; border-radius:10px; box-shadow:0 8px 30px rgba(0,0,0,0.2); }
    .modal h3 { margin-top:0; }
    .row { display:flex; gap:10px; }
    .close-btn { float:right; cursor:pointer; color:#888; }
  </style>
</head>
<body>
  <div class="container">
    <div class="box">
      <h2>Login</h2>
      <form id="loginForm">
        <input type="text" id="identifier" name="identifier" placeholder="Email or Username" required>
        <input type="password" id="password" name="password" placeholder="Password" required>
        <button type="submit">Login</button>
      </form>

      <div class="small">
        Don't have an account? <a href="#" id="openSignup">Sign Up</a>
      </div>
    </div>
  </div>

  <!-- Sign Up Modal -->
  <div class="modal-backdrop" id="signupBackdrop">
    <div class="modal" role="dialog" aria-modal="true" aria-labelledby="signupTitle">
      <span class="close-btn" id="closeSignup">&times;</span>
      <h3 id="signupTitle">Create an account</h3>
      <form id="signupForm">
        <input type="email" id="su_email" name="email" placeholder="Email" required>
        <input type="text" id="su_username" name="username" placeholder="Username" required>
        <input type="password" id="su_password" name="password" placeholder="Password (min 6 chars)" required>
        <button type="submit">Sign up</button>
      </form>
      <div class="small" id="signupMsg"></div>
    </div>
  </div>

  <script>
  // ------------------------------
  // CLASS: AuthService
  // ------------------------------
  class AuthService {
    constructor(baseURL = 'api/') {
      this.baseURL = baseURL;
    }

    async postJSON(url, data) {
      const res = await fetch(url, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        credentials: 'include',
        body: JSON.stringify(data)
      });
      return res.json();
    }

    login(identifier, password) {
      return this.postJSON(this.baseURL + 'login.php', { identifier, password });
    }

    signup(email, username, password) {
      return this.postJSON(this.baseURL + 'signup.php', { email, username, password });
    }
  }

  // ------------------------------
  // CLASS: LoginForm
  // ------------------------------
  class LoginForm {
    constructor(formId, authService) {
      this.form = document.getElementById(formId);
      this.identifierInput = document.getElementById('identifier');
      this.passwordInput = document.getElementById('password');
      this.authService = authService;

      this.form.addEventListener('submit', (e) => this.handleSubmit(e));
    }

    async handleSubmit(e) {
      e.preventDefault();
      const identifier = this.identifierInput.value.trim();
      const password = this.passwordInput.value;

      const resp = await this.authService.login(identifier, password);
      if (resp.success) {
        this.redirectToDashboard(resp.role);
      } else {
        alert(resp.message || 'Login failed.');
      }
    }

    redirectToDashboard(role) {
      if (role === 'admin') {
        window.location.href = 'admin-dash.php';
      } else {
        window.location.href = 'dash.php';
      }
}
  }

  // ------------------------------
  // CLASS: SignupModal
  // ------------------------------
  class SignupModal {
    constructor(backdropId, formId, authService) {
      this.backdrop = document.getElementById(backdropId);
      this.form = document.getElementById(formId);
      this.emailInput = document.getElementById('su_email');
      this.usernameInput = document.getElementById('su_username');
      this.passwordInput = document.getElementById('su_password');
      this.messageLabel = document.getElementById('signupMsg');
      this.authService = authService;

      document.getElementById('openSignup').addEventListener('click', (e) => {
        e.preventDefault();
        this.openModal();
      });
      document.getElementById('closeSignup').addEventListener('click', () => this.closeModal());
      this.form.addEventListener('submit', (e) => this.handleSubmit(e));
      this.backdrop.addEventListener('click', (e) => this.handleBackdropClick(e));
    }

    openModal() {
      this.backdrop.style.display = 'flex';
    }

    closeModal() {
      this.backdrop.style.display = 'none';
    }

    async handleSubmit(e) {
      e.preventDefault();
      const email = this.emailInput.value.trim();
      const username = this.usernameInput.value.trim();
      const password = this.passwordInput.value;

      this.messageLabel.textContent = 'Signing up...';
      const resp = await this.authService.signup(email, username, password);

      if (resp.success) {
        this.messageLabel.textContent = resp.message || 'Check your email for verification.';
      } else {
        this.messageLabel.textContent = resp.message || 'Sign up failed.';
      }
    }

    handleBackdropClick(e) {
      if (e.target === this.backdrop) this.closeModal();
    }
  }

  // ------------------------------
  // CLASS: UIController
  // ------------------------------
  class UIController {
    constructor() {
      this.authService = new AuthService();
      this.loginForm = new LoginForm('loginForm', this.authService);
      this.signupModal = new SignupModal('signupBackdrop', 'signupForm', this.authService);
    }

    init() {
      // Additional UI setup can go here if needed
    }
  }

//UI Initializer
  const app = new UIController();
  app.init();
  </script>
</body>
</html>
