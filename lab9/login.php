<?php
require 'functions.php';

if (isset($_SESSION['user'])) {
    header('Location: index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    global $users;

    $login = trim($_POST['login'] ?? '');
    $password = $_POST['password'] ?? '';

    if (
        isset($users[$login]) &&
        password_verify($password, $users[$login])
    ) {

        session_regenerate_id(true);

        $_SESSION['user'] = $login;
        $_SESSION['login_time'] = time();

        if (!isset($_SESSION['tasks'])) {
            $_SESSION['tasks'] = [];
        }

        header('Location: index.php');
        exit;

    } else {
        $error = 'Nieprawidłowy login lub hasło';
    }
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Logowanie</title>
</head>
<body>

<h2>Logowanie</h2>

<?php if ($error): ?>
    <div style="color:red">
        <?= htmlspecialchars($error) ?>
    </div>
<?php endif; ?>

<form method="POST">
    <input type="text" name="login" placeholder="Login">
    <input type="password" name="password" placeholder="Hasło">
    <button type="submit">
        Zaloguj
    </button>

</form>

</body>
</html>