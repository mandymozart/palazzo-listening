# Listening Sessions

## Local development

Start the built-in PHP server from the project root:

```
php -S localhost:8000
```

Then open http://localhost:8000 in your browser.

The add/edit token defaults to `admin` unless a `config.php` defining `TOKEN` is present. On deploy, `config.php` is generated from the `ADD_TOKEN` GitHub secret (see the deploy workflow).

## Data format

One text file per session in `data/`, named after the date (`2026-07-01.txt`). Each line is a song:

```
Song - Artist
Song - Artist https://example.com/link
```

An empty file announces an upcoming session: the date is listed without songs.

## Adding and editing

`/add.php` opens today's session. Pick another date to open it, or choose one from the list at the bottom. Changes are saved automatically while typing. The token is remembered in the browser's local storage.
