<?php require __DIR__ . '/lib.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Listening Sessions</title>
<link rel="stylesheet" href="style.css">
</head>
<body>

<?php
$files = listSessions();

if (empty($files)) {
    echo "<p>No listening sessions yet.</p>";
}

foreach ($files as $file) {
    $date = basename($file, '.txt');
    $timestamp = strtotime($date);
    $title = $timestamp ? date('F j, Y', $timestamp) : $date;

    echo "<h2>" . htmlspecialchars($title) . "</h2>\n";
    echo "<ul>\n";

    $lines = file($file, FILE_IGNORE_NEW_LINES);
    foreach ($lines as $line) {
        if (trim($line) === '') {
            continue;
        }
        $parsed = parseLine($line);
        $text = htmlspecialchars($parsed['text']);

        if ($parsed['url']) {
            $url = htmlspecialchars($parsed['url']);
            echo "<li><a href=\"$url\" target=\"_blank\">$text</a></li>\n";
        } else {
            echo "<li>$text</li>\n";
        }
    }

    echo "</ul>\n";
}
?>

<footer>
<a href="add.php">Add a listening session</a>
</footer>

</body>
</html>
