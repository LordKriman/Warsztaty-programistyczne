<?php

$theme = $_COOKIE['theme'] ?? 'light';

$isDark = $theme === 'dark';
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Menadżer zadań</title>

    <style>

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: Arial, Sans-Serif;

            background:
                <?= $isDark ? '#1a1a2e' : '#ffffff' ?>;

            color:
                <?= $isDark ? '#ffffff' : '#000000' ?>;
        }

        a {
            color: <?= $isDark ? '#7ec8ff' : 'blue' ?>;
        }

        .wrapper {
            display: grid;
            grid-template-areas:
                "header header"
                "sidebar main";

            grid-template-columns: 280px 1fr;
            grid-template-rows: 80px 1fr;

            min-height: 100vh;
        }

        header {
            grid-area: header;

            background-color: #1e2b3b;

            color: white;

            display: flex;

            align-items: center;

            justify-content: space-between;

            padding: 10px 15px;
        }

        aside {
            grid-area: sidebar;
            padding: 10px;
            border-right: 1px solid #999;
        }

        main {
            grid-area: main;
            padding: 10px;
        }

        form {
            display: flex;
            flex-direction: column;
        }

        input, select, textarea, button {
            margin-bottom: 8px;
            padding: 5px;
        }

        .task {
            border: 1px solid #999;
            padding: 8px;
            margin-bottom: 8px;
        }

        .error {
            background: #ffd2d2;
            padding: 8px;
            margin-bottom: 8px;
        }

        .success {
            background: #d7ffd7;
            padding: 8px;
            margin-bottom: 8px;
        }

    </style>
</head>

<body>

<div class="wrapper">
<header>
    <div>
        <h2>Menadżer zadań</h2>
    </div>

    <div>
        Zalogowany jako:
        <strong><?= htmlspecialchars($_SESSION['user']) ?></strong>
        |
        <a href="logout.php">
            Wyloguj
        </a>
        <br>
        Czas sesji:
        <?= sessionTime() ?>
    </div>
</header>