<?php

require_once __DIR__ . '/../templates/header.php';

$recipes  = getLatestRecipes(3);
$cuisines = getAllCuisines();

renderHeader('Let Me Cook! – Home');
?>

<div class="p-5 mb-4 bg-dark text-white rounded-3 text-center">
  <h1 class="display-5 fw-bold">🧑‍🍳 Let Me Cook!</h1>
  <p class="fs-5">Discover amazing recipes from around the world.</p>
  <a href="<?= url('/recipes') ?>" class="btn btn-warning btn-lg fw-bold me-2">Browse Recipes</a>
  <a href="<?= url('/suggest') ?>"  class="btn btn-outline-light btn-lg">🧠 Suggest by Ingredients</a>
</div>

<h4 class="mb-3">Browse by Cuisine</h4>
<div class="d-flex flex-wrap gap-2 mb-5">
  <?php foreach ($cuisines as $cuisine): ?>
    <a href="<?= url('/recipes') ?>&cuisine=<?= urlencode($cuisine['name']) ?>"
       class="btn btn-outline-secondary btn-sm">
      <?= e($cuisine['flag_emoji']) ?> <?= e($cuisine['name']) ?>
    </a>
  <?php endforeach; ?>
</div>

<h4 class="mb-3">✨ Latest Recipes</h4>
<div class="row row-cols-1 row-cols-md-3 g-4">
  <?php foreach ($recipes as $recipe): ?>
  <div class="col">
    <div class="card h-100 shadow-sm">
      <?php if ($recipe['image_filename']): ?>
        <img src="<?= BASE ?>/uploads/recipes/<?= e($recipe['image_filename']) ?>"
             class="card-img-top" style="height:180px;object-fit:cover"
             alt="<?= e($recipe['title']) ?>">
      <?php else: ?>
        <div class="bg-light d-flex align-items-center justify-content-center text-muted"
             style="height:180px;font-size:3rem">🍽️</div>
      <?php endif; ?>
      <div class="card-body">
        <span class="badge bg-warning text-dark"><?= e(capitalize($recipe['meal_type'])) ?></span>
        <span class="badge bg-secondary"><?= e(capitalize($recipe['difficulty'])) ?></span>
        <h5 class="card-title mt-2"><?= e($recipe['title']) ?></h5>
        <p class="text-muted small">🌍 <?= e($recipe['cuisine_name']) ?> <?= e($recipe['flag_emoji']) ?></p>
      </div>
      <div class="card-footer d-flex justify-content-between align-items-center">
        <small>⏱ <?= (int)$recipe['prep_time'] + (int)$recipe['cook_time'] ?> min
               · 👤 <?= e($recipe['servings']) ?> servings</small>
        <a href="<?= url('/recipes/' . $recipe['id']) ?>" class="btn btn-sm btn-warning">View →</a>
      </div>
    </div>
  </div>
  <?php endforeach; ?>
</div>

<?php renderFooter(); ?>
