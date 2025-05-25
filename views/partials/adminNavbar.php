<link rel="stylesheet" href="./public/assets/styles.css">
<?php
// Démarre la session si elle n'est pas déjà active
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Vérifie si l'utilisateur est connecté
$estConnecte = isset($_SESSION['student']);
$prenomUtilisateur = $estConnecte ? htmlspecialchars($_SESSION['student']['prenom']) : null;
$roleUtilisateur = $estConnecte ? htmlspecialchars($_SESSION['student']['role']) : null;

// Récupère le nom de la page actuelle
$pageActuelle = basename($_SERVER['PHP_SELF']);
?>

<header>
    <h1>StageHub</h1>
    <nav>
        <ul>
            <li>
                <?php if ($pageActuelle === 'home'): ?>
                    <a class="a1">Page d'accueil</a>
                <?php else: ?>
                    <a href="../home">Page d'accueil</a>
                <?php endif; ?>
            </li>
            <li>
                <?php if ($pageActuelle === 'internships'): ?>
                    <a class="a1">Stages</a>
                <?php else: ?>
                    <a href="../internships">Stages</a>
                <?php endif; ?>
            </li>
            <li>
                <?php if ($pageActuelle === 'entreprises'): ?>
                    <a class="a1">Sociétés</a>
                <?php else: ?>
                    <a href="../entreprises">Sociétés</a>
                <?php endif; ?>
            </li>
            <li>
                <?php if ($pageActuelle === 'favoris'): ?>
                    <a class="a1">Favoris</a>
                <?php else: ?>
                    <a href="../favoris">Favoris</a>
                <?php endif; ?>
            </li>
            <li class="nav-item"><a href="adminCandidatures.php" class="nav-link">Candidatures</a></li>
        </ul>
        <div id="user-buttons">
            <?php if ($estConnecte): ?>
                <button class="big-button student-name" onclick="toggleStudentMenu()">Salut, <?= $prenomUtilisateur ?></button>
                <div id="student-menu" class="hidden">
                    <?php if ($roleUtilisateur === 'admin'): ?>
                        <a href="./admin/applicants">Gestion des candidats</a>
                        <a href="./admin/mentors">Gestion des tuteurs</a>
                        <a href="./admin/entreprises">Gestion des sociétés</a>
                        <a href="./admin/internships">Gestion des stages</a>
                    <?php elseif ($roleUtilisateur === 'mentor'): ?>
                        <a href="./admin/applicants">Gestion des candidats</a>
                        <a href="./admin/entreprises">Gestion des sociétés</a>
                        <a href="./admin/internships">Gestion des stages</a>
                    <?php endif; ?>
                    <a href="views/students/logout.php" class="logout-link">Déconnexion</a>
                </div>
            <?php else: ?>
                <button id="loginButton" class="big-button" onclick="toggleElement('loginForm')">S'identifier</button>
            <?php endif; ?>
        </div>
    </nav>

    <!-- Formulaire d'identification -->
    <div id="loginForm" class="hidden">
        <form action="/StageHub/login" method="POST">
            <input type="email" name="email" placeholder="Adresse électronique" required>
            <input type="password" name="password" placeholder="Code secret" required>
            <button type="submit">S'authentifier</button>
        </form>
        <div id="errorMessage" style="display: none;">Identifiants incorrects, veuillez réessayer.</div>
    </div>
</header>

<script>
    function toggleStudentMenu() {
        const studentMenu = document.getElementById('student-menu');
        studentMenu.classList.toggle('hidden');
    }

    function toggleElement(id) {
        document.getElementById(id).classList.toggle("active");
    }
</script>
