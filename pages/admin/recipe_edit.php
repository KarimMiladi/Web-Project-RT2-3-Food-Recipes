<?php

require_once __DIR__ . '/../../templates/header.php';
requireAdmin();

$recipe   = getRecipeById($recipeId);
$cuisines = getAllCuisines();
$errors   = [];

if (!$recipe) {
    http_response_code(404);
    renderHeader('Not Found');
    echo '<div class="alert alert-danger">Recipe not found.</div>';
    renderFooter();
    exit;
}

$values = [
    'title'        => $recipe['title'],
    'description'  => $recipe['description'],
    'meal_type'    => $recipe['meal_type'],
    'difficulty'   => $recipe['difficulty'],
    'prep_time'    => $recipe['prep_time'],
    'cook_time'    => $recipe['cook_time'],
    'servings'     => $recipe['servings'],
    'instructions' => $recipe['instructions'],
    'cuisine_id'   => $recipe['cuisine_id'],
];
$ingredients = array_map(fn($ri) => [
    'ingredientName' => $ri['ingredient_name'],
    'quantity'       => $ri['quantity'],
], $recipe['ingredients']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $values = [
        'title'        => trim($_POST['title']        ?? ''),
        'description'  => trim($_POST['description']  ?? ''),
        'meal_type'    => trim($_POST['meal_type']     ?? ''),
        'difficulty'   => trim($_POST['difficulty']    ?? ''),
        'prep_time'    => (int)($_POST['prep_time']    ?? 0),
        'cook_time'    => (int)($_POST['cook_time']    ?? 0),
        'servings'     => (int)($_POST['servings']     ?? 1),
        'instructions' => trim($_POST['instructions']  ?? ''),
        'cuisine_id'   => (int)($_POST['cuisine_id']   ?? 0),
    ];
    $ingredients = $_POST['recipe']['recipeIngredients'] ?? [];

    if (!$values['title'])       $errors['title']      = 'Title is required.';
    if (!$values['cuisine_id'])  $errors['cuisine_id'] = 'Cuisine is required.';
    if (!$values['meal_type'])   $errors['meal_type']  = 'Meal type is required.';
    if (!$values['difficulty'])  $errors['difficulty'] = 'Difficulty is required.';

    if (!$errors) {
        $imageFilename = null;
        if (!empty($_FILES['imageFile']['name'])) {
            try {
                $imageFilename = handleImageUpload(
                    $_FILES['imageFile'],
                    __DIR__ . '/../../uploads/recipes'
                );
            } catch (\RuntimeException $e) {
                $errors['image'] = $e->getMessage();
            }
        }

        if (!$errors) {
            $values['image_filename'] = $imageFilename; 
            updateRecipe($recipeId, $values);
            saveRecipeIngredients($recipeId, $ingredients);

            setFlash('success', 'Recipe updated!');
            header('Location: ' . BASE . '/index.php?route=/admin');
            exit;
        }
    }
}

$isEdit = true;
renderHeader('Edit Recipe');
?>

<?php include __DIR__ . '/../../templates/recipe_form.php'; ?>

<?php renderFooter(); ?>
