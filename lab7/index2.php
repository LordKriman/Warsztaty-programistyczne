<?php
// =======================
// DANE STARTOWE
// =======================
$tasks = [
    [
        'title' => 'Mapa świata',
        'category' => 'Nauka',
        'priority' => 'średni',
        'status' => 'do zrobienia',
        'estimated_minutes' => 120,
        'tags' => ['frontend', 'backend']
    ],
    [
        'title' => 'Sprzątanie pokoju',
        'category' => 'Dom',
        'priority' => 'niski',
        'status' => 'zakończone',
        'estimated_minutes' => 60,
        'tags' => ['dom']
    ],
    [
        'title' => 'Trening',
        'category' => 'Zdrowie',
        'priority' => 'wysoki',
        'status' => 'w trakcie',
        'estimated_minutes' => 90,
        'tags' => ['pilne']
    ],
    [
        'title' => 'Projekt PHP',
        'category' => 'Praca',
        'priority' => 'wysoki',
        'status' => 'do zrobienia',
        'estimated_minutes' => 240,
        'tags' => ['backend', 'zespół']
    ],
];

$allowedCategories = ['Praca', 'Dom', 'Nauka', 'Zdrowie', 'Inne'];
$allowedPriorities = ['niski', 'średni', 'wysoki'];
$allowedStatuses = ['do zrobienia', 'w trakcie', 'zakończone'];
$allowedTags = ['pilne', 'zespół', 'backend', 'frontend', 'dom', 'zakupy'];

$errors = [];

$title = '';
$category = '';
$priority = '';
$status = '';
$estimated_minutes = '';
$tags = [];


// =======================
// OBSŁUGA POST
// =======================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $title = trim($_POST['title'] ?? '');
    $category = $_POST['category'] ?? '';
    $priority = $_POST['priority'] ?? '';
    $status = $_POST['status'] ?? '';
    $estimated_minutes = $_POST['estimated_minutes'] ?? '';
    $tags = $_POST['tags'] ?? [];

    // walidacja
    if ($title === '') {
        $errors[] = "Tytuł nie może być pusty.";
    }

    if (!is_numeric($estimated_minutes) || (int)$estimated_minutes <= 0) {
        $errors[] = "Szacowany czas musi być dodatnią liczbą.";
    }

    if (!in_array($category, $allowedCategories)) {
        $errors[] = "Niepoprawna kategoria.";
    }

    if (!in_array($priority, $allowedPriorities)) {
        $errors[] = "Niepoprawny priorytet.";
    }

    if (!in_array($status, $allowedStatuses)) {
        $errors[] = "Niepoprawny status.";
    }

    if (empty($tags)) {
        $errors[] = "Wybierz co najmniej jeden tag.";
    }

    // jeśli OK → dodaj zadanie
    if (empty($errors)) {

        $cleanTags = array_values(array_filter($tags));
        sort($cleanTags);

        $tasks[] = [
            'title' => $title,
            'category' => $category,
            'priority' => $priority,
            'status' => $status,
            'estimated_minutes' => (int)$estimated_minutes,
            'tags' => $cleanTags
        ];

        // reset formularza
        $title = $category = $priority = $status = $estimated_minutes = '';
        $tags = [];
    }
}


// =======================
// STATYSTYKI
// =======================
$totalTasks = count($tasks);

$todoCount = 0;
$doneCount = 0;
$totalMinutes = 0;

foreach ($tasks as $t) {
    $totalMinutes += (int)$t['estimated_minutes'];

    if ($t['status'] === 'do zrobienia') $todoCount++;
    if ($t['status'] === 'zakończone') $doneCount++;
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Menadżer zadań</title>

    <style>
        * {
        box-sizing: border-box; margin: 0; padding: 0;
        }

        body {
        font-family: Arial, Sans-Serif;
        }

        .wrapper {
          display: grid;
          grid-template-areas: "header header" "sidebar main";
          grid-template-columns: 280px 1fr;
          grid-template-rows: 60px 1fr;
          min-height: 100vh;
        }

        header {
          grid-area: header;
          position: sticky;
          background-color: #1e2b3b;
          color: white;
          top: 0;
          display: flex;
          align-items: center;
          padding: 0 15px;
          justify-content: space-between;
        }
        aside {
          grid-area: sidebar; padding: 10px; border-right: 1px solid #ccc;
        }

        main {
          grid-area: main; padding: 10px;
        }

        form {
          display: flex; flex-direction: column;
        }
        input, select {
          margin-bottom: 8px;
        }

        .error {
        background: #ffd2d2; padding: 5px; margin-bottom: 5px;
        }

        .task {
        border: 1px solid #000; margin: 5px; padding: 5px;
        }
    </style>
</head>

<body>
<div class="wrapper">

<header>
    <h2>Menadżer zadań</h2>
</header>

<aside>
    <h3>Dodaj zadanie</h3>

    <?php if (!empty($errors)): ?>
        <ul class="error">
            <?php foreach ($errors as $e): ?>
                <li><?= htmlspecialchars($e) ?></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <form method="POST">
        <input type="text" name="title" placeholder="Tytuł">

        <select name="category">
            <?php foreach ($allowedCategories as $c): ?>
                <option value="<?= $c ?>" <?= $c === $category ? 'selected' : '' ?>>
                    <?= $c ?>
                </option>
            <?php endforeach; ?>
        </select>

        <select name="priority">
            <?php foreach ($allowedPriorities as $p): ?>
                <option value="<?= $p ?>" <?= $p === $priority ? 'selected' : '' ?>>
                    <?= $p ?>
                </option>
            <?php endforeach; ?>
        </select>

        <select name="status">
            <?php foreach ($allowedStatuses as $s): ?>
                <option value="<?= $s ?>" <?= $s === $status ? 'selected' : '' ?>>
                    <?= $s ?>
                </option>
            <?php endforeach; ?>
        </select>

        <input type="text" name="estimated_minutes"
               placeholder="Czas (min)"
               value="<?= htmlspecialchars($estimated_minutes) ?>">

        <div>
            <?php foreach ($allowedTags as $tag): ?>
                <label>
                    <input type="checkbox" name="tags[]"
                           value="<?= $tag ?>"
                        <?= in_array($tag, $tags) ? 'checked' : '' ?>>
                    <?= $tag ?>
                </label><br>
            <?php endforeach; ?>
        </div>

        <button type="submit">Dodaj</button>
    </form>
</aside>

<main>

    <h3>Statystyki</h3>
    <p>Wszystkie: <?= $totalTasks ?></p>
    <p>Do zrobienia: <?= $todoCount ?></p>
    <p>Zakończone: <?= $doneCount ?></p>
    <p>Suma minut: <?= $totalMinutes ?></p>

    <hr>

    <h3>Lista zadań</h3>

    <?php foreach ($tasks as $t): ?>
        <div class="task">
            <strong><?= htmlspecialchars($t['title']) ?></strong><br>
            Kategoria: <?= htmlspecialchars($t['category']) ?><br>
            Priorytet: <?= htmlspecialchars($t['priority']) ?><br>
            Status: <?= htmlspecialchars($t['status']) ?><br>
            Czas: <?= (int)$t['estimated_minutes'] ?> min<br>
            Tagi: <?= htmlspecialchars(implode(', ', $t['tags'])) ?>
        </div>
    <?php endforeach; ?>

</main>

</div>
</body>
</html>