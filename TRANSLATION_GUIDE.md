# Symfony → Plain PHP Translation Guide
## Let Me Cook! – Food Recipes Project

---

## 1. Project Structure

```
Symfony version                     Plain PHP version
──────────────────────────────────  ──────────────────────────────────────
config/                             config/
  services.yaml                       database.php          ← PDO config
  packages/security.yaml
  packages/doctrine.yaml
src/
  Controller/                       pages/
    HomeController.php                home.php
    RecipeController.php              recipes.php
                                      recipe_show.php
    FavoriteController.php            favorites.php
                                      favorite_toggle.php
    ProfileController.php             profile.php
                                      profile_edit.php
                                      profile_delete.php
    SuggestionController.php          suggest.php
    QuizineController.php             quizine.php
    SecurityController.php            login.php
                                      logout.php
    RegistrationController.php        register.php
    AdminController.php             pages/admin/
                                      dashboard.php
                                      recipe_new.php
                                      recipe_edit.php
                                      recipe_delete.php
  Entity/                           sql/
    User.php                          schema.sql            ← CREATE TABLE
    Recipe.php
    Cuisine.php
    Ingredient.php
    RecipeIngredient.php
    Favorite.php
  Repository/                       includes/
    RecipeRepository.php              functions.php         ← all DB queries
    UserRepository.php
    CuisineRepository.php
    FavoriteRepository.php
    IngredientRepository.php
  Form/                             (no equivalent – manual HTML forms)
    RecipeType.php
    EditProfileType.php
    RegistrationFormType.php
  Security/
    LoginFormAuthenticator.php      includes/auth.php
templates/                          templates/
  base.html.twig                      header.php
                                      footer.php     (inside header.php)
  admin/dashboard.html.twig         pages/admin/dashboard.php
  admin/recipe_form.html.twig       templates/recipe_form.php
  recipe/list.html.twig             pages/recipes.php
  recipe/show.html.twig             pages/recipe_show.php
  favorite/list.html.twig           pages/favorites.php
  profile/index.html.twig           pages/profile.php
  profile/edit.html.twig            pages/profile_edit.php
  suggestion/index.html.twig        pages/suggest.php
  quizine/index.html.twig           pages/quizine.php
  security/login.html.twig          pages/login.php
  registration/register.html.twig   pages/register.php
public/index.php (Symfony kernel)   index.php             ← manual router
```

---

## 2. Setup Instructions (XAMPP)

### Step 1 – Copy project
Place the `food-recipes-php/` folder inside `htdocs/`.

### Step 2 – Enable mod_rewrite
In `httpd.conf` (Apache), make sure:
```
LoadModule rewrite_module modules/mod_rewrite.so
AllowOverride All          # inside your <Directory> block
```

### Step 3 – Create the database
Open `http://localhost/phpmyadmin`, click **SQL**, paste and run `sql/schema.sql`.

### Step 4 – Edit DB credentials
Open `config/database.php` and set `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS`.

### Step 5 – Create upload folder
Make sure `uploads/recipes/` exists and is writable:
```
food-recipes-php/uploads/recipes/   ← put this folder here
```

### Step 6 – Visit
`http://localhost/food-recipes-php/`

---

## 3. Concept-by-Concept Translation

### 3.1 Routing

| Symfony | Plain PHP |
|---------|-----------|
| `#[Route('/recipes', name: 'app_recipes')]` | `if ($uri === '/recipes' && $method === 'GET')` in `index.php` |
| `{{ path('app_recipes') }}` | `href="/recipes"` |
| Route parameter `{id}` | `preg_match('#^/recipes/(\d+)$#', $uri, $m)` |
| `$this->redirectToRoute('app_admin')` | `header('Location: /admin'); exit;` |

### 3.2 Twig → PHP templates

