<?php

define('DATA_DIR', __DIR__ . '/data');

if (file_exists(__DIR__ . '/config.php')) {
    require __DIR__ . '/config.php';
}
if (!defined('TOKEN')) {
    define('TOKEN', 'admin');
}

function listSessions() {
    $files = glob(DATA_DIR . '/*.txt');
    rsort($files);
    return $files;
}

function isValidDate($date) {
    $d = DateTime::createFromFormat('Y-m-d', $date);
    return $d && $d->format('Y-m-d') === $date && $d->format('Y') >= 2000;
}

function loadListing($date) {
    $file = DATA_DIR . '/' . $date . '.txt';
    return is_file($file) ? rtrim(file_get_contents($file)) : '';
}

function saveListing($date, $listing) {
    $listing = trim($listing);
    $content = $listing === '' ? '' : $listing . "\n";
    file_put_contents(DATA_DIR . '/' . $date . '.txt', $content, LOCK_EX);
}

function parseLine($line) {
    $line = trim($line);
    $url = null;

    if (preg_match('/^(.*?)\s*(https?:\/\/\S+)\s*$/', $line, $m)) {
        $text = trim($m[1], " \t-");
        $url = $m[2];
    } else {
        $text = $line;
    }

    return ['text' => $text, 'url' => $url];
}
