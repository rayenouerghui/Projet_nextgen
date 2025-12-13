<?php

class TrackingService {
    private $originLat;
    private $originLng;
    private $originLabel;

    public function __construct() {
        // Origin: Tunis warehouse
        $this->originLat = 36.8065;
        $this->originLng = 10.1815;
        $this->originLabel = 'Entrepôt Tunis';
    }

    /**
     * SIMPLE INDEX-BASED TRACKING - foolproof, always forward
     * Car moves from point 0 -> 1 -> 2 -> 3 ... along stored route
     */
    public function simulateProgress(array $livraison, ?array $trajet = null): ?array {
        if (!isset($livraison['position_lat'], $livraison['position_lng'])) {
            return null;
        }

        $destination = [
            'lat' => (float)$livraison['position_lat'],
            'lng' => (float)$livraison['position_lng'],
        ];
        $origin = ['lat' => $this->originLat, 'lng' => $this->originLng];

        $route = null;
        $currentIndex = 0;

        if ($trajet && !empty($trajet['route_json'])) {
            $route = json_decode($trajet['route_json'], true);
            $currentIndex = (int)($trajet['current_index'] ?? 0);
        }

        if (!$route || empty($route)) {
            $routeData = $this->getOSRMRoute($origin, $destination);
            if ($routeData && !empty($routeData['coordinates'])) {
                $route = $routeData['coordinates'];
                error_log("✅ OSRM route created with " . count($route) . " points (real roads)");
            } else {
                // Fallback: create linear route
                $route = $this->createLinearRoute($origin, $destination, 50);
                error_log("⚠️ Using fallback linear route");
            }
        }

        if (empty($route)) {
            error_log("❌ No valid route available");
            return null;
        }

        $totalPoints = count($route);

        // Ajuster l'incrément selon le statut pour un mouvement plus réaliste
        $increment = 1;
        $statut = strtolower($livraison['statut'] ?? '');
        
        if ($statut === 'en_route' || $statut === 'en_transit') {
            $increment = mt_rand(2, 4); // Mouvement normal
        } elseif ($statut === 'preparée') {
            $increment = mt_rand(1, 2); // Départ lent
        } else {
            $increment = mt_rand(3, 5); // Mouvement rapide
        }
        
        $newIndex = $currentIndex + $increment;
        
        if ($newIndex >= $totalPoints) {
            $newIndex = $totalPoints - 1;
        }

        $hasArrived = $newIndex >= $totalPoints - 1;

        $statut = strtolower($livraison['statut'] ?? '');
        if ($statut === 'livrée' || $statut === 'livree') {
            $newIndex = $totalPoints - 1;
            $hasArrived = true;
        }

        $coords = $route[$newIndex];
        $progress = ($newIndex / max(1, $totalPoints - 1)) * 100;

        $status = $hasArrived
            ? 'Arrivée au point de livraison'
            : 'En chemin (' . (int)round($progress) . '%)';

        return [
            'latitude' => $coords['lat'],
            'longitude' => $coords['lng'],
            'statut' => $status,
            'origin' => $origin,
            'destination' => $destination,
            'route' => $route,
            'route_json' => json_encode($route),
            'current_index' => $newIndex,
            'total_points' => $totalPoints,
            'has_arrived' => $hasArrived,
        ];
    }

