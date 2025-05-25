<?php
require_once './config/database.php';
class FavorisModel {
    private $pdo;

    public function __construct() {
        $this->pdo = Database::getConnection();
    }

    public function getFavorisParEtudiant($idEtudiant) {
        $sql = "SELECT stages.* FROM favoris 
                JOIN stages ON favoris.id_stage = stages.id_stage 
                WHERE favoris.id_etudiant = :idEtudiant";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':idEtudiant', $idEtudiant);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
