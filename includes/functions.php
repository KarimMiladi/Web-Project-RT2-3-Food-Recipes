<?php

require_once __DIR__ . '/../config/database.php';


function getLatestRecipes(int $limit = 3): array {
    $stmt = getDB()->prepare(
        'SELECT r.*, c.name AS cuisine_name, c.flag_emoji,
                u.username AS author_username
         FROM recipes r
         JOIN cuisines c ON r.cuisine_id = c.id
         JOIN users    u ON r.author_id  = u.id
         ORDER BY r.created_at DESC
         LIMIT ?'
    );
    $stmt->execute([$limit]);
    return $stmt->fetchAll();
}

function getAllRecipes(): array {
    return getDB()->query(
        'SELECT r.*, c.name AS cuisine_name, c.flag_emoji,
                u.username AS author_username
         FROM recipes r
         JOIN cuisines c ON r.cuisine_id = c.id
         JOIN users    u ON r.author_id  = u.id
         ORDER BY r.created_at DESC'
    )->fetchAll();
}

function getRecipesByFilters(?string $cuisine, ?string $mealType, ?string $difficulty, ?string $search): array {
    $sql    = 'SELECT r.*, c.name AS cuisine_name, c.flag_emoji,
                      u.username AS author_username
               FROM recipes r
               JOIN cuisines c ON r.cuisine_id = c.id
               JOIN users    u ON r.author_id  = u.id
               WHERE 1=1';
    $params = [];

    if ($cuisine) {
        $sql .= ' AND c.name = ?';
        $params[] = $cuisine;
    }
    if ($mealType) {
        $sql .= ' AND r.meal_type = ?';
        $params[] = $mealType;
    }
    if ($difficulty) {
        $sql .= ' AND r.difficulty = ?';
        $params[] = $difficulty;
    }
    if ($search) {
        $sql .= ' AND (r.title LIKE ? OR r.description LIKE ?)';
        $params[] = "%$search%";
        $params[] = "%$search%";
    }

    $sql .= ' ORDER BY r.created_at DESC';
    $stmt = getDB()->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function getRecipeById(int $id): ?array {
    $stmt = getDB()->prepare(
        'SELECT r.*, c.name AS cuisine_name, c.flag_emoji, c.id AS cuisine_id,
                u.username AS author_username
         FROM recipes r
         JOIN cuisines c ON r.cuisine_id = c.id
         JOIN users    u ON r.author_id  = u.id
         WHERE r.id = ?'
    );
    $stmt->execute([$id]);
    $recipe = $stmt->fetch();
    if (!$recipe) return null;

    $recipe['ingredients'] = getRecipeIngredients($id);
    return $recipe;
}

function createRecipe(array $data): int {
    $pdo  = getDB();
    $stmt = $pdo->prepare(
        'INSERT INTO recipes
            (title, description, meal_type, difficulty, prep_time, cook_time,
             servings, instructions, image_filename, cuisine_id, author_id, created_at)
         VALUES (?,?,?,?,?,?,?,?,?,?,?, NOW())'
    );
    $stmt->execute([
        $data['title'],
        $data['description'],
        $data['meal_type'],
        $data['difficulty'],
        $data['prep_time'],
        $data['cook_time'],
        $data['servings'],
        $data['instructions'],
        $data['image_filename'] ?? null,
        $data['cuisine_id'],
        $data['author_id'],
    ]);
    return (int) $pdo->lastInsertId();
}

function updateRecipe(int $id, array $data): void {
    $stmt = getDB()->prepare(
        'UPDATE recipes SET
            title=?, description=?, meal_type=?, difficulty=?,
            prep_time=?, cook_time=?, servings=?, instructions=?,
            cuisine_id=?
            ' . ($data['image_filename'] ? ', image_filename=?' : '') . '
         WHERE id=?'
    );
    $params = [
        $data['title'], $data['description'], $data['meal_type'], $data['difficulty'],
        $data['prep_time'], $data['cook_time'], $data['servings'], $data['instructions'],
        $data['cuisine_id'],
    ];
    if ($data['image_filename']) $params[] = $data['image_filename'];
    $params[] = $id;
    $stmt->execute($params);
}

function deleteRecipe(int $id): void {
    getDB()->prepare('DELETE FROM recipes WHERE id = ?')->execute([$id]);
}


function getRecipeIngredients(int $recipeId): array {
    $stmt = getDB()->prepare(
        'SELECT ri.*, i.name AS ingredient_name_resolved
         FROM recipe_ingredients ri
         JOIN ingredients i ON ri.ingredient_id = i.id
         WHERE ri.recipe_id = ?'
    );
    $stmt->execute([$recipeId]);
    return $stmt->fetchAll();
}

function deleteRecipeIngredients(int $recipeId): void {
    getDB()->prepare('DELETE FROM recipe_ingredients WHERE recipe_id = ?')->execute([$recipeId]);
}

function saveRecipeIngredients(int $recipeId, array $items): void {
    $pdo = getDB();
    deleteRecipeIngredients($recipeId);

    foreach ($items as $item) {
        $name = trim($item['ingredientName'] ?? '');
        if (!$name) continue;

        $stmt = $pdo->prepare('SELECT id FROM ingredients WHERE name = ?');
        $stmt->execute([$name]);
        $ingredient = $stmt->fetch();

        if ($ingredient) {
            $ingredientId = $ingredient['id'];
        } else {
            $pdo->prepare('INSERT INTO ingredients (name) VALUES (?)')->execute([$name]);
            $ingredientId = (int) $pdo->lastInsertId();
        }

        $pdo->prepare(
            'INSERT INTO recipe_ingredients (recipe_id, ingredient_id, ingredient_name, quantity)
             VALUES (?, ?, ?, ?)'
        )->execute([$recipeId, $ingredientId, $name, trim($item['quantity'] ?? '')]);
    }
}

function getAllCuisines(): array {
    return getDB()->query('SELECT * FROM cuisines ORDER BY name')->fetchAll();
}

function getCuisineById(int $id): ?array {
    $stmt = getDB()->prepare('SELECT * FROM cuisines WHERE id = ?');
    $stmt->execute([$id]);
    return $stmt->fetch() ?: null;
}

function getUserByEmail(string $email): ?array {
    $stmt = getDB()->prepare('SELECT * FROM users WHERE email = ?');
    $stmt->execute([$email]);
    return $stmt->fetch() ?: null;
}

function getUserById(int $id): ?array {
    $stmt = getDB()->prepare('SELECT * FROM users WHERE id = ?');
    $stmt->execute([$id]);
    return $stmt->fetch() ?: null;
}

function createUser(string $username, string $email, string $hashedPassword): int {
    $pdo  = getDB();
    $stmt = $pdo->prepare(
        'INSERT INTO users (username, email, password, roles, is_verified, created_at)
         VALUES (?, ?, ?, \'["ROLE_USER"]\', 0, NOW())'
    );
    $stmt->execute([$username, $email, $hashedPassword]);
    return (int) $pdo->lastInsertId();
}

function updateUser(int $id, array $data): void {
    $fields = [];
    $params = [];

    if (isset($data['username'])) { $fields[] = 'username=?'; $params[] = $data['username']; }
    if (isset($data['email']))    { $fields[] = 'email=?';    $params[] = $data['email']; }
    if (isset($data['password'])) { $fields[] = 'password=?'; $params[] = $data['password']; }

    if (!$fields) return;
    $params[] = $id;
    getDB()->prepare('UPDATE users SET ' . implode(',', $fields) . ' WHERE id=?')->execute($params);
}

function deleteUser(int $id): void {
    getDB()->prepare('DELETE FROM users WHERE id = ?')->execute([$id]);
}


function getFavoritesByUser(int $userId): array {
    $stmt = getDB()->prepare(
        'SELECT f.*, r.title, r.image_filename, r.meal_type, r.difficulty,
                c.name AS cuisine_name
         FROM favorites f
         JOIN recipes  r ON f.recipe_id  = r.id
         JOIN cuisines c ON r.cuisine_id = c.id
         WHERE f.user_id = ?
         ORDER BY f.added_at DESC'
    );
    $stmt->execute([$userId]);
    return $stmt->fetchAll();
}

function getFavorite(int $userId, int $recipeId): ?array {
    $stmt = getDB()->prepare(
        'SELECT * FROM favorites WHERE user_id = ? AND recipe_id = ?'
    );
    $stmt->execute([$userId, $recipeId]);
    return $stmt->fetch() ?: null;
}

function addFavorite(int $userId, int $recipeId): void {
    getDB()->prepare(
        'INSERT IGNORE INTO favorites (user_id, recipe_id, added_at) VALUES (?, ?, NOW())'
    )->execute([$userId, $recipeId]);
}

function removeFavorite(int $userId, int $recipeId): void {
    getDB()->prepare(
        'DELETE FROM favorites WHERE user_id = ? AND recipe_id = ?'
    )->execute([$userId, $recipeId]);
}

function handleImageUpload(array $file, string $uploadDir): ?string {
    if (empty($file['name']) || $file['error'] !== UPLOAD_ERR_OK) {
        return null;
    }

    $allowed   = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
    $mimeType  = mime_content_type($file['tmp_name']);

    if (!in_array($mimeType, $allowed, true)) {
        throw new \RuntimeException('Invalid image type.');
    }

    $ext         = pathinfo($file['name'], PATHINFO_EXTENSION);
    $slugName    = preg_replace('/[^a-z0-9]+/', '-', strtolower(pathinfo($file['name'], PATHINFO_FILENAME)));
    $newFilename = $slugName . '-' . uniqid() . '.' . $ext;

    if (!is_dir($uploadDir)) mkdir($uploadDir, 0775, true);
    move_uploaded_file($file['tmp_name'], $uploadDir . '/' . $newFilename);

    return $newFilename;
}


function e(mixed $value): string {
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function nl2brSafe(string $text): string {
    return nl2br(e($text));
}

function capitalize(string $s): string {
    return ucfirst(strtolower($s));
}
