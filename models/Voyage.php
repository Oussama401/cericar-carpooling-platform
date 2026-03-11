<?php

namespace app\models;

use yii\db\ActiveRecord;

// Imports nécessaires pour les relations
use app\models\Trajet;
use app\models\Internaute;
use app\models\Reservation;
use app\models\TypeVehicule;
use app\models\MarqueVehicule;

class Voyage extends ActiveRecord

{
    public static function tableName()
    {
        return 'fredouil.voyage';
    }

    // === Relations ===

    //Récupère l'Internaute qui propose ce voyage (le conducteur).
    public function getConducteurInfo()
    {
        // 'conducteur' est la FK dans voyage qui pointe vers 'id' de internaute
        return $this->hasOne(Internaute::class, ['id' => 'conducteur']);
    }
    //Récupère le Trajet associé à ce voyage
    public function getTrajetInfo()
    {
        // 'trajet' est la FK dans voyage qui pointe vers 'id' de trajet
        return $this->hasOne(Trajet::class, ['id' => 'trajet']);
    }
    //Récupère le Type du véhicule.
    public function getTypeVehicule()
    {
        // 'idtypev' est la FK dans voyage qui pointe vers 'id' de typevehicule
        return $this->hasOne(TypeVehicule::class, ['id' => 'idtypev']);
    }
    //Récupère la Marque du véhicule.
    public function getMarqueVehicule()
    {
        // 'idmarquev' est la FK dans voyage qui pointe vers 'id' de marquevehicule
        return $this->hasOne(MarqueVehicule::class, ['id' => 'idmarquev']);
    }
    //Récupère toutes les Réservations pour ce voyage.
    public function getReservations()
    {
        // 'voyage' est la FK dans reservation qui pointe vers 'id' de voyage
        return $this->hasMany(Reservation::class, ['voyage' => 'id']);
    }
    // récupère les voyages d’un trajet
    public static function getVoyagesByTrajetId($idTrajet)
    {   
       return self::findAll(['trajet' => $idTrajet]);
    }
    //récupère les réservations d’un voyage
    public static function getReservationsByVoyageId($idVoyage)
    {
       return Reservation::findAll(['voyage' => $idVoyage]);
    }
}
