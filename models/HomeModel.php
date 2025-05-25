<?php
class TableauDeBordModel {
    private $pdo;

    public function __construct() {
        $this->pdo = Database::getConnection();
    }

    // Méthode pour obtenir les statistiques du tableau de bord
    public function getStats() {
        $stats = [
            'stages' => 0,
            'entreprises' => 0,
            'etudiants' => 0
        ];

        try {
            // Nombre de stages
            $stmt = $this->pdo->query("SELECT COUNT(*) as total FROM stages");
            $stats['stages'] = $stmt->fetch()['total'];

            // Nombre d'entreprises
            $stmt = $this->pdo->query("SELECT COUNT(*) as total FROM entreprises");
            $stats['entreprises'] = $stmt->fetch()['total'];

            // Nombre d'étudiants
            $stmt = $this->pdo->query("SELECT COUNT(*) as total FROM etudiants WHERE role = 'student'");
            $stats['etudiants'] = $stmt->fetch()['total'];
        } catch (PDOException $e) {
            die("Erreur lors de la récupération des statistiques : " . $e->getMessage());
        }

        return $stats;
    }
}
