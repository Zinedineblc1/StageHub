<?php
require_once './config/database.php';

// Liste les tuteurs avec pagination
function listerTuteurs($page, $parPage) {
    $offset = ($page - 1) * $parPage;
    $pdo = Database::getConnection();
    $stmt = $pdo->prepare("
        SELECT id_tuteur, nom, prenom, courriel 
        FROM tuteurs 
        WHERE role = 'mentor' 
        LIMIT :limit OFFSET :offset
    ");
    $stmt->bindValue(':limit', $parPage, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Calcule le nombre total de tuteurs
function compterTuteurs() {
    $pdo = Database::getConnection();
    $stmt = $pdo->query("SELECT COUNT(*) FROM tuteurs WHERE role = 'mentor'");
    return $stmt->fetchColumn();
}

// Ajoute un tuteur vide
function creerTuteur() {
    $pdo = Database::getConnection();
    $stmt = $pdo->prepare("
        INSERT INTO tuteurs (nom, prenom, courriel, motdepasse, role) 
        VALUES ('', '', '', MD5('motdepasse_par_defaut'), 'mentor')
    ");
    $stmt->execute();
    return $pdo->lastInsertId();
}

// Supprime un tuteur
function retirerTuteur($id) {
    $pdo = Database::getConnection();
    $stmt = $pdo->prepare("DELETE FROM tuteurs WHERE id_tuteur = ?");
    $stmt->execute([$id]);
}

// Met à jour un tuteur
function modifierTuteur($id, $nom, $prenom, $courriel, $motdepasse = null) {
    $pdo = Database::getConnection();
    if ($motdepasse) {
        $motdepasseHash = md5($motdepasse);
        $stmt = $pdo->prepare("
            UPDATE tuteurs 
            SET nom = ?, prenom = ?, courriel = ?, motdepasse = ? 
            WHERE id_tuteur = ?
        ");
        $stmt->execute([$nom, $prenom, $courriel, $motdepasseHash, $id]);
    } else {
        $stmt = $pdo->prepare("
            UPDATE tuteurs 
            SET nom = ?, prenom = ?, courriel = ? 
            WHERE id_tuteur = ?
        ");
        $stmt->execute([$nom, $prenom, $courriel, $id]);
    }
}

// Gère les requêtes AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                $id = creerTuteur();
                echo json_encode(['success' => true, 'id' => $id]);
                break;
            case 'delete':
                if (isset($_POST['id'])) {
                    retirerTuteur($_POST['id']);
                    echo json_encode(['success' => true]);
                }
                break;
            case 'update':
                if (isset($_POST['id'], $_POST['nom'], $_POST['prenom'], $_POST['courriel'])) {
                    $motdepasse = $_POST['motdepasse'] ?? null;
                    modifierTuteur($_POST['id'], $_POST['nom'], $_POST['prenom'], $_POST['courriel'], $motdepasse);
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
$totalTuteurs = compterTuteurs();
$totalPages = ceil($totalTuteurs / $parPage);
$tuteurs = listerTuteurs($page, $parPage);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Espace d'administration - Tuteurs</title>
    <link rel="stylesheet" href="../public/assets/styles.css">
    <script>
        async function creerTuteur() {
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

        async function retirerTuteur(id) {
            if (confirm("Confirmez-vous la suppression de ce tuteur ?")) {
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
                if (index < 4) {
                    const texteActuel = cellule.innerText;
                    if (index === 3) {
                        cellule.innerHTML = `<input type='password' placeholder='Nouveau code secret'>`;
                    } else {
                        cellule.innerHTML = `<input type='text' value='${texteActuel}'>`;
                    }
                }
            });
            bouton.innerText = 'Valider';
            bouton.setAttribute('onclick', `sauvegarderLigne(this, ${id})`);
        }

        async function sauvegarderLigne(bouton, id) {
            const ligne = bouton.closest('tr');
            const cellules = ligne.querySelectorAll('.table-data');
            const nom = cellules[0].querySelector('input').value;
            const prenom = cellules[1].querySelector('input').value;
            const courriel = cellules[2].querySelector('input').value;
            const motdepasse = cellules[3].querySelector('input').value;

            const reponse = await fetch('', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({ action: 'update', id: id, nom: nom, prenom: prenom, courriel: courriel, motdepasse: motdepasse })
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
                <li class="nav-item"><a href="adminInternships.php" class="nav-link">Stages</a></li>
                <li class="nav-item"><a href="adminCandidatures.php" class="nav-link">Candidatures</a></li>
                <li class="nav-item"><a class="nav-on nav-link">Tuteurs</a></li>
                <li class="nav-item"><a href="../dashboard" class="nav-link">Tableau de bord</a></li>
            </ul>
        </nav>
    </header>

    <main class="main-content">
        <div class="top-bar">
            <button class="pagination-button" onclick="creerTuteur()">Nouveau tuteur</button>
        </div>
        <section class="management-section">
            <table class="management-table">
                <thead>
                    <tr class="table-header-row">
                        <th class="table-header">Nom</th>
                        <th class="table-header">Prénom</th>
                        <th class="table-header">Courriel</th>
                        <th class="table-header">Code secret</th>
                        <th class="table-header">Modifier</th>
                        <th class="table-header">Voir</th>
                        <th class="table-header">Retirer</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($tuteurs as $tuteur): ?>
                    <tr class="table-row">
                        <td class="table-data"><?= htmlspecialchars($tuteur['nom']) ?></td>
                        <td class="table-data"><?= htmlspecialchars($tuteur['prenom']) ?></td>
                        <td class="table-data"><?= htmlspecialchars($tuteur['courriel']) ?></td>
                        <td class="table-data">********</td>
                        <td class="table-data">
                            <button class="edit-button" onclick="editerLigne(this, <?= $tuteur['id_tuteur'] ?>)">Modifier</button>
                        </td>
                        <td class="table-data"><button class="view-button">Aperçu</button></td>
                        <td class="table-data">
                            <button class="delete-button" onclick="retirerTuteur(<?= $tuteur['id_tuteur'] ?>)">Retirer</button>
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
