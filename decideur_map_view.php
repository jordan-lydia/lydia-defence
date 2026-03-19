<?php
session_start();
require_once 'includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'Décideur') {
    header('Location: connexion.php');
    exit();
}

try {
    $sql = "SELECT zs.nom_zone, COUNT(dd.id) as total_deces, zs.latitude, zs.longitude FROM declarations_deces dd JOIN zones_sante zs ON dd.zone_sante_id = zs.id WHERE dd.statut_validation = 'valide' AND zs.latitude IS NOT NULL AND zs.longitude IS NOT NULL GROUP BY zs.id, zs.nom_zone, zs.latitude, zs.longitude";
    $stmt = $pdo->query($sql);
    $map_data = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Erreur de récupération des données: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vue Cartographique - Décideur</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
    <link rel="stylesheet" href="css/decideur.css">
    <link rel="stylesheet" href="css/admine.css">
</head>
<body class="admin-body">

<div class="admin-wrapper">
    <?php include 'includes/decideur_sidebar.php'; ?>
    <div class="page-container">
        <?php include 'includes/decideur_navbar.php'; ?>
        <main class="page-content">
            <div class="page-header mb-4">
                <h1 class="page-title">Vue Cartographique des Zones à Risque</h1>
                <p class="page-lead">Visualisation géospatiale interactive des cas de mortalité validés.</p>
            </div>
            <div class="content-block p-0"><div id="map"></div></div>
        </main>
    </div>
</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<!-- === SCRIPT JS COMPLET ET AMÉLIORÉ === -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const mapData = <?php echo json_encode($map_data); ?>;
    const mapContainer = document.getElementById('map');
    
    // Seuil à partir duquel une zone est considérée comme critique et doit clignoter
    const CRITICAL_THRESHOLD = 20;

    if (!mapContainer) return;

    if (!mapData || mapData.length === 0) {
        mapContainer.innerHTML = `<div class="d-flex flex-column justify-content-center align-items-center h-100 text-center text-muted p-5"><i class="fas fa-map-marked-alt fa-3x mb-3"></i><h4>Aucune donnée géolocalisée à afficher.</h4><p>Veuillez vérifier que les zones de santé ont des coordonnées GPS valides.</p></div>`;
        return;
    }

    const map = L.map('map').setView([-11.665, 27.48], 12);

    L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', {
        attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors © <a href="https://carto.com/attributions">CARTO</a>'
    }).addTo(map);

    function getColor(d) {
        return d > 50 ? '#800026' : d > CRITICAL_THRESHOLD ? '#BD0026' : d > 10 ? '#E31A1C' : d > 5 ? '#FC4E2A' : '#FD8D3C';
    }
    
    mapData.forEach(zone => {
        let marker;
        const total_deces = parseInt(zone.total_deces);

        if (total_deces >= CRITICAL_THRESHOLD) {
            // Créer un marqueur HTML personnalisé avec l'animation de pulsation
            const pulsingIcon = L.divIcon({
                className: 'pulsing-icon-container', // Conteneur pour éviter les interférences de style
                html: `<div class="pulsing-icon">${total_deces}</div>`,
                iconSize: [20, 20],
                iconAnchor: [10, 10]
            });
            marker = L.marker([zone.latitude, zone.longitude], { icon: pulsingIcon });
        } else {
            // Créer un cercle normal pour les zones non critiques
            marker = L.circleMarker([zone.latitude, zone.longitude], {
                radius: 6 + Math.log(total_deces + 1) * 2, // Rayon dynamique
                fillColor: getColor(total_deces),
                color: "#fff",
                weight: 1,
                opacity: 1,
                fillOpacity: 0.8
            });
        }
        
        marker.addTo(map)
              .bindPopup(`<b>${zone.nom_zone}</b><br>${total_deces} décès reportés.`)
              .on('click', function(e) {
                  map.setView(e.latlng, 14); // Zoom sur la zone au clic
              });
    });

    const legend = L.control({position: 'bottomright'});
    legend.onAdd = function (map) {
        const div = L.DomUtil.create('div', 'info legend');
        const grades = [1, 5, 10, CRITICAL_THRESHOLD, 50];
        div.innerHTML = '<strong>Décès par Zone</strong><br>';
        
        // Ajout de la légende pour les points clignotants
        div.innerHTML += '<i class="pulsing-icon" style="animation: none; background: #BD0026; border-radius: 50%; width: 18px; height: 18px; float: left; margin-right: 8px;"></i> > ' + CRITICAL_THRESHOLD + ' (Zone Critique)<br>';
        
        for (let i = grades.length - 2; i >= 0; i--) {
            div.innerHTML += `<i style="background:${getColor(grades[i] + 1)}"></i> ${grades[i]}${grades[i+1] ? '–' + grades[i+1] + '<br>' : '+'}`;
        }
        return div;
    };
    legend.addTo(map);
});
</script>

</body>
</html>