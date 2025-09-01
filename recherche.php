<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Carte Mapbox</title>
    <script src='https://api.mapbox.com/mapbox-gl-js/v2.9.1/mapbox-gl.js'></script>
    <link href='https://api.mapbox.com/mapbox-gl-js/v2.9.1/mapbox-gl.css' rel='stylesheet' />
    <script src="config.js"></script>
    <style>
        #map {
            width: 100%;
            height: 100vh;
        }
        body {
            margin: 0;
            padding: 0;
        }
    </style>
</head>
<body>
    <div id="map"></div>
    
    
    <script>
        // Initialisation de la carte Mapbox
        mapboxgl.accessToken = CONFIG.MAPBOX_TOKEN;
        const map = new mapboxgl.Map({
            container: 'map',
            style: 'mapbox://styles/mapbox/streets-v11',
            center: [2.3522, 48.8566], // Paris par défaut
            zoom: 12,
            attributionControl: true, // Garder les attributions (requis par Mapbox)
            trackResize: true,
            refreshExpiredTiles: false,
            // Options pour réduire les requêtes
            collectResourceTiming: false,
            // Désactiver les événements analytics qui causent les erreurs CORS
            transformRequest: (url, resourceType) => {
                // Bloquer les requêtes vers events.mapbox.com
                if (url.includes('events.mapbox.com')) {
                    return { url: url, credentials: 'omit' };
                }
                return { url: url };
            }
        });

        let userLocation = null;

        // Attendre que la carte soit chargée avant d'ajouter des fonctionnalités
        map.on('load', () => {
            console.log('Carte chargée avec succès');
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

        // Gérer les erreurs de la carte
        map.on('error', (e) => {
            console.error('Erreur Mapbox:', e);
        });

        // Réduire les avertissements WebGL (ces avertissements sont souvent inévitables)
        const originalWarn = console.warn;
        console.warn = function(...args) {
            const message = args.join(' ');
            if (message.includes('WebGL warning') && message.includes('Alpha-premult')) {
                return; // Ignorer ces avertissements spécifiques
            }
            originalWarn.apply(console, args);
        };
    </script>
</body>
</html>