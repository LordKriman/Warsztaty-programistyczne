<?php
session_start();

// =======================
// UŻYTKOWNICY
// =======================

$users = [
    'admin' => password_hash('admin123', PASSWORD_DEFAULT),
    'jan' => password_hash('haslo456', PASSWORD_DEFAULT),
    'anna' => password_hash('anna789', PASSWORD_DEFAULT)
];

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

        $text = $t['title'] . ' ' . $t['description'];

        if (@preg_match("/$pattern/i", $text)) {
            $result[] = $t;
        }
    }

    return $result;
}

function searchKeyPattern($tasks, $key, $pattern) {

    if ($pattern === '') return $tasks;

    $result = [];

    foreach ($tasks as $t) {

        $text = $t[$key] ?? '';

        if (is_array($text)) {
            $text = implode(' ', $text);
        }

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

    // TELEFON
    $text = preg_replace(
        '/[0-9]{9}/',
        '<a href="tel:$0">$0</a>',
        $text
    );

    // DATA
    $text = preg_replace(
        '/[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|1[0-9]|2[0-9]|3[01])$/',
        '<span style="color:brown">$0</span>',
        $text
    );

    return nl2br($text);
}

function sessionTime() {

    if (!isset($_SESSION['login_time'])) {
        return '0 min';
    }

    $seconds = time() - $_SESSION['login_time'];

    $minutes = floor($seconds / 60);
    $hours = floor($minutes / 60);

    $minutes = $minutes % 60;

    return $hours . ' h ' . $minutes . ' min';
}