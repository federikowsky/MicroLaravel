<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://www.phptutorial.net/app/css/style.css">
    <!-- includi il css in base all'url richiesto -->
    <title>
        <?= $title ?? 'Home' ?>
    </title>
    <?php if (isset($css_files)) : ?>
        <?php foreach ($css_files as $css): ?>
            <link rel="stylesheet" href="<?= $css ?>">
        <?php endforeach; ?>
    <?php endif; ?>
</head>

<body>
    <?php flash() ?>