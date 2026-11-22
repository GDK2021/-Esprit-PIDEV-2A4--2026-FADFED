<?php
// TEMPORAIRE : simule une connexion admin pour tester ajout / liste / sup.
// À SUPPRIMER dès que ta vraie page de login (branche "authenti") est intégrée.
session_start();
$_SESSION['role']    = 'admin';
$_SESSION['id_user'] = 1; // mets ici l'ID d'un admin existant
echo "Session admin active. <a href='ajout.html'>Ajouter</a> | <a href='liste.html'>Liste</a>";
