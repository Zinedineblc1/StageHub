<?php
require_once './config/database.php';

// Ce fichier gère la gestion des sociétés (entreprises) pour l'administration

// Liste toutes les entreprises avec leurs détails
function listerEntreprises($page, $parPage) {
    $pdo = Database::getConnection();
    $offset = ($page - 1) * $parPage;
    $stmt = $pdo->prepare("
        SELECT * FROM entreprises
        LIMIT :limit OFFSET :offset
    ");
    $stmt->bindValue(':limit', $parPage, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Calcule le nombre global d'entreprises
function compterEntreprises() {
    $pdo = Database::getConnection();
    $stmt = $pdo->query("SELECT COUNT(*) AS total FROM entreprises");
    return $stmt->fetchColumn();
}

// Ajoute une nouvelle entreprise
function creerEntreprise() {
    $pdo = Database::getConnection();
    $stmt = $pdo->prepare("INSERT INTO entreprises (nom_enseigne, presentation, courriel, telephone, evaluation) VALUES ('', '', '', '', 0)");
    $stmt->execute();
    return $pdo->lastInsertId();
}

// Supprime une entreprise
function retirerEntreprise($id) {
    $pdo = Database::getConnection();
    $stmt = $pdo->prepare("DELETE FROM entreprises WHERE id_entreprise = ?");
    $stmt->execute([$id]);
}

// Met à jour une entreprise
function modifierEntreprise($id, $nom_enseigne, $presentation, $courriel, $telephone, $evaluation) {
    $pdo = Database::getConnection();
    $stmt = $pdo->prepare("UPDATE entreprises SET nom_enseigne = ?, presentation = ?, courriel = ?, telephone = ?, evaluation = ? WHERE id_entreprise = ?");
    $stmt->execute([$nom_enseigne, $presentation, $courriel, $telephone, $evaluation, $id]);
}

// Gère les requêtes AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                $id = creerEntreprise();
                echo json_encode(['success' => true, 'id' => $id]);
                break;
            case 'delete':
                if (isset($_POST['id'])) {
                    retirerEntreprise($_POST['id']);
                    echo json_encode(['success' => true]);
                }
                break;
            case 'update':
                if (isset($_POST['id'], $_POST['nom_enseigne'], $_POST['presentation'], $_POST['courriel'], $_POST['telephone'], $_POST['evaluation'])) {
                    modifierEntreprise($_POST['id'], $_POST['nom_enseigne'], $_POST['presentation'], $_POST['courriel'], $_POST['telephone'], $_POST['evaluation']);
                    echo json_encode(['success' => true]);
                }
                break;
        }
    }
    exit;
}

// Pagination
$parPage = 20;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$totalEntreprises = compterEntreprises();
$totalPages = ceil($totalEntreprises / $parPage);
$entreprises = listerEntreprises($page, $parPage);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Espace d'administration - Entreprises</title>
    <link rel="stylesheet" href="../public/assets/styles.css">
    <script>
        async function creerEntreprise() {
            const reponse = await fetch('', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({ action: 'add' })
            });
            const resultat = await reponse.json();
            if (resultat.success) {
                location.reload();
            }
        }

        async function retirerEntreprise(id) {
            if (confirm("Confirmez-vous la suppression de cette société ?")) {
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

        function editerLigne(bouton, id) {
            const ligne = bouton.closest('tr');
            const cellules = ligne.querySelectorAll('.table-data');
            cellules.forEach((cellule, index) => {
                if (index < 5) {
                    const texteActuel = cellule.innerText;
                    cellule.innerHTML = `<input type='text' value='${texteActuel}'>`;
                }
            });
            bouton.innerText = 'Valider';
            bouton.setAttribute('onclick', `sauvegarderLigne(this, ${id})`);
        }

        async function sauvegarderLigne(bouton, id) {
            const ligne = bouton.closest('tr');
            const cellules = ligne.querySelectorAll('.table-data');
            const nom_enseigne = cellules[0].querySelector('input').value;
            const presentation = cellules[1].querySelector('input').value;
            const courriel = cellules[2].querySelector('input').value;
            const telephone = cellules[3].querySelector('input').value;
            const evaluation = cellules[4].querySelector('input').value;

            const reponse = await fetch('', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({ action: 'update', id: id, nom_enseigne: nom_enseigne, presentation: presentation, courriel: courriel, telephone: telephone, evaluation: evaluation })
            });
            const resultat = await reponse.json();
            if (resultat.success) {
                location.reload();
            }
        }
    </script>
</head>
<body class="page-body">
    <header class="navbar">
        <h1 class="nav-header">Espace d'administration</h1>
        <nav class="nav-container">
            <ul class="nav-list">
                <li class="nav-item"><a class="nav-on nav-link">Entreprises</a></li>
                <li class="nav-item"><a href="./adminApplicants.php" class="nav-link">Candidats</a></li>
                <li class="nav-item"><a href="./adminInternships.php" class="nav-link">Stages</a></li>
                <li class="nav-item"><a href="./adminMentors.php" class="nav-link">Tuteurs</a></li>
                <li class="nav-item"><a href="adminCandidatures.php" class="nav-link">Candidatures</a></li>
                <li class="nav-item"><a href="../dashboard" class="nav-link">Tableau de bord</a></li>
            </ul>
        </nav>
    </header>

    <main class="main-content">
        <div class="top-bar">
            <button class="pagination-button" onclick="creerEntreprise()">Nouvelle entreprise</button>
        </div>
        <section class="management-section">
            <table class="management-table">
                <thead>
                    <tr class="table-header-row">
                        <th class="table-header">Nom de la société</th>
                        <th class="table-header">Présentation</th>
                        <th class="table-header">Courriel</th>
                        <th class="table-header">Téléphone</th>
                        <th class="table-header">Évaluation</th>
                        <th class="table-header">Modifier</th>
                        <th class="table-header">Voir</th>
                        <th class="table-header">Retirer</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($entreprises as $entreprise): ?>
                    <tr class="table-row">
                        <td class="table-data"><?= htmlspecialchars($entreprise['nom_enseigne']) ?></td>
                        <td class="table-data"><?= htmlspecialchars($entreprise['presentation']) ?></td>
                        <td class="table-data"><?= htmlspecialchars($entreprise['courriel']) ?></td>
                        <td class="table-data"><?= htmlspecialchars($entreprise['telephone']) ?></td>
                        <td class="table-data"><?= htmlspecialchars($entreprise['evaluation']) ?></td>
                        <td class="table-data">
                            <button class="edit-button" onclick="editerLigne(this, <?= $entreprise['id_entreprise'] ?>)">Modifier</button>
                        </td>
                        <td class="table-data"><button class="view-button">Aperçu</button></td>
                        <td class="table-data">
                            <button class="delete-button" onclick="retirerEntreprise(<?= $entreprise['id_entreprise'] ?>)">Retirer</button>
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
