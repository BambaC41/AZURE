CREATE DATABASE IF NOT EXISTS school;
USE school;

DROP TABLE IF EXISTS notes;
DROP TABLE IF EXISTS etudiants;
DROP TABLE IF EXISTS matieres;
DROP TABLE IF EXISTS classes;
DROP TABLE IF EXISTS ecoles;

CREATE TABLE IF NOT EXISTS ecoles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(255) NOT NULL,
    adresse VARCHAR(255),
    ville VARCHAR(100),
    telephone VARCHAR(50),
    email VARCHAR(255),
    directeur VARCHAR(255)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS classes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    niveau VARCHAR(100) NOT NULL,
    filiere VARCHAR(100) NOT NULL,
    annee_scolaire VARCHAR(20) NOT NULL,
    salle VARCHAR(50),
    professeur_principal VARCHAR(255),
    ecole_id INT NOT NULL,
    nombre_eleves INT NOT NULL DEFAULT 0,
    moyenne_classe DECIMAL(5,2) NOT NULL DEFAULT 0,
    note_min DECIMAL(5,2) NOT NULL DEFAULT 0,
    note_max DECIMAL(5,2) NOT NULL DEFAULT 0,
    CONSTRAINT fk_classes_ecole FOREIGN KEY (ecole_id) REFERENCES ecoles(id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS etudiants (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100) NOT NULL,
    matricule VARCHAR(100) NOT NULL UNIQUE,
    email VARCHAR(255),
    telephone VARCHAR(50),
    date_naissance VARCHAR(50),
    genre VARCHAR(20),
    classe_id INT NOT NULL,
    moyenne_generale DECIMAL(5,2) NOT NULL DEFAULT 0,
    rang INT NOT NULL DEFAULT 0,
    mention VARCHAR(100),
    statut VARCHAR(100),
    CONSTRAINT fk_etudiants_classe FOREIGN KEY (classe_id) REFERENCES classes(id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS matieres (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    coefficient DECIMAL(5,2) NOT NULL DEFAULT 1,
    enseignant VARCHAR(255)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS notes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    valeur DECIMAL(5,2) NOT NULL,
    type_note VARCHAR(100) NOT NULL,
    date_note VARCHAR(50),
    etudiant_id INT NOT NULL,
    matiere_id INT NOT NULL,
    CONSTRAINT fk_notes_etudiant FOREIGN KEY (etudiant_id) REFERENCES etudiants(id),
    CONSTRAINT fk_notes_matiere FOREIGN KEY (matiere_id) REFERENCES matieres(id)
) ENGINE=InnoDB;

INSERT INTO ecoles (nom, adresse, ville, telephone, email, directeur) VALUES
('Institut Supérieur de Technologie', '12 rue des Lilas', 'Paris', '0102030405', 'contact@ist.fr', 'Mme Bernard'),
('Ecole Européenne de Management', '45 avenue Victor Hugo', 'Lyon', '0203040506', 'contact@eem.fr', 'M. Martin');

INSERT INTO classes (nom, niveau, filiere, annee_scolaire, salle, professeur_principal, ecole_id, nombre_eleves, moyenne_classe, note_min, note_max) VALUES
('L1 INFO A', 'Licence 1', 'Informatique', '2025-2026', 'B101', 'M. Dupont', 1, 0, 0, 0, 0),
('L2 INFO B', 'Licence 2', 'Informatique', '2025-2026', 'B204', 'Mme Leroy', 1, 0, 0, 0, 0),
('M1 MANAGEMENT', 'Master 1', 'Management', '2025-2026', 'A302', 'M. Girard', 2, 0, 0, 0, 0);

INSERT INTO matieres (nom, coefficient, enseignant) VALUES
('Algorithmique', 3, 'M. Dupont'),
('Bases de données', 2, 'Mme Leroy'),
('Réseaux', 2, 'M. Petit'),
('Management stratégique', 4, 'M. Girard'),
('Marketing', 3, 'Mme Simon');

INSERT INTO etudiants (nom, prenom, matricule, email, telephone, date_naissance, genre, classe_id, moyenne_generale, rang, mention, statut) VALUES
('Diallo', 'Mamadou', 'ETU2026001', 'mamadou.diallo@ecole.fr', '0600000001', '2003-05-14', 'M', 1, 0, 0, '', ''),
('Sow', 'Aminata', 'ETU2026002', 'aminata.sow@ecole.fr', '0600000002', '2004-01-20', 'F', 1, 0, 0, '', ''),
('Barry', 'Ibrahima', 'ETU2026003', 'ibrahima.barry@ecole.fr', '0600000003', '2003-09-10', 'M', 1, 0, 0, '', ''),
('Nguyen', 'Linh', 'ETU2026004', 'linh.nguyen@ecole.fr', '0600000004', '2002-11-08', 'F', 2, 0, 0, '', ''),
('Martin', 'Lucas', 'ETU2026005', 'lucas.martin@ecole.fr', '0600000005', '2002-03-17', 'M', 2, 0, 0, '', ''),
('Bernard', 'Emma', 'ETU2026006', 'emma.bernard@ecole.fr', '0600000006', '2001-07-25', 'F', 3, 0, 0, '', '');

INSERT INTO notes (valeur, type_note, date_note, etudiant_id, matiere_id) VALUES
(15.5, 'Examen', '2026-01-15', 1, 1),
(14.0, 'TP', '2026-01-22', 1, 2),
(13.5, 'Devoir', '2026-02-02', 1, 3),
(17.0, 'Examen', '2026-01-15', 2, 1),
(16.5, 'TP', '2026-01-22', 2, 2),
(15.0, 'Devoir', '2026-02-02', 2, 3),
(11.0, 'Examen', '2026-01-15', 3, 1),
(12.5, 'TP', '2026-01-22', 3, 2),
(10.0, 'Devoir', '2026-02-02', 3, 3),
(14.0, 'Examen', '2026-01-18', 4, 1),
(13.0, 'TP', '2026-01-25', 4, 2),
(12.0, 'Devoir', '2026-02-05', 4, 3),
(9.5, 'Examen', '2026-01-18', 5, 1),
(10.0, 'TP', '2026-01-25', 5, 2),
(11.0, 'Devoir', '2026-02-05', 5, 3),
(16.0, 'Examen', '2026-01-20', 6, 4),
(15.0, 'Projet', '2026-01-28', 6, 5);