<?php
require_once './config/database.php';
require_once './views/partials/mainNavbar.php'; // Barre de navigation principale avec gestion de session

// Liste les stages avec pagination
function listerStages($page, $parPage) {
    $pdo = Database::getConnection();
    $offset = ($page - 1) * $parPage;
    $stmt = $pdo->prepare("
        SELECT 
            internships.ID_Internship,
            internships.Title,
            internships.presentation,
            internships.Stipend,
            internships.Start_Date,
            internships.End_Date,
            entreprises.nom_enseigne AS Nom_Entreprise
        FROM internships
        INNER JOIN entreprises ON internships.id_entreprise = entreprises.id_entreprise
        LIMIT :limit OFFSET :offset
    ");
    $stmt->bindValue(':limit', $parPage, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Calcule le nombre total de stages
function compterStages() {
    $pdo = Database::getConnection();
    $stmt = $pdo->query("SELECT COUNT(*) AS total FROM internships");
    return $stmt->fetchColumn();
}

// Enregistre une nouvelle candidature
function enregistrerCandidature($idStage, $idEtudiant, $cvPath, $lettrePath = null) {
    $pdo = Database::getConnection();
    $stmt = $pdo->prepare("
        INSERT INTO candidatures (date_candidature, chemin_cv, chemin_lettre, id_stage, id_etudiant)
        VALUES (CURDATE(), ?, ?, ?, ?)
    ");
    $stmt->execute([$cvPath, $lettrePath, $idStage, $idEtudiant]);
}

// S'assure que les dossiers d'upload existent
function creerDossiers() {
    $cvDir = './Dossier/CV';
    $lettreDir = './Dossier/Lettre';
    if (!is_dir($cvDir)) {
        mkdir($cvDir, 0777, true);
    }
    if (!is_dir($lettreDir)) {
        mkdir($lettreDir, 0777, true);
    }
}
creerDossiers();

// Gère la soumission du formulaire de candidature
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'apply') {
    $idStage = $_POST['internshipId'];
    $idEtudiant = $_SESSION['student']['id'];
    $cvPath = null;
    $lettrePath = null;

    // Gestion du dépôt de CV
    if (isset($_FILES['cv']) && $_FILES['cv']['error'] === UPLOAD_ERR_OK) {
        $cvName = uniqid() . '_' . basename($_FILES['cv']['name']);
        $cvPath = './Dossier/CV/' . $cvName;
        move_uploaded_file($_FILES['cv']['tmp_name'], $cvPath);
    } else {
        echo "<script>alert('Le CV est requis !');</script>";
        exit;
    }

    // Gestion du dépôt de lettre de motivation (optionnel)
    if (isset($_FILES['cover_letter']) && $_FILES['cover_letter']['error'] === UPLOAD_ERR_OK) {
        $lettreName = uniqid() . '_' . basename($_FILES['cover_letter']['name']);
        $lettrePath = './Dossier/Lettre/' . $lettreName;
        move_uploaded_file($_FILES['cover_letter']['tmp_name'], $lettrePath);
    }

    // Enregistre la candidature
    enregistrerCandidature($idStage, $idEtudiant, $cvPath, $lettrePath);
    echo "<script>alert('Votre dossier a été transmis avec succès !');</script>";
}

// Logique de pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$parPage = 5;
$totalStages = compterStages();
$totalPages = ceil($totalStages / $parPage);

// Récupère les stages pour la page courante
$stages = listerStages($page, $parPage);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StageHub - Stages à découvrir</title>
    <link rel="stylesheet" href="./public/assets/styles.css">
</head>
<body>
<main>
    <h2 class="nav-header">Parcourez les stages proposés</h2>
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
                    <?php if (isset($studentRole) && $studentRole === 'student'): ?>
                        <th>Déposer un dossier</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($stages as $stage): ?>
                    <tr>
                        <td><?= htmlspecialchars($stage['Nom_Entreprise']); ?></td>
                        <td><?= htmlspecialchars($stage['Title']); ?></td>
                        <td><?= htmlspecialchars($stage['presentation']); ?></td>
                        <td><?= htmlspecialchars($stage['Stipend']); ?></td>
                        <td><?= htmlspecialchars($stage['Start_Date']); ?></td>
                        <td><?= htmlspecialchars($stage['End_Date']); ?></td>
                        <?php if (isset($studentRole) && $studentRole === 'student'): ?>
                            <td>
                                <form method="POST" enctype="multipart/form-data">
                                    <input type="hidden" name="action" value="apply">
                                    <input type="hidden" name="internshipId" value="<?= $stage['ID_Internship']; ?>">
                                    <label>CV (PDF obligatoire) :
                                        <input type="file" name="cv" accept="application/pdf" required>
                                    </label>
                                    <label>Lettre de motivation (optionnelle) :
                                        <input type="file" name="cover_letter" accept="application/pdf">
                                    </label>
                                    <button class="btn-primary" type="submit">Transmettre</button>
                                </form>
                            </td>
                        <?php else: ?>
                            <td>Non accessible</td>
                        <?php endif; ?>
                    </tr>
                <?php endforeach; ?>
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
