<!DOCTYPE html>
<html lang="fr">
<?php include './views/partials/mainNavbar.php'; ?>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StageHub - Espace principal</title>
    <link rel="stylesheet" href="./public/assets/styles.css">
</head>
<body>

<main>
    <section class="description">
        <h2>Accueil sur StageHub</h2>
        <p>Découvrez des stages uniques, explorez nos partenaires professionnels et organisez vos coups de cœur en toute tranquillité.</p>
    </section>

    <?php
    // Affichage des chiffres clés du tableau de bord
    require_once __DIR__ . '/../../controllers/DashboardController.php';
    $stats = $model->getStats();
    ?>

    <section class="stats">
        <div><h3><?= $stats['internships'] ?></h3><p>Stages à pourvoir</p></div>
        <div><h3><?= $stats['entreprises'] ?></h3><p>Partenaires institutionnels</p></div>
        <div><h3><?= $stats['applicants'] ?></h3><p>Étudiants connectés</p></div>
    </section>

    <section class="images-container">
        <a href="internships"><img src="placeholder.png" alt="Stages"><p>Voir les stages</p></a>
        <a href="entreprises"><img src="placeholder.png" alt="Sociétés"><p>Explorer les sociétés</p></a>
        <a href="favoris"><img src="placeholder.png" alt="Favoris"><p>Mes favoris</p></a>
    </section>
</main>

<footer class="site-footer">
    <div class="footer-legal">
        <p>&copy; 2025 StageHub - Tous droits réservés</p>
        <p>
            <strong>Informations légales :</strong><br>
            Raison sociale : Solutions StageHub<br>
            Numéro d'immatriculation : 987 654 321 00021<br>
            Forme juridique : Société par actions simplifiée<br>
            Capital : 12 000 €<br>
            Adresse du siège : 42 avenue de l'Innovation
        </p>
        <p>
            <strong>Hébergement :</strong><br>
            Fournisseur : Hébergement StageHub<br>
            Localisation : 99 avenue du Nuage<br>
            Téléphone : 01 23 45 67 89
        </p>
    </div>
    <div class="footer-links">
        <p>
            <a href="#" target="_blank" rel="noopener noreferrer">Informations légales</a> | 
            <a href="#" target="_blank" rel="noopener noreferrer">Politique de confidentialité</a>
        </p>
    </div>
</footer>

</body>
</html>