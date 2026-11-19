<?php
require __DIR__ . '/lib.php';

function field($name) {
    return is_string($_POST[$name] ?? null) ? $_POST[$name] : '';
}

$errors = [];
$saved = false;
$posting = $_SERVER['REQUEST_METHOD'] === 'POST';

if ($posting) {
    $date = field('date');
    $listing = str_replace(["\r\n", "\r"], "\n", field('listing'));

    if (TOKEN === '' || !hash_equals(TOKEN, field('token'))) {
        $errors[] = 'Incorrect token.';
    }

    if (!isValidDate($date)) {
        $errors[] = 'Please choose a valid date.';
    }

    if (!empty($_FILES['file']['name'])) {
        $ext = strtolower(pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, ['txt', 'md'])) {
            $errors[] = 'Uploaded file must be .txt or .md.';
        } elseif ($_FILES['file']['error'] === UPLOAD_ERR_OK) {
            $listing = str_replace(["\r\n", "\r"], "\n", file_get_contents($_FILES['file']['tmp_name']));
        } else {
            $errors[] = 'File upload failed.';
        }
    }

    if (!$errors) {
        saveListing($date, $listing);
        $saved = true;
    }

    // Autosave requests from the page's JavaScript only need a short answer.
    if (isset($_POST['ajax'])) {
        header('Content-Type: text/plain; charset=utf-8');
        if ($errors) {
            http_response_code(400);
        }
        echo $errors ? 'Not saved: ' . implode(' ', $errors) : 'Saved';
        exit;
    }
} else {
    $date = $_GET['date'] ?? '';
    if (!is_string($date) || !isValidDate($date)) {
        $date = date('Y-m-d');
    }
    $listing = loadListing($date);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Add or Edit Listening Session</title>
<link rel="stylesheet" href="style.css?v=<?= filemtime(__DIR__ . '/style.css') ?>">
</head>
<body>

<h2>Add or edit a listening session</h2>

<p>Pick a date. Leave the listing empty to announce an upcoming session.</p>

<?php if ($saved): ?>
<p>Saved.</p>
<?php endif; ?>

<?php if ($errors): ?>
<ul>
<?php foreach ($errors as $error): ?>
<li><?= htmlspecialchars($error) ?></li>
<?php endforeach; ?>
</ul>
<?php endif; ?>

<form id="form" method="post" enctype="multipart/form-data">
<label>Token
<input type="password" name="token" value="<?= htmlspecialchars($posting ? field('token') : 'admin') ?>" required autocomplete="off"></label>
<label>Date
<input type="date" name="date" value="<?= htmlspecialchars(isValidDate($date) ? $date : '') ?>" required></label>
<label>Listing (one song per line: Song - Artist, optionally followed by a link)
<textarea name="listing" rows="30" cols="80"><?= htmlspecialchars($listing) ?></textarea></label>
<label>Or load a file (.txt / .md)
<input type="file" name="file" accept=".txt,.md"></label>
<p>
<button type="submit">Save</button>
<span id="status">Changes are saved automatically.</span>
</p>
</form>

<h2>Edit a session</h2>
<ul>
<?php foreach (listSessions() as $file): $d = basename($file, '.txt'); ?>
<?php if (isValidDate($d)): ?>
<li><a href="add.php?date=<?= htmlspecialchars($d) ?>"><?= htmlspecialchars($d) ?></a></li>
<?php endif; ?>
<?php endforeach; ?>
</ul>

<footer>
<a href="index.php">Back to list</a>
</footer>

<script>
const form = document.getElementById('form');
const token = form.elements.token;
const date = form.elements.date;
const listing = form.elements.listing;
const file = form.elements.file;
const note = document.getElementById('status');

// The token is remembered in this browser so autosave can run without asking.
try { token.value = localStorage.getItem('token') || token.value; } catch (e) {}
token.addEventListener('input', () => {
    try { localStorage.setItem('token', token.value); } catch (e) {}
});

let timer;
let latest = 0;

function save() {
    clearTimeout(timer);
    if (!date.value) {
        note.textContent = 'Pick a date to save.';
        return;
    }

    const request = ++latest;
    const data = new FormData();
    data.set('ajax', '1');
    data.set('token', token.value);
    data.set('date', date.value);
    data.set('listing', listing.value);

    note.textContent = 'Saving...';
    fetch('add.php', { method: 'POST', body: data })
        .then(response => response.text().then(text => ({ ok: response.ok, text })))
        .then(result => {
            if (request !== latest) return;
            note.textContent = result.ok ? 'Saved ' + new Date().toLocaleTimeString() : result.text;
        })
        .catch(() => {
            if (request === latest) note.textContent = 'Not saved: connection problem.';
        });
}

listing.addEventListener('input', () => {
    clearTimeout(timer);
    note.textContent = 'Typing...';
    timer = setTimeout(save, 800);
});

form.addEventListener('submit', event => {
    event.preventDefault();
    save();
});

// Choosing a file fills the listing, which is then saved like typed text.
file.addEventListener('change', async () => {
    if (!file.files[0]) return;
    listing.value = await file.files[0].text();
    file.value = '';
    save();
});

// Choosing another date opens that session. The short delay avoids jumping
// away while a date is still being typed.
let dateTimer;
date.addEventListener('change', () => {
    clearTimeout(dateTimer);
    dateTimer = setTimeout(() => {
        if (/^20\d\d-\d\d-\d\d$/.test(date.value)) {
            location.href = 'add.php?date=' + date.value;
        }
    }, 800);
});
</script>

</body>
</html>
