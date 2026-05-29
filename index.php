<?php
define('BASE', '/food-recipes-php');

function url(string $path = '/'): string {
    $path = '/' . ltrim($path, '/');
    if ($path === '/') return BASE . '/index.php';
    return BASE . '/index.php?route=' . $path;
}
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/csrf.php';


$uri    = $_GET['route'] ?? '/';
$uri    = '/' . ltrim($uri, '/');
$uri    = rtrim($uri, '/') ?: '/';
$method = $_SERVER['REQUEST_METHOD'];

if ($uri === '/' && $method === 'GET') {
    require __DIR__ . '/pages/home.php';

} elseif ($uri === '/recipes' && $method === 'GET') {
    require __DIR__ . '/pages/recipes.php';

} elseif (preg_match('#^/recipes/(\d+)$#', $uri, $m) && $method === 'GET') {
    $recipeId = (int) $m[1];
    require __DIR__ . '/pages/recipe_show.php';

} elseif ($uri === '/suggest' && $method === 'GET') {
    require __DIR__ . '/pages/suggest.php';

} elseif ($uri === '/quizine' && $method === 'GET') {
    require __DIR__ . '/pages/quizine.php';

} elseif ($uri === '/favorites' && $method === 'GET') {
    require __DIR__ . '/pages/favorites.php';

} elseif (preg_match('#^/favorites/toggle/(\d+)$#', $uri, $m) && $method === 'POST') {
    $recipeId = (int) $m[1];
    require __DIR__ . '/pages/favorite_toggle.php';

} elseif ($uri === '/login') {
    require __DIR__ . '/pages/login.php';

} elseif ($uri === '/logout') {
    require __DIR__ . '/pages/logout.php';

} elseif ($uri === '/register') {
    require __DIR__ . '/pages/register.php';

} elseif ($uri === '/profile' && $method === 'GET') {
    require __DIR__ . '/pages/profile.php';

} elseif ($uri === '/profile/edit') {
    require __DIR__ . '/pages/profile_edit.php';

} elseif ($uri === '/profile/delete' && $method === 'POST') {
    require __DIR__ . '/pages/profile_delete.php';

} elseif ($uri === '/admin' && $method === 'GET') {
    require __DIR__ . '/pages/admin/dashboard.php';

} elseif ($uri === '/admin/recipe/new') {
    require __DIR__ . '/pages/admin/recipe_new.php';

} elseif (preg_match('#^/admin/recipe/(\d+)/edit$#', $uri, $m)) {
    $recipeId = (int) $m[1];
    require __DIR__ . '/pages/admin/recipe_edit.php';

} elseif (preg_match('#^/admin/recipe/(\d+)/delete$#', $uri, $m) && $method === 'POST') {
    $recipeId = (int) $m[1];
    require __DIR__ . '/pages/admin/recipe_delete.php';

} else {
    http_response_code(404);
    echo '<h1>404 – Page not found</h1><p>URI: ' . htmlspecialchars($uri) . '</p>';
}
