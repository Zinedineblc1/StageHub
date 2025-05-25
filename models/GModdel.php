<?php

require_once './config/database.php';

// Modèle pour les opérations de données du panneau d'administration
class PanneauModel
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getConnection(); // Connexion à la base
    }

    // Récupérer toutes les entreprises
    public function recupererToutesEntreprises()
    {
        $query = $this->db->prepare("SELECT * FROM entreprises");
        $query->execute();
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    // Récupérer tous les candidats
    public function recupererTousCandidats()
    {
        $query = $this->db->prepare("SELECT * FROM candidats");
        $query->execute();
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    // Récupérer tous les stages
    public function recupererTousStages()
    {
        $query = $this->db->prepare("SELECT * FROM stages");
        $query->execute();
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    // Récupérer tous les tuteurs
    public function recupererTousTuteurs()
    {
        $query = $this->db->prepare("SELECT * FROM tuteurs");
        $query->execute();
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }
}
