<?php

require_once __DIR__ . '/../templates/header.php';
requireLogin();

$favorites = getFavoritesByUser(getCurrentUserId());

renderHeader('My Favorites');
?>

<div class="d-flex justify-content-between align-items-center mb-4">
  <h1>❤️ My Favorites</h1>
  <a href="<?= url('/recipes') ?>" class="btn btn-outline-secondary">← Back to Recipes</a>
</div>

<?php if (empty($favorites)): ?>
  <div class="alert alert-info">You haven't saved any recipes yet.
    <a href="<?= url('/recipes') ?>">Browse recipes →</a>
  </div>
<?php else: ?>
  <div class="row row-cols-1 row-cols-md-3 g-4">
    <?php foreach ($favorites as $fav): ?>
    <div class="col">
      <div class="card h-100 shadow-sm">
        <?php if ($fav['image_filename']): ?>
          <img src="<?= BASE ?>/uploads/recipes/<?= e($fav['image_filename']) ?>"
               class="card-img-top" style="height:180px;object-fit:cover"
               alt="<?= e($fav['title']) ?>">
        <?php else: ?>
          <div class="bg-light d-flex align-items-center justify-content-center text-muted"
               style="height:180px;font-size:3rem">🍽️</div>
        <?php endif; ?>
        <div class="card-body">
          <span class="badge bg-warning text-dark"><?= e(capitalize($fav['meal_type'])) ?></span>
          <span class="badge bg-secondary"><?= e(capitalize($fav['difficulty'])) ?></span>
          <h5 class="card-title mt-2"><?= e($fav['title']) ?></h5>
          <p class="text-muted small">🌍 <?= e($fav['cuisine_name']) ?></p>
        </div>
        <div class="card-footer d-flex justify-content-between align-items-center">
          <small>❤️ Saved <?= date('M d, Y', strtotime($fav['added_at'])) ?></small>
          <a href="<?= url('/recipes/' . $fav['recipe_id']) ?>" class="btn btn-sm btn-warning">View →</a>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>

<?php renderFooter(); ?>
