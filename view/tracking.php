<?php
require_once __DIR__ . '/../config/paths.php';
require_once CONFIG_PATH . '/config.php';

$id_livraison = isset($_GET['id_livraison']) ? (int)$_GET['id_livraison'] : 0;

// Récupérer les informations de la livraison
$livraisonInfo = null;
if ($id_livraison > 0) {
    try {
        $pdo = config::getConnexion();
        $sql = "SELECT l.*, 
                       j.titre as nom_jeu,
                       CONCAT('CMD-', l.id_livraison) as numero_commande
                FROM livraisons l
                LEFT JOIN jeu j ON l.id_jeu = j.id_jeu
                WHERE l.id_livraison = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':id' => $id_livraison]);
        $livraisonInfo = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        error_log("Erreur récupération livraison: " . $e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Suivi en temps réel - NextGen</title>
    
    <!-- PWA Manifest -->
    <link rel="manifest" href="../../PROJET_WEB_NEXTGEN-main/public/manifest.json">
    <meta name="theme-color" content="#667eea">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <link rel="apple-touch-icon" href="../../PROJET_WEB_NEXTGEN-main/public/images/icon-192.png">
    
    <!-- MapLibre GL JS -->
    <link rel="stylesheet" href="https://unpkg.com/maplibre-gl@3.6.2/dist/maplibre-gl.css">
    <script src="https://unpkg.com/maplibre-gl@3.6.2/dist/maplibre-gl.js"></script>
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <link rel="stylesheet" href="css/tracking_custom.css">
    
    <style>
        /* Badge Styles - Same as livraison_tracking.php */
        .badge {
            display: inline-block;
            padding: 6px 16px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: bold;
        }
        .badge-commandee, .badge-commandée { background: #f59e0b; color: black; }
        .badge-preparée, .badge-preparee { background: #3b82f6; color: white; }
        .badge-en_route, .badge-en_transit { background: #667eea; color: white; }
        .badge-livree, .badge-livrée { background: #10b981; color: white; }
        .badge-annulée, .badge-annulee { background: #ef4444; color: white; }
        
        /* Voice Control Styles */
        /* Professional Voice Control Button */
        .voice-button {
            position: fixed;
            bottom: 2rem;
            right: 2rem;
            width: 56px;
            height: 56px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea, #764ba2);
            border: none;
            color: white;
            cursor: pointer;
            box-shadow: 0 4px 20px rgba(102, 126, 234, 0.4);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            z-index: 1000;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .voice-button:hover {
            transform: translateY(-2px) scale(1.05);
            box-shadow: 0 8px 30px rgba(102, 126, 234, 0.6);
        }
        
        .voice-button.listening {
            animation: pulse 2s ease-in-out infinite;
            background: linear-gradient(135deg, #10b981, #059669);
        }
        
        .voice-button i {
            font-size: 1.5rem;
        }
        
        @keyframes pulse {
            0%, 100% { 
                transform: scale(1);
                box-shadow: 0 4px 20px rgba(16, 185, 129, 0.4);
            }
            50% { 
                transform: scale(1.1);
                box-shadow: 0 8px 35px rgba(16, 185, 129, 0.7);
            }
        }
        
        /* Smooth Loading Animation */
        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(15, 12, 41, 0.95);
            backdrop-filter: blur(10px);
            z-index: 10000;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            transition: opacity 0.5s ease;
        }
        
        .loading-overlay.hidden {
            opacity: 0;
            pointer-events: none;
        }
        
        .loading-spinner {
            width: 60px;
            height: 60px;
            border: 4px solid rgba(102, 126, 234, 0.2);
            border-top-color: #667eea;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin-bottom: 1.5rem;
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        
        .loading-text {
            color: white;
            font-size: 1.1rem;
            font-weight: 600;
            letter-spacing: 0.5px;
        }
        
        /* Professional Info Card */
        .delivery-info-card {
            background: rgba(26, 26, 46, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            padding: 1.75rem 2rem;
            border: 1px solid rgba(102, 126, 234, 0.2);
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.5);
            transition: all 0.3s ease;
        }
        
        .delivery-info-card:hover {
            border-color: rgba(102, 126, 234, 0.4);
            box-shadow: 0 15px 50px rgba(0, 0, 0, 0.6);
        }
    </style>
</head>
<body>

    <div class="particles" id="particles"></div>
    
    <div id="map"></div>
    
    <!-- Professional Delivery Info Card -->
    <?php if ($livraisonInfo): ?>
    <div style="position: absolute; top: 20px; left: 20px; right: 20px; z-index: 1000; max-width: 1400px; margin: 0 auto; padding: 0;">
        <div class="delivery-info-card">
            <div style="display: flex; justify-content: space-between; align-items: start; flex-wrap: wrap; gap: 1.5rem;">
                <div style="flex: 1; min-width: 300px;">
                    <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 0.75rem;">
                        <div style="width: 48px; height: 48px; background: linear-gradient(135deg, #667eea, #764ba2); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                            <i class="bi bi-box-seam" style="font-size: 1.5rem; color: white;"></i>
                        </div>
                        <div>
                            <h3 style="margin: 0; font-size: 1.5rem; color: white; font-weight: 700; letter-spacing: -0.5px;">
                                <?php echo htmlspecialchars($livraisonInfo['nom_jeu'] ?? 'Jeu'); ?>
                            </h3>
                            <p style="margin: 0.25rem 0 0 0; color: #a5b4fc; font-size: 0.9rem; font-weight: 500;">
                                <i class="bi bi-receipt" style="margin-right: 6px;"></i>
                                Commande <?php echo htmlspecialchars($livraisonInfo['numero_commande'] ?? 'CMD-' . $id_livraison); ?>
                            </p>
                        </div>
                    </div>
                    <div style="margin-top: 1.25rem; display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1.25rem; font-size: 0.9rem;">
                        <div style="display: flex; align-items: start; gap: 0.75rem;">
                            <i class="bi bi-geo-alt-fill" style="color: #8b5cf6; font-size: 1.2rem; margin-top: 2px;"></i>
                            <div>
                                <strong style="color: #8b5cf6; display: block; margin-bottom: 0.25rem; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.5px;">Adresse</strong>
                                <span style="color: #e0e7ff; line-height: 1.5;"><?php echo htmlspecialchars($livraisonInfo['adresse_complete'] ?? 'Non spécifiée'); ?></span>
                            </div>
                        </div>
                        <div style="display: flex; align-items: start; gap: 0.75rem;">
                            <i class="bi bi-currency-euro" style="color: #8b5cf6; font-size: 1.2rem; margin-top: 2px;"></i>
                            <div>
                                <strong style="color: #8b5cf6; display: block; margin-bottom: 0.25rem; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.5px;">Prix livraison</strong>
                                <span style="color: #e0e7ff; font-weight: 600; font-size: 1.05rem;"><?php echo number_format((float)($livraisonInfo['prix_livraison'] ?? 0), 2, ',', ' '); ?> €</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div style="display: flex; align-items: center; gap: 1rem;">
                    <span class="badge badge-<?php echo htmlspecialchars($livraisonInfo['statut'] ?? 'en_route'); ?>" style="font-size: 0.9rem; padding: 0.6rem 1.25rem;">
                        <i class="bi bi-circle-fill" style="font-size: 0.5rem; margin-right: 6px; vertical-align: middle;"></i>
                        <?php 
                        $statut = $livraisonInfo['statut'] ?? 'en_route';
                        echo ucfirst(str_replace(['_', 'ee'], [' ', 'ée'], $statut)); 
                        ?>
                    </span>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Banner removed for cleaner view -->

    <div class="stats-panel">
        <div class="stats-grid" id="stats">
            <div class="stat-card">
                <div class="stat-label">Progression</div>
                <div class="stat-value">0%</div>
                <div class="stat-subtext">Chargement...</div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Distance</div>
                <div class="stat-value">0 km</div>
                <div class="stat-subtext">Calcul...</div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Statut</div>
                <div class="stat-value" style="font-size: 1.4rem;">Chargement...</div>
            </div>
        </div>
    </div>

    <div class="loading-overlay" id="loading">
        <div class="loading-spinner"></div>
        <div class="loading-text">
            <i class="bi bi-geo-alt-fill" style="margin-right: 8px; color: #667eea;"></i>
            Connexion au suivi GPS...
        </div>
        <div style="margin-top: 1rem; width: 200px; height: 4px; background: rgba(102, 126, 234, 0.2); border-radius: 2px; overflow: hidden;">
            <div style="width: 100%; height: 100%; background: linear-gradient(90deg, #667eea, #764ba2); animation: loadingBar 2s ease-in-out infinite;"></div>
        </div>
        <div style="margin-top: 0.75rem; color: rgba(255,255,255,0.6); font-size: 0.9rem;">
            Ouverture du suivi en temps réel...
        </div>
    </div>
    
    <style>
        @keyframes loadingBar {
            0% { transform: translateX(-100%); }
            50% { transform: translateX(0%); }
            100% { transform: translateX(100%); }
        }
    </style>

    <!-- Professional Voice Control Button -->
    <button class="voice-button" id="voice-btn" onclick="if(window.voiceController) voiceController.start(); else Swal.fire({icon: 'info', title: 'Commande vocale', text: 'Fonctionnalité en cours de chargement...', background: '#1a1a2e', color: '#fff', confirmButtonColor: '#667eea'});" title="Commande vocale">
        <i class="bi bi-mic-fill"></i>
    </button>
    
    <!-- Voice Controller Script -->
    <script src="../../PROJET_WEB_NEXTGEN-main/public/js/voice-tracking.js"></script>

    <script>
        
        const particlesContainer = document.getElementById('particles');
        for (let i = 0; i < 30; i++) {
            const particle = document.createElement('div');
            particle.className = 'particle';
            particle.style.left = Math.random() * 100 + '%';
            particle.style.animationDelay = Math.random() * 20 + 's';
            particle.style.animationDuration = (15 + Math.random() * 10) + 's';
            particlesContainer.appendChild(particle);
        }

        const LIVRAISON_ID = <?php echo $id_livraison; ?>;
        let map, carMarker, routeLayerId;

        
        function initMap() {
            fetchTrackingData().then(function(data) {
                if (!data || data.error) {
                    var errorMsg = (data && data.error) ? data.error : 'Données introuvables';
                    document.getElementById('loading').classList.add('hidden');
                    
                    Swal.fire({
                        icon: 'error',
                        title: 'Erreur de connexion',
                        text: errorMsg,
                        confirmButtonText: 'Réessayer',
                        confirmButtonColor: '#667eea',
                        background: '#1a1a2e',
                        color: '#fff',
                        allowOutsideClick: false,
                        allowEscapeKey: false
                    }).then(function() {
                        location.reload();
                    });
                    return;
                }
                setupMapAndStart(data);
            });
        }
        
        function setupMapAndStart(data) {

            
            map = new maplibregl.Map({
                container: 'map',
                style: {
                    version: 8,
                    sources: {
                        'osm': {
                            type: 'raster',
                            tiles: ['https://a.tile.openstreetmap.org/{z}/{x}/{y}.png'],
                            tileSize: 256,
                            attribution: '© OpenStreetMap contributors'
                        }
                    },
                    layers: [{
                        id: 'osm',
                        type: 'raster',
                        source: 'osm',
                        minzoom: 0,
                        maxzoom: 19
                    }]
                },
                center: [data.trajet.position_lng, data.trajet.position_lat],
                zoom: 13,
                pitch: 0,
                bearing: 0
            });

            map.addControl(new maplibregl.NavigationControl({ visualizePitch: true }), 'top-right');
            map.addControl(new maplibregl.FullscreenControl(), 'top-right');

            map.on('load', function() {
                setTimeout(function() {
                    document.getElementById('loading').classList.add('hidden');
                    setTimeout(function() {
                        document.getElementById('loading').style.display = 'none';
                        // Show success notification
                        Swal.fire({
                            icon: 'success',
                            title: 'Connexion établie',
                            text: 'Suivi GPS activé avec succès',
                            confirmButtonText: 'OK',
                            confirmButtonColor: '#667eea',
                            background: '#1a1a2e',
                            color: '#fff',
                            timer: 2000,
                            timerProgressBar: true,
                            showConfirmButton: false
                        });
                    }, 500);
                }, 300);
                setupMap(data);
                startTracking();
            });
        }

        function setupMap(data) {
            
            if (data.route && data.route.length > 0) {
                // Handle both formats: array [lng, lat] or object {lat, lng}
                const routeCoords = data.route.map(p => 
                    Array.isArray(p) ? p : [p.lng, p.lat]
                );
                const currentIndex = data.trajet.current_index || 0;

                
                const completedRoute = routeCoords.slice(0, currentIndex + 1);
                const remainingRoute = routeCoords.slice(currentIndex);

                
                if (completedRoute.length > 1) {
                    map.addSource('route-completed', {
                        type: 'geojson',
                        data: {
                            type: 'Feature',
                            properties: {},
                            geometry: {
                                type: 'LineString',
                                coordinates: completedRoute
                            }
                        }
                    });

                    
                    map.addLayer({
                        id: 'route-completed-glow',
                        type: 'line',
                        source: 'route-completed',
                        layout: {
                            'line-join': 'round',
                            'line-cap': 'round'
                        },
                        paint: {
                            'line-color': '#667eea',
                            'line-width': 20,
                            'line-opacity': 0.3,
                            'line-blur': 6
                        }
                    });

                    map.addLayer({
                        id: 'route-completed-line',
                        type: 'line',
                        source: 'route-completed',
                        layout: {
                            'line-join': 'round',
                            'line-cap': 'round'
                        },
                        paint: {
                            'line-color': [
                                'interpolate',
                                ['linear'],
                                ['line-progress'],
                                0, '#667eea',
                                0.5, '#a855f7',
                                1, '#ec4899'
                            ],
                            'line-width': 10,
                            'line-opacity': 1,
                            'line-gradient': true
                        }
                    });

                    
                    map.addLayer({
                        id: 'route-completed-pulse',
                        type: 'line',
                        source: 'route-completed',
                        layout: {
                            'line-join': 'round',
                            'line-cap': 'round'
                        },
                        paint: {
                            'line-color': '#ffffff',
                            'line-width': 5,
                            'line-opacity': 0.7
                        }
                    });
                }

                
                if (remainingRoute.length > 1) {
                    map.addSource('route-remaining', {
                        type: 'geojson',
                        data: {
                            type: 'Feature',
                            properties: {},
                            geometry: {
                                type: 'LineString',
                                coordinates: remainingRoute
                            }
                        }
                    });

                  
                    map.addLayer({
                        id: 'route-remaining-line',
                        type: 'line',
                        source: 'route-remaining',
                        layout: {
                            'line-join': 'round',
                            'line-cap': 'round'
                        },
                        paint: {
                            'line-color': '#1a1a1a',
                            'line-width': 7,
                            'line-opacity': 0.6,
                            'line-dasharray': [3, 3]
                        }
                    });
                }

                
                const bounds = routeCoords.reduce((bounds, coord) => {
                    return bounds.extend(coord);
                }, new maplibregl.LngLatBounds(routeCoords[0], routeCoords[0]));

                map.fitBounds(bounds, { padding: 120, duration: 1500 });
            }

        
            // Add origin marker with professional icon
            const originEl = document.createElement('div');
            originEl.style.width = '48px';
            originEl.style.height = '48px';
            originEl.style.background = 'linear-gradient(135deg, #10b981, #059669)';
            originEl.style.borderRadius = '50%';
            originEl.style.display = 'flex';
            originEl.style.alignItems = 'center';
            originEl.style.justifyContent = 'center';
            originEl.style.cursor = 'pointer';
            originEl.style.boxShadow = '0 4px 20px rgba(16, 185, 129, 0.5)';
            originEl.style.border = '3px solid white';
            originEl.innerHTML = '<i class="bi bi-geo-alt-fill" style="color: white; font-size: 1.5rem;"></i>';

            new maplibregl.Marker({
                element: originEl,
                anchor: 'center'
            })
            .setLngLat([data.origin.lng, data.origin.lat])
            .setPopup(new maplibregl.Popup({ offset: 25 }).setHTML('<div style="font-weight:700;color:#10b981;display:flex;align-items:center;gap:6px;"><i class="bi bi-geo-alt-fill"></i> Départ</div><div style="font-size:0.9rem;color:#666;margin-top:4px;">Entrepôt</div>'))
            .addTo(map);

            // Add destination marker with professional icon
            const destEl = document.createElement('div');
            destEl.style.width = '48px';
            destEl.style.height = '48px';
            destEl.style.background = 'linear-gradient(135deg, #ef4444, #dc2626)';
            destEl.style.borderRadius = '50%';
            destEl.style.display = 'flex';
            destEl.style.alignItems = 'center';
            destEl.style.justifyContent = 'center';
            destEl.style.cursor = 'pointer';
            destEl.style.boxShadow = '0 4px 20px rgba(239, 68, 68, 0.5)';
            destEl.style.border = '3px solid white';
            destEl.innerHTML = '<i class="bi bi-pin-map-fill" style="color: white; font-size: 1.5rem;"></i>';

            new maplibregl.Marker({
                element: destEl,
                anchor: 'center'
            })
            .setLngLat([data.destination.lng, data.destination.lat])
            .setPopup(new maplibregl.Popup({ offset: 25 }).setHTML('<div style="font-weight:700;color:#ef4444;display:flex;align-items:center;gap:6px;"><i class="bi bi-pin-map-fill"></i> Destination</div><div style="font-size:0.9rem;color:#666;margin-top:4px;">Adresse de livraison</div>'))
            .addTo(map);

            // Create professional delivery truck marker
            const carEl = document.createElement('div');
            carEl.style.width = '56px';
            carEl.style.height = '56px';
            carEl.style.background = 'linear-gradient(135deg, #667eea, #764ba2)';
            carEl.style.borderRadius = '50%';
            carEl.style.display = 'flex';
            carEl.style.alignItems = 'center';
            carEl.style.justifyContent = 'center';
            carEl.style.cursor = 'pointer';
            carEl.style.boxShadow = '0 6px 25px rgba(102, 126, 234, 0.6)';
            carEl.style.border = '3px solid white';
            carEl.style.transition = 'all 0.3s ease';
            carEl.innerHTML = '<i class="bi bi-truck" style="color: white; font-size: 1.8rem;"></i>';

            carEl.onmouseenter = function() {
                carEl.style.transform = 'scale(1.15)';
                carEl.style.boxShadow = '0 8px 35px rgba(102, 126, 234, 0.8)';
            };
            carEl.onmouseleave = function() {
                carEl.style.transform = 'scale(1)';
                carEl.style.boxShadow = '0 6px 25px rgba(102, 126, 234, 0.6)';
            };

            var popupHtml = '<div style="font-weight:700;color:#667eea;display:flex;align-items:center;gap:6px;"><i class="bi bi-truck"></i> En livraison</div>';
            if (data.livraison && data.livraison.id) {
                popupHtml += '<div style="font-size:0.85rem;color:#666;margin-top:4px;"><i class="bi bi-hash" style="font-size:0.7rem;"></i> Livraison #' + data.livraison.id + '</div>';
            }
            if (data.location && data.location.display_name) {
                popupHtml += '<div style="font-size:0.85rem;color:#666;margin-top:6px;max-width:250px;"><i class="bi bi-geo-alt" style="font-size:0.7rem;margin-right:4px;"></i>' + data.location.display_name + '</div>';
            }
            
            carMarker = new maplibregl.Marker({
                element: carEl,
                anchor: 'center',
                pitchAlignment: 'viewport',
                rotationAlignment: 'viewport'
            })
            .setLngLat([data.trajet.position_lng, data.trajet.position_lat])
            .setPopup(new maplibregl.Popup({ offset: 30 }).setHTML(popupHtml))
            .addTo(map);

            updateStats(data);
        }

        function fetchTrackingData() {
            return new Promise(function(resolve, reject) {
                var xhr = new XMLHttpRequest();
                xhr.open('GET', 'api/trajet.php?id_livraison=' + LIVRAISON_ID, true);
                xhr.onreadystatechange = function() {
                    if (xhr.readyState === 4) {
                        if (xhr.status === 200) {
                            try {
                                var data = JSON.parse(xhr.responseText);
                                if (data.error) {
                                    console.error('API returned error:', data.error);
                                }
                                resolve(data);
                            } catch (e) {
                                console.error('JSON parse error:', e);
                                resolve({ error: 'Erreur de parsing des données' });
                            }
                        } else {
                            console.error('API response not OK:', xhr.status, xhr.statusText);
                            resolve({ error: 'Erreur HTTP ' + xhr.status + ': ' + xhr.statusText });
                        }
                    }
                };
                xhr.onerror = function() {
                    console.error('Fetch error');
                    resolve({ error: 'Erreur de connexion' });
                };
                xhr.send();
            });
        }

        function startTracking() {
            let isAnimating = false;
            let animationFrame = null;

            setInterval(function() {
                if (isAnimating) return;

                fetchTrackingData().then(function(data) {
                    if (!data || data.error) return;
                    updateTrackingData(data);
                });
            }, 2000);
            
            function updateTrackingData(data) {

                if (data.livraison && (data.livraison.statut === 'livrée' || data.livraison.statut === 'livree' || data.livraison.statut === 'annulée')) {
                    if (data.livraison.statut === 'livrée' || data.livraison.statut === 'livree') {
                        // Show delivery completed notification only once
                        if (!window.deliveryCompletedShown) {
                            window.deliveryCompletedShown = true;
                            Swal.fire({
                                icon: 'success',
                                title: 'Livraison terminée',
                                text: 'Votre colis a été livré avec succès!',
                                confirmButtonText: 'Fermer',
                                confirmButtonColor: '#10b981',
                                background: '#1a1a2e',
                                color: '#fff',
                                timer: 5000,
                                timerProgressBar: true
                            });
                        }
                    }
                    return;
                }

                // Update car position smoothly
                if (carMarker) {
                    var currentPos = carMarker.getLngLat();
                    var targetPos = { lng: data.trajet.position_lng, lat: data.trajet.position_lat };
                    
                    animateCarMovement(currentPos, targetPos, 2000);
                    
                    // Mettre à jour le popup avec la nouvelle position
                    if (data.location && data.location.display_name) {
                        var popupHtml = '<div style="font-weight:700;color:#667eea;display:flex;align-items:center;gap:6px;"><i class="bi bi-truck"></i> En livraison</div>';
                        if (data.livraison && data.livraison.id) {
                            popupHtml += '<div style="font-size:0.85rem;color:#666;margin-top:4px;"><i class="bi bi-hash" style="font-size:0.7rem;"></i> Livraison #' + data.livraison.id + '</div>';
                        }
                        popupHtml += '<div style="font-size:0.85rem;color:#666;margin-top:6px;max-width:250px;"><i class="bi bi-geo-alt" style="font-size:0.7rem;margin-right:4px;"></i>' + data.location.display_name + '</div>';
                        carMarker.setPopup(new maplibregl.Popup({ offset: 30 }).setHTML(popupHtml));
                    }
                }

                // Update route highlighting based on current progress
                if (data.route && data.route.length > 0) {
                    // Handle both formats: array [lng, lat] or object {lat, lng}
                    const routeCoords = data.route.map(p => 
                        Array.isArray(p) ? p : [p.lng, p.lat]
                    );
                    const currentIndex = data.trajet.current_index || 0;

                    const completedRoute = routeCoords.slice(0, currentIndex + 1);
                    const remainingRoute = routeCoords.slice(currentIndex);

                    // Update completed route
                    if (map.getSource('route-completed') && completedRoute.length > 1) {
                        map.getSource('route-completed').setData({
                            type: 'Feature',
                            properties: {},
                            geometry: {
                                type: 'LineString',
                                coordinates: completedRoute
                            }
                        });
                    }

                    // Update remaining route
                    if (map.getSource('route-remaining') && remainingRoute.length > 1) {
                        map.getSource('route-remaining').setData({
                            type: 'Feature',
                            properties: {},
                            geometry: {
                                type: 'LineString',
                                coordinates: remainingRoute
                            }
                        });
                    }
                }

                updateStats(data);
            }

            function animateCarMovement(start, end, duration) {
                isAnimating = true;
                const startTime = performance.now();
                const startLng = start.lng;
                const startLat = start.lat;
                const endLng = end.lng;
                const endLat = end.lat;

                function animate(currentTime) {
                    const elapsed = currentTime - startTime;
                    const progress = Math.min(elapsed / duration, 1);
                    
                    const eased = progress < 0.5
                        ? 4 * progress * progress * progress
                        : 1 - Math.pow(-2 * progress + 2, 3) / 2;
                    
                    const lng = startLng + (endLng - startLng) * eased;
                    const lat = startLat + (endLat - startLat) * eased;
                    
                    carMarker.setLngLat({ lng, lat });
                    
                    if (progress < 1) {
                        animationFrame = requestAnimationFrame(animate);
                    } else {
                        isAnimating = false;
                    }
                }
                
                if (animationFrame) {
                    cancelAnimationFrame(animationFrame);
                }
                animationFrame = requestAnimationFrame(animate);
            }
        }

        function updateStats(data) {
            var pct = 0;
            var km = '0.0';
            var total = '0.0';
            var status = 'En cours';
            var isDelivered = false;
            
            if (data && data.progress) {
                pct = Math.round(data.progress.progress_pct || 0);
                km = (data.progress.covered_km || 0).toFixed(1);
                total = (data.progress.total_km || 0).toFixed(1);
            }
            
            if (data && data.trajet) {
                status = data.trajet.statut_realtime || 'En cours';
            }
            
            if (data && data.livraison) {
                isDelivered = data.livraison.statut === 'livrée' || data.livraison.statut === 'livree';
            }

            var progressIcon = isDelivered ? '<i class="bi bi-check-circle-fill" style="color: #10b981; font-size: 1.1rem;"></i>' : '<i class="bi bi-arrow-up-circle-fill" style="color: #667eea; font-size: 1.1rem;"></i>';
            var statusIcon = isDelivered ? '<i class="bi bi-check-circle-fill" style="color: #10b981; font-size: 1.3rem;"></i>' : '<i class="bi bi-truck" style="color: #667eea; font-size: 1.3rem;"></i>';
            var statusText = isDelivered ? 'Livrée' : status;
            var subtext = isDelivered ? 'Colis reçu' : 'En déplacement';

            document.getElementById('stats').innerHTML = 
                '<div class="stat-card">' +
                    '<div class="stat-label"><i class="bi bi-graph-up-arrow" style="margin-right: 6px; font-size: 0.9rem;"></i>Progression</div>' +
                    '<div class="stat-value">' + pct + '%</div>' +
                    '<div class="stat-subtext" style="display: flex; align-items: center; justify-content: center; gap: 6px;">' + progressIcon + '<span>' + (isDelivered ? 'Terminé' : 'En cours') + '</span></div>' +
                '</div>' +
                '<div class="stat-card">' +
                    '<div class="stat-label"><i class="bi bi-signpost-2" style="margin-right: 6px; font-size: 0.9rem;"></i>Distance</div>' +
                    '<div class="stat-value">' + km + ' km</div>' +
                    '<div class="stat-subtext">sur ' + total + ' km total</div>' +
                '</div>' +
                '<div class="stat-card">' +
                    '<div class="stat-label"><i class="bi bi-info-circle" style="margin-right: 6px; font-size: 0.9rem;"></i>Statut</div>' +
                    '<div class="stat-value" style="font-size: 1.4rem; display: flex; align-items: center; justify-content: center; gap: 8px;">' + statusIcon + '<span>' + statusText + '</span></div>' +
                    '<div class="stat-subtext">' + subtext + '</div>' +
                '</div>';
        }

        // Start the app
        initMap();
    </script>
</body>
</html>