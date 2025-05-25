<?php
require_once './config/database.php';

// Liste tous les stages avec leurs informations
function listerStages($page, $parPage) {
    $pdo = Database::getConnection();
    $offset = ($page - 1) * $parPage;
    $stmt = $pdo->prepare("
        SELECT 
            internships.ID_Internship,
            internships.Title,
            internships.Description,
            internships.Stipend,
            internships.Start_Date,
            internships.End_Date,
            entreprises.nom_enseigne AS Nom_Entreprise,
            (SELECT COUNT(*) FROM applications WHERE applications.ID_Internship = internships.ID_Internship) AS Application_Count
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

// Récupère l'identifiant d'une entreprise par son nom
function obtenirIdEntrepriseParNom($nomEntreprise) {
    $pdo = Database::getConnection();
    $stmt = $pdo->prepare("SELECT id_entreprise FROM entreprises WHERE nom_enseigne = ?");
    $stmt->execute([$nomEntreprise]);
    return $stmt->fetchColumn();
}

// Ajoute un nouveau stage
function creerStage($nomEntreprise) {
    $idEntreprise = obtenirIdEntrepriseParNom($nomEntreprise);
    if (!$idEntreprise) {
        return false;
    }
    $pdo = Database::getConnection();
    $stmt = $pdo->prepare("
        INSERT INTO internships (Title, Start_Date, End_Date, Description, Stipend, id_entreprise) 
        VALUES ('Nouveau stage', CURDATE(), CURDATE(), '', 0, ?)
    ");
    $stmt->execute([$idEntreprise]);
    return $pdo->lastInsertId();
}

// Supprime un stage
function retirerStage($idStage) {
    $pdo = Database::getConnection();
    $stmt = $pdo->prepare("DELETE FROM internships WHERE ID_Internship = ?");
    $stmt->execute([$idStage]);
}

// Met à jour un stage
function modifierStage($idStage, $titre, $description, $indemnite, $dateDebut, $dateFin) {
    $pdo = Database::getConnection();
    $stmt = $pdo->prepare("
        UPDATE internships 
        SET Title = ?, Description = ?, Stipend = ?, Start_Date = ?, End_Date = ?
        WHERE ID_Internship = ?
    ");
    $stmt->execute([$titre, $description, $indemnite, $dateDebut, $dateFin, $idStage]);
}

// Gère les requêtes AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                if (isset($_POST['nom_enseigne'])) {
                    $id = creerStage($_POST['nom_enseigne']);
                    if ($id) {
                        echo json_encode(['success' => true, 'id' => $id]);
                    } else {
                        echo json_encode(['success' => false, 'message' => "Entreprise introuvable"]);
                    }
                }
                break;
            case 'delete':
                if (isset($_POST['id'])) {
                    retirerStage($_POST['id']);
                    echo json_encode(['success' => true]);
                }
                break;
            case 'update':
                if (isset($_POST['id'], $_POST['title'], $_POST['description'], $_POST['stipend'], $_POST['startDate'], $_POST['endDate'])) {
                    modifierStage($_POST['id'], $_POST['title'], $_POST['description'], $_POST['stipend'], $_POST['startDate'], $_POST['endDate']);
                    echo json_encode(['success' => true]);
                }
                break;
        }
    }
    exit;
}

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$parPage = 10;
$totalStages = compterStages();
$totalPages = ceil($totalStages / $parPage);
$stages = listerStages($page, $parPage);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Espace d'administration - Stages</title>
    <link rel="stylesheet" href="../public/assets/styles.css">
    <script>
        async function creerStage() {
            const nomEntreprise = prompt("Veuillez saisir le nom de l'entreprise pour ce stage :");
            if (nomEntreprise) {
                const reponse = await fetch('', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: new URLSearchParams({ action: 'add', nom_enseigne: nomEntreprise })
                });
                const resultat = await reponse.json();
                if (resultat.success) {
                    location.reload();
                } else {
                    alert(resultat.message);
                }
            }
        }

        async function retirerStage(id) {
            if (confirm("Confirmez-vous la suppression de ce stage ?")) {
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
                if (index > 0 && index < cellules.length - 3) {
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
            const titre = cellules[1].querySelector('input').value;
            const description = cellules[2].querySelector('input').value;
            const indemnite = cellules[3].querySelector('input').value;
            const dateDebut = cellules[4].querySelector('input').value;
            const dateFin = cellules[5].querySelector('input').value;

            const reponse = await fetch('', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({ action: 'update', id: id, title: titre, description: description, stipend: indemnite, startDate: dateDebut, endDate: dateFin })
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
                <li class="nav-item"><a href="adminEntreprises.php" class="nav-link">Sociétés</a></li>
                <li class="nav-item"><a href="adminApplicants.php" class="nav-link">Candidats</a></li>
                <li class="nav-item"><a href="adminCandidatures.php" class="nav-link">Candidatures</a></li>
                <li class="nav-item"><a href="adminMentors.php" class="nav-link">Tuteurs</a></li>
                <li class="nav-item"><a href="../dashboard" class="nav-link">Tableau de bord</a></li>
            </ul>
        </nav>
    </header>

    <main class="main-content">
        <div class="top-bar">
            <button class="pagination-button" onclick="creerStage()">Nouveau stage</button>
        </div>
        <section class="management-section">
            <table class="management-table">
                <thead>
                    <tr class="table-header-row">
                        <th class="table-header">Entreprise</th>
                        <th class="table-header">Intitulé</th>
                        <th class="table-header">Description</th>
                        <th class="table-header">Indemnité</th>
                        <th class="table-header">Début</th>
                        <th class="table-header">Fin</th>
                        <th class="table-header">Candidatures</th>
                        <th class="table-header">Modifier</th>
                        <th class="table-header">Voir</th>
                        <th class="table-header">Retirer</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($stages as $stage): ?>
                    <tr class="table-row">
                        <td class="table-data"><?= htmlspecialchars($stage['Nom_Entreprise']) ?></td>
                        <td class="table-data"><?= htmlspecialchars($stage['Title']) ?></td>
                        <td class="table-data"><?= htmlspecialchars($stage['Description']) ?></td>
                        <td class="table-data"><?= htmlspecialchars($stage['Stipend']) ?></td>
                        <td class="table-data"><?= htmlspecialchars($stage['Start_Date']) ?></td>
                        <td class="table-data"><?= htmlspecialchars($stage['End_Date']) ?></td>
                        <td class="table-data"><?= htmlspecialchars($stage['Application_Count']) ?></td>
                        <td class="table-data">
                            <button class="edit-button" onclick="editerLigne(this, <?= $stage['ID_Internship'] ?>)">Modifier</button>
                        </td>
                        <td class="table-data"><button class="view-button">Aperçu</button></td>
                        <td class="table-data">
                            <button class="delete-button" onclick="retirerStage(<?= $stage['ID_Internship'] ?>)">Retirer</button>
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
