<link rel="stylesheet" href="./public/assets/styles.css">
<?php
// Lance la session si besoin
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Vérifie la connexion de l'utilisateur
$estConnecte = isset($_SESSION['student']);
$prenomUtilisateur = $estConnecte ? htmlspecialchars($_SESSION['student']['prenom']) : null;
$roleUtilisateur = $estConnecte ? htmlspecialchars($_SESSION['student']['role']) : null;

// Détermine la page courante
$pageActuelle = basename($_SERVER['PHP_SELF']);
?>

<header>
    <div class="navbar-container">
        <h1>StageHub</h1>
        <!-- Bouton menu principal -->
        <button id="burger-menu">☰</button>
        <div id="user-buttons">
            <?php if ($estConnecte): ?>
                <button class="big-button student-name" onclick="toggleStudentMenu()">Bonjour, <?= $prenomUtilisateur ?></button>
                <div id="student-menu" class="hidden">
                    <?php if ($roleUtilisateur === 'admin'): ?>
                        <a href="./admin/applicants">Candidats - Administration</a>
                        <a href="./admin/mentors">Tuteurs - Administration</a>
                        <a href="./admin/entreprises">Entreprises - Administration</a>
                        <a href="./admin/internships">Stages - Administration</a>
                    <?php elseif ($roleUtilisateur === 'mentor'): ?>
                        <a href="./admin/applicants">Candidats - Administration</a>
                        <a href="./admin/entreprises">Entreprises - Administration</a>
                        <a href="./admin/internships">Stages - Administration</a>
                    <?php endif; ?>
                    <a href="views/students/logout.php" class="logout-link">Se déconnecter</a>
                </div>
            <?php else: ?>
                <button id="loginButton" class="big-button" onclick="toggleElement('loginForm')">Connexion</button>
            <?php endif; ?>
        </div>
    </div>
    <nav>
        <ul id="nav-links">
            <li>
                <?php if ($pageActuelle === 'home'): ?>
                    <a class="a1">Accueil</a>
                <?php else: ?>
                    <a href="./home">Accueil</a>
                <?php endif; ?>
            </li>
            <li>
                <?php if ($pageActuelle === 'internships'): ?>
                    <a class="a1">Stages</a>
                <?php else: ?>
                    <a href="./internships">Stages</a>
                <?php endif; ?>
            </li>
            <li>
                <?php if ($pageActuelle === 'entreprises'): ?>
                    <a class="a1">Entreprises</a>
                <?php else: ?>
                    <a href="./entreprises">Entreprises</a>
                <?php endif; ?>
            </li>
            <li>
                <?php if ($pageActuelle === 'favoris'): ?>
                    <a class="a1">Favoris</a>
                <?php else: ?>
                    <a href="./favoris">Favoris</a>
                <?php endif; ?>
            </li>
        </ul>
    </nav>

    <!-- Formulaire de connexion -->
    <div id="loginForm" class="hidden">
        <form action="/StageHub/login" method="POST">
            <input type="email" name="email" placeholder="Adresse courriel" required>
            <input type="password" name="password" placeholder="Mot de passe" required>
            <button type="submit">Valider</button>
        </form>
        <div id="errorMessage" style="display: none;">Erreur d'identification, veuillez vérifier vos informations.</div>
    </div>
</header>

<script>
    // Fonction du menu principal
    const burgerMenu = document.getElementById('burger-menu');
    const navLinks = document.getElementById('nav-links');

    burgerMenu.addEventListener('click', () => {
        navLinks.classList.toggle('active');
    });

    function toggleStudentMenu() {
        const studentMenu = document.getElementById('student-menu');
        studentMenu.classList.toggle('hidden');
    }

    function toggleElement(id) {
        document.getElementById(id).classList.toggle("active");
    }
</script>
