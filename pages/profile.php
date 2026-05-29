<?php
require_once __DIR__ . '/../templates/header.php';
requireLogin();

$user  = getCurrentUser();
$roles = json_decode($user['roles'], true) ?? [];
$isAdminUser = in_array('ROLE_ADMIN', $roles, true);

renderHeader('My Profile');
?>

<div class="row justify-content-center">
  <div class="col-md-6">
    <div class="card shadow">
      <div class="card-body p-4 text-center">
        <div class="display-1 mb-3">👤</div>
        <h2><?= e($user['username']) ?></h2>
        <p class="text-muted"><?= e($user['email']) ?></p>
        <span class="badge <?= $isAdminUser ? 'bg-danger' : 'bg-success' ?> fs-6">
          <?= $isAdminUser ? '👨‍🍳 Chef (Admin)' : '🧑 Member' ?>
        </span>
        <hr>
        <p class="text-muted">Member since <?= date('F Y', strtotime($user['created_at'])) ?></p>

        <div class="d-flex flex-wrap justify-content-center gap-2">
          <a href="<?= url('/favorites') ?>"    class="btn btn-outline-danger">❤️ My Favorites</a>
          <a href="<?= url('/profile/edit') ?>" class="btn btn-outline-primary">✏️ Edit Profile</a>
        </div>

        <hr>
        <button class="btn btn-sm btn-link text-danger"
                data-bs-toggle="collapse" data-bs-target="#deleteSection">
          Delete my account
        </button>
        <div class="collapse mt-3" id="deleteSection">
          <div class="alert alert-danger text-start">
            <strong>⚠️ This is permanent.</strong> Your account and all data will be deleted.
            <form action="<?= url('/profile/delete') ?>" method="POST" class="mt-2">
              <?= csrfField('delete-account') ?>
              <button class="btn btn-danger btn-sm w-100">Yes, delete my account</button>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<?php renderFooter(); ?>
