<?php
if (!isset($pageContent) || !is_string($pageContent)) {
    throw new LogicException('La variable $pageContent doit contenir le HTML de la page.');
}
?>
<!DOCTYPE html>
<html lang="fr">
<?php require_once __DIR__ . '/includes/header.php'; ?>
<body>
    <?php require_once __DIR__ . '/includes/navbar.php'; ?>

    <main>
        <?= $pageContent ?>
    </main>

    <?php require_once __DIR__ . '/includes/footer.php'; ?>
</body>
</html>