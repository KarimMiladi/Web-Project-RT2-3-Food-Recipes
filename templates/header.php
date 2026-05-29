<?php

function renderHeader(string $title = 'Let Me Cook!'): void {
    $flashes = getFlashes();
    $user    = getCurrentUser();
    ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($title) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="app.css">
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
  <div class="container">
    <a class="navbar-brand fw-bold" href="<?= url('/') ?>">🧑‍🍳 Let Me Cook!</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navMenu">
      <ul class="navbar-nav me-auto">
        <li class="nav-item"><a class="nav-link" href="<?= url('/') ?>">🏠 Home</a></li>
        <li class="nav-item"><a class="nav-link" href="<?= url('/recipes') ?>">🔪 Recipes</a></li>
        <li class="nav-item"><a class="nav-link" href="<?= url('/suggest') ?>">🧠 Suggest</a></li>
        <li class="nav-item"><a class="nav-link" href="<?= url('/quizine') ?>">🌍 Quizine</a></li>
        <?php if (isLoggedIn()): ?>
          <li class="nav-item"><a class="nav-link" href="<?= url('/favorites') ?>">❤️ Favorites</a></li>
        <?php endif; ?>
        <?php if (isAdmin()): ?>
          <li class="nav-item"><a class="nav-link text-warning" href="<?= url('/admin') ?>">⚙️ Admin</a></li>
        <?php endif; ?>
      </ul>

      <form class="d-flex me-3" id="navbar-search-form" action="<?= url('/recipes') ?>" method="GET">
        <input class="form-control form-control-sm me-2" type="search"
               name="search" id="navbar-search-input"
               placeholder="Search recipes..." autocomplete="off"
               value="<?= e($_GET['search'] ?? '') ?>" style="width:200px">
        <button class="btn btn-outline-warning btn-sm" type="submit">🔍</button>
      </form>

      <ul class="navbar-nav">
        <?php if ($user): ?>
          <li class="nav-item"><a class="nav-link" href="<?= url('/profile') ?>">👤 <?= e($user['username']) ?></a></li>
          <li class="nav-item"><a class="nav-link" href="<?= url('/logout') ?>">Logout</a></li>
        <?php else: ?>
          <li class="nav-item"><a class="nav-link" href="<?= url('/login') ?>">Login</a></li>
          <li class="nav-item"><a class="nav-link" href="<?= url('/register') ?>">Sign Up</a></li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>

<div class="container mt-2">
  <?php foreach ($flashes as $type => $messages): ?>
    <?php foreach ($messages as $msg): ?>
      <div class="alert alert-<?= e($type) ?> alert-dismissible fade show">
        <?= e($msg) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    <?php endforeach; ?>
  <?php endforeach; ?>
</div>

<main class="container my-4">
    <?php
}

function renderFooter(): void { ?>
</main>

<footer class="bg-dark text-light py-4 mt-5">
  <div class="container text-center">
    <p class="mb-1">🍽️ <strong>Let Me Cook!</strong> — Discover &amp; Share Amazing Recipes</p>
    <small class="text-muted">© <?= date('Y') ?> Food Recipes Project. All rights reserved.</small>
  </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php }
