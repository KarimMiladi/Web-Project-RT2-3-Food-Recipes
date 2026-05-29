<?php

$password = $_POST['password'] ?? '';
$hash     = $password ? password_hash($password, PASSWORD_BCRYPT) : '';
?>
<!DOCTYPE html>
<html>
<head>
  <title>Hash Generator</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container py-5">
  <div class="col-md-6 mx-auto">
    <h2>🔐 Bcrypt Hash Generator</h2>
    <p class="text-muted">Use this to generate the admin password hash for your DB.</p>

    <form method="POST">
      <div class="mb-3">
        <label class="form-label">Password to hash</label>
        <input type="text" name="password" class="form-control"
               value="<?= htmlspecialchars($password) ?>" placeholder="e.g. admin123">
      </div>
      <button type="submit" class="btn btn-warning">Generate Hash</button>
    </form>

    <?php if ($hash): ?>
    <div class="mt-4">
      <label class="form-label fw-bold">Bcrypt Hash:</label>
      <input type="text" class="form-control font-monospace" value="<?= htmlspecialchars($hash) ?>" readonly onclick="this.select()">
      <small class="text-muted">Click to select, then copy and paste into your SQL.</small>

      <div class="mt-3 alert alert-info">
        <strong>Run this in phpMyAdmin:</strong><br>
        <code>UPDATE users SET password='<?= htmlspecialchars($hash) ?>' WHERE username='admin';</code>
      </div>
    </div>
    <?php endif; ?>

    <hr>
    <p class="text-danger"><strong>⚠️ Delete this file after use!</strong></p>
  </div>
</body>
</html>
