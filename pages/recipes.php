<?php

require_once __DIR__ . '/../templates/header.php';

$cuisine    = $_GET['cuisine']    ?? null;
$mealType   = $_GET['mealType']   ?? null;
$difficulty = $_GET['difficulty'] ?? null;
$search     = $_GET['search']     ?? null;

$recipes = getRecipesByFilters($cuisine, $mealType, $difficulty, $search);

renderHeader('All Recipes');
?>

<h1 class="mb-4">🍽️ All Recipes</h1>

<!-- Filters -->
<form method="GET" action="<?= url('/recipes') ?>" class="row g-3 mb-4">
  <input type="hidden" name="route" value="/recipes">
  <?php if ($cuisine): ?>
    <input type="hidden" name="cuisine" value="<?= e($cuisine) ?>">
  <?php endif; ?>
  <div class="col-md-3">
    <select name="mealType" class="form-select">
      <option value="">All Meal Types</option>
      <?php foreach (['breakfast','lunch','dinner','snack','dessert'] as $type): ?>
        <option value="<?= e($type) ?>" <?= ($mealType === $type) ? 'selected' : '' ?>>
          <?= capitalize($type) ?>
        </option>
      <?php endforeach; ?>
    </select>
  </div>
  <div class="col-md-3">
    <select name="difficulty" class="form-select">
      <option value="">All Difficulties</option>
      <?php foreach (['easy','medium','hard'] as $d): ?>
        <option value="<?= e($d) ?>" <?= ($difficulty === $d) ? 'selected' : '' ?>>
          <?= capitalize($d) ?>
        </option>
      <?php endforeach; ?>
    </select>
  </div>
  <div class="col-md-2">
    <button class="btn btn-dark w-100">Filter</button>
  </div>
</form>

<div class="row row-cols-1 row-cols-md-3 g-4">
  <?php if (empty($recipes)): ?>
    <p class="text-muted">No recipes found.</p>
  <?php endif; ?>

  <?php foreach ($recipes as $recipe): ?>
  <div class="col recipe-card">
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
        <h5 class="card-title mt-2 recipe-title"><?= e($recipe['title']) ?></h5>
        <p class="text-muted small">🌍 <?= e($recipe['cuisine_name']) ?></p>
        <p class="recipe-description"><?= e(mb_substr($recipe['description'] ?? '', 0, 100)) ?>...</p>
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

<script>
const searchInput = document.getElementById('navbar-search-input');
const cards = document.querySelectorAll('.recipe-card');

if (searchInput && cards.length > 0) {
  document.getElementById('navbar-search-form').addEventListener('submit', function(e) {
    e.preventDefault();
  });

  searchInput.addEventListener('input', function () {
    const query = this.value.toLowerCase().trim();
    cards.forEach(card => {
      const title = card.querySelector('.recipe-title')?.textContent.toLowerCase() || '';
      const desc  = card.querySelector('.recipe-description')?.textContent.toLowerCase() || '';
      card.style.display = (title.includes(query) || desc.includes(query)) ? '' : 'none';
    });
    const visible = [...cards].filter(c => c.style.display !== 'none');
    let noResults = document.getElementById('no-results-msg');
    if (visible.length === 0) {
      if (!noResults) {
        noResults = document.createElement('p');
        noResults.id = 'no-results-msg';
        noResults.className = 'text-muted text-center mt-4 col-12';
        noResults.textContent = 'No recipes match your search.';
        document.querySelector('.row').appendChild(noResults);
      }
    } else if (noResults) { noResults.remove(); }
  });

  if (searchInput.value) searchInput.dispatchEvent(new Event('input'));
}
</script>

<?php renderFooter(); ?>
