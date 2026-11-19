<?php require __DIR__ . '/lib.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Listening Sessions</title>
<link rel="stylesheet" href="style.css">
</head>
<body>

<?php
$files = listSessions();
$today = date('Y-m-d');

if (empty($files)) {
    echo "<p>No listening sessions yet.</p>";
}

foreach ($files as $file) {
    $date = basename($file, '.txt');
    $timestamp = strtotime($date);
    $title = $timestamp ? date('F j, Y', $timestamp) : $date;

    echo "<h2>" . htmlspecialchars($title) . "</h2>\n";

    $lines = array_filter(file($file, FILE_IGNORE_NEW_LINES), fn($l) => trim($l) !== '');

    if (empty($lines)) {
        echo $date >= $today ? "<p>Upcoming. Songs will be listed here.</p>\n" : "<p>No songs listed.</p>\n";
        continue;
    }

    echo "<ul>\n";
    foreach ($lines as $line) {
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
<a href="add.php">Add or edit sessions</a>
</footer>

</body>
</html>
