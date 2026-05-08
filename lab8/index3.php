<?php
session_start();

// =======================
// DANE STARTOWE
// =======================

if (!isset($_SESSION['tasks'])) {
    $_SESSION['tasks'] = [
        [
            'title' => 'Mapa świata',
            'category' => 'Nauka',
            'priority' => 'średni',
            'status' => 'do zrobienia',
            'estimated_minutes' => 120,
            'tags' => ['frontend', 'backend'],
            'description' => 'Sprawdź https://example.com i napisz do admin@example.com #php'
        ],
        [
            'title' => 'Sprzątanie pokoju',
            'category' => 'Dom',
            'priority' => 'niski',
            'status' => 'zakończone',
            'estimated_minutes' => 60,
            'tags' => ['dom'],
            'description' => "- odkurzyć\n- umyć podłogę\n- biurko"
        ],  
    ];
}

$tasks = $_SESSION['tasks'];

$allowedCategories = ['Praca', 'Dom', 'Nauka', 'Zdrowie', 'Inne'];
$allowedPriorities = ['niski', 'średni', 'wysoki'];
$allowedStatuses = ['do zrobienia', 'w trakcie', 'zakończone'];

$errors = [];

// =======================
// FUNKCJE
// =======================

function extractTags($text) {
    preg_match_all('/([a-zA-Z0-9_]+)/', $text, $m);
    return $m[1];
}

function searchTasks($tasks, $pattern) {
    if ($pattern === '') return $tasks;
    $result = [];
    foreach ($tasks as $t) {

        $text =$t['title'] . ' ' . $t['description'];
        if (@preg_match("/$pattern/i", $text)) {
            $result[] = $t;
        }
    }

    return $result;
}

function filterTasksByTag($tasks, $tag) {
    return array_filter($tasks, fn($t) => in_array($tag, $t['tags']));
}

function formatTaskDescription($text) {

    $text = htmlspecialchars($text);

    // URL
    $text = preg_replace(
        '/\b(?:https?|ftp):\/\/[a-z0-9-+&@#\/%?=~_|!:,.;]*[a-z0-9-+&@#\/%=~_|]/i',
        '<a href="$0" target="_blank">$0</a>',
        $text
    );

    // TAGI
    $text = preg_replace(
        '/#([a-zA-Z0-9_]+)/',
        '<span style="color:green">#$1</span>',
        $text
    );

    // LISTY
    $text = preg_replace('/^[\s]*[-*+][\s]+(.+)$/m', '<li>$1</li>', $text);

    if (strpos($text, '<li>') !== false) {
        $text = '<ul>' . $text . '</ul>';
        $text = str_replace('</ul><ul>', '', $text);
    }

    // EMAIL
    $text = preg_replace(
        '/[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}/',
        '<a href="mailto:$0">$0</a>',
        $text
    );

    return nl2br($text);
}

// =======================
// DODAWANIE
// =======================

$title = $category = $priority = $status = $estimated_minutes = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $title = trim($_POST['title'] ?? '');
    $category = $_POST['category'] ?? '';
    $priority = $_POST['priority'] ?? '';
    $status = $_POST['status'] ?? '';
    $estimated_minutes = $_POST['estimated_minutes'] ?? '';
    $tagsText = $_POST['tags'] ?? '';
    $description = $_POST['description'] ?? '';

    $tags = extractTags($tagsText);

    if ($title === '') $errors[] = "Brak tytułu";
    if (!is_numeric($estimated_minutes)) $errors[] = "Zły czas";
    if (empty($tags)) $errors[] = "Brak tagów";

    if (!$errors) {
        $_SESSION['tasks'][] = [
            'title' => $title,
            'category' => $category,
            'priority' => $priority,
            'status' => $status,
            'estimated_minutes' => (int)$estimated_minutes,
            'tags' => $tags,
            'description' => $description
        ];
    }
}

// =======================
// FILTROWANIE
// =======================

$tasks = $_SESSION['tasks'];

if (!empty($_GET['search'])) {
    $tasks = searchTasks($tasks, $_GET['search']);
}

if (!empty($_GET['filter_tag'])) {
    $tasks = filterTasksByTag($tasks, $_GET['filter_tag']);
}

if (!empty($_GET['filter_status'])) {
    $tasks = array_filter($tasks, fn($t) => $t['status'] === $_GET['filter_status']);
}

if (!empty($_GET['filter_priority'])) {
    $tasks = array_filter($tasks, fn($t) => $t['priority'] === $_GET['filter_priority']);
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
          border: 1px solid #000; margin: 5px; padding: 5px; overflow-wrap: break-word; word-break: break-word;
        }

        .task li {
          list-style-position: inside; word-break: break-word;
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
        <input type="text"
            name="tags"
            placeholder="Tagi (oddzielone spacjami)">
        </div>

        <textarea name="description"
          placeholder="Opis zadania"></textarea>

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

    <form method="GET">

        <input type="text" name="search" placeholder="Regexowe szukanie">

        <select name="filter_status">
            <option value="">Status</option>
            <option value="do zrobienia">Do zrobienia</option>
            <option value="w trakcie">W trakcie</option>
            <option value="zakończone">Zakończone</option>
        </select>

        <select name="filter_priority">
            <option value="">Priorytet</option>
            <option value="niski">Niski</option>
            <option value="średni">Średni</option>
            <option value="wysoki">Wysoki</option>
        </select>

    </form>

    <h3>Lista zadań</h3>

    <?php foreach ($tasks as $t): ?>
        <div class="task">
            <strong><?= htmlspecialchars($t['title']) ?></strong><br>
            Kategoria: <?= htmlspecialchars($t['category']) ?><br>
            Priorytet: <?= htmlspecialchars($t['priority']) ?><br>
            Status: <?= htmlspecialchars($t['status']) ?><br>
            Czas: <?= (int)$t['estimated_minutes'] ?> min<br>
            Tagi: <?= htmlspecialchars(implode(', ', $t['tags'])) ?><br>
            <p><?= formatTaskDescription($t['description']) ?></p>
        </div>
    <?php endforeach; ?>

</main>

</div>
</body>
</html>