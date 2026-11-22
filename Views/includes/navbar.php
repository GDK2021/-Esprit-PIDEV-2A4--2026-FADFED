<?php
// $currentPage doit être défini dans la page appelante (ex: $currentPage = 'index';)
if (!isset($currentPage)) {
    $currentPage = '';
}

function navActive($page, $currentPage) {
    return $page === $currentPage ? 'active' : '';
}

// Le menu "Mon espace" est actif si la page courante est l'un de ses sous-liens
$espacePages = ['suivi', 'rendezvous', 'orientation'];
$espaceActive = in_array($currentPage, $espacePages, true) ? 'active' : '';
?>
<!-- Navbar Start -->
<nav class="navbar navbar-expand-lg navbar-fadfed sticky-top p-0">
    <a href="index.php" class="navbar-brand d-flex align-items-center px-4 px-lg-5">
        <i class="fa fa-leaf me-2 brand-leaf"></i>
        <span class="brand-name">FADFED</span>
        <span class="brand-ar ms-2">فضفض</span>
    </a>
    <button type="button" class="navbar-toggler me-4" data-bs-toggle="collapse" data-bs-target="#navbarCollapse">
        <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarCollapse">
        <div class="navbar-nav ms-auto p-4 p-lg-0">
            <a href="index.php" class="nav-item nav-link <?php echo navActive('index', $currentPage); ?>">Accueil</a>
            <a href="exercices.php" class="nav-item nav-link <?php echo navActive('exercices', $currentPage); ?>">Exercices</a>
            <a href="communaute.php" class="nav-item nav-link <?php echo navActive('communaute', $currentPage); ?>">Communauté</a>
            <div class="nav-item dropdown">
                <a href="#" class="nav-link dropdown-toggle <?php echo $espaceActive; ?>" data-bs-toggle="dropdown">Mon espace</a>
                <div class="dropdown-menu m-0">
                    <a href="suivi.php" class="dropdown-item <?php echo navActive('suivi', $currentPage); ?>">Mon suivi et mon jardin</a>
                    <a href="rendezvous.php" class="dropdown-item <?php echo navActive('rendezvous', $currentPage); ?>">Mes rendez-vous</a>
                    <a href="orientation.php" class="dropdown-item <?php echo navActive('orientation', $currentPage); ?>">Questionnaire d'orientation</a>
                </div>
            </div>
            <a href="ressources.php" class="nav-item nav-link <?php echo navActive('ressources', $currentPage); ?>">Ressources</a>
            <a href="contact.php" class="nav-item nav-link <?php echo navActive('contact', $currentPage); ?>">Contact</a>
        </div>
        <a href="signin.php" class="btn btn-outline-fadfed me-2 d-none d-lg-block">Connexion</a>
        <a href="signup.php" class="btn btn-fadfed py-3 px-lg-4 me-lg-4 d-none d-lg-block">Inscription</a>
    </div>
</nav>
<!-- Navbar End -->
