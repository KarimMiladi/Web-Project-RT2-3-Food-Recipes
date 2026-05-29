<?php

requireLogin();

if (!validateCsrfToken('toggle' . $recipeId, $_POST['_token'] ?? '')) {
    setFlash('danger', 'Invalid request.');
    header('Location: ' . BASE . '/index.php?route=/recipes/' . $recipeId);
    exit;
}

$userId   = getCurrentUserId();
$existing = getFavorite($userId, $recipeId);

if ($existing) {
    removeFavorite($userId, $recipeId);
} else {
    addFavorite($userId, $recipeId);
}

header('Location: ' . BASE . '/index.php?route=/recipes/' . $recipeId);
exit;
