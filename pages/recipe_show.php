<?php
require_once __DIR__ . '/../templates/header.php';

$recipe = getRecipeById($recipeId);

if (!$recipe) {
    http_response_code(404);
    renderHeader('Not Found');
    echo '<div class="alert alert-danger">Recipe not found.</div>';
    renderFooter();
    exit;
}

$isFavorited = false;
if (isLoggedIn()) {
    $isFavorited = getFavorite(getCurrentUserId(), $recipe['id']) !== null;
}

renderHeader($recipe['title']);
?>

<div class="row">
  <div class="col-md-8">
    <h1><?= e($recipe['title']) ?></h1>
    <div class="mb-2">
      <span class="badge bg-warning text-dark fs-6"><?= e(capitalize($recipe['meal_type'])) ?></span>
      <span class="badge bg-secondary fs-6"><?= e(capitalize($recipe['difficulty'])) ?></span>
      <span class="badge bg-info text-dark fs-6">🌍 <?= e($recipe['cuisine_name']) ?></span>
    </div>
    <p class="lead"><?= e($recipe['description']) ?></p>

    <div class="row text-center my-3">
      <div class="col"><strong>⏱ Prep</strong><br><?= (int)$recipe['prep_time'] ?> min</div>
      <div class="col"><strong>🔥 Cook</strong><br><?= (int)$recipe['cook_time'] ?> min</div>
      <div class="col"><strong>👤 Serves</strong><br><?= e($recipe['servings']) ?></div>
    </div>

    <h4>🧂 Ingredients</h4>
    <?php if (empty($recipe['ingredients'])): ?>
      <p class="text-muted">No ingredients listed.</p>
    <?php else: ?>
      <ul class="list-group list-group-flush mb-3">
        <?php foreach ($recipe['ingredients'] as $ri): ?>
          <li class="list-group-item d-flex justify-content-between">
            <span><?= e($ri['ingredient_name']) ?></span>
            <span class="text-muted"><?= e($ri['quantity']) ?></span>
          </li>
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>

    <h4 class="mt-4">📋 Instructions</h4>
    <p><?= nl2brSafe($recipe['instructions'] ?? '') ?></p>
  </div>

  <div class="col-md-4">
    <?php if ($recipe['image_filename']): ?>
      <img src="<?= BASE ?>/uploads/recipes/<?= e($recipe['image_filename']) ?>"
           class="img-fluid rounded shadow" alt="<?= e($recipe['title']) ?>">
    <?php endif; ?>

    <div class="mt-3">
      <p class="text-muted">By <strong><?= e($recipe['author_username']) ?></strong></p>
      <p class="text-muted">Posted: <?= date('M d, Y', strtotime($recipe['created_at'])) ?></p>
    </div>

    <?php if (isLoggedIn()): ?>
    <form action="<?= url('/favorites/toggle/' . $recipe['id']) ?>" method="POST">
      <?= csrfField('toggle' . $recipe['id']) ?>
      <button class="btn btn-<?= $isFavorited ? 'danger' : 'outline-danger' ?> w-100 mt-2">
        <?= $isFavorited ? '❤️ Remove from Favorites' : '🤍 Add to Favorites' ?>
      </button>
    </form>
    <?php endif; ?>

    <?php if (isAdmin()): ?>
    <a href="<?= url('/admin/recipe/' . $recipe['id'] . '/edit') ?>"
       class="btn btn-outline-primary w-100 mt-2">✏️ Edit Recipe</a>
    <?php endif; ?>
  </div>
</div>

<a href="<?= url('/recipes') ?>" class="btn btn-outline-secondary mt-4">← Back to Recipes</a>

<?php renderFooter(); ?>
