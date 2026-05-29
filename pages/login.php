<?php
require_once __DIR__ . '/../templates/header.php';

if (isLoggedIn()) {
    header('Location: ' . BASE . '/index.php');
    exit;
}

$error        = null;
$lastUsername = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email']    ?? '');
    $password = trim($_POST['password'] ?? '');
    $lastUsername = $email;

    $user = getUserByEmail($email);

    if (!$user || !password_verify($password, $user['password'])) {
        $error = 'Invalid credentials.';
    } else {
        loginUser($user);
        setFlash('success', 'Welcome back, ' . $user['username'] . '!');
        header('Location: ' . BASE . '/index.php');
        exit;
    }
}

renderHeader('Login');
?>

<div class="row justify-content-center">
  <div class="col-md-5">
    <div class="card shadow mt-4">
      <div class="card-body p-4">
        <h2 class="text-center mb-4">🔐 Welcome Back</h2>

        <?php if ($error): ?>
          <div class="alert alert-danger"><?= e($error) ?></div>
        <?php endif; ?>
        <form method="POST">
          <?= csrfField('authenticate') ?>
          <div class="mb-3">
            <label class="form-label">Email</label>
            <input class="form-control" type="email" name="email"
                   value="<?= e($lastUsername) ?>" autocomplete="email" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Password</label>
            <input class="form-control" type="password" name="password"
                   autocomplete="current-password" required>
          </div>
          <button class="btn btn-warning w-100 fw-bold" type="submit">Login</button>
        </form>

        <hr>
        <p class="text-center mb-0">No account? <a href="/food-recipes-php/register">Sign up</a></p>
      </div>
    </div>
  </div>
</div>

<?php renderFooter(); ?>
