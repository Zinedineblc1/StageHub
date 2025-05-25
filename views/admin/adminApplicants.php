<?php
require_once './config/database.php';

// Compte le nombre total de postulants
function compterPostulants() {
    $pdo = Database::getConnection();
    $stmt = $pdo->query("SELECT COUNT(*) FROM etudiants WHERE role = 'student'");
    return $stmt->fetchColumn();
}

// Récupère les postulants avec pagination
function recupererPostulantsParPage($page, $parPage) {
    $offset = ($page - 1) * $parPage;
    $pdo = Database::getConnection();
    $stmt = $pdo->prepare("SELECT id_etudiant, prenom, nom, courriel FROM etudiants WHERE role = 'student' LIMIT :limit OFFSET :offset");
    $stmt->bindValue(':limit', $parPage, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Ajoute un postulant vide
function ajouterPostulant() {
    $pdo = Database::getConnection();
    $stmt = $pdo->prepare("INSERT INTO etudiants (prenom, nom, courriel, motdepasse, role) VALUES ('', '', '', MD5('motdepasse_par_defaut'), 'student')");
    $stmt->execute();
    return $pdo->lastInsertId();
}

// Supprime un postulant
function supprimerPostulant($id) {
    $pdo = Database::getConnection();
    $stmt = $pdo->prepare("DELETE FROM etudiants WHERE id_etudiant = ?");
    $stmt->execute([$id]);
}

// Met à jour un postulant
function modifierPostulant($id, $prenom, $nom, $courriel, $motdepasse = null) {
    $pdo = Database::getConnection();
    if ($motdepasse) {
        $motdepasseHash = md5($motdepasse);
        $stmt = $pdo->prepare("UPDATE etudiants SET prenom = ?, nom = ?, courriel = ?, motdepasse = ? WHERE id_etudiant = ?");
        $stmt->execute([$prenom, $nom, $courriel, $motdepasseHash, $id]);
    } else {
        $stmt = $pdo->prepare("UPDATE etudiants SET prenom = ?, nom = ?, courriel = ? WHERE id_etudiant = ?");
        $stmt->execute([$prenom, $nom, $courriel, $id]);
    }
}

// Gère les requêtes AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                $id = ajouterPostulant();
                echo json_encode(['success' => true, 'id' => $id]);
                break;
            case 'delete':
                if (isset($_POST['id'])) {
                    supprimerPostulant($_POST['id']);
                    echo json_encode(['success' => true]);
                }
                break;
            case 'update':
                if (isset($_POST['id'], $_POST['prenom'], $_POST['nom'], $_POST['courriel'])) {
                    $motdepasse = $_POST['motdepasse'] ?? null;
                    modifierPostulant($_POST['id'], $_POST['prenom'], $_POST['nom'], $_POST['courriel'], $motdepasse);
                    echo json_encode(['success' => true]);
                }
                break;
        }
    }
    exit;
}

// Pagination
$parPage = 10;
$totalPostulants = compterPostulants();
$totalPages = ceil($totalPostulants / $parPage);
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
if ($page > $totalPages) $page = $totalPages;
$postulants = recupererPostulantsParPage($page, $parPage);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administration - Gestion des postulants</title>
    <link rel="stylesheet" href="../public/assets/styles.css">
    <script>
        async function ajouterPostulant() {
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

        async function supprimerPostulant(id) {
            if (confirm("Voulez-vous vraiment retirer ce postulant ?")) {
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
                if (index < 4) { // Prénom, Nom, Courriel, Mot de passe
                    const texteActuel = cellule.innerText;
                    if (index === 3) {
                        cellule.innerHTML = `<input type='password' placeholder='Nouveau code secret'>`;
                    } else {
                        cellule.innerHTML = `<input type='text' value='${texteActuel}'>`;
                    }
                }
            });
            bouton.innerText = 'Enregistrer';
            bouton.setAttribute('onclick', `sauvegarderLigne(this, ${id})`);
        }

        async function sauvegarderLigne(bouton, id) {
            const ligne = bouton.closest('tr');
            const cellules = ligne.querySelectorAll('.table-data');
            const prenom = cellules[0].querySelector('input').value;
            const nom = cellules[1].querySelector('input').value;
            const courriel = cellules[2].querySelector('input').value;
            const motdepasse = cellules[3].querySelector('input').value;

            const reponse = await fetch('', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({ action: 'update', id: id, prenom: prenom, nom: nom, courriel: courriel, motdepasse: motdepasse })
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
                <li class="nav-item"><a class="nav-on nav-link">Candidats</a></li>
                <li class="nav-item"><a href="adminInternships.php" class="nav-link">Stages</a></li>
                <li class="nav-item"><a href="adminMentors.php" class="nav-link">Tuteurs</a></li>
                <li class="nav-item"><a href="adminCandidatures.php" class="nav-link">Candidatures</a></li>
                <li class="nav-item"><a href="../dashboard" class="nav-link">Tableau de bord</a></li>
            </ul>
        </nav>
    </header>
    <main class="main-content">
        <div class="top-bar">
            <button class="pagination-button" onclick="ajouterPostulant()">Ajouter un candidat</button>
        </div>
        <section class="management-section">
            <table class="management-table">
                <thead>
                    <tr class="table-header-row">
                        <th class="table-header">Prénom</th>
                        <th class="table-header">Nom</th>
                        <th class="table-header">Courriel</th>
                        <th class="table-header">Code secret</th>
                        <th class="table-header">Modifier</th>
                        <th class="table-header">Voir</th>
                        <th class="table-header">Retirer</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($postulants as $postulant): ?>
                    <tr class="table-row">
                        <td class="table-data"><?= htmlspecialchars($postulant['prenom']) ?></td>
                        <td class="table-data"><?= htmlspecialchars($postulant['nom']) ?></td>
                        <td class="table-data"><?= htmlspecialchars($postulant['courriel']) ?></td>
                        <td class="table-data">********</td>
                        <td class="table-data">
                            <button class="edit-button" onclick="editerLigne(this, <?= $postulant['id_etudiant'] ?>)">Modifier</button>
                        </td>
                        <td class="table-data"><button class="view-button">Aperçu</button></td>
                        <td class="table-data">
                            <button class="delete-button" onclick="supprimerPostulant(<?= $postulant['id_etudiant'] ?>)">Retirer</button>
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
