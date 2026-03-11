<?php

namespace app\models; 

use yii\db\ActiveRecord;

class Trajet extends ActiveRecord
{
    public static function tableName()
    {
        return 'fredouil.trajet';
    }

    // --- RELATIONS ---

    /**
     * Relation One-to-Many.
     * Récupère tous les objets Voyage qui utilisent ce Trajet.
     */
    public function getVoyages()
    {
        // 'trajet' est la clé étrangère dans la table 'voyage' qui pointe vers 'id' de la table 'trajet'
        return $this->hasMany(Voyage::class, ['trajet' => 'id']);
    }
    

    // Récupère un objet de type trajet à partir de la ville de départ et de la ville d'arrivée.
    public static function getTrajet($villeDepart, $villeArrivee)
    {
        return self::findOne([
            'depart' => $villeDepart, 
            'arrivee' => $villeArrivee
        ]);
    }
    
    // Récupère l'ensemble des voyages correspondant à l'ID de ce trajet.
    public static function getVoyagesByTrajetId($trajetId)
    {
        return Voyage::findAll(['trajet' => $trajetId]);
    }

    // Formate l'heure d'arrivée au format "HHhMM" à partir d'une heure de départ.
    public function formatHeureArrivee($heureDepart)
    {
        $arriveeMinutes = $this->getArriveeMinutes($heureDepart);
        $arriveeHeures = intdiv($arriveeMinutes, 60);
        $arriveeMins = $arriveeMinutes % 60;

        return sprintf('%02dh%02d', $arriveeHeures, $arriveeMins);
    }

    // Calcule l'arrivée en minutes depuis 00:00 en ajoutant la durée du trajet au départ.
    public function getArriveeMinutes($heureDepart)
    {
        $departMinutes = $this->getDepartMinutes($heureDepart);
        // La durée est stockée dans distance (en minutes).
        $dureeMinutes = (int) round((float)$this->distance);

        return ($departMinutes + $dureeMinutes) % (24 * 60);
    }

    // Convertit une heure entière (ex: 14) en minutes depuis 00:00.
    public function getDepartMinutes($heureDepart)
    {
        return ((int)$heureDepart) * 60;
    }
}
