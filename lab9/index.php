<?php
require 'functions.php';

// =======================
// OCHRONA
// =======================

if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

// =======================
// PREFERENCJE
// =======================

if (isset($_POST['save_preferences'])) {

    setcookie(
        'theme',
        $_POST['theme'],
        time() + 30*24*60*60,
        '/'
    );

    setcookie(
        'tasks_per_page',
        $_POST['tasks_per_page'],
        time() + 30*24*60*60,
        '/'
    );

    setcookie(
        'sort_by',
        $_POST['sort_by'],
        time() + 30*24*60*60,
        '/'
    );

    $_COOKIE['theme'] = $_POST['theme'];
    $_COOKIE['tasks_per_page'] = $_POST['tasks_per_page'];
    $_COOKIE['sort_by'] = $_POST['sort_by'];

    header('Location: index.php');
    exit;
}

$tasksPerPage = (int)($_COOKIE['tasks_per_page'] ?? 10);
$sortBy = $_COOKIE['sort_by'] ?? 'created_at';

// =======================
// TASKI
// =======================

if (!isset($_SESSION['tasks'])) {
    $_SESSION['tasks'] = [];
}

$tasks = $_SESSION['tasks'];

$errors = [];

// =======================
// DODAWANIE
// =======================

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['save_preferences'])) {

    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');

    $priority = $_POST['priority'] ?? 'low';
    $status = $_POST['status'] ?? 'todo';

    $tagsText = $_POST['tags'] ?? '';

    $tags = extractTags($tagsText);

    if ($title === '') {
        $errors[] = 'Brak tytułu';
    }

    if (empty($tags)) {
        $errors[] = 'Brak tagów';
    }

    if (!$errors) {

        $_SESSION['tasks'][] = [
            'id' => uniqid(),
            'title' => $title,
            'description' => $description,
            'priority' => $priority,
            'status' => $status,
            'tags' => $tags,
            'created_at' => date('Y-m-d H:i:s'),
            'created_by' => $_SESSION['user']
        ];

        header('Location: index.php');
        exit;
    }
}

// =======================
// USUWANIE
// =======================

if (isset($_GET['delete'])) {

    foreach ($_SESSION['tasks'] as $k => $t) {

        if ($t['id'] === $_GET['delete']) {
            unset($_SESSION['tasks'][$k]);
        }
    }

    header('Location: index.php');
    exit;
}

// =======================
// STATUS
// =======================

if (isset($_GET['change_status'])) {

    foreach ($_SESSION['tasks'] as &$t) {

        if ($t['id'] === $_GET['change_status']) {

            if ($t['status'] === 'todo') {
                $t['status'] = 'in_progress';
            }
            else if ($t['status'] === 'in_progress') {
                $t['status'] = 'done';
            }
            else {
                $t['status'] = 'todo';
            }
        }
    }

    header('Location: index.php');
    exit;
}

// =======================
// SORTOWANIE
// =======================

$tasks = $_SESSION['tasks'];

usort($tasks, function($a, $b) use ($sortBy) {

    return strcmp(
        $a[$sortBy],
        $b[$sortBy]
    );
});

$currentPage = (int)($_GET['page'] ?? 1);

if ($currentPage < 1) {
    $currentPage = 1;
}

$totalTasksCount = count($tasks);

$totalPages = ceil($totalTasksCount / $tasksPerPage);

$offset = ($currentPage - 1) * $tasksPerPage;

$tasks = array_slice(
    $tasks,
    $offset,
    $tasksPerPage
);

$totalTasks = count($_SESSION['tasks']);

$inProgress = 0;
$done = 0;

foreach ($_SESSION['tasks'] as $t) {

    if ($t['status'] === 'in_progress') {
        $inProgress++;
    }

    if ($t['status'] === 'done') {
        $done++;
    }
}

require 'header.php';
?>

<aside>

    <h3>Dodaj zadanie</h3>
    <?php if ($errors): ?>
        <ul class="error">

            <?php foreach ($errors as $e): ?>
                <li>
                    <?= htmlspecialchars($e) ?>
                </li>
            <?php endforeach; ?>

        </ul>
    <?php endif; ?>

    <form method="POST">

        <input
            type="text"
            name="title"
            placeholder="Tytuł">

        <textarea
            name="description"
            placeholder="Opis"></textarea>

        <select name="priority">
            <option value="low">low</option>
            <option value="medium">medium</option>
            <option value="high">high</option>
        </select>

        <select name="status">
            <option value="todo">todo</option>
            <option value="in_progress">in_progress</option>
            <option value="done">done</option>
        </select>

        <input
            type="text"
            name="tags"
            placeholder="Tagi">
        <button type="submit">
            Dodaj
        </button>
    </form>

    <hr>

    <h3>Preferencje</h3>
    <form method="POST">
        <select name="theme">
            <option value="light">
                light
            </option>
            <option value="dark">
                dark
            </option>
        </select>

        <select name="tasks_per_page">
            <option value="5">5</option>
            <option value="10">10</option>
            <option value="25">25</option>
        </select>

        <select name="sort_by">
            <option value="created_at">
                data
            </option>

            <option value="priority">
                priorytet
            </option>

            <option value="title">
                nazwa
            </option>
        </select>

        <button
            type="submit"
            name="save_preferences">

            Zapisz preferencje

        </button>

    </form>

</aside>

<main>
    <h3>Statystyki</h3>

    <p>
        Wszystkie:
        <?= $totalTasks ?>
    </p>

    <p>
        W toku:
        <?= $inProgress ?>
    </p>

    <p>
        Ukończone:
        <?= $done ?>
    </p>

    <hr>

    <h3>Lista zadań</h3>

    <?php foreach ($tasks as $t): ?>

        <div class="task">
            <strong>
                <?= htmlspecialchars($t['title']) ?>
            </strong>
            <br>
            Priorytet:
            <?= htmlspecialchars($t['priority']) ?>
            <br>
            Status:
            <?= htmlspecialchars($t['status']) ?>
            <br>
            Autor:
            <?= htmlspecialchars($t['created_by']) ?>
            <br>
            Data:
            <?= htmlspecialchars($t['created_at']) ?>
            <br>
            Tagi:
            <?= htmlspecialchars(implode(', ', $t['tags'])) ?>
            <br><br>
            <?= formatTaskDescription($t['description']) ?>
            <br><br>
            <a href="?change_status=<?= $t['id'] ?>">
                Zmień status
            </a>
            |
            <a href="?delete=<?= $t['id'] ?>">
                Usuń
            </a>
        </div>

    <?php endforeach; ?>

    <hr>

    <div>
        <?php if ($currentPage > 1): ?>
            <a href="?page=<?= $currentPage - 1 ?>">
                <- Poprzednia
            </a>
        <?php endif; ?>
        Strona
        <?= $currentPage ?>
        z
        <?= $totalPages ?>
        |
        <?php if ($currentPage < $totalPages): ?>
            <a href="?page=<?= $currentPage + 1 ?>">
                Następna ->
            </a>
        <?php endif; ?>

    </div>
</main>
<?php require 'footer.php'; ?>