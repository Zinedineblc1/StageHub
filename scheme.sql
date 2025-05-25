CREATE DATABASE IF NOT EXISTS stagehub CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE stagehub;

-- Table: entreprises
CREATE TABLE entreprises (
    id_entreprise INT AUTO_INCREMENT PRIMARY KEY,
    nom_enseigne VARCHAR(255) NOT NULL,
    presentation TEXT,
    courriel VARCHAR(255) NOT NULL,
    telephone VARCHAR(50),
    evaluation INT DEFAULT 0
);

-- Table: etudiants
CREATE TABLE etudiants (
    id_etudiant INT AUTO_INCREMENT PRIMARY KEY,
    prenom VARCHAR(100) NOT NULL,
    nom VARCHAR(100) NOT NULL,
    courriel VARCHAR(255) NOT NULL UNIQUE,
    motdepasse VARCHAR(255) NOT NULL,
    role VARCHAR(50) NOT NULL DEFAULT 'student'
);

-- Table: tuteurs
CREATE TABLE tuteurs (
    id_tuteur INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100) NOT NULL,
    courriel VARCHAR(255) NOT NULL UNIQUE,
    motdepasse VARCHAR(255) NOT NULL,
    role VARCHAR(50) NOT NULL DEFAULT 'mentor'
);

-- Table: internships (stages)
CREATE TABLE internships (
    ID_Internship INT AUTO_INCREMENT PRIMARY KEY,
    Title VARCHAR(255) NOT NULL,
    presentation TEXT,
    Stipend DECIMAL(10,2),
    Start_Date DATE,
    End_Date DATE,
    id_entreprise INT,
    FOREIGN KEY (id_entreprise) REFERENCES entreprises(id_entreprise)
);

-- Table: candidatures
CREATE TABLE candidatures (
    id_candidature INT AUTO_INCREMENT PRIMARY KEY,
    date_candidature DATE NOT NULL,
    chemin_cv VARCHAR(255),
    chemin_lettre VARCHAR(255),
    id_stage INT,
    id_etudiant INT,
    FOREIGN KEY (id_stage) REFERENCES internships(ID_Internship),
    FOREIGN KEY (id_etudiant) REFERENCES etudiants(id_etudiant)
);

-- Table: favoris
CREATE TABLE favoris (
    id_favori INT AUTO_INCREMENT PRIMARY KEY,
    id_etudiant INT,
    id_stage INT,
    FOREIGN KEY (id_etudiant) REFERENCES etudiants(id_etudiant),
    FOREIGN KEY (id_stage) REFERENCES internships(ID_Internship)
);