    private function getOSRMRoute(array $origin, array $destination): ?array {
        $url = sprintf(
            'https://router.project-osrm.org/route/v1/driving/%f,%f;%f,%f?overview=full&geometries=geojson&steps=true&annotations=true',
            $origin['lng'], $origin['lat'],
            $destination['lng'], $destination['lat']
        );

        $context = stream_context_create([
            'http' => [
                'header' => "User-Agent: NextGenDelivery/2.0\r\n",
                'timeout' => 15,
                'ignore_errors' => true
            ]
        ]);

        $response = @file_get_contents($url, false, $context);
        
        if ($response === false) {
            error_log("OSRM fetch failed for URL: $url");
            return null;
        }

        $data = json_decode($response, true);
        
        if (!isset($data['routes'][0]['geometry']['coordinates'])) {
            error_log("OSRM response missing geometry");
            return null;
        }

        $coordinates = [];
        foreach ($data['routes'][0]['geometry']['coordinates'] as $point) {
            $coordinates[] = ['lat' => $point[1], 'lng' => $point[0]];
        }

        if (count($coordinates) < 5) {
            error_log("OSRM returned too few points: " . count($coordinates));
            return null;
        }

        $distance = ($data['routes'][0]['distance'] ?? 0) / 1000; // Convertir m en km
        $duration = ($data['routes'][0]['duration'] ?? 0) / 60; // Convertir s en minutes

        return [
            'coordinates' => $coordinates,
            'distance' => round($distance, 2),
            'duration' => round($duration, 0),
        ];
    }

    private function createLinearRoute(array $origin, array $destination, int $numPoints = 50): array {
        $route = [];
        for ($i = 0; $i <= $numPoints; $i++) {
            $progress = $i / $numPoints;
            $route[] = [
                'lat' => $origin['lat'] + ($destination['lat'] - $origin['lat']) * $progress,
                'lng' => $origin['lng'] + ($destination['lng'] - $origin['lng']) * $progress,
            ];
        }
        return $route;
    }

    public function getOrigin(): array {
        return [
            'lat' => $this->originLat,
            'lng' => $this->originLng,
            'label' => $this->originLabel,
        ];
    }

    public function haversine(array $a, array $b): float {
        $R = 6371.0;
        $lat1 = deg2rad($a['lat']);
        $lat2 = deg2rad($b['lat']);
        $dlat = deg2rad($b['lat'] - $a['lat']);
        $dlng = deg2rad($b['lng'] - $a['lng']);
        $h = sin($dlat/2) * sin($dlat/2) + cos($lat1) * cos($lat2) * sin($dlng/2) * sin($dlng/2);
        $c = 2 * atan2(sqrt($h), sqrt(1-$h));
        return $R * $c;
    }

    public function progressData(array $origin, array $destination, array $current): array {
        $total = max(0.0, $this->haversine($origin, $destination));
        $covered = max(0.0, $this->haversine($origin, $current));
        $remaining = max(0.0, $total - $covered);
        $pct = $total > 0 ? max(0.0, min(100.0, ($covered / $total) * 100)) : 100.0;
        
        // Calculer ETA basé sur distance restante (vitesse moyenne 40 km/h)
        $avgSpeed = 40.0; // km/h
        $etaMinutes = $remaining > 0 ? ($remaining / $avgSpeed) * 60 : 0;
        
        return [
            'total_km' => round($total, 2),
            'covered_km' => round($covered, 2),
            'remaining_km' => round($remaining, 2),
            'progress_pct' => round($pct, 1),
            'eta_minutes' => round($etaMinutes, 0),
        ];
    }

    public function reverseGeocode(float $lat, float $lng): ?array {
        $url = 'https://nominatim.openstreetmap.org/reverse?format=json&lat=' . urlencode((string)$lat) . '&lon=' . urlencode((string)$lng);
        $context = stream_context_create(['http' => ['header' => "User-Agent: NextGenLivraison/1.0\r\n", 'timeout' => 5]]);
        $response = @file_get_contents($url, false, $context);
        if ($response === false) {
            return null;
        }
        $data = json_decode($response, true);
        if (!$data) {
            return null;
        }
        $addr = $data['address'] ?? [];
        return [
            'display_name' => $data['display_name'] ?? null,
            'city' => $addr['city'] ?? $addr['town'] ?? $addr['village'] ?? null,
            'state' => $addr['state'] ?? null,
            'country' => $addr['country'] ?? null,
        ];
    }
}
