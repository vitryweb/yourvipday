CREATE TABLE `Partenaires` (
  `id` Int PRIMARY KEY NOT NULL AUTO_INCREMENT,
  `nom` Varchar(100) NOT NULL,
  `email` Varchar(320) NOT NULL,
  `telephone` Varchar(10) NOT NULL
);

CREATE TABLE `Occasions` (
  `id` Int PRIMARY KEY NOT NULL AUTO_INCREMENT,
  `nom` Varchar(100) NOT NULL,
  `description` Text
);

CREATE TABLE `Experiences` (
  `id` Int PRIMARY KEY NOT NULL AUTO_INCREMENT,
  `titre` Varchar(100) NOT NULL,
  `description` Text NOT NULL,
  `est_premium` Bool NOT NULL,
  `lien` Varchar(255) NOT NULL,
  `prix` Decimal NOT NULL,
  `nb_places_total` Int NOT NULL,
  `localisation` Varchar(255) NOT NULL,
  `est_rare` Bool DEFAULT false,
  `duree_limitee` Date,
  `offre_surprise` Bool DEFAULT false,
  `duree_experience` Int NOT NULL COMMENT 'Durée en minutes',
  `reservation_obligatoire` Bool DEFAULT true,
  `delai_annulation_heures` Int DEFAULT 24,
  `id_Partenaires` Int NOT NULL
);

CREATE TABLE `Regles_Disponibilite` (
  `id` Int PRIMARY KEY NOT NULL AUTO_INCREMENT,
  `jour_semaine` ENUM ('lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi', 'samedi', 'dimanche') NOT NULL,
  `heure_debut` Time NOT NULL,
  `heure_fin` Time NOT NULL,
  `places_max` Int NOT NULL,
  `est_actif` Bool DEFAULT true,
  `id_Experiences` Int NOT NULL
);

CREATE TABLE `Exceptions_Disponibilite` (
  `id` Int PRIMARY KEY NOT NULL AUTO_INCREMENT,
  `date_exception` Date NOT NULL,
  `type_exception` ENUM ('ferme', 'horaires_speciaux', 'complet') NOT NULL,
  `heure_debut` Time COMMENT 'Si horaires_speciaux',
  `heure_fin` Time COMMENT 'Si horaires_speciaux',
  `places_max` Int COMMENT 'Si horaires_speciaux',
  `motif` Varchar(255),
  `id_Experiences` Int NOT NULL
);

CREATE TABLE `Utilisateurs` (
  `id` Int PRIMARY KEY NOT NULL AUTO_INCREMENT,
  `nom` Varchar(120) NOT NULL,
  `prenom` Varchar(120) NOT NULL,
  `email` Varchar(320) NOT NULL,
  `mot_de_passe` Varchar(72),
  `date_inscription` Date NOT NULL,
  `photo` Varchar(255) NOT NULL,
  `budget` Int NOT NULL,
  `cookie` Bool NOT NULL,
  `google_id` Varchar(255),
  `instagram_id` Varchar(255),
  `tiktok_id` Varchar(255),
  `points_parrainage` Int DEFAULT 0
);

CREATE TABLE `Occasions_Utilisateur` (
  `id` Int PRIMARY KEY NOT NULL AUTO_INCREMENT,
  `nom` Varchar(150) NOT NULL,
  `date_evenement` Date NOT NULL,
  `id_Utilisateurs` Int NOT NULL,
  `id_Occasions` Int NOT NULL
);

CREATE TABLE `Abonnement_Premium` (
  `id` Int PRIMARY KEY NOT NULL AUTO_INCREMENT,
  `date_debut` Date NOT NULL,
  `date_fin` Date NOT NULL,
  `id_Utilisateurs` Int NOT NULL
);

CREATE TABLE `categorie` (
  `id` Int PRIMARY KEY NOT NULL AUTO_INCREMENT,
  `nom` Varchar(100) NOT NULL
);

CREATE TABLE `type_experience` (
  `id` Int PRIMARY KEY NOT NULL AUTO_INCREMENT,
  `nom` Varchar(100) NOT NULL
);

CREATE TABLE `avis` (
  `id` Int PRIMARY KEY NOT NULL AUTO_INCREMENT,
  `commentaire` Text NOT NULL,
  `note` Int NOT NULL,
  `date` Datetime NOT NULL,
  `id_Utilisateurs` Int NOT NULL,
  `id_Experiences` Int NOT NULL
);

