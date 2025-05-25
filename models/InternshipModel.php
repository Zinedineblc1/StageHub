<?php
require_once './config/database.php';
class StageModel {
    private $pdo;

    public function __construct() {
        $this->pdo = Database::getConnection();
    }

    public function getAllStages() {
        $sql = "SELECT stages.*, entreprises.nom_entreprise AS entreprise 
                FROM stages 
                JOIN entreprises ON stages.id_entreprise = entreprises.id_entreprise";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
