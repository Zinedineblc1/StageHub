<?php
session_start();

// Connexion à la base de données (paramètres locaux)
$hote = 'localhost';
$base = 'stagehub';
$utilisateur = 'root';
$motdepasse = '';

try {
    $pdo = new PDO("mysql:host=$hote;dbname=$base", $utilisateur, $motdepasse);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Impossible d'établir la connexion à la base : " . $e->getMessage());
}

// Traitement du formulaire d'authentification
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $courriel = $_POST['email'];
    $motSecret = $_POST['password'];

    // Recherche de l'étudiant via l'adresse électronique
    $sql = "SELECT * FROM etudiants WHERE courriel = :courriel";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':courriel', $courriel);
    $stmt->execute();
    $etudiant = $stmt->fetch(PDO::FETCH_ASSOC);

    // Vérification du mot secret (à remplacer par password_verify si possible)
    if ($etudiant && md5($motSecret) === $etudiant['motdepasse']) {
        $_SESSION['student'] = [
            'id' => $etudiant['id_etudiant'],
            'prenom' => $etudiant['prenom'],
            'nom' => $etudiant['nom'],
            'role' => $etudiant['role']
        ];
        header('Location: /StageHub/home'); // Redirige vers l'espace d'accueil
        exit;
    } else {
        header('Location: views/students/err_login.php'); // Redirige en cas d'échec
        exit;
    }
} else {
    // Si la méthode n'est pas POST, on retourne vers le formulaire d'accès
    header('Location: login_form.php');
    exit;
}