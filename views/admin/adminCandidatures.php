<?php
require_once './config/database.php';

// Liste les candidatures avec pagination
function listerCandidatures($page, $parPage) {
    $pdo = Database::getConnection();
    $offset = ($page - 1) * $parPage;
    $stmt = $pdo->prepare("
        SELECT 
            candidatures.id_candidature,
            candidatures.date_candidature,
            candidatures.chemin_cv,
            candidatures.chemin_lettre,
            candidatures.id_stage,
            candidatures.id_etudiant,
            entreprises.nom_enseigne AS nom_entreprise,
            etudiants.prenom AS prenom_etudiant,
            etudiants.nom AS nom_etudiant
        FROM candidatures
        INNER JOIN internships ON candidatures.id_stage = internships.ID_Internship
        INNER JOIN entreprises ON internships.id_entreprise = entreprises.id_entreprise
        INNER JOIN etudiants ON candidatures.id_etudiant = etudiants.id_etudiant
        LIMIT :limit OFFSET :offset
    ");
    $stmt->bindValue(':limit', $parPage, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Calcule le nombre total de candidatures
function compterCandidatures() {
    $pdo = Database::getConnection();
    $stmt = $pdo->query("SELECT COUNT(*) FROM candidatures");
    return $stmt->fetchColumn();
}

// Supprime une candidature
function retirerCandidature($id) {
    $pdo = Database::getConnection();
    $stmt = $pdo->prepare("DELETE FROM candidatures WHERE id_candidature = ?");
    $stmt->execute([$id]);
}

// Gère les requêtes AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'delete':
                if (isset($_POST['id'])) {
                    retirerCandidature($_POST['id']);
                    echo json_encode(['success' => true]);
                }
                break;
        }
    }
    exit;
}

// Pagination
$parPage = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$totalCandidatures = compterCandidatures();
$totalPages = ceil($totalCandidatures / $parPage);
$candidatures = listerCandidatures($page, $parPage);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Espace d'administration - Candidatures</title>
    <link rel="stylesheet" href="../public/assets/styles.css">
    <script>
        async function retirerCandidature(id) {
            if (confirm("Confirmez-vous la suppression de cette candidature ?")) {
                const reponse = await fetch('', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: new URLSearchParams({ action: 'delete', id: id })
                });
                const resultat = await reponse.json();
                if (resultat.success) {
                    location.reload();
                }
            }
        }
    </script>
</head>
<body class="page-body">
    <header class="navbar">
        <h1 class="nav-header">Espace d'administration</h1>
        <nav class="nav-container">
            <ul class="nav-list">
                <li class="nav-item"><a href="adminEntreprises.php" class="nav-link">Sociétés</a></li>
                <li class="nav-item"><a href="adminApplicants.php" class="nav-link">Candidats</a></li>
                <li class="nav-item"><a href="adminInternships.php" class="nav-link">Stages</a></li>
                <li class="nav-item"><a href="adminMentors.php" class="nav-link">Tuteurs</a></li>
                <li class="nav-item"><a class="nav-on nav-link">Candidatures</a></li>
                <li class="nav-item"><a href="../dashboard" class="nav-link">Tableau de bord</a></li>
            </ul>
        </nav>
    </header>
    <main class="main-content">
        <section class="management-section">
            <table class="management-table">
                <thead>
                    <tr class="table-header-row">
                        <th class="table-header">Entreprise</th>
                        <th class="table-header">Étudiant</th>
                        <th class="table-header">Date de candidature</th>
                        <th class="table-header">CV</th>
                        <th class="table-header">Lettre de motivation</th>
                        <th class="table-header">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($candidatures as $candidature): ?>
                    <tr class="table-row">
                        <td class="table-data"><?= htmlspecialchars($candidature['nom_entreprise']) ?></td>
                        <td class="table-data"><?= htmlspecialchars($candidature['prenom_etudiant'] . ' ' . $candidature['nom_etudiant']) ?></td>
                        <td class="table-data"><?= htmlspecialchars($candidature['date_candidature']) ?></td>
                        <td class="table-data"><?php if ($candidature['chemin_cv']): ?><a href="<?= htmlspecialchars($candidature['chemin_cv']) ?>" target="_blank">Voir CV</a><?php else: ?>—<?php endif; ?></td>
                        <td class="table-data"><?php if ($candidature['chemin_lettre']): ?><a href="<?= htmlspecialchars($candidature['chemin_lettre']) ?>" target="_blank">Voir lettre</a><?php else: ?>—<?php endif; ?></td>
                        <td class="table-data">
                            <button class="delete-button" onclick="retirerCandidature(<?= $candidature['id_candidature'] ?>)">Retirer</button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </section>
        <div class="pagination-container">
            <button class="pagination-button" onclick="location.href='?page=1'" <?= $page == 1 ? 'disabled' : '' ?>>&laquo;</button>
            <button class="pagination-button" onclick="location.href='?page=<?= $page - 1 ?>'" <?= $page == 1 ? 'disabled' : '' ?>>Précédent</button>
            <span class="pagination-info">Page <?= $page ?> sur <?= $totalPages ?></span>
            <button class="pagination-button" onclick="location.href='?page=<?= $page + 1 ?>'" <?= $page == $totalPages ? 'disabled' : '' ?>>Suivant</button>
            <button class="pagination-button" onclick="location.href='?page=<?= $totalPages ?>'" <?= $page == $totalPages ? 'disabled' : '' ?>>&raquo;</button>
        </div>
    </main>
</body>
</html> 