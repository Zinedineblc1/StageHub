<?php
session_start();
// Met fin à la session utilisateur et efface toutes les données de connexion
session_destroy();
header('Location: /StageHub/home');  // Redirige vers la page d'accueil principale
exit;
?>