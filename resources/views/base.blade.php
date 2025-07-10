<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="theme-color" content="#409EFF">

        <title>GestCondo</title>

        <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@100;200;300;400;500;600;700;800;900&display=swap" rel="stylesheet">
        <link href="https://fonts.googleapis.com/css2?family=Baloo+Paaji+2:wght@400;500;600;700&display=swap" rel="stylesheet">
    </head>
    <body>
        <div id="app"></div>
        <div id="appLoading"></div>
        
        @vite([
            'resources/js/app.js',
            'resources/css/app.css'
        ])
    </body>
</html>
