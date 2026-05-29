<?php

require_once __DIR__ . '/../templates/header.php';
requireLogin();

$user   = getCurrentUser();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username    = trim($_POST['username']        ?? '');
    $email       = trim($_POST['email']           ?? '');
    $newPassword = $_POST['newPassword']['first']  ?? '';
    $confirmPass = $_POST['newPassword']['second'] ?? '';

    if (mb_strlen($username) < 3) $errors['username'] = 'Username must be at least 3 characters.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors['email'] = 'Invalid email.';

    if ($newPassword || $confirmPass) {
        if ($newPassword !== $confirmPass) $errors['password'] = 'Passwords do not match.';
        elseif (mb_strlen($newPassword) < 6) $errors['password'] = 'Password must be at least 6 characters.';
    }

    if (!$errors) {
        $pdo = getDB();
        $check = $pdo->prepare('SELECT id FROM users WHERE username = ? AND id != ?');
        $check->execute([$username, $user['id']]);
        if ($check->fetch()) $errors['username'] = 'Username already taken.';

        $check = $pdo->prepare('SELECT id FROM users WHERE email = ? AND id != ?');
        $check->execute([$email, $user['id']]);
        if ($check->fetch()) $errors['email'] = 'Email already in use.';
    }

    if (!$errors) {
        $data = ['username' => $username, 'email' => $email];
        if ($newPassword) {
            $data['password'] = password_hash($newPassword, PASSWORD_BCRYPT);
        }
        updateUser($user['id'], $data);

        $_SESSION['username'] = $username;
        $_SESSION['email']    = $email;

        setFlash('success', 'Profile updated successfully!');
        header('Location: ' . BASE . '/index.php?route=/profile');
        exit;
    }

    $user['username'] = $username;
    $user['email']    = $email;
}

renderHeader('Edit Profile');
?>

<div class="row justify-content-center">
  <div class="col-md-6">
    <div class="card shadow">
      <div class="card-body p-4">
        <h2 class="mb-4">✏️ Edit Profile</h2>

        <form method="POST">
          <?= csrfField('edit-profile') ?>

          <div class="mb-3">
            <label class="form-label fw-bold">Username</label>
            <input class="form-control <?= isset($errors['username']) ? 'is-invalid' : '' ?>"
                   type="text" name="username" value="<?= e($user['username']) ?>" required>
            <?php if (isset($errors['username'])): ?>
              <div class="invalid-feedback"><?= e($errors['username']) ?></div>
            <?php endif; ?>
          </div>

          <div class="mb-3">
            <label class="form-label fw-bold">Email</label>
            <input class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>"
                   type="email" name="email" value="<?= e($user['email']) ?>" required>
            <?php if (isset($errors['email'])): ?>
              <div class="invalid-feedback"><?= e($errors['email']) ?></div>
            <?php endif; ?>
          </div>

          <hr>
          <p class="text-muted small">Leave password fields empty to keep your current password.</p>

          <div class="mb-3">
            <label class="form-label fw-bold">New Password</label>
            <input class="form-control <?= isset($errors['password']) ? 'is-invalid' : '' ?>"
                   type="password" name="newPassword[first]">
            <?php if (isset($errors['password'])): ?>
              <div class="invalid-feedback"><?= e($errors['password']) ?></div>
            <?php endif; ?>
          </div>

          <div class="mb-3">
            <label class="form-label fw-bold">Confirm New Password</label>
            <input class="form-control" type="password" name="newPassword[second]">
          </div>

          <div class="d-flex gap-2">
            <button type="submit" class="btn btn-warning fw-bold">Save Changes</button>
            <a href="/food-recipes-php/profile" class="btn btn-outline-secondary">Cancel</a>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<?php renderFooter(); ?>
