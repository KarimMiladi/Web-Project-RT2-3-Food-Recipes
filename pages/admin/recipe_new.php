<?php

require_once __DIR__ . '/../../templates/header.php';
requireAdmin();

$cuisines = getAllCuisines();
$errors   = [];
$values   = [
    'title' => '', 'description' => '', 'meal_type' => '',
    'difficulty' => '', 'prep_time' => 0, 'cook_time' => 0,
    'servings' => 1, 'instructions' => '', 'cuisine_id' => '',
];
$ingredients = [['ingredientName' => '', 'quantity' => '']];

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
            $values['author_id']      = getCurrentUserId();
            $recipeId = createRecipe($values);
            saveRecipeIngredients($recipeId, $ingredients);

            setFlash('success', 'Recipe published successfully!');
            header('Location: ' . BASE . '/index.php?route=/admin');
            exit;
        }
    }
}

renderHeader('New Recipe');
?>

<?php include __DIR__ . '/../../templates/recipe_form.php'; ?>

<?php renderFooter(); ?>