| Twig | PHP |
|------|-----|
| `{% extends 'base.html.twig' %}` | `require_once 'templates/header.php'; renderHeader('Title');` at top, `renderFooter();` at bottom |
| `{% block body %}…{% endblock %}` | everything between `renderHeader()` and `renderFooter()` |
| `{{ variable }}` | `<?= e($variable) ?>` (`e()` = `htmlspecialchars`) |
| `{{ recipe.title }}` | `<?= e($recipe['title']) ?>` |
| `{% if condition %}…{% endif %}` | `<?php if ($condition): ?>…<?php endif; ?>` |
| `{% for item in list %}` | `<?php foreach ($list as $item): ?>` |
| `{{ recipe.createdAt\|date('M d, Y') }}` | `date('M d, Y', strtotime($recipe['created_at']))` |
| `{{ text\|nl2br }}` | `nl2brSafe($text)` (defined in functions.php) |
| `{{ text\|slice(0,100) }}` | `mb_substr($text, 0, 100)` |
| `{{ text\|capitalize }}` | `capitalize($text)` or `ucfirst($text)` |
| `{{ asset('uploads/recipes/' ~ file) }}` | `/uploads/recipes/<?= e($file) ?>` |
| `{{ csrf_token('delete42') }}` | `generateCsrfToken('delete42')` |
| `{{ form_start(form) }}` | `<form method="POST">` |
| `{{ form_widget(form.field) }}` | `<input name="field" value="...">` |
| `{% for type, messages in app.flashes %}` | `foreach (getFlashes() as $type => $messages)` |
| `{{ app.user.username }}` | `$_SESSION['username']` or `getCurrentUser()['username']` |
| `{{ is_granted('ROLE_USER') }}` | `isLoggedIn()` |
| `{{ is_granted('ROLE_ADMIN') }}` | `isAdmin()` |
| `{{ \"now\"\|date(\"Y\") }}` | `date('Y')` |
| `{{ recipe.instructions\|nl2br }}` | `nl2br(htmlspecialchars($recipe['instructions']))` |

### 3.3 Entities → Database

Each Symfony entity class maps to a database table:

| Entity class | Table | Key mapping |
|---|---|---|
| `User` | `users` | `$user->getUsername()` → `$user['username']` |
| `Recipe` | `recipes` | `$recipe->getTitle()` → `$recipe['title']` |
| `Cuisine` | `cuisines` | `$cuisine->getName()` → `$cuisine['name']` |
| `Ingredient` | `ingredients` | `$ingredient->getName()` → `$ingredient['name']` |
| `RecipeIngredient` | `recipe_ingredients` | `$ri->getQuantity()` → `$ri['quantity']` |
| `Favorite` | `favorites` | `$fav->getAddedAt()` → `$fav['added_at']` |

**Doctrine lazy-loading** (e.g. `$recipe->getCuisine()->getName()`) becomes a **JOIN** in SQL:
```sql
-- Symfony ORM (automatic):
$recipe->getCuisine()->getName()

-- Plain PHP (explicit JOIN):
SELECT r.*, c.name AS cuisine_name
FROM recipes r JOIN cuisines c ON r.cuisine_id = c.id
```

### 3.4 Repositories → functions.php

| Symfony repository call | Plain PHP equivalent |
|---|---|
| `$recipeRepo->findAll()` | `getAllRecipes()` |
| `$recipeRepo->find($id)` | `getRecipeById($id)` |
| `$recipeRepo->findBy([], ['createdAt'=>'DESC'], 3)` | `getLatestRecipes(3)` |
| `$recipeRepo->findByFilters(...)` | `getRecipesByFilters(...)` |
| `$favoriteRepo->findBy(['user'=>$user])` | `getFavoritesByUser($userId)` |
| `$favoriteRepo->findOneBy(['user'=>$u,'recipe'=>$r])` | `getFavorite($userId, $recipeId)` |
| `$em->persist($entity); $em->flush()` | `createRecipe($data)` / `updateRecipe($id, $data)` |
| `$em->remove($entity); $em->flush()` | `deleteRecipe($id)` |

### 3.5 Security (Symfony → plain PHP sessions)

