<?php
session_start();

define('CONFIG_PATH', __DIR__ . '/../data/config.php');

// Not yet configured — run setup first
if (!file_exists(CONFIG_PATH)) {
    header('Location: init.php');
    exit;
}

// Already logged in
if (!empty($_SESSION['admin_logged_in'])) {
    header('Location: dashboard.php');
    exit;
}

require_once CONFIG_PATH;

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === ADMIN_USERNAME && password_verify($password, ADMIN_PASSWORD_HASH)) {
        session_regenerate_id(true);
        $_SESSION['admin_logged_in'] = true;
        header('Location: dashboard.php');
        exit;
    }
    $error = 'Incorrect username or password.';
}

$setup = isset($_GET['setup']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Umeed Admin — Login</title>
  <style>
    *, *::before, *::after { box-sizing: border-box; }
    body {
      margin: 0;
      font-family: system-ui, -apple-system, sans-serif;
      background: #F5EFE6;
      display: flex;
      align-items: center;
      justify-content: center;
      min-height: 100vh;
      padding: 1rem;
    }
    .card {
      background: #fff;
      border: 1px solid #D9CFB8;
      border-radius: 16px;
      padding: 2.5rem 2rem;
      width: 100%;
      max-width: 400px;
      box-shadow: 0 4px 24px rgba(26,20,16,0.08);
    }
    .logo { font-size: 1.8rem; font-weight: 700; color: #C8541F; margin-bottom: 0.1rem; }
    h1 { font-size: 1rem; color: #4A3F35; margin: 0 0 2rem; font-weight: 400; }
    label { display: block; font-size: 0.85rem; font-weight: 600; color: #1A1410; margin-bottom: 0.3rem; }
    input {
      width: 100%;
      padding: 0.65rem 0.9rem;
      border: 1px solid #D9CFB8;
      border-radius: 10px;
      font-size: 1rem;
      background: #F5EFE6;
      color: #1A1410;
      margin-bottom: 1.1rem;
      outline: none;
      transition: border-color 0.2s;
    }
    input:focus { border-color: #C8541F; background: #fff; }
    .btn {
      width: 100%;
      padding: 0.8rem;
      background: #C8541F;
      color: #fff;
      border: none;
      border-radius: 50px;
      font-size: 1rem;
      font-weight: 600;
      cursor: pointer;
      transition: background 0.2s;
      margin-top: 0.25rem;
    }
    .btn:hover { background: #A03F11; }
    .error {
      background: #fdecea;
      border: 1px solid #e57373;
      border-radius: 8px;
      padding: 0.65rem 1rem;
      color: #b71c1c;
      font-size: 0.875rem;
      margin-bottom: 1rem;
    }
    .success {
      background: #e8f5e9;
      border: 1px solid #66bb6a;
      border-radius: 8px;
      padding: 0.65rem 1rem;
      color: #2e7d32;
      font-size: 0.875rem;
      margin-bottom: 1rem;
    }
    .back { display:block; text-align:center; margin-top:1.25rem; font-size:0.85rem; color:#4A3F35; text-decoration:none; }
    .back:hover { color:#C8541F; }
  </style>
</head>
<body>
  <div class="card">
    <div class="logo">Umeed</div>
    <h1>Admin Panel</h1>
    <?php if ($setup): ?>
      <div class="success">Account created! You can now log in.</div>
    <?php endif; ?>
    <?php if ($error): ?>
      <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <form method="POST">
      <label for="username">Username</label>
      <input type="text" id="username" name="username" autocomplete="username" required>
      <label for="password">Password</label>
      <input type="password" id="password" name="password" autocomplete="current-password" required>
      <button type="submit" class="btn">Sign In</button>
    </form>
    <a href="../index.html" class="back">← Back to website</a>
  </div>
</body>
</html>
