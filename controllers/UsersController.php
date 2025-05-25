<?php
require_once './models/EtudiantModel.php';

class EtudiantsController {
    public function login() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $courriel = $_POST['email'];
            $motdepasse = $_POST['motdepasse'];

            $model = new EtudiantModel();
            $etudiant = $model->getEtudiantParCourriel($courriel);

            if ($etudiant && md5($motdepasse) === $etudiant['motdepasse']) {
                session_start();
                $_SESSION['student'] = [
                    'id' => $etudiant['id_etudiant'],
                    'prenom' => $etudiant['prenom'],
                    'nom' => $etudiant['nom'],
                    'role' => $etudiant['role']
                ];
                header('Location: /StageHub/home');
            } else {
                $error = "Identifiants incorrects";
                require './views/students/err_login.php';
            }
        } else {
            require './views/students/login.php';
        }
    }

    public function logout() {
        session_start();
        session_destroy();
        header('Location: /StageHub/home');
    }
}
