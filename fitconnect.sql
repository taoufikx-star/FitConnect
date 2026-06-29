-- ============================================================
--  FitConnect — Implémentation MySQL (dérivée du MLD)
--  Ordre : tables indépendantes → tables dépendantes
-- ============================================================

CREATE DATABASE IF NOT EXISTS fitconnect
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE fitconnect;

-- ------------------------------------------------------------
-- 1. SALLE
-- ------------------------------------------------------------
CREATE TABLE salle (
    id_salle   INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    nom        VARCHAR(100)    NOT NULL,
    adresse    VARCHAR(200)    NOT NULL,
    ville      VARCHAR(100)    NOT NULL,
    telephone  VARCHAR(20)     DEFAULT NULL,
    PRIMARY KEY (id_salle)
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- 2. TYPE_ACTIVITE
-- ------------------------------------------------------------
CREATE TABLE type_activite (
    id_type_activite INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    libelle          VARCHAR(80)     NOT NULL UNIQUE,
    description      TEXT            DEFAULT NULL,
    PRIMARY KEY (id_type_activite)
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- 3. EQUIPEMENT
-- ------------------------------------------------------------
CREATE TABLE equipement (
    id_equipement INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    nom           VARCHAR(100)    NOT NULL,
    categorie     VARCHAR(60)     NOT NULL,
    PRIMARY KEY (id_equipement)
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- 4. ADHERENT  (dépend de SALLE)
-- ------------------------------------------------------------
CREATE TABLE adherent (
    id_adherent      INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    id_salle         INT UNSIGNED    NOT NULL,
    nom              VARCHAR(80)     NOT NULL,
    prenom           VARCHAR(80)     NOT NULL,
    email            VARCHAR(180)    NOT NULL UNIQUE,
    telephone        VARCHAR(20)     DEFAULT NULL,
    date_inscription DATE            NOT NULL,
    PRIMARY KEY (id_adherent),
    CONSTRAINT fk_adherent_salle
        FOREIGN KEY (id_salle) REFERENCES salle (id_salle)
        ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- 5. ABONNEMENT  (dépend de ADHERENT)
-- ------------------------------------------------------------
CREATE TABLE abonnement (
    id_abonnement INT UNSIGNED                              NOT NULL AUTO_INCREMENT,
    id_adherent   INT UNSIGNED                              NOT NULL,
    type          ENUM('mensuel','trimestriel','annuel')    NOT NULL,
    date_debut    DATE                                      NOT NULL,
    date_fin      DATE                                      NOT NULL,
    statut        ENUM('actif','expiré','suspendu')         NOT NULL DEFAULT 'actif',
    PRIMARY KEY (id_abonnement),
    CONSTRAINT fk_abonnement_adherent
        FOREIGN KEY (id_adherent) REFERENCES adherent (id_adherent)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT chk_dates CHECK (date_fin > date_debut)
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- 6. SEANCE  (dépend de ADHERENT, SALLE, TYPE_ACTIVITE)
-- ------------------------------------------------------------
CREATE TABLE seance (
    id_seance        INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    id_adherent      INT UNSIGNED    NOT NULL,
    id_salle         INT UNSIGNED    NOT NULL,
    id_type_activite INT UNSIGNED    NOT NULL,
    date_heure       DATETIME        NOT NULL,
    duree_minutes    SMALLINT UNSIGNED NOT NULL,
    notes            TEXT            DEFAULT NULL,
    PRIMARY KEY (id_seance),
    CONSTRAINT fk_seance_adherent
        FOREIGN KEY (id_adherent) REFERENCES adherent (id_adherent)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_seance_salle
        FOREIGN KEY (id_salle) REFERENCES salle (id_salle)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_seance_activite
        FOREIGN KEY (id_type_activite) REFERENCES type_activite (id_type_activite)
        ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- 7. SEANCE_EQUIPEMENT  (table d'association N-N)
-- ------------------------------------------------------------
CREATE TABLE seance_equipement (
    id_seance     INT UNSIGNED NOT NULL,
    id_equipement INT UNSIGNED NOT NULL,
    PRIMARY KEY (id_seance, id_equipement),
    CONSTRAINT fk_se_seance
        FOREIGN KEY (id_seance)     REFERENCES seance     (id_seance)     ON DELETE CASCADE,
    CONSTRAINT fk_se_equipement
        FOREIGN KEY (id_equipement) REFERENCES equipement (id_equipement) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- ============================================================
--  DONNÉES DE TEST
-- ============================================================

INSERT INTO salle (nom, adresse, ville, telephone) VALUES
    ('FitConnect Montparnasse', '12 rue du Départ', 'Paris',       '0142345678'),
    ('FitConnect Bourse',       '8 place de la Bourse', 'Paris',   '0142456789'),
    ('FitConnect République',   '55 boulevard Voltaire', 'Paris',  '0142567890'),
    ('FitConnect Nation',       '3 avenue Dorian', 'Paris',        '0142678901');

INSERT INTO type_activite (libelle, description) VALUES
    ('Musculation',  'Travail avec charges libres et machines'),
    ('Cardio',       'Vélos, tapis, elliptiques'),
    ('Yoga',         'Séances de yoga et stretching'),
    ('CrossFit',     'Entraînements fonctionnels à haute intensité'),
    ('Natation',     'Bassin 25 m, cours collectifs et libres'),
    ('Boxe',         'Cours de boxe anglaise et sparring');

INSERT INTO equipement (nom, categorie) VALUES
    ('Haltères 10 kg',  'Musculation'),
    ('Barre olympique', 'Musculation'),
    ('Vélo statique',   'Cardio'),
    ('Tapis de course', 'Cardio'),
    ('Tapis de yoga',   'Yoga'),
    ('Corde à sauter',  'CrossFit'),
    ('Gants de boxe',   'Boxe');

INSERT INTO adherent (id_salle, nom, prenom, email, telephone, date_inscription) VALUES
    (1, 'Benali',    'Sara',    'sara.benali@email.com',    '0601010101', '2024-01-10'),
    (1, 'Dupont',    'Marc',    'marc.dupont@email.com',    '0601020202', '2024-02-15'),
    (2, 'Khaldi',    'Yasmine', 'yasmine.khaldi@email.com', '0601030303', '2024-03-01'),
    (3, 'Martin',    'Julien',  'julien.martin@email.com',  '0601040404', '2024-04-20'),
    (4, 'Rousseau',  'Camille', 'camille.rousseau@email.com','0601050505','2024-05-05');

INSERT INTO abonnement (id_adherent, type, date_debut, date_fin, statut) VALUES
    (1, 'mensuel',      '2025-06-01', '2025-06-30', 'actif'),
    (2, 'trimestriel',  '2025-05-01', '2025-07-31', 'actif'),
    (3, 'annuel',       '2025-01-01', '2025-12-31', 'actif'),
    (4, 'mensuel',      '2025-06-01', '2025-06-30', 'actif'),
    (5, 'trimestriel',  '2025-04-01', '2025-06-30', 'expiré');

INSERT INTO seance (id_adherent, id_salle, id_type_activite, date_heure, duree_minutes, notes) VALUES
    (1, 1, 1, '2025-06-10 09:00:00', 60,  'Séance bras et épaules'),
    (1, 1, 2, '2025-06-12 08:30:00', 45,  'Cardio matinal'),
    (2, 1, 4, '2025-06-11 18:00:00', 75,  'CrossFit WOD du soir'),
    (3, 2, 3, '2025-06-09 10:00:00', 60,  'Yoga vinyasa'),
    (4, 3, 6, '2025-06-08 19:00:00', 90,  'Boxe — sparring 3 rounds');

INSERT INTO seance_equipement (id_seance, id_equipement) VALUES
    (1, 1), (1, 2),
    (2, 3), (2, 4),
    (3, 6),
    (4, 5),
    (5, 7);
