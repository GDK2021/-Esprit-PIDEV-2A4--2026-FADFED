<header class="site-header">
    <nav class="navigation" aria-label="Navigation principale">
        <?php $authenticatedUser = current_authenticated_user(); ?>
        <?php
        $navProfile = null;
        if ($authenticatedUser !== null) {
            require_once __DIR__ . '/../../control/userControl.php';
            $profileStatement = getPDO()->prepare('SELECT pseudo, photo_profil FROM user WHERE id_user = :id');
            $profileStatement->execute([':id' => $authenticatedUser['id']]);
            $navProfile = $profileStatement->fetch() ?: null;
        }
        $profilePseudo = is_array($navProfile) ? (string) $navProfile['pseudo'] : 'Mon profil';
        $profilePhoto = is_array($navProfile) ? $navProfile['photo_profil'] : null;
        $profilePhotoUrl = is_string($profilePhoto)
            && preg_match('~^assets/uploads/profiles/profile_\d+_[a-f0-9]{32}\.(?:jpg|png|webp)$~', $profilePhoto)
                ? '../' . $profilePhoto
                : null;
        ?>
        <?php if ($authenticatedUser !== null && $authenticatedUser['role'] === 'admin'): ?>
        <a class="wordmark" href="../BackOffice/liste.php" aria-label="FADFED, administration">FADFED<span>.</span></a>
        <div class="nav-links">
            <a href="../BackOffice/liste.php" aria-current="page">Gestion utilisateurs</a>
            <a href="../frontOffice/index.php">Voir le site</a>
            <a href="../../control/userControl.php?action=logout">Déconnexion</a>
        </div>
        <a class="profile-nav-link" href="../frontOffice/profile.php" aria-label="Profil de <?= htmlspecialchars($profilePseudo, ENT_QUOTES, 'UTF-8') ?>">
            <?php if ($profilePhotoUrl !== null): ?>
            <img class="profile-nav-image" src="<?= htmlspecialchars($profilePhotoUrl, ENT_QUOTES, 'UTF-8') ?>" alt="">
            <?php else: ?>
            <span class="profile-nav-avatar"><?= htmlspecialchars(strtoupper(substr($profilePseudo, 0, 1)), ENT_QUOTES, 'UTF-8') ?></span>
            <?php endif; ?>
            <span class="profile-nav-name"><?= htmlspecialchars($profilePseudo, ENT_QUOTES, 'UTF-8') ?></span>
        </a>
        <?php else: ?>
        <a class="wordmark" href="../frontOffice/index.php#accueil" aria-label="FADFED, accueil">FADFED<span>.</span></a>
        <div class="nav-links">
            <a href="../frontOffice/index.php">Accueil</a>
            <?php if ($authenticatedUser === null): ?>
            <a href="../frontOffice/login.php">Connexion</a>
            <a href="../frontOffice/register.php">Inscription</a>
            <a href="../BackOffice/login.php">Espace admin</a>
            <?php else: ?>
            <a href="../../control/userControl.php?action=logout">Déconnexion</a>
            <?php endif; ?>
        </div>
        <?php if ($authenticatedUser !== null): ?>
        <a class="profile-nav-link" href="../frontOffice/profile.php" aria-label="Profil de <?= htmlspecialchars($profilePseudo, ENT_QUOTES, 'UTF-8') ?>">
            <?php if ($profilePhotoUrl !== null): ?>
            <img class="profile-nav-image" src="<?= htmlspecialchars($profilePhotoUrl, ENT_QUOTES, 'UTF-8') ?>" alt="">
            <?php else: ?>
            <span class="profile-nav-avatar"><?= htmlspecialchars(strtoupper(substr($profilePseudo, 0, 1)), ENT_QUOTES, 'UTF-8') ?></span>
            <?php endif; ?>
            <span class="profile-nav-name"><?= htmlspecialchars($profilePseudo, ENT_QUOTES, 'UTF-8') ?></span>
        </a>
        <?php else: ?>
        <a class="button button-small" href="../frontOffice/index.php#ressources">Prendre soin de moi <span aria-hidden="true">↗</span></a>
        <?php endif; ?>
        <?php endif; ?>
    </nav>
</header>