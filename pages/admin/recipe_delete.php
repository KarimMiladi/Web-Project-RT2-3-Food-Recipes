<?php

requireAdmin();

if (!validateCsrfToken('delete' . $recipeId, $_POST['_token'] ?? '')) {
    setFlash('danger', 'Invalid request.');
    header('Location: ' . BASE . '/index.php?route=/admin');
    exit;
}

deleteRecipe($recipeId);
setFlash('warning', 'Recipe deleted.');
header('Location: ' . BASE . '/index.php?route=/admin');
exit;
