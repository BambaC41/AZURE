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
INSERT INTO ecoles (nom, adresse, ville, telephone, email, directeur) VALUES
('Académie Numérique de Lille', '8 boulevard de la République', 'Lille', '0304050607', 'contact@anl.fr', 'Mme Caron'),
('Campus Atlantique Business School', '22 rue du Port', 'Bordeaux', '0405060708', 'contact@cabs.fr', 'M. Gauthier');
 
INSERT INTO classes (nom, niveau, filiere, annee_scolaire, salle, professeur_principal, ecole_id, nombre_eleves, moyenne_classe, note_min, note_max) VALUES
('L3 INFO C', 'Licence 3', 'Informatique', '2025-2026', 'C112', 'Mme Rossi', 1, 0, 0, 0, 0),
('M2 DATA IA', 'Master 2', 'Data / IA', '2025-2026', 'D301', 'M. Lambert', 1, 0, 0, 0, 0),
('L1 GESTION A', 'Licence 1', 'Gestion', '2025-2026', 'A102', 'Mme Fournier', 2, 0, 0, 0, 0),
('L2 MARKETING', 'Licence 2', 'Marketing', '2025-2026', 'A205', 'M. Perez', 2, 0, 0, 0, 0),
('L3 CYBERSEC', 'Licence 3', 'Cybersécurité', '2025-2026', 'E210', 'M. Haddad', 3, 0, 0, 0, 0),
('M1 CLOUD DEVOPS', 'Master 1', 'Cloud / DevOps', '2025-2026', 'E402', 'Mme Ndao', 3, 0, 0, 0, 0),
('L2 FINANCE', 'Licence 2', 'Finance', '2025-2026', 'B220', 'M. Dias', 4, 0, 0, 0, 0),
('M2 STRATEGIE', 'Master 2', 'Management Stratégique', '2025-2026', 'B315', 'Mme Legrand', 4, 0, 0, 0, 0);
 
INSERT INTO matieres (nom, coefficient, enseignant) VALUES
('Programmation Web', 3, 'Mme Rossi'),
('Systèmes Linux', 2, 'M. Haddad'),
('Cybersécurité Offensive', 4, 'M. Haddad'),
('Cloud Azure', 4, 'Mme Ndao'),
('DevOps', 3, 'Mme Ndao'),
('Intelligence Artificielle', 4, 'M. Lambert'),
('Machine Learning', 3, 'M. Lambert'),
('Comptabilité', 2, 'M. Dias'),
('Finance d’entreprise', 3, 'M. Dias'),
('Communication marketing', 2, 'M. Perez'),
('Droit des affaires', 2, 'Mme Fournier'),
('Gestion de projet', 2, 'Mme Caron');
 
INSERT INTO etudiants (nom, prenom, matricule, email, telephone, date_naissance, genre, classe_id, moyenne_generale, rang, mention, statut) VALUES
('Traore', 'Fatou', 'ETU2026007', 'fatou.traore@ecole.fr', '0600000007', '2003-08-12', 'F', 4, 0, 0, '', ''),
('Lopez', 'Carlos', 'ETU2026008', 'carlos.lopez@ecole.fr', '0600000008', '2002-04-19', 'M', 4, 0, 0, '', ''),
('Moreau', 'Julie', 'ETU2026009', 'julie.moreau@ecole.fr', '0600000009', '2001-09-03', 'F', 5, 0, 0, '', ''),
('Benali', 'Yacine', 'ETU2026010', 'yacine.benali@ecole.fr', '0600000010', '2001-01-11', 'M', 5, 0, 0, '', ''),
('Sarr', 'Awa', 'ETU2026011', 'awa.sarr@ecole.fr', '0600000011', '2004-06-14', 'F', 6, 0, 0, '', ''),
('Petit', 'Nina', 'ETU2026012', 'nina.petit@ecole.fr', '0600000012', '2004-02-07', 'F', 6, 0, 0, '', ''),
('Martin', 'Theo', 'ETU2026013', 'theo.martin@ecole.fr', '0600000013', '2003-11-28', 'M', 7, 0, 0, '', ''),
('Kone', 'Salif', 'ETU2026014', 'salif.kone@ecole.fr', '0600000014', '2002-05-24', 'M', 7, 0, 0, '', ''),
('Dubois', 'Chloe', 'ETU2026015', 'chloe.dubois@ecole.fr', '0600000015', '2002-12-10', 'F', 8, 0, 0, '', ''),
('Ba', 'Mariam', 'ETU2026016', 'mariam.ba@ecole.fr', '0600000016', '2001-10-17', 'F', 8, 0, 0, '', ''),
('Ngom', 'Ibra', 'ETU2026017', 'ibra.ngom@ecole.fr', '0600000017', '2003-03-05', 'M', 9, 0, 0, '', ''),
('Robert', 'Lena', 'ETU2026018', 'lena.robert@ecole.fr', '0600000018', '2003-07-09', 'F', 9, 0, 0, '', ''),
('Camara', 'Moussa', 'ETU2026019', 'moussa.camara@ecole.fr', '0600000019', '2002-08-22', 'M', 10, 0, 0, '', ''),
('Garcia', 'Eva', 'ETU2026020', 'eva.garcia@ecole.fr', '0600000020', '2001-06-30', 'F', 10, 0, 0, '', '');
 