CREATE TABLE `partage_lien` (
  `id` Int PRIMARY KEY NOT NULL AUTO_INCREMENT,
  `lien` Varchar(255) NOT NULL,
  `id_Utilisateurs` Int NOT NULL,
  `id_Experiences` Int NOT NULL
);

CREATE TABLE `image` (
  `id_image` Int PRIMARY KEY NOT NULL AUTO_INCREMENT,
  `lien` Varchar(255) NOT NULL,
  `est_ugc` Bool DEFAULT false,
  `id` Int NOT NULL,
  `id_Experiences` Int NOT NULL
);

CREATE TABLE `aimer` (
  `id` Int NOT NULL,
  `id_categorie` Int NOT NULL,
  PRIMARY KEY (`id`, `id_categorie`)
);

CREATE TABLE `parrainer` (
  `id` Int NOT NULL,
  `id_Utilisateurs` Int NOT NULL,
  `date_parrainage` Date NOT NULL,
  `statut` ENUM ('en_attente', 'valide', 'complete') DEFAULT 'en_attente',
  `bonus_accorde` Bool DEFAULT false,
  PRIMARY KEY (`id`, `id_Utilisateurs`)
);

CREATE TABLE `enregistrer` (
  `id` Int NOT NULL,
  `id_Utilisateurs` Int NOT NULL,
  `date_enregistrement` Date DEFAULT (CURRENT_DATE),
  PRIMARY KEY (`id`, `id_Utilisateurs`)
);

CREATE TABLE `reserver` (
  `id` Int NOT NULL,
  `id_Utilisateurs` Int NOT NULL,
  `date_reservation` Datetime NOT NULL,
  `date_experience` Date NOT NULL,
  `heure_debut` Time NOT NULL,
  `heure_fin` Time NOT NULL,
  `nb_places` Int NOT NULL,
  `statut` ENUM ('confirmee', 'annulee', 'passee') DEFAULT 'confirmee',
  `prix` Decimal NOT NULL,
  `peut_re_reserver` Bool DEFAULT true,
  PRIMARY KEY (`id`, `id_Utilisateurs`)
);

CREATE TABLE `ajouterPanier` (
  `id` Int NOT NULL,
  `id_Utilisateurs` Int NOT NULL,
  `nb_places` Int NOT NULL,
  `date_souhaitee` Date,
  `heure_souhaitee` Time,
  PRIMARY KEY (`id`, `id_Utilisateurs`)
);

CREATE TABLE `etre` (
  `id` Int NOT NULL,
  `id_categorie` Int NOT NULL,
  PRIMARY KEY (`id`, `id_categorie`)
);

CREATE TABLE `avoir` (
  `id` Int NOT NULL,
  `id_Experiences` Int NOT NULL,
  PRIMARY KEY (`id`, `id_Experiences`)
);

CREATE TABLE `concerner` (
  `id_Experiences` Int NOT NULL,
  `id_Occasions` Int NOT NULL,
  PRIMARY KEY (`id_Experiences`, `id_Occasions`)
);

CREATE UNIQUE INDEX `Abonnement_Premium_Utilisateurs_AK` ON `Abonnement_Premium` (`id_Utilisateurs`);

ALTER TABLE `Experiences` ADD CONSTRAINT `Experiences_Partenaires_FK` FOREIGN KEY (`id_Partenaires`) REFERENCES `Partenaires` (`id`);

ALTER TABLE `Regles_Disponibilite` ADD CONSTRAINT `Regles_Disponibilite_Experiences_FK` FOREIGN KEY (`id_Experiences`) REFERENCES `Experiences` (`id`);

ALTER TABLE `Exceptions_Disponibilite` ADD CONSTRAINT `Exceptions_Disponibilite_Experiences_FK` FOREIGN KEY (`id_Experiences`) REFERENCES `Experiences` (`id`);

ALTER TABLE `Occasions_Utilisateur` ADD CONSTRAINT `Occasions_Utilisateur_Utilisateurs_FK` FOREIGN KEY (`id_Utilisateurs`) REFERENCES `Utilisateurs` (`id`);

ALTER TABLE `Occasions_Utilisateur` ADD CONSTRAINT `Occasions_Utilisateur_Occasions_FK` FOREIGN KEY (`id_Occasions`) REFERENCES `Occasions` (`id`);

