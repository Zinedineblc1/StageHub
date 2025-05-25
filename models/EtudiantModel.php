<?php
require_once './config/database.php';
class EtudiantModel {
    private $pdo;

    public function __construct() {
        $this->pdo = Database::getConnection();
    }

    public function getEtudiantParCourriel($courriel) {
        $sql = "SELECT * FROM etudiants WHERE courriel = :courriel";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':courriel', $courriel);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
