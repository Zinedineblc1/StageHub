<?php
require_once './config/database.php';
require_once './views/partials/mainNavbar.php'; // Barre de navigation principale
require_once './controllers/EntreprisesController.php';

// Liste les entreprises avec pagination
function listerEntreprises($page, $parPage) {
    $pdo = Database::getConnection();
    $offset = ($page - 1) * $parPage;
    $stmt = $pdo->prepare("
        SELECT 
            nom_enseigne,
            presentation,
            courriel,
            telephone,
            evaluation
        FROM entreprises
        LIMIT :limit OFFSET :offset
    ");
    $stmt->bindValue(':limit', $parPage, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Calcule le nombre total d'entreprises
function compterEntreprises() {
    $pdo = Database::getConnection();
    $stmt = $pdo->query("SELECT COUNT(*) AS total FROM entreprises");
    return $stmt->fetchColumn();
}

// Logique de pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$parPage = 10;
$totalFirms = compterEntreprises();
$totalPages = ceil($totalFirms / $parPage);

// Récupère les entreprises pour la page courante
$entreprises = listerEntreprises($page, $parPage);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StageHub - Annuaire des entreprises</title>
    <link rel="stylesheet" href="./public/assets/styles.css">
</head>
<body>
<main>
    <h2>Annuaire des partenaires professionnels</h2>
    <div style="overflow-x: auto;">
        <table class="management-table">
            <thead>
                <tr>
                    <th>Nom</th>
                    <th>Présentation</th>
                    <th>Courriel</th>
                    <th>Téléphone</th>
                    <th>Appréciation</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($entreprises)): ?>
                    <tr>
                        <td colspan="5" style="text-align: center;">Aucune entreprise n'est référencée pour le moment.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($entreprises as $entreprise): ?>
                        <tr>
                            <td><?= htmlspecialchars($entreprise['nom_enseigne']); ?></td>
                            <td><?= htmlspecialchars($entreprise['presentation']); ?></td>
                            <td><?= htmlspecialchars($entreprise['courriel']); ?></td>
                            <td><?= htmlspecialchars($entreprise['telephone']); ?></td>
                            <td><?= htmlspecialchars($entreprise['evaluation']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <div class="pagination-container">
        <button class="pagination-button" onclick="location.href='?page=1'" <?= $page == 1 ? 'disabled' : '' ?>>&laquo;</button>
        <button class="pagination-button" onclick="location.href='?page=<?= $page - 1 ?>'" <?= $page == 1 ? 'disabled' : '' ?>>Précédent</button>
        <span class="pagination-info">Page <?= $page ?> sur <?= $totalPages ?></span>
        <button class="pagination-button" onclick="location.href='?page=<?= $page + 1 ?>'" <?= $page == $totalPages ? 'disabled' : '' ?>>Suivant</button>
        <button class="pagination-button" onclick="location.href='?page=<?= $totalPages ?>'" <?= $page == $totalPages ? 'disabled' : '' ?>>&raquo;</button>
    </div>
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
