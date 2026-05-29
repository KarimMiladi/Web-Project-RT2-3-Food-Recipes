<?php
require_once __DIR__ . '/../templates/header.php';

$allRecipes  = getAllRecipes();
$allCuisines = getAllCuisines();

$recipeData = [];
foreach ($allRecipes as $r) {
    $recipeData[] = [
        'id'       => $r['id'],
        'title'    => $r['title'],
        'image'    => $r['image_filename'],
        'cuisine'  => $r['cuisine_name'],
        'flag'     => $r['flag_emoji'],
        'mealType' => $r['meal_type'],
    ];
}

$cuisineData = [];
foreach ($allCuisines as $c) {
    $cuisineData[] = [
        'name' => $c['name'],
        'flag' => $c['flag_emoji'],
    ];
}

$recipesJson  = json_encode($recipeData);
$cuisinesJson = json_encode($cuisineData);

renderHeader('Quizine – Cuisine Quiz');
?>

<div class="text-center mb-4">
  <h1>🌍 Quizine</h1>
  <p class="text-muted">Guess the cuisine from the recipe!</p>
</div>

<div id="quiz-container" class="row justify-content-center">
  <div class="col-md-7">

    <div class="d-flex justify-content-between mb-3">
      <span>Question <span id="q-number">1</span> / <span id="q-total">10</span></span>
      <span>✅ Score: <strong id="score">0</strong></span>
    </div>

    <div class="card shadow mb-3" id="recipe-card">
      <img id="recipe-image" src="" class="card-img-top" style="height:260px;object-fit:cover">
      <div class="card-body text-center">
        <h4 id="recipe-title"></h4>
        <span class="badge bg-warning text-dark" id="recipe-meal"></span>
      </div>
    </div>

    <div class="row g-2" id="options-container"></div>

    <div id="result-msg" class="alert mt-3 d-none"></div>

    <button id="next-btn" class="btn btn-warning w-100 mt-3 d-none">Next Question →</button>

    <div id="final-screen" class="text-center d-none">
      <h2>Quiz complete! 🎉</h2>
      <p class="fs-4">Your score: <strong id="final-score"></strong></p>
      <button onclick="startQuiz()" class="btn btn-warning btn-lg">Play Again</button>
      <a href="/recipes" class="btn btn-outline-secondary btn-lg ms-2">Browse Recipes</a>
    </div>

  </div>
</div>

<script>
const ALL_RECIPES  = <?= $recipesJson ?>;
const ALL_CUISINES = <?= $cuisinesJson ?>;

let questions = [];
let current   = 0;
let score     = 0;

function shuffle(arr) {
    return arr.sort(() => Math.random() - 0.5);
}

function startQuiz() {
    questions = shuffle([...ALL_RECIPES]).slice(0, Math.min(10, ALL_RECIPES.length));
    current   = 0;
    score     = 0;
    document.getElementById('score').textContent = 0;
    document.getElementById('final-screen').classList.add('d-none');
    document.getElementById('recipe-card').classList.remove('d-none');
    document.getElementById('options-container').classList.remove('d-none');
    showQuestion();
}

function showQuestion() {
    if (current >= questions.length) {
        endQuiz(); return;
    }

    const q = questions[current];
    document.getElementById('q-number').textContent = current + 1;
    document.getElementById('q-total').textContent  = questions.length;
    document.getElementById('recipe-title').textContent = q.title;
    document.getElementById('recipe-meal').textContent  = q.mealType;

    const img = document.getElementById('recipe-image');
    img.src = q.image ? '/food-recipes-php/uploads/recipes/' + q.image : '';
    img.style.display = q.image ? '' : 'none';

    const correct = q.cuisine;
    const others  = shuffle(ALL_CUISINES.map(c => c.name).filter(n => n !== correct)).slice(0, 3);
    const options = shuffle([correct, ...others]);

    const container = document.getElementById('options-container');
    container.innerHTML = '';
    options.forEach(opt => {
        const cuisine = ALL_CUISINES.find(c => c.name === opt);
        const flag    = cuisine ? cuisine.flag : '';
        const btn     = document.createElement('div');
        btn.className = 'col-6';
        btn.innerHTML = `<button class="btn btn-outline-secondary w-100 option-btn"
                                 data-answer="${opt}">${flag} ${opt}</button>`;
        container.appendChild(btn);
    });

    document.querySelectorAll('.option-btn').forEach(btn => {
        btn.addEventListener('click', () => checkAnswer(btn, correct));
    });

    document.getElementById('result-msg').classList.add('d-none');
    document.getElementById('next-btn').classList.add('d-none');
}

function checkAnswer(btn, correct) {
    document.querySelectorAll('.option-btn').forEach(b => b.disabled = true);

    const chosen = btn.dataset.answer;
    const msg    = document.getElementById('result-msg');

    if (chosen === correct) {
        score++;
        document.getElementById('score').textContent = score;
        btn.classList.replace('btn-outline-secondary', 'btn-success');
        msg.className  = 'alert alert-success mt-3';
        msg.textContent = '✅ Correct! It\'s ' + correct + '!';
    } else {
        btn.classList.replace('btn-outline-secondary', 'btn-danger');
        document.querySelectorAll('.option-btn').forEach(b => {
            if (b.dataset.answer === correct)
                b.classList.replace('btn-outline-secondary', 'btn-success');
        });
        msg.className  = 'alert alert-danger mt-3';
        msg.textContent = '❌ Wrong! It was ' + correct + '.';
    }

    msg.classList.remove('d-none');
    document.getElementById('next-btn').classList.remove('d-none');
}

document.getElementById('next-btn').addEventListener('click', () => {
    current++;
    showQuestion();
});

function endQuiz() {
    document.getElementById('recipe-card').classList.add('d-none');
    document.getElementById('options-container').classList.add('d-none');
    document.getElementById('result-msg').classList.add('d-none');
    document.getElementById('next-btn').classList.add('d-none');
    document.getElementById('final-score').textContent = score + ' / ' + questions.length;
    document.getElementById('final-screen').classList.remove('d-none');
}

if (ALL_RECIPES.length > 0) startQuiz();
else document.getElementById('quiz-container').innerHTML =
    '<div class="alert alert-warning">No recipes available for the quiz yet!</div>';
</script>

<?php renderFooter(); ?>
