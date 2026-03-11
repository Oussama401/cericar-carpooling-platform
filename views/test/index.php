<?php
// Internaute 
if (!$user) {
    echo "Aucun internaute trouvé.<br>";
    return;
}

echo "Internaute <br>";
echo "Pseudo : {$user->pseudo}<br>";
echo "Nom : {$user->nom} {$user->prenom}<br>";
echo "Mail : {$user->mail}<br><br>";

// Voyages proposés (si conducteur) 
$voyages = $user->voyages;

echo "Voyages proposés <br>";
if (empty($voyages)) {
    echo "Aucun voyage proposé.<br><br>";
} else {
    foreach ($voyages as $v) {

        echo "- Voyage ID {$v->id}<br>";

        // Trajet
        if ($v->trajetInfo) {
            echo "  Trajet : {$v->trajetInfo->depart} → {$v->trajetInfo->arrivee}<br>";
            echo "  Distance : {$v->trajetInfo->distance} km<br>";
        } else {
            echo "  Trajet : Non trouvé<br>";
        }

        // Tarif / places / heure
        echo "  Tarif : {$v->tarif} €/km<br>";
        $total= $v->tarif * $v->trajetInfo->distance;
        echo "  Tarif : {$total} €/km<br>";
        echo "  Places dispo : {$v->nbplacedispo}<br>";
        // Marque et type
        $marque = $v->marqueVehicule ? $v->marqueVehicule->marquev : "Inconnue";
        $type = $v->typeVehicule ? $v->typeVehicule->typev : "Inconnu";
        echo "  Véhicule : {$marque} ({$type})<br><br>";
        
       
    }
}

// ==== Réservations effectuées ====
$reservations = $user->reservations;

echo "=== Réservations ===<br>";
if (empty($reservations)) {
    echo "Aucune réservation.<br><br>";
} else {
    foreach ($reservations as $r) {

        echo "- Réservation ID {$r->id}<br>";

        // Voyage associé
        if ($r->voyageInfo) {
            $voy = $r->voyageInfo;

            if ($voy->trajetInfo) {
                echo "  Trajet : {$voy->trajetInfo->depart} → {$voy->trajetInfo->arrivee}<br>";
            } else {
                echo "  Trajet : Non trouvé<br>";
            }

            echo "  Nb places : {$r->nbplaceresa}<br>";
            echo "  Départ : {$voy->heuredepart} h<br><br>";

        } else {
            echo "  Voyage non trouvé<br><br>";
        }
    }
}
?>
