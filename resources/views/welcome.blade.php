<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="stylesheet" href="public/css/index.css?ver={{ time() }}">

        <title>Clubinno</title>
    </head>
    <body class="antialiased">
        <div id="app"></div>
        <script type="module" crossorigin src="public/js/index.js?ver={{ time() }}"></script>
    </body>
</html>
