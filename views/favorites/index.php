<?php

require_once './config/database.php';
require_once './views/partials/mainNavbar.php'; // Barre de navigation principale avec gestion de session

// Liste les démarches d'un étudiant avec pagination
function listerDossiersParEtudiant($idEtudiant, $page, $parPage) {
    $pdo = Database::getConnection();
    $offset = ($page - 1) * $parPage;
    $stmt = $pdo->prepare("
        SELECT 
            candidatures.id_candidature,
            candidatures.date_candidature,
            candidatures.chemin_cv,
            candidatures.chemin_lettre,
            internships.ID_Internship,
            internships.presentation,
            internships.Stipend,
            internships.Start_Date,
            internships.End_Date,
            entreprises.nom_enseigne AS Nom_Entreprise
        FROM candidatures
        INNER JOIN internships ON candidatures.id_stage = internships.ID_Internship
        INNER JOIN entreprises ON internships.id_entreprise = entreprises.id_entreprise
        WHERE candidatures.id_etudiant = :idEtudiant
        LIMIT :limit OFFSET :offset
    ");
    $stmt->bindValue(':idEtudiant', $idEtudiant, PDO::PARAM_INT);
    $stmt->bindValue(':limit', $parPage, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Calcule le nombre total de démarches pour un étudiant
function compterDossiersParEtudiant($idEtudiant) {
    $pdo = Database::getConnection();
    $stmt = $pdo->prepare("
        SELECT COUNT(*) AS total 
        FROM candidatures 
        WHERE id_etudiant = :idEtudiant
    ");
    $stmt->bindValue(':idEtudiant', $idEtudiant, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchColumn();
}

// Supprime un dossier
function retirerDossier($idCandidature) {
    $pdo = Database::getConnection();
    $stmt = $pdo->prepare("DELETE FROM candidatures WHERE id_candidature = :idCandidature");
    $stmt->bindValue(':idCandidature', $idCandidature, PDO::PARAM_INT);
    $stmt->execute();
}

// Met à jour les fichiers d'un dossier
function mettreAJourFichiersDossier($idCandidature, $cvPath, $lettrePath = null) {
    $pdo = Database::getConnection();
    $stmt = $pdo->prepare("
        UPDATE candidatures
        SET chemin_cv = :cvPath, chemin_lettre = :coverLetterPath
        WHERE id_candidature = :idCandidature
    ");
    $stmt->bindValue(':cvPath', $cvPath, PDO::PARAM_STR);
    $stmt->bindValue(':coverLetterPath', $lettrePath, PDO::PARAM_STR);
    $stmt->bindValue(':idCandidature', $idCandidature, PDO::PARAM_INT);
    $stmt->execute();
}

// Gère les actions de suppression et de mise à jour
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'delete') {
        $idCandidature = $_POST['applicationId'];
        retirerDossier($idCandidature);
        echo "<script>alert('Dossier retiré avec succès !');</script>";
    } elseif (isset($_POST['action']) && $_POST['action'] === 'update') {
        $idCandidature = $_POST['applicationId'];
        $cvPath = null;
        $lettrePath = null;

        // Gestion du dépôt de CV
        if (isset($_FILES['cv']) && $_FILES['cv']['error'] === UPLOAD_ERR_OK) {
            $cvName = uniqid() . '_' . basename($_FILES['cv']['name']);
            $cvPath = './Dossier/CV/' . $cvName;
            move_uploaded_file($_FILES['cv']['tmp_name'], $cvPath);
        }

        // Gestion du dépôt de lettre de motivation (optionnel)
        if (isset($_FILES['cover_letter']) && $_FILES['cover_letter']['error'] === UPLOAD_ERR_OK) {
            $lettreName = uniqid() . '_' . basename($_FILES['cover_letter']['name']);
            $lettrePath = './Dossier/Lettre/' . $lettreName;
            move_uploaded_file($_FILES['cover_letter']['tmp_name'], $lettrePath);
        }

        mettreAJourFichiersDossier($idCandidature, $cvPath, $lettrePath);
        echo "<script>alert('Documents mis à jour avec succès !');</script>";
    }
}

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$parPage = 5;
$totalItems = compterDossiersParEtudiant($_SESSION['student']['id']);
$totalPages = ceil($totalItems / $parPage);

// Récupère les dossiers pour la page courante
$dossiers = listerDossiersParEtudiant($_SESSION['student']['id'], $page, $parPage);

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StageHub - Mes dossiers</title>
    <link rel="stylesheet" href="./public/assets/styles.css">
</head>
<body>
<main>
    <h2 class="nav-header">Mes suivis de stage</h2>
    <div style="overflow-x: auto;">
        <table class="management-table">
            <thead>
                <tr>
                    <th>Entreprise</th>
                    <th>Intitulé</th>
                    <th>Présentation</th>
                    <th>Indemnité (€)</th>
                    <th>Début</th>
                    <th>Fin</th>
                    <th>Date de dépôt</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($dossiers)): ?>
                    <tr>
                        <td colspan="8" style="text-align: center;">Aucun dossier enregistré pour l'instant.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($dossiers as $item): ?>
                        <tr>
                            <td><?= htmlspecialchars($item['Nom_Entreprise']); ?></td>
                            <td><?= htmlspecialchars($item['presentation']); ?></td>
                            <td><?= htmlspecialchars($item['Stipend']); ?></td>
                            <td><?= htmlspecialchars($item['Start_Date']); ?></td>
                            <td><?= htmlspecialchars($item['End_Date']); ?></td>
                            <td><?= htmlspecialchars($item['date_candidature']); ?></td>
                            <td>
                                <!-- Formulaire pour retirer un dossier -->
                                <form method="POST" style="display: inline-block;">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="applicationId" value="<?= $item['id_candidature']; ?>">
                                    <button class="btn-primary" type="submit">Retirer</button>
                                </form>
                                <!-- Formulaire pour mettre à jour les fichiers -->
                                <form method="POST" enctype="multipart/form-data" style="display: inline-block;">
                                    <input type="hidden" name="action" value="update">
                                    <input type="hidden" name="applicationId" value="<?= $item['id_candidature']; ?>">
                                    <label>CV :
                                        <input type="file" name="cv" accept="application/pdf" required>
                                    </label>
                                    <label>Lettre de motivation :
                                        <input type="file" name="cover_letter" accept="application/pdf">
                                    </label>
                                    <button class="btn-primary" type="submit">Actualiser</button>
                                </form>
                            </td>
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