| Symfony | Plain PHP |
|---|---|
| `$this->getUser()` | `getCurrentUser()` |
| `#[IsGranted('ROLE_USER')]` | `requireLogin()` at top of page |
| `#[IsGranted('ROLE_ADMIN')]` and `#[Route('/admin')]` | `requireAdmin()` at top of page |
| `$this->getUser()->getRoles()` | `json_decode($user['roles'], true)` |
| `security.yaml` firewall login config | manual check in `login.php` with `password_verify()` |
| `UserPasswordHasherInterface::hashPassword()` | `password_hash($pass, PASSWORD_BCRYPT)` |
| `UserPasswordHasherInterface::isPasswordValid()` | `password_verify($input, $stored)` |
| `LoginFormAuthenticator` | manual logic in `pages/login.php` |
| `$request->getSession()->invalidate()` | `session_destroy()` |
| `$this->container->get('security.token_storage')->setToken(null)` | `logoutUser()` |

### 3.6 CSRF Tokens

| Symfony | Plain PHP |
|---|---|
| `{{ csrf_token('delete42') }}` | `generateCsrfToken('delete42')` |
| `$this->isCsrfTokenValid('delete42', $token)` | `validateCsrfToken('delete42', $token)` |
| Automatic on login form | `csrfField('authenticate')` in login form |

### 3.7 Flash Messages

| Symfony | Plain PHP |
|---|---|
| `$this->addFlash('success', 'Done!')` | `setFlash('success', 'Done!')` |
| `{% for type, messages in app.flashes %}` | `foreach (getFlashes() as $type => $messages)` |

### 3.8 Image Upload

| Symfony | Plain PHP |
|---|---|
| `SluggerInterface` | `preg_replace('/[^a-z0-9]+/', '-', strtolower($name))` |
| `$file->guessExtension()` | `pathinfo($file, PATHINFO_EXTENSION)` |
| `$file->move($dir, $name)` | `move_uploaded_file($tmp, $dest)` |
| `$this->getParameter('recipes_images_directory')` | hardcoded path `__DIR__ . '/../../uploads/recipes'` |

### 3.9 Forms (Symfony FormType → HTML)

Symfony's `RecipeType`, `EditProfileType`, `RegistrationFormType` are PHP classes that generate forms with built-in validation. In plain PHP:
- The HTML form is written manually in the template
- Validation is done manually in the page file with `if` checks
- Errors are passed to the template as a `$errors` array
- Field values are repopulated from `$_POST`

---

## 4. What Was Simplified

| Feature | Symfony version | Plain PHP version |
|---|---|---|
| Email verification | SymfonyCasts `EmailVerifier` bundle | Skipped — accounts auto-verified. Add PHPMailer to restore. |
| Password hashing | `UserPasswordHasherInterface` | `password_hash()` / `password_verify()` (PHP built-ins) |
| ORM / migrations | Doctrine ORM + migrations | Raw SQL + `schema.sql` run once |
| Form validation | Symfony Form constraints | Manual `if` checks in each page |
| Auto-escaping | Twig auto-escapes all output | Must use `e()` (= `htmlspecialchars`) everywhere |
| Dependency injection | Symfony service container | Functions called directly |
| Slugger | `SluggerInterface` | `preg_replace` one-liner |

---

## 5. File Checklist

```
food-recipes-php/
├── .htaccess                        ← routes all traffic to index.php
├── index.php                        ← router (replaces Symfony routing)
├── config/
│   └── database.php                 ← PDO connection
├── includes/
│   ├── auth.php                     ← login/logout/session/guards
│   ├── csrf.php                     ← CSRF token helpers
│   └── functions.php                ← all DB query functions
├── templates/
│   ├── header.php                   ← navbar + flash (replaces base.html.twig)
│   └── recipe_form.php              ← shared admin form
├── pages/
│   ├── home.php
│   ├── recipes.php
│   ├── recipe_show.php
│   ├── favorites.php
│   ├── favorite_toggle.php
│   ├── suggest.php
│   ├── quizine.php
│   ├── login.php
│   ├── logout.php
│   ├── register.php
│   ├── profile.php
│   ├── profile_edit.php
│   ├── profile_delete.php
│   └── admin/
│       ├── dashboard.php
│       ├── recipe_new.php
│       ├── recipe_edit.php
│       └── recipe_delete.php
├── sql/
│   └── schema.sql                   ← run this once in phpMyAdmin
└── uploads/
    └── recipes/                     ← writable image upload folder
```
