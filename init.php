<?php
define('CONFIG_PATH', __DIR__ . '/../data/config.php');

// If already configured, go straight to login
if (file_exists(CONFIG_PATH)) {
    header('Location: index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm']  ?? '';

    if (!$username || !$password) {
        $error = 'Username and password are required.';
    } elseif (strlen($password) < 8) {
        $error = 'Password must be at least 8 characters.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } else {
        $hash = password_hash($password, PASSWORD_BCRYPT);
        $dataDir = dirname(CONFIG_PATH);
        if (!is_dir($dataDir)) {
            mkdir($dataDir, 0755, true);
        }
        $php = "<?php\ndefine('ADMIN_USERNAME', " . var_export($username, true) . ");\ndefine('ADMIN_PASSWORD_HASH', " . var_export($hash, true) . ");\n";
        file_put_contents(CONFIG_PATH, $php);
        header('Location: index.php?setup=1');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Umeed Admin — First-Time Setup</title>
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
      max-width: 420px;
      box-shadow: 0 4px 24px rgba(26,20,16,0.08);
    }
    .logo { font-size: 1.6rem; font-weight: 700; color: #C8541F; margin-bottom: 0.25rem; }
    h1 { font-size: 1.1rem; color: #4A3F35; margin: 0 0 1.5rem; font-weight: 500; }
    .notice {
      background: #fff8e7;
      border: 1px solid #E89A3C;
      border-radius: 8px;
      padding: 0.75rem 1rem;
      font-size: 0.85rem;
      color: #7a5a1a;
      margin-bottom: 1.5rem;
    }
    label { display: block; font-size: 0.85rem; font-weight: 600; color: #1A1410; margin-bottom: 0.3rem; }
    input {
      width: 100%;
      padding: 0.65rem 0.9rem;
      border: 1px solid #D9CFB8;
      border-radius: 10px;
      font-size: 1rem;
      background: #F5EFE6;
      color: #1A1410;
      margin-bottom: 1rem;
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
  </style>
</head>
<body>
  <div class="card">
    <div class="logo">Umeed</div>
    <h1>Admin — First-Time Setup</h1>
    <div class="notice">
      This page appears only once. Set your admin username and password below. Keep these credentials safe.
    </div>
    <?php if ($error): ?>
      <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <form method="POST">
      <label for="username">Username</label>
      <input type="text" id="username" name="username" value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" autocomplete="username" required>
      <label for="password">Password <span style="font-weight:400;color:#4A3F35">(min. 8 characters)</span></label>
      <input type="password" id="password" name="password" autocomplete="new-password" required>
      <label for="confirm">Confirm Password</label>
      <input type="password" id="confirm" name="confirm" autocomplete="new-password" required>
      <button type="submit" class="btn">Create Admin Account</button>
    </form>
  </div>
</body>
</html>
