<?php
require_once 'functions/search_functions.php';
require_once 'functions/user_functions.php';

// Démarrer la session pour les composants header/footer
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Fonction pour obtenir la classe CSS du placeholder selon la catégorie
function getPlaceholderClass($categories) {
    if (empty($categories)) return 'placeholder-default';
    
    $categories_lower = strtolower($categories);
    
    if (strpos($categories_lower, 'gastronomie') !== false) return 'placeholder-gastronomie';
    if (strpos($categories_lower, 'bien-être') !== false || strpos($categories_lower, 'spa') !== false) return 'placeholder-spa';
    if (strpos($categories_lower, 'hébergement') !== false) return 'placeholder-hebergement';
    if (strpos($categories_lower, 'culture') !== false || strpos($categories_lower, 'art') !== false) return 'placeholder-culture';
    if (strpos($categories_lower, 'sport') !== false || strpos($categories_lower, 'aventure') !== false) return 'placeholder-sport';
    if (strpos($categories_lower, 'shopping') !== false || strpos($categories_lower, 'mode') !== false) return 'placeholder-shopping';
    
    return 'placeholder-default';
}

// Récupérer les données de filtres
$categories = obtenirCategories();
$types_experience = obtenirTypesExperience();
$occasions = obtenirOccasions();

// Traitement des filtres
$filtres = [
    'mot_cle' => $_GET['q'] ?? '',
    'prix_min' => $_GET['prix_min'] ?? '',
    'prix_max' => $_GET['prix_max'] ?? '',
    'categorie' => $_GET['categorie'] ?? '',
    'type_experience' => $_GET['type'] ?? '',
    'ville' => $_GET['localisation'] ?? '', // Utiliser 'ville' mais garder 'localisation' pour l'interface
    'est_rare' => isset($_GET['rare']) ? ($_GET['rare'] === '1') : '',
    'est_premium' => isset($_GET['premium']) ? ($_GET['premium'] === '1') : '',
    'occasion' => $_GET['occasion'] ?? '',
    'tri' => $_GET['tri'] ?? 'prix_asc',
    'page' => $_GET['page'] ?? 1,
    'limite' => 12
];

