<?php

require_once __DIR__ . '/../../templates/header.php';
requireAdmin();

$recipes = getAllRecipes();

renderHeader('Admin Dashboard');
?>

<div class="d-flex justify-content-between align-items-center mb-4">
  <h1>⚙️ Admin Dashboard</h1>
  <a href="<?= url('/admin/recipe/new') ?>" class="btn btn-warning">+ New Recipe</a>
</div>

<?php if (empty($recipes)): ?>
  <div class="alert alert-info">No recipes yet. Create your first one!</div>
<?php else: ?>
<div class="table-responsive">
  <table class="table table-hover align-middle">
    <thead class="table-dark">
      <tr>
        <th>Title</th><th>Cuisine</th><th>Meal Type</th>
        <th>Difficulty</th><th>Date</th><th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($recipes as $recipe): ?>
      <tr>
        <td><strong><?= e($recipe['title']) ?></strong></td>
        <td><?= e($recipe['cuisine_name']) ?></td>
        <td><span class="badge bg-warning text-dark"><?= e(capitalize($recipe['meal_type'])) ?></span></td>
        <td><span class="badge bg-secondary"><?= e(capitalize($recipe['difficulty'])) ?></span></td>
        <td><?= date('M d, Y', strtotime($recipe['created_at'])) ?></td>
        <td>
          <a href="<?= url('/recipes/' . $recipe['id']) ?>" class="btn btn-sm btn-outline-dark">View</a>
          <a href="<?= url('/admin/recipe/' . $recipe['id'] . '/edit') ?>" class="btn btn-sm btn-outline-primary">Edit</a>
          <form action="<?= url('/admin/recipe/' . $recipe['id'] . '/delete') ?>" method="POST" class="d-inline"
                onsubmit="return confirm('Delete this recipe?')">
            <?= csrfField('delete' . $recipe['id']) ?>
            <button class="btn btn-sm btn-outline-danger">Delete</button>
          </form>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php endif; ?>

<?php renderFooter(); ?>
