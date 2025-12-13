<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes livraisons - NextGen</title>
    
    <!-- PWA Manifest -->
    <link rel="manifest" href="../../PROJET_WEB_NEXTGEN-main/public/manifest.json">
    <meta name="theme-color" content="#667eea">
    
    <!-- Fonts & Icons -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&family=Orbitron:wght@700;900&family=Rajdhani:wght@600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <!-- MapLibre GL JS for Real-Time Tracking -->
    <link rel="stylesheet" href="https://unpkg.com/maplibre-gl@3.6.2/dist/maplibre-gl.css">
    <script src="https://unpkg.com/maplibre-gl@3.6.2/dist/maplibre-gl.js"></script>
    
    <!-- Styles -->
    <link rel="stylesheet" href="../../PROJET_WEB_NEXTGEN-main/public/css/friend_styles.css">
    <link rel="stylesheet" href="../../PROJET_WEB_NEXTGEN-main/public/vendor/leaflet/leaflet.css">
    <link rel="stylesheet" href="../../PROJET_WEB_NEXTGEN-main/public/assets/css/mobile-optimizations.css">
    
    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <link rel="stylesheet" href="../../PROJET_WEB_NEXTGEN-main/public/css/livraison_view_custom.css">
</head>
<body>
    <main>
        <header style="text-align: center; margin-bottom: 3rem; background: transparent !important; box-shadow: none !important; border: none !important;">
            <div style="display: inline-flex; align-items: center; gap: 1rem; margin-bottom: 1rem; padding: 1rem 2rem; background: rgba(102, 126, 234, 0.1); border-radius: 20px; border: 1px solid rgba(102, 126, 234, 0.3);">
                <div style="width: 64px; height: 64px; background: linear-gradient(135deg, #667eea, #764ba2); border-radius: 16px; display: flex; align-items: center; justify-content: center; box-shadow: 0 8px 24px rgba(102, 126, 234, 0.4);">
                    <i class="bi bi-truck" style="font-size: 2.5rem; color: white;"></i>
                </div>
                <div style="text-align: left;">
                    <p style="color: #ec4899; font-weight: bold; letter-spacing: 2px; text-transform: uppercase; margin: 0; font-size: 0.9rem;">NextGen • Livraison</p>
                    <h1 style="font-size: 2.5rem; margin: 0.25rem 0; color: white; text-shadow: 0 0 20px rgba(139,92,246,0.5);">ESPACE LIVRAISONS</h1>
                </div>
            </div>
            <p style="font-size: 1.2rem; color: #a5b4fc; margin-top: 1rem;">
                <?php if ($profil): ?>
                    <i class="bi bi-person-circle" style="margin-right: 0.5rem;"></i>Bonjour <span style="color: #00ffc3;"><?php echo htmlspecialchars($profil['prenom']); ?></span>, gère tes commandes et suis tes livraisons en temps réel.
                <?php else: ?>
                    <i class="bi bi-box-seam" style="margin-right: 0.5rem;"></i>Gère tes commandes et suis tes livraisons en temps réel.
                <?php endif; ?>
            </p>
        </header>

        <?php if (!empty($message)): ?>
            <script>
                Swal.fire({
                    icon: '<?php echo $messageType === 'success' ? 'success' : 'info'; ?>',
                    title: '<?php echo $messageType === 'success' ? 'Succès' : 'Information'; ?>',
                    text: '<?php echo addslashes($message); ?>',
                    background: '#1a1a2e',
                    color: '#fff',
                    confirmButtonColor: '#8b5cf6'
                });
            </script>
        <?php endif; ?>

        <!-- SECTION 1: COMMANDES A LIVRER -->
        <section class="card">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
                <div>
                    <h2 style="margin: 0; color: #00ffc3;">1. Mes Commandes</h2>
                    <p style="margin: 0.5rem 0 0; color: #ccc;">Choisis une commande à faire livrer</p>
                </div>
                <!-- Simuler achat btn -->
                <form method="post" style="margin:0;">
                    <input type="hidden" name="action" value="creer_commande">
                    <input type="hidden" name="id_jeu" value="<?php echo $jeux[0]['id_jeu'] ?? 1; ?>">
                    <button type="submit" style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.7rem 1.3rem; background: linear-gradient(135deg, #8b5cf6, #7c3aed); color: white; border: none; border-radius: 10px; font-weight: 600; font-size: 0.85rem; cursor: pointer; transition: all 0.3s ease; box-shadow: 0 4px 12px rgba(139, 92, 246, 0.4);">
                        <i class="bi bi-plus-circle-fill"></i>
                        <span>Simuler un achat</span>
                    </button>
                </form>
            </div>

            <?php if (empty($commandes)): ?>
                <div style="text-align: center; padding: 2rem; border: 2px dashed rgba(255,255,255,0.1); border-radius: 1rem;">
                    <i class="bi bi-cart-x" style="font-size: 3rem; color: #666;"></i>
                    <p>Aucune commande en attente de livraison.</p>
                </div>
            <?php else: ?>
                <div class="commandes-grid">
                    <?php foreach ($commandes as $commande): ?>
                        <article class="commande-card">
                            <div style="display: flex; justify-content: space-between; align-items: start;">
                                <div>
                                    <span style="color: #ec4899; font-size: 0.8rem; font-weight: bold;">#<?php echo htmlspecialchars($commande['numero_commande']); ?></span>
                                    <h3 style="margin: 0.5rem 0;"><?php echo htmlspecialchars($commande['nom_jeu'] ?? 'Jeu mystère'); ?></h3>
                                    <p style="font-size: 0.9rem; color: #aaa;">
                                        <i class="bi bi-calendar"></i> <?php echo htmlspecialchars(date('d/m/Y', strtotime($commande['date_commande']))); ?>
                                    </p>
                                </div>
                                <div style="text-align: right;">
                                    <p style="font-size: 1.2rem; font-weight: bold; color: #00ffc3; margin: 0;">
                                        <?php echo number_format((float)$commande['total'], 2, ',', ' '); ?> €
                                    </p>
                                </div>
                            </div>
                            
                            <?php if (in_array($commande['id_commande'], $idsCommandesLivrees ?? [])): ?>
                                <button 
                                    class="btn secondary"
                                    type="button"
                                    disabled
                                    style="width: 100%; margin-top: 1rem; opacity: 0.5; cursor: not-allowed; display: flex; align-items: center; justify-content: center; gap: 0.5rem; padding: 0.875rem; background: rgba(255,255,255,0.1); color: rgba(255,255,255,0.6); border: 1px solid rgba(255,255,255,0.2); border-radius: 12px; font-weight: 500;"
                                >
                                    <i class="bi bi-check-circle-fill" style="color: #10b981;"></i> 
                                    <span>Livraison déjà planifiée</span>
                                </button>
                            <?php else: ?>
                                <button 
                                    class="btn primary planifier-btn"
                                    type="button"
                                    style="width: 100%; margin-top: 1rem; display: flex; align-items: center; justify-content: center; gap: 0.5rem; padding: 0.875rem; background: linear-gradient(135deg, #667eea, #764ba2); color: white; border: none; border-radius: 12px; font-weight: 600; cursor: pointer; transition: all 0.3s ease; box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);"
                                    data-commande="<?php echo (int)$commande['id_commande']; ?>"
                                    data-commande-label="#<?php echo htmlspecialchars($commande['numero_commande']); ?> - <?php echo htmlspecialchars($commande['nom_jeu'] ?? 'Jeu'); ?>"
                                >
                                    <i class="bi bi-truck" style="font-size: 1.1rem;"></i> 
                                    <span>Planifier la livraison</span>
                                </button>
                            <?php endif; ?>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>

        <!-- SECTION 2: PLANIFICATION -->
        <section class="card" id="planifier-section" style="border-color: #ec4899;">
            <form method="post" id="livraisonForm">
                <input type="hidden" name="action" value="creer_livraison">
                <input type="hidden" name="id_commande" id="selectedCommande">
                <input type="hidden" name="position_lat" id="position_lat">
                <input type="hidden" name="position_lng" id="position_lng">

                <div style="text-align: center; margin-bottom: 2rem;">
                    <h2 style="color: #ec4899;">2. Planifier la livraison</h2>
                    <p id="commandeSummary" style="font-size: 1.2rem; font-weight: bold;">Sélectionne une commande ci-dessus pour commencer</p>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; align-items: start;">
                    <!-- Left: Form -->
                    <div>
                        <label style="display: block; margin-bottom: 0.5rem;">Mode de livraison</label>
                        <div style="display: grid; gap: 1rem; margin-bottom: 1.5rem;">
                            <?php foreach ($deliveryModes as $key => $mode): ?>
                                <label style="display: flex; align-items: center; background: rgba(0,0,0,0.2); padding: 1rem; border-radius: 12px; cursor: pointer; border: 1px solid rgba(255,255,255,0.1);">
                                    <input type="radio" name="mode_livraison" value="<?php echo $key; ?>" <?php echo $key === 'standard' ? 'checked' : ''; ?> style="width: auto; margin: 0 1rem 0 0;">
                                    <div>
                                        <strong style="display: block; color: #fff;"><?php echo htmlspecialchars($mode['label']); ?></strong>
                                        <small style="color: #00ffc3;"><?php echo $mode['price'] > 0 ? '+' . number_format($mode['price'], 2, ',', ' ') . ' €' : 'Gratuit'; ?></small>
                                    </div>
                                </label>
                            <?php endforeach; ?>
                        </div>

                        <label style="display: block; margin-bottom: 0.5rem;">Notes pour le livreur <span style="color: #ef4444;">*</span></label>
                        <textarea name="notes_client" id="notes_client" rows="3" placeholder="Ex: Badge 1234, 2ème étage..."></textarea>
                        
                        <input type="hidden" name="adresse_complete" id="adresse_complete">
                        <input type="hidden" name="ville" id="ville">
                        <input type="hidden" name="code_postal" id="code_postal">
                    </div>

                    <!-- Right: Map Picker -->
                    <div>
                        <label style="display: block; margin-bottom: 0.5rem;">Destination</label>
                        <div id="pickerMap" style="height: 300px; border-radius: 12px; border: 1px solid rgba(139,92,246,0.5);"></div>
                        
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 0.5rem;">
                            <p id="selectedAddrDisplay" style="margin: 0; color: #00ffc3; font-size: 0.9rem;">Clique sur la carte pour choisir l'adresse</p>
                            <button type="button" id="btn-geo" style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.6rem 1.2rem; background: linear-gradient(135deg, #06b6d4, #0891b2); color: white; border: none; border-radius: 10px; font-weight: 600; font-size: 0.85rem; cursor: pointer; transition: all 0.3s ease; box-shadow: 0 4px 12px rgba(6, 182, 212, 0.4);">
                                <i class="bi bi-geo-alt-fill"></i>
                                <span>Me localiser</span>
                            </button>
                        </div>
                    </div>
                </div>

                <div style="text-align: center; margin-top: 2rem;">
                    <button type="submit" style="display: inline-flex; align-items: center; gap: 0.75rem; padding: 1rem 2.5rem; background: linear-gradient(135deg, #10b981, #059669); color: white; border: none; border-radius: 14px; font-weight: 700; font-size: 1.1rem; cursor: pointer; transition: all 0.3s ease; box-shadow: 0 6px 20px rgba(16, 185, 129, 0.4);">
                        <i class="bi bi-check-circle-fill" style="font-size: 1.3rem;"></i>
                        <span>Valider la livraison</span>
                    </button>
                </div>
            </form>
        </section>

        <!-- SECTION 3: SUIVI -->
        <section class="card">
            <h2 style="color: #00ffc3; margin-bottom: 2rem;">3. Suivi en temps réel</h2>
            
            <?php if (empty($livraisons)): ?>
                <p style="text-align: center; color: #aaa;">Aucune livraison programmée.</p>
            <?php else: ?>
                <div style="display: grid; gap: 2rem;">
                    <?php foreach ($livraisons as $livraison): ?>
                        <article class="livraison-card">
                            <div style="display: flex; justify-content: space-between; flex-wrap: wrap; gap: 1rem;">
                                <div>
                                    <h3 style="margin: 0;"><?php echo htmlspecialchars($livraison['nom_jeu'] ?? 'Jeu'); ?></h3>
                                    <p style="margin: 0.5rem 0; color: #aaa;">
                                        Commande #<?php echo htmlspecialchars($livraison['numero_commande']); ?>
                                    </p>
                                </div>
                                <div>
                                    <span class="badge badge-<?php echo htmlspecialchars($livraison['statut']); ?>">
                                        <?php echo ucfirst($livraison['statut']); ?>
                                    </span>
                                </div>
                            </div>

                            <div style="margin: 1.5rem 0; display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; font-size: 0.9rem;">
                                <div>
                                    <strong style="color: #8b5cf6;">Adresse:</strong><br>
                                    <?php echo htmlspecialchars($livraison['adresse_complete']); ?>
                                </div>
                                <div>
                                    <strong style="color: #8b5cf6;">Date prévue:</strong><br>
                                    <?php echo htmlspecialchars(date('d/m/Y', strtotime($livraison['date_livraison']))); ?>
                                </div>
                                <div>
                                    <strong style="color: #8b5cf6;">Prix:</strong><br>
                                    <?php echo number_format((float)$livraison['prix_livraison'], 2, ',', ' '); ?> €
                                </div>
                            </div>

                            <?php if (in_array($livraison['statut'], ['en_route', 'livrée'], true) && $livraison['position_lat']): ?>
                                <div class="trajet-map" id="map-<?php echo (int)$livraison['id_livraison']; ?>"
                                     data-lat="<?php echo $livraison['position_lat']; ?>"
                                     data-lng="<?php echo $livraison['position_lng']; ?>"
                                     data-current-lat="<?php echo $livraison['trajet']['position_lat'] ?? $livraison['position_lat']; ?>"
                                     data-current-lng="<?php echo $livraison['trajet']['position_lng'] ?? $livraison['position_lng']; ?>">
                                </div>
                                <div style="text-align: center; margin-top: 1rem;">
                                    <a href="tracking.php?id_livraison=<?php echo (int)$livraison['id_livraison']; ?>" 
                                       target="_blank" class="btn-tracking" style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.75rem 1.5rem; background: linear-gradient(135deg, #667eea, #764ba2); color: white; border: none; border-radius: 12px; font-weight: 600; text-decoration: none; transition: all 0.3s ease; box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);">
                                        <i class="bi bi-geo-alt-fill"></i> 
                                        <span>Suivi en Temps Réel</span>
                                    </a>
                                </div>
                            <?php endif; ?>

                            <div style="margin-top: 1.5rem; border-top: 1px solid rgba(255,255,255,0.1); padding-top: 1rem; text-align: right;">
                                <form method="post" onsubmit="return confirm('Annuler cette livraison ?');" style="display: inline;">
                                    <input type="hidden" name="action" value="supprimer_livraison">
                                    <input type="hidden" name="id_livraison" value="<?php echo (int)$livraison['id_livraison']; ?>">
                                    <button type="submit" style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.6rem 1.2rem; background: linear-gradient(135deg, #ef4444, #dc2626); color: white; border: none; border-radius: 10px; font-weight: 600; font-size: 0.85rem; cursor: pointer; transition: all 0.3s ease; box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);">
                                        <i class="bi bi-x-circle-fill"></i>
                                        <span>Annuler la livraison</span>
                                    </button>
                                </form>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
    </main>

    <script src="../../PROJET_WEB_NEXTGEN-main/public/vendor/leaflet/leaflet.js"></script>
    <script>
       
        const locationIcon = L.divIcon({
            html: '<i class="bi bi-geo-alt-fill" style="font-size: 48px; color: #ec4899; filter: drop-shadow(0 0 15px rgba(236,72,153,0.9));"></i>',
            className: '', // Remove default leaflet-div-icon styles
            iconSize: [48, 48],
            iconAnchor: [24, 48], // Bottom center
            popupAnchor: [0, -48]
        });

        // --- Map Picker Logic ---
        let pickerMap, pickerMarker;
        
        function initPickerMap() {
            pickerMap = L.map('pickerMap').setView([36.8065, 10.1815], 12);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OpenStreetMap'
            }).addTo(pickerMap);

            pickerMap.on('click', async function(e) {
                const lat = e.latlng.lat;
                const lng = e.latlng.lng;

                if (pickerMarker) pickerMarker.remove();
                pickerMarker = L.marker([lat, lng], {icon: locationIcon}).addTo(pickerMap);

                document.getElementById('position_lat').value = lat;
                document.getElementById('position_lng').value = lng;
                document.getElementById('selectedAddrDisplay').textContent = `Position: ${lat.toFixed(5)}, ${lng.toFixed(5)} (Recherche adresse...)`;

                
                try {
                    const res = await fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}`);
                    const data = await res.json();
                    const addr = data.display_name || 'Adresse inconnue';
                    
                    document.getElementById('adresse_complete').value = addr;
                    document.getElementById('ville').value = data.address.city || data.address.town || '';
                    document.getElementById('code_postal').value = data.address.postcode || '';
                    document.getElementById('selectedAddrDisplay').textContent = addr;
                } catch (err) {
                    document.getElementById('selectedAddrDisplay').textContent = `Position: ${lat.toFixed(5)}, ${lng.toFixed(5)}`;
                    document.getElementById('adresse_complete').value = `Lat: ${lat}, Lng: ${lng}`;
                }
            });
        }

        // --- Init Maps ---
        document.addEventListener('DOMContentLoaded', () => {
            initPickerMap();

            // Init Real-Time Tracking Maps (MapLibre GL JS for smooth animation)
            document.querySelectorAll('.trajet-map').forEach(el => {
                const livraisonId = parseInt(el.id.replace('map-', ''));
                const destLat = parseFloat(el.dataset.lat);
                const destLng = parseFloat(el.dataset.lng);
                
                // Create MapLibre map for real-time tracking
                const trackMap = new maplibregl.Map({
                    container: el.id,
                    style: {
                        version: 8,
                        sources: {
                            'osm': {
                                type: 'raster',
                                tiles: ['https://a.tile.openstreetmap.org/{z}/{x}/{y}.png'],
                                tileSize: 256
                            }
                        },
                        layers: [{
                            id: 'osm',
                            type: 'raster',
                            source: 'osm'
                        }]
                    },
                    center: [destLng, destLat],
                    zoom: 13,
                    attributionControl: false
                });

                let carMarker = null;
                let isFirstLoad = true;

                // Fetch and update tracking data
                async function updateTracking() {
                    try {
                        const response = await fetch(`api/trajet.php?id_livraison=${livraisonId}`);
                        const data = await response.json();
                        
                        if (data.error) return;

                        // Setup map on first load
                        if (isFirstLoad) {
                            isFirstLoad = false;
                            
                            // Add route if available
                            if (data.route && data.route.length > 0) {
                                const routeCoords = data.route.map(p => [p.lng, p.lat]);
                                const currentIndex = data.trajet.current_index || 0;

                                // Completed route (green gradient)
                                const completedRoute = routeCoords.slice(0, currentIndex + 1);
                                if (completedRoute.length > 1) {
                                    trackMap.addSource('route-completed', {
                                        type: 'geojson',
                                        data: {
                                            type: 'Feature',
                                            geometry: {
                                                type: 'LineString',
                                                coordinates: completedRoute
                                            }
                                        }
                                    });

                                    trackMap.addLayer({
                                        id: 'route-completed',
                                        type: 'line',
                                        source: 'route-completed',
                                        paint: {
                                            'line-color': '#10b981',
                                            'line-width': 4,
                                            'line-opacity': 0.8
                                        }
                                    });
                                }

                                // Remaining route (dashed gray)
                                const remainingRoute = routeCoords.slice(currentIndex);
                                if (remainingRoute.length > 1) {
                                    trackMap.addSource('route-remaining', {
                                        type: 'geojson',
                                        data: {
                                            type: 'Feature',
                                            geometry: {
                                                type: 'LineString',
                                                coordinates: remainingRoute
                                            }
                                        }
                                    });

                                    trackMap.addLayer({
                                        id: 'route-remaining',
                                        type: 'line',
                                        source: 'route-remaining',
                                        paint: {
                                            'line-color': '#666',
                                            'line-width': 3,
                                            'line-opacity': 0.5,
                                            'line-dasharray': [2, 2]
                                        }
                                    });
                                }

                                // Fit bounds to show full route
                                const bounds = routeCoords.reduce((bounds, coord) => {
                                    return bounds.extend(coord);
                                }, new maplibregl.LngLatBounds(routeCoords[0], routeCoords[0]));
                                trackMap.fitBounds(bounds, { padding: 40 });
                            }

                            // Destination marker
                            const destEl = document.createElement('div');
                            destEl.innerHTML = '<i class="bi bi-geo-alt-fill" style="font-size: 32px; color: #ec4899;"></i>';
                            new maplibregl.Marker({ element: destEl })
                                .setLngLat([data.destination.lng, data.destination.lat])
                                .addTo(trackMap);

                            // Delivery truck marker (animated)
                            const carEl = document.createElement('div');
                            carEl.innerHTML = '<i class="bi bi-truck" style="font-size: 28px; color: #667eea; filter: drop-shadow(0 4px 8px rgba(102,126,234,0.6));"></i>';
                            carMarker = new maplibregl.Marker({ element: carEl })
                                .setLngLat([data.trajet.position_lng, data.trajet.position_lat])
                                .addTo(trackMap);
                        } else {
                            // Update truck position smoothly
                            if (carMarker) {
                                const currentPos = carMarker.getLngLat();
                                const targetPos = { lng: data.trajet.position_lng, lat: data.trajet.position_lat };
                                
                                // Smooth animation
                                animateMarker(carMarker, currentPos, targetPos, 1500);
                            }

                            // Update routes
                            if (data.route && data.route.length > 0) {
                                const routeCoords = data.route.map(p => [p.lng, p.lat]);
                                const currentIndex = data.trajet.current_index || 0;

                                // Update completed route
                                const completedRoute = routeCoords.slice(0, currentIndex + 1);
                                if (trackMap.getSource('route-completed') && completedRoute.length > 1) {
                                    trackMap.getSource('route-completed').setData({
                                        type: 'Feature',
                                        geometry: {
                                            type: 'LineString',
                                            coordinates: completedRoute
                                        }
                                    });
                                }

                                // Update remaining route
                                const remainingRoute = routeCoords.slice(currentIndex);
                                if (trackMap.getSource('route-remaining') && remainingRoute.length > 1) {
                                    trackMap.getSource('route-remaining').setData({
                                        type: 'Feature',
                                        geometry: {
                                            type: 'LineString',
                                            coordinates: remainingRoute
                                        }
                                    });
                                }
                            }
                        }
                    } catch (err) {
                        console.error('Tracking update error:', err);
                    }
                }

                function animateMarker(marker, start, end, duration) {
                    const startTime = performance.now();
                    
                    function animate(currentTime) {
                        const elapsed = currentTime - startTime;
                        const progress = Math.min(elapsed / duration, 1);
                        
                        // Easing function
                        const eased = progress < 0.5
                            ? 4 * progress * progress * progress
                            : 1 - Math.pow(-2 * progress + 2, 3) / 2;
                        
                        const lng = start.lng + (end.lng - start.lng) * eased;
                        const lat = start.lat + (end.lat - start.lat) * eased;
                        
                        marker.setLngLat({ lng, lat });
                        
                        if (progress < 1) {
                            requestAnimationFrame(animate);
                        }
                    }
                    
                    requestAnimationFrame(animate);
                }

                // Initial load
                trackMap.on('load', () => {
                    updateTracking();
                    // Update every 2 seconds
                    setInterval(updateTracking, 2000);
                });
            });

            // --- Form Selection Logic ---
            const btns = document.querySelectorAll('.planifier-btn');
            btns.forEach(btn => {
                btn.addEventListener('click', () => {
                    document.getElementById('selectedCommande').value = btn.dataset.commande;
                    document.getElementById('commandeSummary').textContent = `Commande sélectionnée : ${btn.dataset.commandeLabel}`;
                    document.getElementById('commandeSummary').style.color = '#00ffc3';
                    document.getElementById('planifier-section').scrollIntoView({behavior: 'smooth'});
                });
            });

            // --- Geolocation Logic ---
            document.getElementById('btn-geo').addEventListener('click', async () => {
                Swal.fire({
                    title: 'Localisation...',
                    text: 'Recherche de votre position (GPS/IP)',
                    allowOutsideClick: false,
                    didOpen: () => Swal.showLoading(),
                    background: '#1a1a2e',
                    color: '#fff'
                });

                const updateMap = async (lat, lng, source) => {
                    if (pickerMarker) pickerMarker.remove();
                    pickerMarker = L.marker([lat, lng], {icon: locationIcon}).addTo(pickerMap);
                    pickerMap.setView([lat, lng], 16);

                    document.getElementById('position_lat').value = lat;
                    document.getElementById('position_lng').value = lng;
                    
                    try {
                        const res = await fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}`);
                        const data = await res.json();
                        const addr = data.display_name || 'Adresse inconnue';
                        
                        document.getElementById('adresse_complete').value = addr;
                        document.getElementById('ville').value = data.address.city || data.address.town || '';
                        document.getElementById('code_postal').value = data.address.postcode || '';
                        document.getElementById('selectedAddrDisplay').textContent = addr;
                        
                        Swal.fire({
                            icon: 'success', 
                            title: 'Trouvé !', 
                            text: `Position détectée via ${source}`,
                            timer: 2000,
                            background: '#1a1a2e',
                            color: '#fff'
                        });
                    } catch (err) {
                        document.getElementById('selectedAddrDisplay').textContent = `Lat: ${lat}, Lng: ${lng}`;
                        Swal.close();
                    }
                };

                if (navigator.geolocation) {
                    navigator.geolocation.getCurrentPosition(
                        (pos) => updateMap(pos.coords.latitude, pos.coords.longitude, 'GPS'),
                        async () => {
                            // Fallback IP
                            try {
                                const res = await fetch('https://ipapi.co/json/');
                                const data = await res.json();
                                if (data.latitude && data.longitude) {
                                    updateMap(data.latitude, data.longitude, 'IP (' + data.city + ')');
                                } else {
                                    throw new Error('IP fail');
                                }
                            } catch (e) {
                                Swal.fire({icon: 'error', title: 'Erreur', text: 'Impossible de vous localiser.', background: '#1a1a2e', color: '#fff'});
                            }
                        },
                        { enableHighAccuracy: true, timeout: 5000 }
                    );
                } else {
                    // Fallback IP directly
                    try {
                        const res = await fetch('https://ipapi.co/json/');
                        const data = await res.json();
                        updateMap(data.latitude, data.longitude, 'IP (' + data.city + ')');
                    } catch (e) {
                        Swal.fire({icon: 'error', title: 'Erreur', text: 'Géolocalisation non supportée.', background: '#1a1a2e', color: '#fff'});
                    }
                }
            });

            // --- Validation ---
            document.getElementById('livraisonForm').addEventListener('submit', function(e) {
                const cmd = document.getElementById('selectedCommande').value;
                const lat = document.getElementById('position_lat').value;
                const notes = document.getElementById('notes_client').value;

                if (!cmd) {
                    e.preventDefault();
                    Swal.fire({icon: 'warning', title: 'Attention', text: 'Sélectionne une commande d\'abord !', background: '#1a1a2e', color: '#fff'});
                    return;
                }
                if (!lat) {
                    e.preventDefault();
                    Swal.fire({icon: 'warning', title: 'Attention', text: 'Clique sur la carte pour définir la destination !', background: '#1a1a2e', color: '#fff'});
                    return;
                }
                if (!notes) {
                    e.preventDefault();
                    Swal.fire({icon: 'warning', title: 'Attention', text: 'Ajoute une note pour le livreur (étage, code, etc.)', background: '#1a1a2e', color: '#fff'});
                    return;
                }
            });
        });
    </script>
    
    <!-- Voice Control - Inline for reliability -->
    <button class="voice-btn-float" id="voice-btn" title="Commande vocale">
        <span class="voice-icon"><i class="bi bi-mic-fill" style="font-size: 1.8rem;"></i></span>
        <span class="voice-pulse"></span>
    </button>
    <div id="voice-indicator-float"></div>
    
    <style>
    /* Professional Button Styles */
    .btn-tracking:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(102, 126, 234, 0.6);
    }
    
    button[type="submit"]:hover:not(:disabled) {
        transform: translateY(-2px);
    }
    
    button[type="button"]:hover:not(:disabled) {
        transform: translateY(-2px);
    }
    
    .planifier-btn:hover {
        transform: translateY(-2px) scale(1.02);
        box-shadow: 0 6px 20px rgba(102, 126, 234, 0.5);
    }
    
    .voice-btn-float {
        position: fixed !important; bottom: 2rem !important; right: 2rem !important; width: 70px !important; height: 70px !important; border-radius: 50% !important;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important; border: none !important; color: white !important;
        cursor: pointer !important; box-shadow: 0 8px 24px rgba(102, 126, 234, 0.4) !important; transition: all 0.3s ease !important;
        z-index: 99999 !important; display: flex !important; align-items: center !important; justify-content: center !important;
        overflow: hidden !important; opacity: 1 !important; visibility: visible !important;
    }
    .voice-icon { font-size: 2rem; position: relative; z-index: 2; }
    .voice-pulse { position: absolute; width: 100%; height: 100%; border-radius: 50%; background: rgba(102, 126, 234, 0.4); opacity: 0; pointer-events: none; }
    .voice-btn-float:hover { transform: scale(1.1); box-shadow: 0 12px 32px rgba(102, 126, 234, 0.6); }
    .voice-btn-float.listening { background: linear-gradient(135deg, #10b981 0%, #059669 100%); animation: buttonPulse 1.5s ease-in-out infinite; }
    .voice-btn-float.listening .voice-pulse { animation: ringPulse 1.5s ease-out infinite; }
    @keyframes buttonPulse { 0%, 100% { transform: scale(1); } 50% { transform: scale(1.1); } }
    @keyframes ringPulse { 0% { transform: scale(0.8); opacity: 0.8; } 100% { transform: scale(2); opacity: 0; } }
    #voice-indicator-float {
        position: fixed; bottom: 8rem; right: 2rem; background: rgba(0, 0, 0, 0.95); color: white;
        padding: 1.2rem 1.5rem; border-radius: 16px; font-size: 0.95rem; max-width: 320px;
        box-shadow: 0 12px 32px rgba(0, 0, 0, 0.4); backdrop-filter: blur(15px);
        border: 1px solid rgba(255, 255, 255, 0.1); z-index: 999; display: none;
        animation: slideIn 0.3s ease-out;
    }
    @keyframes slideIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
    #voice-indicator-float.speaking { background: linear-gradient(135deg, rgba(16, 185, 129, 0.9), rgba(5, 150, 105, 0.9)); }
    </style>
    
    <script>
    class VoiceCtrl {
        constructor() {
            this.recognition = null; this.synthesis = window.speechSynthesis; this.isListening = false;
            this.deliveries = <?php echo json_encode($livraisons ?? []); ?>;
            this.initRecognition(); this.initButton();
            console.log('✅ Voice Control Ready!', this.deliveries.length, 'deliveries');
        }
        initRecognition() {
            const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
            if (!SpeechRecognition) { console.warn('⚠️ Speech recognition not supported'); return; }
            this.recognition = new SpeechRecognition(); this.recognition.lang = 'fr-FR';
            this.recognition.continuous = true; this.recognition.interimResults = true;
            this.recognition.onstart = () => { console.log('🎤 Listening started'); this.isListening = true;
                document.getElementById('voice-btn').classList.add('listening'); this.showMsg('🎤 Je vous écoute...'); };
            this.recognition.onresult = (event) => {
                const last = event.results.length - 1; const command = event.results[last][0].transcript.toLowerCase();
                const isFinal = event.results[last].isFinal; console.log('🎤', isFinal ? 'Final:' : 'Interim:', command);
                if (!isFinal) this.showMsg('🎤 "' + command + '"'); else this.processCommand(command);
            };
            this.recognition.onerror = (event) => { console.error('❌ Speech error:', event.error);
                if (event.error === 'no-speech') this.showMsg('❌ Aucune parole détectée');
                else if (event.error === 'not-allowed') { this.showMsg('❌ Microphone bloqué'); alert('Veuillez autoriser l\'accès au microphone'); }
                else this.showMsg('❌ Erreur: ' + event.error);
            };
            this.recognition.onend = () => { console.log('🎤 Listening stopped'); this.isListening = false;
                document.getElementById('voice-btn').classList.remove('listening'); setTimeout(() => this.hideMsg(), 2000); };
        }
        initButton() { const btn = document.getElementById('voice-btn'); btn.addEventListener('click', () => this.toggleListening()); }
        toggleListening() {
            if (!this.recognition) { this.speak('Commande vocale non supportée sur ce navigateur'); return; }
            if (this.isListening) this.recognition.stop();
            else { try { this.recognition.start(); } catch (e) { console.error('Failed to start:', e); this.showMsg('❌ Erreur de démarrage'); } }
        }
        processCommand(command) {
            console.log('📝 Processing:', command); this.showMsg('💭 "' + command + '"');
            if (command.includes('combien') || command.includes('nombre')) {
                const count = this.deliveries.length; this.speak(`Vous avez ${count} livraison${count > 1 ? 's' : ''} programmée${count > 1 ? 's' : ''}`);
            } else if (command.includes('où') || command.includes('position') || command.includes('localisation')) {
                if (this.deliveries.length > 0) {
                    const city = this.deliveries[0].ville || 'une ville inconnue'; const address = this.deliveries[0].adresse_complete || '';
                    this.speak(`Votre colis est en route vers ${city}. ${address ? 'Adresse: ' + address : ''}`);
                } else this.speak('Aucune livraison en cours pour le moment');
            } else if (command.includes('quand') || command.includes('arrive') || command.includes('délai')) {
                if (this.deliveries.length > 0) {
                    const date = new Date(this.deliveries[0].date_livraison); const today = new Date();
                    today.setHours(0, 0, 0, 0); date.setHours(0, 0, 0, 0); const days = Math.ceil((date - today) / 86400000);
                    if (days < 0) this.speak('Attention! Votre colis est en retard');
                    else if (days === 0) this.speak('Bonne nouvelle! Votre colis devrait arriver aujourd\'hui');
                    else if (days === 1) this.speak('Votre colis devrait arriver demain');
                    else if (days <= 7) this.speak(`Votre colis devrait arriver dans ${days} jours`);
                    else { const dateStr = date.toLocaleDateString('fr-FR', {day: 'numeric', month: 'long'}); this.speak(`Votre colis devrait arriver le ${dateStr}`); }
                } else this.speak('Aucune livraison programmée');
            } else if (command.includes('statut') || command.includes('état')) {
                if (this.deliveries.length > 0) {
                    const status = this.deliveries[0].statut || 'inconnu';
                    const statusMap = { 'preparée': 'en préparation', 'en_route': 'en route', 'livrée': 'livrée', 'annulée': 'annulée' };
                    this.speak(`Le statut de votre livraison est: ${statusMap[status] || status}`);
                } else this.speak('Aucune livraison à vérifier');
            } else if (command.includes('aide') || command.includes('commande')) {
                this.speak('Je peux vous dire combien de livraisons vous avez, où est votre colis, quand il arrive, et son statut');
            } else this.speak('Commande non reconnue. Dites: combien de livraisons, où est mon colis, quand arrive mon colis, ou quel est le statut');
        }
        speak(text) {
            console.log('🔊 Speaking:', text); this.synthesis.cancel();
            const utterance = new SpeechSynthesisUtterance(text); utterance.lang = 'fr-FR'; utterance.rate = 0.95;
            utterance.pitch = 1.0; utterance.volume = 1.0;
            utterance.onstart = () => { document.getElementById('voice-indicator-float').classList.add('speaking'); };
            utterance.onend = () => { document.getElementById('voice-indicator-float').classList.remove('speaking'); setTimeout(() => this.hideMsg(), 1500); };
            this.synthesis.speak(utterance); this.showMsg('🔊 ' + text);
        }
        showMsg(text) { const el = document.getElementById('voice-indicator-float'); el.textContent = text; el.style.display = 'block'; }
        hideMsg() { const el = document.getElementById('voice-indicator-float'); if (!this.isListening) el.style.display = 'none'; }
    }
    document.addEventListener('DOMContentLoaded', () => { window.voiceCtrl = new VoiceCtrl(); });
    </script>
</body>
</html>