ALTER TABLE `Abonnement_Premium` ADD CONSTRAINT `Abonnement_Premium_Utilisateurs_FK` FOREIGN KEY (`id_Utilisateurs`) REFERENCES `Utilisateurs` (`id`);

ALTER TABLE `avis` ADD CONSTRAINT `avis_Utilisateurs_FK` FOREIGN KEY (`id_Utilisateurs`) REFERENCES `Utilisateurs` (`id`);

ALTER TABLE `avis` ADD CONSTRAINT `avis_Experiences0_FK` FOREIGN KEY (`id_Experiences`) REFERENCES `Experiences` (`id`);

ALTER TABLE `partage_lien` ADD CONSTRAINT `partage_lien_Utilisateurs_FK` FOREIGN KEY (`id_Utilisateurs`) REFERENCES `Utilisateurs` (`id`);

ALTER TABLE `partage_lien` ADD CONSTRAINT `partage_lien_Experiences0_FK` FOREIGN KEY (`id_Experiences`) REFERENCES `Experiences` (`id`);

ALTER TABLE `image` ADD CONSTRAINT `image_avis_FK` FOREIGN KEY (`id`) REFERENCES `avis` (`id`);

ALTER TABLE `image` ADD CONSTRAINT `image_Experiences0_FK` FOREIGN KEY (`id_Experiences`) REFERENCES `Experiences` (`id`);

ALTER TABLE `aimer` ADD CONSTRAINT `aimer_Utilisateurs_FK` FOREIGN KEY (`id`) REFERENCES `Utilisateurs` (`id`);

ALTER TABLE `aimer` ADD CONSTRAINT `aimer_categorie0_FK` FOREIGN KEY (`id_categorie`) REFERENCES `categorie` (`id`);

ALTER TABLE `parrainer` ADD CONSTRAINT `parrainer_Utilisateurs_FK` FOREIGN KEY (`id`) REFERENCES `Utilisateurs` (`id`);

ALTER TABLE `parrainer` ADD CONSTRAINT `parrainer_Utilisateurs0_FK` FOREIGN KEY (`id_Utilisateurs`) REFERENCES `Utilisateurs` (`id`);

ALTER TABLE `enregistrer` ADD CONSTRAINT `enregistrer_Experiences_FK` FOREIGN KEY (`id`) REFERENCES `Experiences` (`id`);

ALTER TABLE `enregistrer` ADD CONSTRAINT `enregistrer_Utilisateurs0_FK` FOREIGN KEY (`id_Utilisateurs`) REFERENCES `Utilisateurs` (`id`);

ALTER TABLE `reserver` ADD CONSTRAINT `reserver_Experiences_FK` FOREIGN KEY (`id`) REFERENCES `Experiences` (`id`);

ALTER TABLE `reserver` ADD CONSTRAINT `reserver_Utilisateurs0_FK` FOREIGN KEY (`id_Utilisateurs`) REFERENCES `Utilisateurs` (`id`);

ALTER TABLE `ajouterPanier` ADD CONSTRAINT `ajouterPanier_Experiences_FK` FOREIGN KEY (`id`) REFERENCES `Experiences` (`id`);

ALTER TABLE `ajouterPanier` ADD CONSTRAINT `ajouterPanier_Utilisateurs0_FK` FOREIGN KEY (`id_Utilisateurs`) REFERENCES `Utilisateurs` (`id`);

ALTER TABLE `etre` ADD CONSTRAINT `etre_Experiences_FK` FOREIGN KEY (`id`) REFERENCES `Experiences` (`id`);

ALTER TABLE `etre` ADD CONSTRAINT `etre_categorie0_FK` FOREIGN KEY (`id_categorie`) REFERENCES `categorie` (`id`);

ALTER TABLE `avoir` ADD CONSTRAINT `avoir_type_experience_FK` FOREIGN KEY (`id`) REFERENCES `type_experience` (`id`);

ALTER TABLE `avoir` ADD CONSTRAINT `avoir_Experiences0_FK` FOREIGN KEY (`id_Experiences`) REFERENCES `Experiences` (`id`);

ALTER TABLE `concerner` ADD CONSTRAINT `concerner_Experiences_FK` FOREIGN KEY (`id_Experiences`) REFERENCES `Experiences` (`id`);

ALTER TABLE `concerner` ADD CONSTRAINT `concerner_Occasions_FK` FOREIGN KEY (`id_Occasions`) REFERENCES `Occasions` (`id`);
