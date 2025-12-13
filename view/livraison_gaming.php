<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes livraisons - NextGen</title>
    
    <!-- Fonts & Icons -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&family=Orbitron:wght@700;900&family=Rajdhani:wght@600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <!-- MapLibre GL JS -->
    <link rel="stylesheet" href="https://unpkg.com/maplibre-gl@3.6.2/dist/maplibre-gl.css">
    <script src="https://unpkg.com/maplibre-gl@3.6.2/dist/maplibre-gl.js"></script>
    
    <!-- Leaflet for picker map -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    
    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        :root {
            --primary: #8b5cf6; --secondary: #ec4899; --accent: #00ffc3;
            --dark: #0f0c29; --darker: #1a1a2e;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, var(--dark), var(--darker), #302b63);
            min-height: 100vh; color: #e0e7ff; padding: 2rem;
        }
        main { max-width: 1200px; margin: 0 auto; }
        
        header { text-align: center; margin-bottom: 3rem; }
        header p:first-child { color: var(--secondary); font-weight: bold; letter-spacing: 2px; text-transform: uppercase; }
        header h1 { font-family: 'Orbitron', sans-serif; font-size: 2.5rem; color: white; text-shadow: 0 0 20px rgba(139,92,246,0.5); margin: 0.5rem 0; }
        header p:last-child { font-size: 1.1rem; color: #a5b4fc; }
        header span { color: var(--accent); }
        
        .card {
            background: rgba(255,255,255,0.05); backdrop-filter: blur(10px);
            border-radius: 20px; padding: 2rem; margin-bottom: 2rem;
            border: 1px solid rgba(139,92,246,0.2);
        }
        
        .btn { padding: 12px 24px; border: none; border-radius: 30px; font-weight: bold; cursor: pointer; transition: all 0.3s; }
        .btn.primary { background: linear-gradient(135deg, var(--primary), var(--secondary)); color: white; }
        .btn.secondary { background: rgba(255,255,255,0.1); color: white; border: 1px solid rgba(255,255,255,0.2); }
        .btn.danger { background: linear-gradient(135deg, #ef4444, #dc2626); color: white; }
        .btn:hover { transform: translateY(-2px); box-shadow: 0 10px 30px rgba(0,0,0,0.3); }
        
        .commandes-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 1.5rem; }
        .commande-card {
            background: rgba(0,0,0,0.3); border-radius: 16px; padding: 1.5rem;
            border: 1px solid rgba(139,92,246,0.3); transition: all 0.3s;
        }
        .commande-card:hover { border-color: var(--accent); transform: translateY(-5px); }
        
        .livraison-card {
            background: rgba(0,0,0,0.3); border-radius: 16px; padding: 1.5rem;
            border: 1px solid rgba(139,92,246,0.3); margin-bottom: 1.5rem;
        }
        
        .trajet-map { height: 250px; border-radius: 12px; margin-top: 1rem; }
        
        #pickerMap { height: 300px; border-radius: 12px; border: 1px solid rgba(139,92,246,0.5); }
        
        textarea, select { 
            width: 100%; padding: 12px; border-radius: 10px; 
            background: rgba(0,0,0,0.3); border: 1px solid rgba(255,255,255,0.2); 
            color: white; margin-top: 0.5rem;
        }
        
        .badge {
            display: inline-block; padding: 6px 16px; border-radius: 20px; font-size: 0.85rem; font-weight: bold;
        }
        .badge-commandee { background: #f59e0b; color: black; }
        .badge-en_transit { background: #3b82f6; color: white; }
        .badge-livree { background: #10b981; color: white; }
        
        /* Voice Control Button */
        .voice-btn-float {
            position: fixed !important; bottom: 2rem !important; right: 2rem !important; 
            width: 70px !important; height: 70px !important; border-radius: 50% !important;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important; 
            border: none !important; color: white !important; cursor: pointer !important; 
            box-shadow: 0 8px 24px rgba(102, 126, 234, 0.4) !important; 
            transition: all 0.3s ease !important; z-index: 99999 !important; 
            display: flex !important; align-items: center !important; justify-content: center !important;
        }
        .voice-icon { font-size: 2rem; }
        .voice-pulse { position: absolute; width: 100%; height: 100%; border-radius: 50%; background: rgba(102, 126, 234, 0.4); opacity: 0; }
        .voice-btn-float:hover { transform: scale(1.1); box-shadow: 0 12px 32px rgba(102, 126, 234, 0.6); }
        .voice-btn-float.listening { background: linear-gradient(135deg, #10b981 0%, #059669 100%) !important; animation: buttonPulse 1.5s ease-in-out infinite; }
        .voice-btn-float.listening .voice-pulse { animation: ringPulse 1.5s ease-out infinite; }
        @keyframes buttonPulse { 0%, 100% { transform: scale(1); } 50% { transform: scale(1.1); } }
        @keyframes ringPulse { 0% { transform: scale(0.8); opacity: 0.8; } 100% { transform: scale(2); opacity: 0; } }
        #voice-indicator-float {
            position: fixed; bottom: 8rem; right: 2rem; background: rgba(0, 0, 0, 0.95); color: white;
            padding: 1.2rem 1.5rem; border-radius: 16px; font-size: 0.95rem; max-width: 320px;
            box-shadow: 0 12px 32px rgba(0, 0, 0, 0.4); z-index: 999; display: none;
        }
    </style>
</head>
<body>
<main>
    <header>
        <p>NextGen • Livraison</p>
        <h1>ESPACE LIVRAISONS</h1>
        <p>
            <?php if ($profil): ?>
                Bonjour <span><?php echo htmlspecialchars($profil['prenom']); ?></span>, gère tes commandes et suis tes livraisons en temps réel.
            <?php else: ?>
                Gère tes commandes et suis tes livraisons en temps réel.
            <?php endif; ?>
        </p>
    </header>

    <?php if (!empty($message)): ?>
        <script>
            Swal.fire({
                icon: '<?php echo $messageType === 'success' ? 'success' : 'info'; ?>',
                title: '<?php echo $messageType === 'success' ? 'Succès' : 'Information'; ?>',
                text: '<?php echo addslashes($message); ?>',
                background: '#1a1a2e', color: '#fff', confirmButtonColor: '#8b5cf6'
            });
        </script>
    <?php endif; ?>

    <!-- SECTION 1: COMMANDES -->
    <section class="card">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
            <div>
                <h2 style="margin: 0; color: #00ffc3;">1. Mes Commandes</h2>
                <p style="margin: 0.5rem 0 0; color: #ccc;">Choisis une commande à faire livrer</p>
            </div>
            <form method="post" style="margin:0;">
                <input type="hidden" name="action" value="creer_commande">
                <input type="hidden" name="id_jeu" value="<?php echo $jeux[0]['id_jeu'] ?? 1; ?>">
                <button class="btn secondary" type="submit" style="font-size: 0.8rem;">
                    <i class="bi bi-plus-circle"></i> Simuler un achat
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
                        <div style="display: flex; justify-content: space-between;">
                            <div>
                                <span style="color: #ec4899; font-size: 0.8rem; font-weight: bold;">#<?php echo htmlspecialchars($commande['numero_commande']); ?></span>
                                <h3 style="margin: 0.5rem 0;"><?php echo htmlspecialchars($commande['nom_jeu'] ?? 'Jeu'); ?></h3>
                                <p style="font-size: 0.9rem; color: #aaa;">
                                    <i class="bi bi-calendar"></i> <?php echo date('d/m/Y', strtotime($commande['date_commande'])); ?>
                                </p>
                            </div>
                            <div style="text-align: right;">
                                <p style="font-size: 1.2rem; font-weight: bold; color: #00ffc3; margin: 0;">
                                    <?php echo number_format((float)$commande['total'], 2, ',', ' '); ?> €
                                </p>
                            </div>
                        </div>
                        
                        <?php if (in_array($commande['id_jeu'], $idsCommandesLivrees ?? [])): ?>
                            <button class="btn secondary" disabled style="width: 100%; margin-top: 1rem; opacity: 0.5;">
                                <i class="bi bi-check-circle"></i> Livraison déjà planifiée
                            </button>
                        <?php else: ?>
                            <button class="btn primary planifier-btn" type="button" style="width: 100%; margin-top: 1rem;"
                                    data-commande="<?php echo (int)$commande['id_jeu']; ?>"
                                    data-commande-label="#<?php echo htmlspecialchars($commande['numero_commande']); ?> - <?php echo htmlspecialchars($commande['nom_jeu'] ?? 'Jeu'); ?>">
                                <i class="bi bi-truck"></i> Planifier la livraison
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
            <input type="hidden" name="adresse_complete" id="adresse_complete">

            <div style="text-align: center; margin-bottom: 2rem;">
                <h2 style="color: #ec4899;">2. Planifier la livraison</h2>
                <p id="commandeSummary" style="font-size: 1.2rem; font-weight: bold;">Sélectionne une commande ci-dessus</p>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
                <div>
                    <label style="display: block; margin-bottom: 0.5rem;">Mode de livraison</label>
                    <div style="display: grid; gap: 1rem; margin-bottom: 1.5rem;">
                        <?php foreach ($deliveryModes as $key => $mode): ?>
                            <label style="display: flex; align-items: center; background: rgba(0,0,0,0.2); padding: 1rem; border-radius: 12px; cursor: pointer;">
                                <input type="radio" name="mode_livraison" value="<?php echo $key; ?>" <?php echo $key === 'standard' ? 'checked' : ''; ?> style="width: auto; margin-right: 1rem;">
                                <div>
                                    <strong style="color: #fff;"><?php echo $mode['label']; ?></strong><br>
                                    <small style="color: #00ffc3;"><?php echo $mode['price'] > 0 ? '+' . number_format($mode['price'], 2) . ' €' : 'Gratuit'; ?></small>
                                </div>
                            </label>
                        <?php endforeach; ?>
                    </div>

                    <label>Notes pour le livreur</label>
                    <textarea name="notes_client" rows="3" placeholder="Ex: Badge 1234, 2ème étage..."></textarea>
                </div>

                <div>
                    <label style="display: block; margin-bottom: 0.5rem;">Destination (cliquez sur la carte)</label>
                    <div id="pickerMap"></div>
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 0.5rem;">
                        <p id="selectedAddrDisplay" style="margin: 0; color: #00ffc3; font-size: 0.9rem;">Clique pour choisir l'adresse</p>
                        <button type="button" id="btn-geo" class="btn secondary" style="font-size: 0.8rem; padding: 0.4rem 0.8rem;">
                            <i class="bi bi-geo-alt-fill"></i> Me localiser
                        </button>
                    </div>
                </div>
            </div>

            <div style="text-align: center; margin-top: 2rem;">
                <button type="submit" class="btn primary" style="font-size: 1.2rem; padding: 1rem 3rem;">
                    Valider la livraison
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
            <?php foreach ($livraisons as $livraison): ?>
                <article class="livraison-card">
                    <div style="display: flex; justify-content: space-between; flex-wrap: wrap; gap: 1rem;">
                        <div>
                            <h3 style="margin: 0;"><?php echo htmlspecialchars($livraison['nom_jeu'] ?? 'Jeu'); ?></h3>
                            <p style="margin: 0.5rem 0; color: #aaa;">
                                Commande #<?php echo htmlspecialchars($livraison['numero_commande']); ?>
                            </p>
                        </div>
                        <span class="badge badge-<?php echo htmlspecialchars($livraison['statut']); ?>">
                            <?php echo ucfirst($livraison['statut']); ?>
                        </span>
                    </div>

                    <div style="margin: 1.5rem 0; display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; font-size: 0.9rem;">
                        <div>
                            <strong style="color: #8b5cf6;">Adresse:</strong><br>
                            <?php echo htmlspecialchars(substr($livraison['adresse_complete'] ?? '', 0, 50)); ?>...
                        </div>
                        <div>
                            <strong style="color: #8b5cf6;">Prix livraison:</strong><br>
                            <?php echo number_format((float)($livraison['prix_livraison'] ?? 0), 2); ?> €
                        </div>
                    </div>

                    <?php 
                    $statusLower = strtolower($livraison['statut']);
                    $isTracking = in_array($statusLower, ['en_route', 'en route', 'en_route', 'livree', 'livrée']);
                    ?>
                    
                    <?php if ($isTracking && $livraison['position_lat']): ?>
                        <!-- MAP VISIBLE ONLY AFTER ADMIN CONFIRMS -->
                        <div class="trajet-map" id="map-<?php echo (int)$livraison['id_livraison']; ?>"
                             data-lat="<?php echo $livraison['position_lat']; ?>"
                             data-lng="<?php echo $livraison['position_lng']; ?>"
                             data-current-lat="<?php echo $livraison['trajet']['position_lat'] ?? $livraison['position_lat']; ?>"
                             data-current-lng="<?php echo $livraison['trajet']['position_lng'] ?? $livraison['position_lng']; ?>">
                        </div>
                        <div style="text-align: center; margin-top: 1rem;">
                            <a href="tracking.php?id_livraison=<?php echo (int)$livraison['id_livraison']; ?>" 
                               class="btn primary" style="font-size: 0.9rem;" target="_blank">
                                <i class="bi bi-arrows-fullscreen"></i> Plein écran
                            </a>
                        </div>
                    <?php elseif (in_array($statusLower, ['commandee', 'commandée', 'preparee', 'preparée', 'préparée'])): ?>
                        <!-- MESSAGE WHEN WAITING FOR ADMIN CONFIRMATION -->
                        <div style="background: linear-gradient(135deg, rgba(139, 92, 246, 0.2), rgba(59, 130, 246, 0.2)); 
                                    border: 2px dashed rgba(139, 92, 246, 0.5); 
                                    border-radius: 16px; 
                                    padding: 2rem; 
                                    text-align: center; 
                                    margin: 1.5rem 0;">
                            <div style="font-size: 3rem; margin-bottom: 1rem;">⏳</div>
                            <h4 style="color: #8b5cf6; margin: 0 0 0.5rem 0;">En attente de confirmation</h4>
                            <p style="color: #aaa; margin: 0;">
                                Votre livraison est en cours de préparation.<br>
                                Le suivi GPS s'activera une fois que l'administrateur aura confirmé le départ.
                            </p>
                        </div>
                    <?php endif; ?>

                    <div style="margin-top: 1.5rem; border-top: 1px solid rgba(255,255,255,0.1); padding-top: 1rem; text-align: right;">
                        <form method="post" onsubmit="return confirm('Annuler cette livraison ?');" style="display: inline;">
                            <input type="hidden" name="action" value="supprimer_livraison">
                            <input type="hidden" name="id_livraison" value="<?php echo (int)$livraison['id_livraison']; ?>">
                            <button class="btn danger" style="font-size: 0.8rem; padding: 0.5rem 1rem;">Annuler</button>
                        </form>
                    </div>
                </article>
            <?php endforeach; ?>
        <?php endif; ?>
    </section>
</main>

<!-- Voice Control Button -->
<button class="voice-btn-float" id="voice-btn" title="Commande vocale">
    <span class="voice-icon">🎤</span>
    <span class="voice-pulse"></span>
</button>
<div id="voice-indicator-float"></div>

<script>
    // Map Picker
    let pickerMap, pickerMarker;
    const locationIcon = L.divIcon({
        html: '<i class="bi bi-geo-alt-fill" style="font-size: 48px; color: #ec4899; filter: drop-shadow(0 0 15px rgba(236,72,153,0.9));"></i>',
        className: '', iconSize: [48, 48], iconAnchor: [24, 48]
    });

    function initPickerMap() {
        pickerMap = L.map('pickerMap').setView([36.8065, 10.1815], 12);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(pickerMap);

        pickerMap.on('click', async function(e) {
            const lat = e.latlng.lat, lng = e.latlng.lng;
            if (pickerMarker) pickerMarker.remove();
            pickerMarker = L.marker([lat, lng], {icon: locationIcon}).addTo(pickerMap);

            document.getElementById('position_lat').value = lat;
            document.getElementById('position_lng').value = lng;
            document.getElementById('selectedAddrDisplay').textContent = `Position: ${lat.toFixed(5)}, ${lng.toFixed(5)}`;

            try {
                const res = await fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}`);
                const data = await res.json();
                if (data.display_name) {
                    document.getElementById('adresse_complete').value = data.display_name;
                    document.getElementById('selectedAddrDisplay').textContent = data.display_name;
                }
            } catch (err) { console.error(err); }
        });
    }

    document.addEventListener('DOMContentLoaded', () => {
        initPickerMap();

        // Init tracking maps with MapLibre
        document.querySelectorAll('.trajet-map').forEach(el => {
            const livraisonId = parseInt(el.id.replace('map-', ''));
            const destLat = parseFloat(el.dataset.lat);
            const destLng = parseFloat(el.dataset.lng);
            const curLat = parseFloat(el.dataset.currentLat);
            const curLng = parseFloat(el.dataset.currentLng);

            const trackMap = new maplibregl.Map({
                container: el.id,
                style: { version: 8, sources: { 'osm': { type: 'raster', tiles: ['https://a.tile.openstreetmap.org/{z}/{x}/{y}.png'], tileSize: 256 } },
                         layers: [{ id: 'osm', type: 'raster', source: 'osm' }] },
                center: [destLng, destLat], zoom: 13
            });

            trackMap.on('load', () => {
                // Destination marker
                const destEl = document.createElement('div');
                destEl.innerHTML = '<i class="bi bi-geo-alt-fill" style="font-size: 32px; color: #ec4899;"></i>';
                new maplibregl.Marker({ element: destEl }).setLngLat([destLng, destLat]).addTo(trackMap);

                // Truck marker
                const truckEl = document.createElement('div');
                truckEl.innerHTML = '<i class="bi bi-truck" style="font-size: 28px; color: #667eea; filter: drop-shadow(0 4px 8px rgba(102,126,234,0.6));"></i>';
                new maplibregl.Marker({ element: truckEl }).setLngLat([curLng, curLat]).addTo(trackMap);
            });
        });

        // Planifier buttons
        document.querySelectorAll('.planifier-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                document.getElementById('selectedCommande').value = btn.dataset.commande;
                document.getElementById('commandeSummary').textContent = `Commande: ${btn.dataset.commandeLabel}`;
                document.getElementById('commandeSummary').style.color = '#00ffc3';
                document.getElementById('planifier-section').scrollIntoView({behavior: 'smooth'});
            });
        });

        // Geolocation with multiple fallbacks - ALWAYS WORKS!
        document.getElementById('btn-geo').addEventListener('click', async () => {
            Swal.fire({ title: 'Localisation...', html: 'Recherche de votre position...', allowOutsideClick: false, didOpen: () => Swal.showLoading(), background: '#1a1a2e', color: '#fff' });

            // Function to update map with found position
            const updatePosition = async (lat, lng, source) => {
                if (pickerMarker) pickerMarker.remove();
                pickerMarker = L.marker([lat, lng], {icon: locationIcon}).addTo(pickerMap);
                pickerMap.setView([lat, lng], 15);
                document.getElementById('position_lat').value = lat;
                document.getElementById('position_lng').value = lng;

                // Reverse geocode
                try {
                    const res = await fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}`);
                    const data = await res.json();
                    document.getElementById('adresse_complete').value = data.display_name || `${lat}, ${lng}`;
                    document.getElementById('selectedAddrDisplay').textContent = data.display_name || `${lat}, ${lng}`;
                } catch(e) {
                    document.getElementById('adresse_complete').value = `Lat: ${lat}, Lng: ${lng}`;
                    document.getElementById('selectedAddrDisplay').textContent = `Position: ${lat.toFixed(5)}, ${lng.toFixed(5)}`;
                }
                
                Swal.fire({ icon: 'success', title: 'Trouvé!', text: `Position via ${source}`, timer: 2500, background: '#1a1a2e', color: '#fff', confirmButtonColor: '#8b5cf6' });
            };

            // IP Geolocation services (multiple fallbacks)
            const ipServices = [
                { url: 'https://ipapi.co/json/', extract: d => d.latitude && d.longitude ? {lat: d.latitude, lng: d.longitude, city: d.city} : null },
                { url: 'https://ipwho.is/', extract: d => d.latitude && d.longitude ? {lat: d.latitude, lng: d.longitude, city: d.city} : null },
                { url: 'https://freeipapi.com/api/json', extract: d => d.latitude && d.longitude ? {lat: d.latitude, lng: d.longitude, city: d.cityName} : null },
                { url: 'https://ip-api.com/json/?fields=lat,lon,city,status', extract: d => d.status === 'success' ? {lat: d.lat, lng: d.lon, city: d.city} : null },
            ];

            const getFromIP = async () => {
                for (const service of ipServices) {
                    try {
                        const res = await fetch(service.url, { timeout: 5000 });
                        const data = await res.json();
                        const result = service.extract(data);
                        if (result) {
                            await updatePosition(result.lat, result.lng, `IP (${result.city || 'détecté'})`);
                            return true;
                        }
                    } catch (e) { console.log('IP service failed, trying next...'); }
                }
                return false;
            };

            // Try GPS first
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(
                    async pos => {
                        await updatePosition(pos.coords.latitude, pos.coords.longitude, 'GPS précis');
                    },
                    async () => {
                        // GPS failed, try IP services
                        const ipSuccess = await getFromIP();
                        if (!ipSuccess) {
                            // All failed - use default Tunis position
                            await updatePosition(36.8065, 10.1815, 'Position par défaut (Tunis)');
                        }
                    },
                    { enableHighAccuracy: true, timeout: 8000, maximumAge: 60000 }
                );
            } else {
                // No GPS available, try IP
                const ipSuccess = await getFromIP();
                if (!ipSuccess) {
                    await updatePosition(36.8065, 10.1815, 'Position par défaut (Tunis)');
                }
            }
        });

        // Validation
        document.getElementById('livraisonForm').addEventListener('submit', e => {
            if (!document.getElementById('selectedCommande').value) {
                e.preventDefault();
                Swal.fire({ icon: 'warning', title: 'Attention', text: 'Sélectionne une commande!', background: '#1a1a2e', color: '#fff' });
            }
            if (!document.getElementById('position_lat').value) {
                e.preventDefault();
                Swal.fire({ icon: 'warning', title: 'Attention', text: 'Clique sur la carte!', background: '#1a1a2e', color: '#fff' });
            }
        });
    });

    // Voice Control
    class VoiceCtrl {
        constructor() {
            this.recognition = null; this.synthesis = window.speechSynthesis; this.isListening = false;
            this.deliveries = <?php echo json_encode($livraisons ?? []); ?>;
            this.initRecognition(); this.initButton();
        }
        initRecognition() {
            const SR = window.SpeechRecognition || window.webkitSpeechRecognition;
            if (!SR) return;
            this.recognition = new SR(); this.recognition.lang = 'fr-FR'; this.recognition.continuous = true;
            this.recognition.onstart = () => { this.isListening = true; document.getElementById('voice-btn').classList.add('listening'); this.showMsg('🎤 Je vous écoute...'); };
            this.recognition.onresult = e => { const r = e.results[e.results.length - 1]; if (r.isFinal) this.processCommand(r[0].transcript.toLowerCase()); };
            this.recognition.onend = () => { this.isListening = false; document.getElementById('voice-btn').classList.remove('listening'); setTimeout(() => this.hideMsg(), 2000); };
        }
        initButton() { document.getElementById('voice-btn').addEventListener('click', () => this.toggleListening()); }
        toggleListening() { if (!this.recognition) { this.speak('Non supporté'); return; } this.isListening ? this.recognition.stop() : this.recognition.start(); }
        processCommand(cmd) {
            this.showMsg('💭 "' + cmd + '"');
            if (cmd.includes('combien')) this.speak(`Vous avez ${this.deliveries.length} livraison(s)`);
            else if (cmd.includes('statut')) this.speak(this.deliveries[0] ? `Statut: ${this.deliveries[0].statut}` : 'Aucune livraison');
            else if (cmd.includes('aide')) this.speak('Dites: combien de livraisons, ou statut');
            else this.speak('Commande non reconnue. Dites aide.');
        }
        speak(text) { this.synthesis.cancel(); const u = new SpeechSynthesisUtterance(text); u.lang = 'fr-FR'; this.synthesis.speak(u); this.showMsg('🔊 ' + text); }
        showMsg(t) { document.getElementById('voice-indicator-float').textContent = t; document.getElementById('voice-indicator-float').style.display = 'block'; }
        hideMsg() { if (!this.isListening) document.getElementById('voice-indicator-float').style.display = 'none'; }
    }
    document.addEventListener('DOMContentLoaded', () => { window.voiceCtrl = new VoiceCtrl(); });
</script>
</body>
</html>
