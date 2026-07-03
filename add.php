<?php require __DIR__ . '/lib.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Add Listening Session</title>
<link rel="stylesheet" href="style.css">
</head>
<body>

<?php
$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'] ?? '';
    $date = $_POST['date'] ?? '';
    $listing = trim($_POST['listing'] ?? '');

    if ($password !== PASSWORD) {
        $errors[] = 'Incorrect password.';
    }

    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
        $errors[] = 'Please choose a valid date.';
    }

    if (!empty($_FILES['file']['name'])) {
        $ext = strtolower(pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, ['txt', 'md'])) {
            $errors[] = 'Uploaded file must be .txt or .md.';
        } elseif ($_FILES['file']['error'] === UPLOAD_ERR_OK) {
            $listing = trim(file_get_contents($_FILES['file']['tmp_name']));
        } else {
            $errors[] = 'File upload failed.';
        }
    }

    if ($listing === '') {
        $errors[] = 'Please provide a listing, either pasted or as a file.';
    } elseif (!validateListing($listing)) {
        $errors[] = 'Listing is not in the expected format. Each line must be "Song - Artist", optionally followed by a link.';
    }

    if (empty($errors)) {
        file_put_contents(DATA_DIR . '/' . $date . '.txt', $listing . "\n");
        $success = true;
    }
}
?>

<h2>Add a listening session</h2>

<?php if ($success): ?>
<p>Session added. <a href="index.php">Back to list</a></p>
<?php else: ?>

<?php if (!empty($errors)): ?>
<ul>
<?php foreach ($errors as $error): ?>
<li><?= htmlspecialchars($error) ?></li>
<?php endforeach; ?>
</ul>
<?php endif; ?>

<form method="post" enctype="multipart/form-data">
<p>
<label>Password<br>
<input type="password" name="password" required></label>
</p>
<p>
<label>Date<br>
<input type="date" name="date" required></label>
</p>
<p>
<label>Listing (one song per line, e.g. "Song - Artist" or "Song - Artist https://link")<br>
<textarea name="listing" rows="10" cols="60"></textarea></label>
</p>
<p>
<label>...or upload a file (.txt / .md)<br>
<input type="file" name="file" accept=".txt,.md"></label>
</p>
<p>
<button type="submit">Add</button>
</p>
</form>

<?php endif; ?>

<footer>
<a href="index.php">Back to list</a>
</footer>

</body>
</html>
