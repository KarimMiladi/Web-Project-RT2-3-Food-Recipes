<?php
$isEdit      = $isEdit ?? false;
$ingredients = $ingredients ?? [['ingredientName'=>'','quantity'=>'']];
if (empty($ingredients)) $ingredients = [['ingredientName'=>'','quantity'=>'']];
?>

<div class="row justify-content-center">
  <div class="col-md-8">
    <div class="card shadow">
      <div class="card-body p-4">
        <h2 class="mb-4"><?= $isEdit ? '✏️ Edit Recipe' : '🍳 New Recipe' ?></h2>

        <?php if (isset($errors['form'])): ?>
          <div class="alert alert-danger"><?= e($errors['form']) ?></div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data"
              action="<?= BASE ?>/index.php?route=<?= $isEdit ? '/admin/recipe/' . $recipe['id'] . '/edit' : '/admin/recipe/new' ?>">
          <?= csrfField($isEdit ? 'edit-recipe' : 'new-recipe') ?>
          <div class="row g-3">

            <div class="col-12">
              <label class="form-label fw-bold">Title</label>
              <input class="form-control <?= isset($errors['title']) ? 'is-invalid' : '' ?>"
                     type="text" name="title" value="<?= e($values['title']) ?>" required>
              <?php if (isset($errors['title'])): ?><div class="invalid-feedback"><?= e($errors['title']) ?></div><?php endif; ?>
            </div>

            <div class="col-12">
              <label class="form-label fw-bold">Description</label>
              <textarea class="form-control" name="description" rows="3"><?= e($values['description']) ?></textarea>
            </div>

            <div class="col-md-4">
              <label class="form-label fw-bold">Cuisine</label>
              <select class="form-select <?= isset($errors['cuisine_id']) ? 'is-invalid' : '' ?>" name="cuisine_id" required>
                <option value="">-- Select --</option>
                <?php foreach ($cuisines as $c): ?>
                  <option value="<?= $c['id'] ?>" <?= ((int)$values['cuisine_id'] === (int)$c['id']) ? 'selected' : '' ?>>
                    <?= e($c['flag_emoji']) ?> <?= e($c['name']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
              <?php if (isset($errors['cuisine_id'])): ?><div class="invalid-feedback"><?= e($errors['cuisine_id']) ?></div><?php endif; ?>
            </div>

            <div class="col-md-4">
              <label class="form-label fw-bold">Meal Type</label>
              <select class="form-select" name="meal_type" required>
                <option value="">-- Select --</option>
                <?php foreach (['breakfast','lunch','dinner','snack','dessert'] as $mt): ?>
                  <option value="<?= $mt ?>" <?= ($values['meal_type'] === $mt) ? 'selected' : '' ?>><?= capitalize($mt) ?></option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="col-md-4">
              <label class="form-label fw-bold">Difficulty</label>
              <select class="form-select" name="difficulty" required>
                <option value="">-- Select --</option>
                <?php foreach (['easy','medium','hard'] as $d): ?>
                  <option value="<?= $d ?>" <?= ($values['difficulty'] === $d) ? 'selected' : '' ?>><?= capitalize($d) ?></option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="col-md-4">
              <label class="form-label fw-bold">Prep Time (min)</label>
              <input class="form-control" type="number" name="prep_time" value="<?= (int)$values['prep_time'] ?>" min="0">
            </div>
            <div class="col-md-4">
              <label class="form-label fw-bold">Cook Time (min)</label>
              <input class="form-control" type="number" name="cook_time" value="<?= (int)$values['cook_time'] ?>" min="0">
            </div>
            <div class="col-md-4">
              <label class="form-label fw-bold">Servings</label>
              <input class="form-control" type="number" name="servings" value="<?= (int)$values['servings'] ?>" min="1">
            </div>

            <div class="col-12">
              <label class="form-label fw-bold">Instructions</label>
              <textarea class="form-control" name="instructions" rows="6"><?= e($values['instructions']) ?></textarea>
              <small class="text-muted">Write each step on a new line.</small>
            </div>

            <div class="col-12">
              <label class="form-label fw-bold">Recipe Image</label>
              <input class="form-control" type="file" name="imageFile" accept="image/*">
              <?php if (isset($errors['image'])): ?><div class="text-danger small"><?= e($errors['image']) ?></div><?php endif; ?>
              <?php if ($isEdit && !empty($recipe['image_filename'])): ?>
                <div class="mt-2">
                  <img src="<?= url('/uploads/recipes/' . e($recipe['image_filename'])) ?>" height="80" class="rounded">
                  <small class="text-muted ms-2">Current image</small>
                </div>
              <?php endif; ?>
            </div>

            <div class="col-12">
              <label class="form-label fw-bold">Ingredients</label>
              <div id="ingredients-wrapper">
                <?php foreach ($ingredients as $idx => $ing): ?>
                <div class="ingredient-row row g-2 mb-2">
                  <div class="col-5">
                    <input type="text" name="recipe[recipeIngredients][<?= $idx ?>][ingredientName]"
                           class="form-control" placeholder="e.g. Tomato"
                           value="<?= e($ing['ingredientName'] ?? '') ?>">
                  </div>
                  <div class="col-5">
                    <input type="text" name="recipe[recipeIngredients][<?= $idx ?>][quantity]"
                           class="form-control" placeholder="e.g. 2 cups"
                           value="<?= e($ing['quantity'] ?? '') ?>">
                  </div>
                  <div class="col-2">
                    <button type="button" class="btn btn-outline-danger w-100 remove-ingredient">✕</button>
                  </div>
                </div>
                <?php endforeach; ?>
              </div>
              <button type="button" id="add-ingredient" class="btn btn-outline-secondary btn-sm mt-2">+ Add Ingredient</button>
            </div>

            <div class="col-12 d-flex gap-2">
              <button type="submit" class="btn btn-warning fw-bold">
                <?= $isEdit ? 'Save Changes' : 'Publish Recipe' ?>
              </button>
              <a href="<?= url('/admin') ?>" class="btn btn-outline-secondary">Cancel</a>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<script>
const wrapper = document.getElementById('ingredients-wrapper');
let index = wrapper.querySelectorAll('.ingredient-row').length;
document.getElementById('add-ingredient').addEventListener('click', function () {
    const newRow = document.createElement('div');
    newRow.className = 'ingredient-row row g-2 mb-2';
    newRow.innerHTML = `
        <div class="col-5"><input type="text" name="recipe[recipeIngredients][${index}][ingredientName]" class="form-control" placeholder="e.g. Tomato"></div>
        <div class="col-5"><input type="text" name="recipe[recipeIngredients][${index}][quantity]" class="form-control" placeholder="e.g. 2 cups"></div>
        <div class="col-2"><button type="button" class="btn btn-outline-danger w-100 remove-ingredient">✕</button></div>`;
    wrapper.appendChild(newRow);
    index++;
});
wrapper.addEventListener('click', function (e) {
    if (e.target.classList.contains('remove-ingredient'))
        e.target.closest('.ingredient-row').remove();
});
</script>
