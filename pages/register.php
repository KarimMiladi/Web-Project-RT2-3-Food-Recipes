<?php

require_once __DIR__ . '/../templates/header.php';

if (isLoggedIn()) { header('Location: ' . BASE . '/index.php'); exit; }

$errors = [];
$values = ['username' => '', 'email' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username   = trim($_POST['username']        ?? '');
    $email      = trim($_POST['email']           ?? '');
    $password   = $_POST['plainPassword']        ?? '';
    $agreeTerms = !empty($_POST['agreeTerms']);

    $values = compact('username', 'email');

    if (mb_strlen($username) < 3)  $errors['username']   = 'Username must be at least 3 characters.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors['email'] = 'Invalid email address.';
    if (mb_strlen($password) < 6)  $errors['password']   = 'Password must be at least 6 characters.';
    if (!$agreeTerms)              $errors['agreeTerms'] = 'You must agree to the terms.';

    if (!$errors) {
        $pdo = getDB();
        if ($pdo->prepare('SELECT id FROM users WHERE username = ?')->execute([$username]) &&
            $pdo->query('SELECT FOUND_ROWS()')->fetchColumn()) {
            $check = $pdo->prepare('SELECT id FROM users WHERE username = ?');
            $check->execute([$username]);
            if ($check->fetch()) $errors['username'] = 'Username already taken.';
        }
        $check = $pdo->prepare('SELECT id FROM users WHERE email = ?');
        $check->execute([$email]);
        if ($check->fetch()) $errors['email'] = 'Email already registered.';
    }

    if (!$errors) {
        $hashed = password_hash($password, PASSWORD_BCRYPT);
        $id     = createUser($username, $email, $hashed);

        getDB()->prepare('UPDATE users SET is_verified = 1 WHERE id = ?')->execute([$id]);
        setFlash('success', 'Account created successfully! You can now log in.');
        header('Location: ' . BASE . '/index.php?route=/login');
        exit;
    }
}

renderHeader('Create Account');
?>

<div class="row justify-content-center">
  <div class="col-md-5">
    <div class="card shadow mt-4">
      <div class="card-body p-4">
        <h2 class="text-center mb-4">🍽️ Create Account</h2>

        <form method="POST">
          <?= csrfField('register') ?>

          <div class="mb-3">
            <label class="form-label">Username</label>
            <input class="form-control <?= isset($errors['username']) ? 'is-invalid' : '' ?>"
                   type="text" name="username"
                   value="<?= e($values['username']) ?>" placeholder="chef_john" required>
            <?php if (isset($errors['username'])): ?>
              <div class="invalid-feedback"><?= e($errors['username']) ?></div>
            <?php endif; ?>
          </div>

          <div class="mb-3">
            <label class="form-label">Email</label>
            <input class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>"
                   type="email" name="email"
                   value="<?= e($values['email']) ?>" placeholder="you@email.com" required>
            <?php if (isset($errors['email'])): ?>
              <div class="invalid-feedback"><?= e($errors['email']) ?></div>
            <?php endif; ?>
          </div>

          <div class="mb-3">
            <label class="form-label">Password</label>
            <input class="form-control <?= isset($errors['password']) ? 'is-invalid' : '' ?>"
                   type="password" name="plainPassword" required>
            <?php if (isset($errors['password'])): ?>
              <div class="invalid-feedback"><?= e($errors['password']) ?></div>
            <?php endif; ?>
          </div>

          <div class="mb-3 form-check">
            <input class="form-check-input <?= isset($errors['agreeTerms']) ? 'is-invalid' : '' ?>"
                   type="checkbox" name="agreeTerms" id="agreeTerms">
            <label class="form-check-label" for="agreeTerms">I agree to the terms of service</label>
            <?php if (isset($errors['agreeTerms'])): ?>
              <div class="invalid-feedback"><?= e($errors['agreeTerms']) ?></div>
            <?php endif; ?>
          </div>

          <button type="submit" class="btn btn-warning w-100 fw-bold">Create Account</button>
        </form>

        <hr>
        <p class="text-center mb-0">Already have an account? <a href="/food-recipes-php/login">Login</a></p>
      </div>
    </div>
  </div>
</div>

<?php renderFooter(); ?>