INSERT INTO notes (valeur, type_note, date_note, etudiant_id, matiere_id) VALUES
(14.5, 'Examen', '2026-02-10', 7, 6),
(15.0, 'Projet', '2026-02-15', 7, 7),
(13.5, 'TP', '2026-02-20', 7, 12),
 
(11.5, 'Examen', '2026-02-10', 8, 6),
(12.0, 'Projet', '2026-02-15', 8, 7),
(13.0, 'TP', '2026-02-20', 8, 12),
 
(16.5, 'Examen', '2026-02-12', 9, 6),
(17.0, 'Projet', '2026-02-18', 9, 7),
(15.5, 'TP', '2026-02-25', 9, 4),
 
(13.0, 'Examen', '2026-02-12', 10, 6),
(14.0, 'Projet', '2026-02-18', 10, 7),
(12.5, 'TP', '2026-02-25', 10, 4),
 
(15.0, 'Devoir', '2026-02-11', 11, 11),
(14.0, 'Examen', '2026-02-19', 11, 8),
(13.5, 'Projet', '2026-02-27', 11, 12),
 
(17.0, 'Devoir', '2026-02-11', 12, 11),
(16.5, 'Examen', '2026-02-19', 12, 8),
(15.0, 'Projet', '2026-02-27', 12, 12),
 
(10.5, 'Examen', '2026-02-08', 13, 9),
(11.0, 'TP', '2026-02-14', 13, 8),
(12.0, 'Devoir', '2026-02-21', 13, 11),
 
(14.0, 'Examen', '2026-02-08', 14, 9),
(13.5, 'TP', '2026-02-14', 14, 8),
(15.0, 'Devoir', '2026-02-21', 14, 11),
 
(16.0, 'Examen', '2026-02-09', 15, 10),
(15.5, 'Projet', '2026-02-16', 15, 11),
(17.0, 'Oral', '2026-02-23', 15, 12),
 
(12.0, 'Examen', '2026-02-09', 16, 10),
(13.0, 'Projet', '2026-02-16', 16, 11),
(12.5, 'Oral', '2026-02-23', 16, 12),
 
(15.0, 'Examen', '2026-02-13', 17, 3),
(14.5, 'TP', '2026-02-19', 17, 2),
(16.0, 'Projet', '2026-02-26', 17, 4),
 
(17.5, 'Examen', '2026-02-13', 18, 3),
(16.0, 'TP', '2026-02-19', 18, 2),
(18.0, 'Projet', '2026-02-26', 18, 4),
 
(13.0, 'Examen', '2026-02-17', 19, 4),
(14.0, 'TP', '2026-02-24', 19, 5),
(12.5, 'Projet', '2026-03-02', 19, 12),
 
(15.5, 'Examen', '2026-02-17', 20, 4),
(16.0, 'TP', '2026-02-24', 20, 5),
(15.0, 'Projet', '2026-03-02', 20, 12);