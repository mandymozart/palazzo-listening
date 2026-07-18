<?php

define('DATA_DIR', __DIR__ . '/data');

if (file_exists(__DIR__ . '/config.php')) {
    require __DIR__ . '/config.php';
}
if (!defined('PASSWORD')) {
    define('PASSWORD', 'admin');
}

function listSessions() {
    $files = glob(DATA_DIR . '/*.txt');
    rsort($files);
    return $files;
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

function validateListing($content) {
    $lines = preg_split('/\r\n|\r|\n/', trim($content));
    $lines = array_filter($lines, fn($l) => trim($l) !== '');

    if (empty($lines)) {
        return false;
    }

    return true;
}