// Rechercher les expériences
$resultats = rechercherExperiences($filtres);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recherche d'expériences - YourVIPDay</title>
    <link rel="stylesheet" href="assets/css/main.css">
    <script src='https://api.mapbox.com/mapbox-gl-js/v2.15.0/mapbox-gl.js'></script>
    <link href='https://api.mapbox.com/mapbox-gl-js/v2.15.0/mapbox-gl.css' rel='stylesheet' />
    <link href='images/placeholder-styles.css' rel='stylesheet' />
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f8f9fa;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .search-header {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .search-form {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr 1fr auto;
            gap: 15px;
            align-items: end;
        }
        
        .form-group {
            display: flex;
            flex-direction: column;
        }
        
        .form-group label {
            margin-bottom: 5px;
            font-weight: 600;
            color: #333;
        }
        
        .form-group input, .form-group select {
            padding: 10px;
            border: 2px solid #e1e1e1;
            border-radius: 5px;
            font-size: 14px;
        }
        
        .form-group input:focus, .form-group select:focus {
            outline: none;
            border-color: #007bff;
        }
        
        .btn-search {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 600;
        }
        
        .filters-section {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .filters-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
        }
        
        .checkbox-group {
            display: flex;
            gap: 20px;
            margin-top: 10px;
        }
        
        .checkbox-group label {
            display: flex;
            align-items: center;
            gap: 5px;
            cursor: pointer;
        }
        
        .main-content {
            display: grid;
            grid-template-columns: 1fr 400px;
            gap: 20px;
        }
        
        .results-section {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .results-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            border-bottom: 1px solid #eee;
            padding-bottom: 15px;
        }
        
        .sort-select {
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        
        .experiences-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 20px;
        }
        
        .experience-card {
            border: 1px solid #eee;
            border-radius: 10px;
            overflow: hidden;
            transition: transform 0.3s, box-shadow 0.3s;
        }
        
        .experience-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.15);
        }
        
        .card-image {
            height: 200px;
            position: relative;
            overflow: hidden;
        }

        .card-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }
        
        .card-badges {
            position: absolute;
            top: 10px;
            left: 10px;
            display: flex;
            gap: 5px;
            z-index: 2;
        }
        
        .badge {
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .badge-premium {
            background: gold;
            color: #333;
        }
        
        .badge-rare {
            background: #dc3545;
            color: white;
        }
        
        .card-content {
            padding: 15px;
        }
        
        .card-title {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 10px;
            color: #333;
        }
        
        .card-description {
            color: #666;
            font-size: 14px;
            margin-bottom: 15px;
            overflow: hidden;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
        }
        
        .card-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .card-price {
            font-size: 20px;
            font-weight: 700;
            color: #007bff;
        }
        
        .card-location {
            font-size: 12px;
            color: #888;
        }
        
        .map-section {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            height: fit-content;
            position: sticky;
            top: 20px;
        }
        
        #map {
            height: 400px;
            border-radius: 10px;
        }
        
        .pagination {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-top: 30px;
        }
        
        .pagination a, .pagination span {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            text-decoration: none;
            color: #333;
        }
        
        .pagination .current {
            background: #007bff;
            color: white;
            border-color: #007bff;
        }
        
        .pagination a:hover {
            background: #f8f9fa;
        }
        
        @media (max-width: 768px) {
            .search-form {
                grid-template-columns: 1fr;
            }
            
            .main-content {
                grid-template-columns: 1fr;
            }
            
            .map-section {
                position: static;
            }
            
            .experiences-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <?php include 'partials/header.php'; ?>
    
    <div class="container">
        <!-- En-tête de recherche -->
        <div class="search-header">
            <form method="GET" class="search-form">
                <div class="form-group">
                    <label for="q">Rechercher une expérience</label>
                    <input type="text" id="q" name="q" placeholder="Dîner étoilé, spa de luxe..." value="<?= htmlspecialchars($filtres['mot_cle']) ?>">
                </div>
                
                <div class="form-group">
                    <label for="localisation">Localisation</label>
                    <input type="text" id="localisation" name="localisation" placeholder="Paris, Lyon..." value="<?= htmlspecialchars($_GET['localisation'] ?? '') ?>">
                </div>
                
                <div class="form-group">
                    <label for="prix_min">Prix min (€)</label>
                    <input type="number" id="prix_min" name="prix_min" value="<?= htmlspecialchars($filtres['prix_min']) ?>">
                </div>
                
                <div class="form-group">
                    <label for="prix_max">Prix max (€)</label>
                    <input type="number" id="prix_max" name="prix_max" value="<?= htmlspecialchars($filtres['prix_max']) ?>">
                </div>
                
                <button type="submit" class="btn-search">Rechercher</button>
            </form>
        </div>
        
        <!-- Filtres avancés -->
        <div class="filters-section">
            <h3>Filtres</h3>
            <form method="GET">
                <input type="hidden" name="q" value="<?= htmlspecialchars($filtres['mot_cle']) ?>">
                <input type="hidden" name="localisation" value="<?= htmlspecialchars($_GET['localisation'] ?? '') ?>">
                <input type="hidden" name="prix_min" value="<?= htmlspecialchars($filtres['prix_min']) ?>">
                <input type="hidden" name="prix_max" value="<?= htmlspecialchars($filtres['prix_max']) ?>">
                
                <div class="filters-grid">
                    <div class="form-group">
                        <label for="categorie">Catégorie</label>
                        <select id="categorie" name="categorie">
                            <option value="">Toutes les catégories</option>
                            <?php foreach ($categories as $categorie): ?>
                                <option value="<?= $categorie['id'] ?>" <?= $filtres['categorie'] == $categorie['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($categorie['nom']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="type">Type d'expérience</label>
                        <select id="type" name="type">
                            <option value="">Tous les types</option>
                            <?php foreach ($types_experience as $type): ?>
                                <option value="<?= $type['id'] ?>" <?= $filtres['type_experience'] == $type['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($type['nom']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="occasion">Occasion</label>
                        <select id="occasion" name="occasion">
                            <option value="">Toutes les occasions</option>
                            <?php foreach ($occasions as $occasion): ?>
                                <option value="<?= $occasion['id'] ?>" <?= $filtres['occasion'] == $occasion['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($occasion['nom']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Spécialités</label>
                        <div class="checkbox-group">
                            <label>
                                <input type="checkbox" name="premium" value="1" <?= $filtres['est_premium'] ? 'checked' : '' ?>>
                                Premium
                            </label>
                            <label>
                                <input type="checkbox" name="rare" value="1" <?= $filtres['est_rare'] ? 'checked' : '' ?>>
                                Rare
                            </label>
                        </div>
                    </div>
                </div>
                
                <button type="submit" class="btn-search" style="margin-top: 15px;">Appliquer les filtres</button>
            </form>
        </div>
        
        <!-- Contenu principal -->
        <div class="main-content">
            <!-- Résultats -->
            <div class="results-section">
                <div class="results-header">
                    <h2><?= $resultats['total'] ?> expérience(s) trouvée(s)</h2>
                    <form method="GET" style="display: inline;">
                        <input type="hidden" name="q" value="<?= htmlspecialchars($filtres['mot_cle']) ?>">
                        <input type="hidden" name="localisation" value="<?= htmlspecialchars($_GET['localisation'] ?? '') ?>">
                        <input type="hidden" name="prix_min" value="<?= htmlspecialchars($filtres['prix_min']) ?>">
                        <input type="hidden" name="prix_max" value="<?= htmlspecialchars($filtres['prix_max']) ?>">
                        <input type="hidden" name="categorie" value="<?= htmlspecialchars($filtres['categorie']) ?>">
                        <input type="hidden" name="type" value="<?= htmlspecialchars($filtres['type_experience']) ?>">
                        <input type="hidden" name="occasion" value="<?= htmlspecialchars($filtres['occasion']) ?>">
                        <?php if ($filtres['est_premium']): ?><input type="hidden" name="premium" value="1"><?php endif; ?>
                        <?php if ($filtres['est_rare']): ?><input type="hidden" name="rare" value="1"><?php endif; ?>
                        
                        <select name="tri" class="sort-select" onchange="this.form.submit()">
                            <option value="prix_asc" <?= $filtres['tri'] == 'prix_asc' ? 'selected' : '' ?>>Prix croissant</option>
                            <option value="prix_desc" <?= $filtres['tri'] == 'prix_desc' ? 'selected' : '' ?>>Prix décroissant</option>
                            <option value="titre" <?= $filtres['tri'] == 'titre' ? 'selected' : '' ?>>Alphabétique</option>
                            <option value="popularite" <?= $filtres['tri'] == 'popularite' ? 'selected' : '' ?>>Popularité</option>
                        </select>
                    </form>
                </div>
                
                <div class="experiences-grid">
                    <?php foreach ($resultats['experiences'] as $experience): ?>
                        <div class="experience-card">
                            <div class="card-image image-container">
                                <?php if (!empty($experience['image_principale'])): ?>
                                    <img src="assets/img/experiences/<?= htmlspecialchars($experience['image_principale']) ?>" 
                                         alt="<?= htmlspecialchars($experience['titre']) ?>" 
                                         class="experience-image"
                                         onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                    <div class="placeholder-image <?= getPlaceholderClass($experience['categories']) ?>" style="display: none;"></div>
                                <?php else: ?>
                                    <div class="placeholder-image <?= getPlaceholderClass($experience['categories']) ?>"></div>
                                <?php endif; ?>
                                
                                <div class="card-badges">
                                    <?php if ($experience['est_premium']): ?>
                                        <span class="badge badge-premium">Premium</span>
                                    <?php endif; ?>
                                    <?php if ($experience['est_rare']): ?>
                                        <span class="badge badge-rare">Rare</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="card-content">
                                <h3 class="card-title"><?= htmlspecialchars($experience['titre']) ?></h3>
                                <p class="card-description"><?= htmlspecialchars($experience['description']) ?></p>
                                <div class="card-footer">
                                    <span class="card-price"><?= number_format($experience['prix'], 0, ',', ' ') ?> €</span>
                                    <span class="card-location"><?= htmlspecialchars($experience['ville'] ?? 'Non spécifiée') ?></span>
                                </div>
                                <a href="experience.php?id=<?= $experience['id'] ?>" style="display: block; text-align: center; margin-top: 15px; padding: 10px; background: #007bff; color: white; text-decoration: none; border-radius: 5px;">Voir les détails</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <!-- Pagination -->
                <?php if ($resultats['pages_total'] > 1): ?>
                    <div class="pagination">
                        <?php for ($i = 1; $i <= $resultats['pages_total']; $i++): ?>
                            <?php
                            $url_params = $_GET;
                            $url_params['page'] = $i;
                            $url = '?' . http_build_query($url_params);
                            ?>
                            <?php if ($i == $resultats['page']): ?>
                                <span class="current"><?= $i ?></span>
                            <?php else: ?>
                                <a href="<?= $url ?>"><?= $i ?></a>
                            <?php endif; ?>
                        <?php endfor; ?>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Carte -->
            <div class="map-section">
                <h3>Localisation sur la carte</h3>
                <div id="map"></div>
            </div>
        </div>
    </div>
    
    <script>
        // Configuration Mapbox (vous devez remplacer par votre token)
        mapboxgl.accessToken = 'pk.eyJ1Ijoidml0cnl3ZWIiLCJhIjoiY203MGdyYWF1MDIxajJqcGJxMXA4YXZxNiJ9.9nbfBOY5bGLxB6dH28xEFg';
        
        const map = new mapboxgl.Map({
            container: 'map',
            style: 'mapbox://styles/mapbox/streets-v11',
            center: [2.3522, 48.8566], // Paris par défaut
            zoom: 10
        });

        // Demander la géolocalisation de l'utilisateur
        if ("geolocation" in navigator) {
            navigator.geolocation.getCurrentPosition(
                (position) => {
                    userLocation = [position.coords.longitude, position.coords.latitude];
                    console.log("Position de l'utilisateur :", userLocation);
                    
                    // Centrer la carte sur la position de l'utilisateur
                    map.setCenter(userLocation);
                    map.setZoom(14);
                    
                    // Ajouter un marqueur pour la position de l'utilisateur
                    new mapboxgl.Marker({ color: 'red' })
                        .setLngLat(userLocation)
                        .setPopup(new mapboxgl.Popup().setText('Votre position'))
                        .addTo(map);
                },
                (error) => {
                    console.error("Erreur de géolocalisation :", error);
                    let errorMessage = "Impossible d'obtenir votre position.";
                    
                    switch(error.code) {
                        case error.PERMISSION_DENIED:
                            errorMessage = "Géolocalisation refusée. Veuillez autoriser l'accès à votre position.";
                            break;
                        case error.POSITION_UNAVAILABLE:
                            errorMessage = "Position non disponible.";
                            break;
                        case error.TIMEOUT:
                            errorMessage = "Délai d'attente dépassé pour la géolocalisation.";
                            break;
                    }
                    
                    console.warn(errorMessage);
                },
                {
                    enableHighAccuracy: true,
                    timeout: 10000,
                    maximumAge: 600000 // Cache la position pendant 10 minutes
                }
            );
        } else {
            console.warn("La géolocalisation n'est pas supportée par votre navigateur.");
        }

        // Ajouter les marqueurs pour les expériences
        <?php if (!empty($resultats['experiences'])): ?>
            const experiences = <?= json_encode($resultats['experiences']) ?>;
            const validExperiences = [];
            
            experiences.forEach(experience => {
                // Utiliser les coordonnées réelles de la base de données
                const lng = parseFloat(experience.longitude);
                const lat = parseFloat(experience.latitude);
                
                // Vérifier que les coordonnées sont valides
                if (!isNaN(lng) && !isNaN(lat) && lng !== 0 && lat !== 0) {
                    validExperiences.push({lng, lat, experience});
                    
                    const popup = new mapboxgl.Popup({ offset: 25 })
                        .setHTML(`
                            <div style="padding: 10px;">
                                <h4 style="margin: 0 0 10px 0;">${experience.titre}</h4>
                                <p style="margin: 0 0 5px 0; font-weight: bold; color: #007bff;">${experience.prix} €</p>
                                <p style="margin: 0 0 10px 0; color: #666;">${experience.ville || 'Localisation non spécifiée'}</p>
                                <a href="experience.php?id=${experience.id}" style="background: #007bff; color: white; padding: 5px 10px; text-decoration: none; border-radius: 3px; font-size: 12px;">Voir détails</a>
                            </div>
                        `);
                    
                    new mapboxgl.Marker({
                        color: experience.est_premium ? '#FFD700' : (experience.est_rare ? '#DC3545' : '#007bff')
                    })
                        .setLngLat([lng, lat])
                        .setPopup(popup)
                        .addTo(map);
                }
            });
            
            // Ajuster la vue pour inclure tous les marqueurs valides
            if (validExperiences.length > 0) {
                if (validExperiences.length === 1) {
                    // Une seule expérience, centrer dessus
                    map.setCenter([validExperiences[0].lng, validExperiences[0].lat]);
                    map.setZoom(14);
                } else {
                    // Plusieurs expériences, ajuster la vue pour toutes les inclure
                    const bounds = new mapboxgl.LngLatBounds();
                    validExperiences.forEach(exp => {
                        bounds.extend([exp.lng, exp.lat]);
                    });
                    map.fitBounds(bounds, {padding: 50});
                }
            }
        <?php endif; ?>
    </script>
    
    <?php include 'partials/footer.php'; ?>
</body>
</html>
