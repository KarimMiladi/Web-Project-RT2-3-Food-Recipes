<?php

require_once __DIR__ . '/../templates/header.php';

$suggestions = [];
$input       = trim($_GET['ingredients'] ?? '');

if ($input) {
    $userIngredients = array_filter(
        array_map(fn($i) => strtolower(trim($i)), explode(',', $input))
    );

    $allRecipes = getAllRecipes();

    foreach ($allRecipes as $recipe) {
        $recipeIngredients = getRecipeIngredients($recipe['id']);
        $totalIngredients  = count($recipeIngredients);
        if ($totalIngredients === 0) continue;

        $matchCount = 0;
        foreach ($recipeIngredients as $ri) {
            $name = strtolower($ri['ingredient_name']);
            foreach ($userIngredients as $userIng) {
                if (str_contains($name, $userIng) || str_contains($userIng, $name)) {
                    $matchCount++;
                    break;
                }
            }
        }

        if ($matchCount > 0) {
            $suggestions[] = [
                'recipe'           => $recipe,
                'matchCount'       => $matchCount,
                'totalIngredients' => $totalIngredients,
                'matchPercent'     => round(($matchCount / $totalIngredients) * 100),
            ];
        }
    }

    usort($suggestions, fn($a, $b) => $b['matchCount'] - $a['matchCount']);
}

renderHeader('Ingredient Suggestions');
?>

<h1 class="mb-2">🧠 Recipe Suggester</h1>
<p class="text-muted">Enter ingredients you have, separated by commas.</p>

<form method="GET" action="<?= url('/suggest') ?>" class="d-flex gap-2 mb-4">
  <input type="hidden" name="route" value="/suggest">
  <input type="text" name="ingredients" class="form-control"
         placeholder="e.g. tomato, garlic, pasta"
         value="<?= e($input) ?>">
  <button class="btn btn-warning fw-bold">Suggest!</button>
</form>

<?php if ($input && empty($suggestions)): ?>
  <div class="alert alert-warning">No recipes found with those ingredients. Try others!</div>
<?php endif; ?>

<?php if (!empty($suggestions)): ?>
  <h4 class="mb-3">Found <?= count($suggestions) ?> suggestion(s):</h4>
  <div class="row row-cols-1 row-cols-md-3 g-4">
    <?php foreach ($suggestions as $s):
          $r = $s['recipe']; ?>
    <div class="col">
      <div class="card h-100 shadow-sm">
        <?php if ($r['image_filename']): ?>
          <img src="/food-recipes-php/uploads/recipes/<?= e($r['image_filename']) ?>"
               class="card-img-top" style="height:160px;object-fit:cover"
               alt="<?= e($r['title']) ?>">
        <?php else: ?>
          <div class="bg-light d-flex align-items-center justify-content-center text-muted"
               style="height:160px;font-size:3rem">🍽️</div>
        <?php endif; ?>
        <div class="card-body">
          <h5 class="card-title"><?= e($r['title']) ?></h5>
          <p class="text-muted small">🌍 <?= e($r['cuisine_name']) ?></p>

          <div class="d-flex justify-content-between small mb-1">
            <span>Ingredient match</span>
            <strong><?= $s['matchPercent'] ?>%</strong>
          </div>
          <div class="progress mb-2" style="height:8px">
            <div class="progress-bar bg-success" style="width:<?= $s['matchPercent'] ?>%"></div>
          </div>
          <small class="text-muted">
            <?= $s['matchCount'] ?> / <?= $s['totalIngredients'] ?> ingredients matched
          </small>
        </div>
        <div class="card-footer">
          <a href="<?= url('/recipes/' . $r['id']) ?>" class="btn btn-sm btn-warning w-100">View Recipe →</a>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>

<?php renderFooter(); ?>
