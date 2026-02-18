-- database/database.sql
-- MySQL 8+ / MariaDB

DROP TABLE IF EXISTS plat_allergene;
DROP TABLE IF EXISTS menu_plat;
DROP TABLE IF EXISTS menu_images;
DROP TABLE IF EXISTS commandes_statuts;
DROP TABLE IF EXISTS avis;
DROP TABLE IF EXISTS contacts;
DROP TABLE IF EXISTS horaires;
DROP TABLE IF EXISTS commandes;
DROP TABLE IF EXISTS allergenes;
DROP TABLE IF EXISTS plats;
DROP TABLE IF EXISTS menus;
DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS roles;

CREATE TABLE roles (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(30) NOT NULL UNIQUE
);

CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  role_id INT NOT NULL,
  nom VARCHAR(80) NOT NULL,
  prenom VARCHAR(80) NOT NULL,
  email VARCHAR(150) NOT NULL UNIQUE,
  gsm VARCHAR(30) NOT NULL,
  adresse VARCHAR(255) NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_users_roles FOREIGN KEY (role_id) REFERENCES roles(id)
);

CREATE TABLE menus (
  id INT AUTO_INCREMENT PRIMARY KEY,
  titre VARCHAR(120) NOT NULL,
  description TEXT NOT NULL,
  theme VARCHAR(50) NOT NULL,
  regime VARCHAR(50) NOT NULL,
  nb_personnes_min INT NOT NULL,
  prix_min DECIMAL(10,2) NOT NULL,
  conditions TEXT,
  stock INT NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NULL
);

CREATE TABLE menu_images (
  id INT AUTO_INCREMENT PRIMARY KEY,
  menu_id INT NOT NULL,
  url VARCHAR(255) NOT NULL,
  CONSTRAINT fk_menu_images_menu FOREIGN KEY (menu_id) REFERENCES menus(id) ON DELETE CASCADE
);

CREATE TABLE plats (
  id INT AUTO_INCREMENT PRIMARY KEY,
  type ENUM('entree','plat','dessert') NOT NULL,
  nom VARCHAR(120) NOT NULL,
  description TEXT
);

CREATE TABLE menu_plat (
  menu_id INT NOT NULL,
  plat_id INT NOT NULL,
  PRIMARY KEY (menu_id, plat_id),
  CONSTRAINT fk_menu_plat_menu FOREIGN KEY (menu_id) REFERENCES menus(id) ON DELETE CASCADE,
  CONSTRAINT fk_menu_plat_plat FOREIGN KEY (plat_id) REFERENCES plats(id) ON DELETE CASCADE
);

CREATE TABLE allergenes (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nom VARCHAR(120) NOT NULL UNIQUE
);

CREATE TABLE plat_allergene (
  plat_id INT NOT NULL,
  allergene_id INT NOT NULL,
  PRIMARY KEY (plat_id, allergene_id),
  CONSTRAINT fk_plat_allergene_plat FOREIGN KEY (plat_id) REFERENCES plats(id) ON DELETE CASCADE,
  CONSTRAINT fk_plat_allergene_allergene FOREIGN KEY (allergene_id) REFERENCES allergenes(id) ON DELETE CASCADE
);

CREATE TABLE commandes (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  menu_id INT NOT NULL,
  nb_personnes INT NOT NULL,
  date_prestation DATE NOT NULL,
  heure_livraison TIME NOT NULL,
  adresse_livraison VARCHAR(255) NOT NULL,
  ville_livraison VARCHAR(120) NOT NULL,
  km_hors_bordeaux DECIMAL(10,2) NOT NULL DEFAULT 0,
  prix_menu DECIMAL(10,2) NOT NULL DEFAULT 0,
  remise DECIMAL(10,2) NOT NULL DEFAULT 0,
  prix_livraison DECIMAL(10,2) NOT NULL DEFAULT 0,
  prix_total DECIMAL(10,2) NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_commandes_user FOREIGN KEY (user_id) REFERENCES users(id),
  CONSTRAINT fk_commandes_menu FOREIGN KEY (menu_id) REFERENCES menus(id)
);

CREATE TABLE commandes_statuts (
  id INT AUTO_INCREMENT PRIMARY KEY,
  commande_id INT NOT NULL,
  statut ENUM(
    'accepte',
    'en_preparation',
    'en_cours_de_livraison',
    'livre',
    'en_attente_retour_materiel',
    'terminee'
  ) NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_statuts_commande FOREIGN KEY (commande_id) REFERENCES commandes(id) ON DELETE CASCADE
);

CREATE TABLE avis (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  menu_id INT NOT NULL,
  note TINYINT NOT NULL,
  commentaire TEXT,
  is_validated TINYINT(1) NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT chk_note CHECK (note BETWEEN 1 AND 5),
  CONSTRAINT fk_avis_user FOREIGN KEY (user_id) REFERENCES users(id),
  CONSTRAINT fk_avis_menu FOREIGN KEY (menu_id) REFERENCES menus(id)
);

CREATE TABLE horaires (
  id INT AUTO_INCREMENT PRIMARY KEY,
  jour ENUM('lundi','mardi','mercredi','jeudi','vendredi','samedi','dimanche') NOT NULL UNIQUE,
  heure_ouverture TIME NOT NULL,
  heure_fermeture TIME NOT NULL
);

CREATE TABLE contacts (
  id INT AUTO_INCREMENT PRIMARY KEY,
  email VARCHAR(150) NOT NULL,
  titre VARCHAR(120) NOT NULL,
  description TEXT NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- Données de test minimales
INSERT INTO roles (name) VALUES ('utilisateur'), ('employe'), ('admin');

-- admin de test (hash à remplacer plus tard)
INSERT INTO users (role_id, nom, prenom, email, gsm, adresse, password_hash)
VALUES
(3, 'Admin', 'Jose', 'admin@vite-gourmand.test', '0600000000', 'Bordeaux', '$2y$10$CHANGE_ME_HASH');

INSERT INTO menus (titre, description, theme, regime, nb_personnes_min, prix_min, conditions, stock)
VALUES
('Menu Classique', 'Un menu simple et gourmand.', 'classique', 'classique', 4, 80.00, 'Commander 48h à l’avance.', 10);

INSERT INTO plats (type, nom) VALUES
('entree', 'Salade de saison'),
('plat', 'Poulet rôti et pommes de terre'),
('dessert', 'Tarte aux pommes');

INSERT INTO menu_plat (menu_id, plat_id) VALUES
(1, 1), (1, 2), (1, 3);

INSERT INTO allergenes (nom) VALUES ('gluten'), ('lactose'), ('fruits_a_coque');

INSERT INTO plat_allergene (plat_id, allergene_id) VALUES
(3, 1);

INSERT INTO horaires (jour, heure_ouverture, heure_fermeture) VALUES
('lundi','09:00:00','18:00:00'),
('mardi','09:00:00','18:00:00'),
('mercredi','09:00:00','18:00:00'),
('jeudi','09:00:00','18:00:00'),
('vendredi','09:00:00','18:00:00'),
('samedi','10:00:00','16:00:00'),
('dimanche','10:00:00','14:00:00');
