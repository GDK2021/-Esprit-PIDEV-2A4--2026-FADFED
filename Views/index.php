<?php
$pageTitle = 'FADFED - Parce que ta santé mentale compte';
$currentPage = 'index';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <?php require_once 'includes/head.php'; ?>
</head>
<body>
    <?php require_once 'includes/spinner.php'; ?>
    <?php require_once 'includes/navbar.php'; ?>

    <!-- Hero Start -->
    <div class="container-fluid hero-fadfed py-5 mb-5">
        <div class="container py-4">
            <div class="row g-5 align-items-center">
                <div class="col-lg-6">
                    <h1 class="display-5 mb-3">Prends soin de ta santé mentale, à ton rythme</h1>
                    <p class="fs-5 mb-4">FADFED (فضفض) est un espace calme pour t'exprimer, suivre ton humeur, faire grandir ton jardin et être accompagné(e) par des professionnels en Tunisie.</p>
                    <a href="signup.php" class="btn btn-fadfed py-3 px-5 me-2">Commencer</a>
                    <a href="exercices.php" class="btn btn-outline-fadfed py-3 px-5">Voir les exercices</a>
                </div>
                <div class="col-lg-6 text-center">
                    <img class="hero-logo" src="assets/img/logo_FADFED.jpg" alt="FADFED, parce que ta santé mentale compte">
                </div>
            </div>
        </div>
    </div>
    <!-- Hero End -->

    <!-- Modules Start -->
    <div class="container py-5">
        <div class="text-center mb-5">
            <h2>Ce que tu peux faire sur FADFED</h2>
        </div>
        <div class="row g-4">
            <div class="col-lg-3 col-md-6">
                <div class="card-fadfed">
                    <div class="icon-circle bg-lavande"><i class="fa fa-spa"></i></div>
                    <h5>Exercices</h5>
                    <p>Respiration, relaxation, sommeil : des exercices classés par catégorie.</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="card-fadfed">
                    <div class="icon-circle bg-sauge"><i class="fa fa-tree"></i></div>
                    <h5>Suivi et jardin</h5>
                    <p>Note ton humeur chaque jour : après 3 jours, un arbre pousse dans ton jardin.</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="card-fadfed">
                    <div class="icon-circle bg-peche"><i class="fa fa-comments"></i></div>
                    <h5>Communauté</h5>
                    <p>Partage et échange dans un espace bienveillant.</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="card-fadfed">
                    <div class="icon-circle bg-bleu"><i class="fa fa-calendar-check"></i></div>
                    <h5>Rendez-vous</h5>
                    <p>Prends rendez-vous avec un professionnel et retrouve ta fiche.</p>
                </div>
            </div>
        </div>
    </div>
    <!-- Modules End -->

    <!-- Orientation Start -->
    <div class="container py-5">
        <div class="card-fadfed" style="background: var(--gris-brume);">
            <h3 class="mb-3">Tu ne sais pas par où commencer ?</h3>
            <p class="mb-4">Réponds à un court questionnaire pour être orienté(e) vers les ressources adaptées.</p>
            <div><a href="orientation.php" class="btn btn-fadfed py-3 px-5">Faire le questionnaire</a></div>
        </div>
    </div>
    <!-- Orientation End -->

    <?php require_once 'includes/footer.php'; ?>
    <?php require_once 'includes/scripts.php'; ?>
</body>
</html>
