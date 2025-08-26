<?php
require_once '../src/config/db.php';

/**
 * Fonction pour obtenir toutes les catégories
 */
function obtenirCategories() {
    $pdo = getDbConnection();
    try {
        $stmt = $pdo->query("SELECT id, nom FROM categorie ORDER BY nom");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Erreur obtenirCategories : " . $e->getMessage());
        return [];
    }
}

/**
 * Fonction pour obtenir tous les types d'expérience
 */
function obtenirTypesExperience() {
    $pdo = getDbConnection();
    try {
        $stmt = $pdo->query("SELECT id, nom FROM type_experience ORDER BY nom");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Erreur obtenirTypesExperience : " . $e->getMessage());
        return [];
    }
}

/**
 * Fonction pour obtenir toutes les occasions
 */
function obtenirOccasions() {
    $pdo = getDbConnection();
    try {
        $stmt = $pdo->query("SELECT id, nom FROM Occasions ORDER BY nom");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Erreur obtenirOccasions : " . $e->getMessage());
        return [];
    }
}

/**
 * Fonction pour rechercher des expériences avec filtres
 */
function rechercherExperiences($filtres = []) {
    $pdo = getDbConnection();
    
    try {
        // Construction de la requête de base
        $sql_base = "
            FROM Experiences e
            LEFT JOIN Partenaires p ON e.id_Partenaires = p.id
            WHERE 1=1
        ";
        
        $params = [];
        
        // Filtre par prix
        if (!empty($filtres['prix_min'])) {
            $sql_base .= " AND e.prix >= ?";
            $params[] = $filtres['prix_min'];
        }
        
        if (!empty($filtres['prix_max'])) {
            $sql_base .= " AND e.prix <= ?";
            $params[] = $filtres['prix_max'];
        }
        
        // Filtre par ville
        if (!empty($filtres['ville'])) {
            $sql_base .= " AND e.ville LIKE ?";
            $params[] = '%' . $filtres['ville'] . '%';
        }
        
        // Filtre par rareté
        if (isset($filtres['est_rare']) && $filtres['est_rare'] !== '') {
            $sql_base .= " AND e.est_rare = ?";
            $params[] = $filtres['est_rare'] ? 1 : 0;
        }
        
        // Filtre premium
        if (isset($filtres['est_premium']) && $filtres['est_premium'] !== '') {
            $sql_base .= " AND e.est_premium = ?";
            $params[] = $filtres['est_premium'] ? 1 : 0;
        }
        
        // Filtre par mot-clé
        if (!empty($filtres['mot_cle'])) {
            $sql_base .= " AND (e.titre LIKE ? OR e.description LIKE ?)";
            $mot_cle = '%' . $filtres['mot_cle'] . '%';
            $params[] = $mot_cle;
            $params[] = $mot_cle;
        }
        
        // Compter le total
        $sql_count = "SELECT COUNT(*) as total " . $sql_base;
        $stmt_count = $pdo->prepare($sql_count);
        $stmt_count->execute($params);
        $total = $stmt_count->fetch()['total'];
        
        // Requête principale
        $sql = "
            SELECT e.*, 
                   p.nom as nom_partenaire,
                   (SELECT img.lien FROM image img WHERE img.id_Experiences = e.id LIMIT 1) as image_principale
        " . $sql_base;
        
        // Tri
        $tri = $filtres['tri'] ?? 'prix_asc';
        switch ($tri) {
            case 'prix_desc':
                $sql .= " ORDER BY e.prix DESC";
                break;
            case 'titre':
                $sql .= " ORDER BY e.titre ASC";
                break;
            case 'popularite':
                $sql .= " ORDER BY e.nb_places_total DESC";
                break;
            default:
                $sql .= " ORDER BY e.prix ASC";
        }
        
        // Pagination
        $page = max(1, intval($filtres['page'] ?? 1));
        $limite = intval($filtres['limite'] ?? 12);
        $offset = ($page - 1) * $limite;
        
        $sql .= " LIMIT " . $offset . ", " . $limite;
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $experiences = $stmt->fetchAll();
        
        // Ajouter les catégories, types et occasions pour chaque expérience
        foreach ($experiences as &$exp) {
            // Catégories
            $stmt_cat = $pdo->prepare("
                SELECT c.nom 
                FROM categorie c 
                JOIN etre et ON c.id = et.id_categorie 
                WHERE et.id = ?
            ");
            $stmt_cat->execute([$exp['id']]);
            $categories = $stmt_cat->fetchAll(PDO::FETCH_COLUMN);
            $exp['categories'] = implode(', ', $categories);
            
            // Types d'expérience
            $stmt_type = $pdo->prepare("
                SELECT te.nom 
                FROM type_experience te 
                JOIN avoir av ON te.id = av.id 
                WHERE av.id_Experiences = ?
            ");
            $stmt_type->execute([$exp['id']]);
            $types = $stmt_type->fetchAll(PDO::FETCH_COLUMN);
            $exp['types_experience'] = implode(', ', $types);
            
            // Occasions
            $stmt_occ = $pdo->prepare("
                SELECT o.nom 
                FROM Occasions o 
                JOIN concerner co ON o.id = co.id_Occasions 
                WHERE co.id_Experiences = ?
            ");
            $stmt_occ->execute([$exp['id']]);
            $occasions = $stmt_occ->fetchAll(PDO::FETCH_COLUMN);
            $exp['occasions'] = implode(', ', $occasions);
        }
        
        return [
            'experiences' => $experiences,
            'total' => $total,
            'page' => $page,
            'pages_total' => ceil($total / $limite)
        ];
        
    } catch (PDOException $e) {
        error_log("Erreur recherche : " . $e->getMessage());
        return [
            'experiences' => [],
            'total' => 0,
            'page' => 1,
            'pages_total' => 0
        ];
    }
}

/**
 * Fonction pour obtenir une expérience par ID avec tous ses détails
 */
function obtenirExperienceComplete($experience_id) {
    $pdo = getDbConnection();
    
    try {
        // Informations de base de l'expérience
        $stmt = $pdo->prepare("
            SELECT e.*, p.nom as nom_partenaire, p.email as email_partenaire, p.telephone as telephone_partenaire
            FROM Experiences e
            LEFT JOIN Partenaires p ON e.id_Partenaires = p.id
            WHERE e.id = ?
        ");
        $stmt->execute([$experience_id]);
        $experience = $stmt->fetch();
        
        if (!$experience) {
            return null;
        }
        
        // Catégories
        $stmt = $pdo->prepare("
            SELECT c.* FROM categorie c
            JOIN etre et ON c.id = et.id_categorie
            WHERE et.id = ?
        ");
        $stmt->execute([$experience_id]);
        $experience['categories'] = $stmt->fetchAll();
        
        // Types d'expérience
        $stmt = $pdo->prepare("
            SELECT te.* FROM type_experience te
            JOIN avoir av ON te.id = av.id
            WHERE av.id_Experiences = ?
        ");
        $stmt->execute([$experience_id]);
        $experience['types'] = $stmt->fetchAll();
        
        // Occasions
        $stmt = $pdo->prepare("
            SELECT o.* FROM Occasions o
            JOIN concerner co ON o.id = co.id_Occasions
            WHERE co.id_Experiences = ?
        ");
        $stmt->execute([$experience_id]);
        $experience['occasions'] = $stmt->fetchAll();
        
        // Images
        $stmt = $pdo->prepare("SELECT * FROM image WHERE id_Experiences = ?");
        $stmt->execute([$experience_id]);
        $experience['images'] = $stmt->fetchAll();
        
        // Avis
        $stmt = $pdo->prepare("
            SELECT a.*, u.nom, u.prenom, u.photo 
            FROM avis a
            JOIN Utilisateurs u ON a.id_Utilisateurs = u.id
            WHERE a.id_Experiences = ?
            ORDER BY a.date DESC
        ");
        $stmt->execute([$experience_id]);
        $experience['avis'] = $stmt->fetchAll();
        
        // Note moyenne
        $stmt = $pdo->prepare("
            SELECT AVG(note) as note_moyenne, COUNT(*) as nb_avis 
            FROM avis 
            WHERE id_Experiences = ?
        ");
        $stmt->execute([$experience_id]);
        $stats_avis = $stmt->fetch();
        $experience['note_moyenne'] = $stats_avis['note_moyenne'] ? round($stats_avis['note_moyenne'], 1) : 0;
        $experience['nb_avis'] = $stats_avis['nb_avis'] ?? 0;
        
        return $experience;
        
    } catch (PDOException $e) {
        error_log("Erreur expérience complète : " . $e->getMessage());
        return null;
    }
}

/**
 * Fonction pour obtenir les expériences géolocalisées pour Mapbox
 */
function obtenirExperiencesGeolocalisees($filtres = []) {
    $resultats = rechercherExperiences($filtres);
    $experiences_geo = [];
    
    foreach ($resultats['experiences'] as $experience) {
        $experiences_geo[] = [
            'type' => 'Feature',
            'geometry' => [
                'type' => 'Point',
                'coordinates' => [
                    floatval($experience['longitude'] ?? 0), 
                    floatval($experience['latitude'] ?? 0)
                ]
            ],
            'properties' => [
                'id' => $experience['id'],
                'titre' => $experience['titre'],
                'prix' => $experience['prix'],
                'ville' => $experience['ville'],
                'est_premium' => $experience['est_premium'],
                'est_rare' => $experience['est_rare']
            ]
        ];
    }
    
    return [
        'type' => 'FeatureCollection',
        'features' => $experiences_geo
    ];
}
?>
