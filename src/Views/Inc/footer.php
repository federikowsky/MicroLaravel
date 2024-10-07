</body>

<?php if (isset($js_files)) : ?>
    <?php foreach ($js_files as $js): ?>
        <script src="<?= $js ?>"></script>
    <?php endforeach; ?>
<?php endif; ?>

</html>