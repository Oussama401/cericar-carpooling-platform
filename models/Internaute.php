<?php

namespace app\models;

use yii\db\ActiveRecord;


use app\models\Voyage;
use app\models\Reservation;

class Internaute extends ActiveRecord
{
    public static function tableName()
    {
        // Retourne le nom de la table dans le schéma PostgreSQL
        return 'fredouil.internaute';
    }

    /**
     * Relation One-to-Many.
     * Récupère tous les objets Voyage où cet internaute est le conducteur.
     */

    public function getVoyages()
    {    //Un internaute peut proposer plusieurs voyages → relation 1 → N.
        // 'conducteur' est la clé étrangère dans la table 'voyage' qui pointe vers 'id' de la table 'internaute'
        return $this->hasMany(Voyage::class, ['conducteur' => 'id']);
    }
    /**
     * Relation Many-to-Many implicite. 
     * Récupère tous les objets Reservation où cet internaute est le voyageur.
     */
    public function getReservations()
    {   //Un internaute peut être voyageur dans plusieurs réservations.
        // 'voyageur' est la clé étrangère dans la table 'reservation' qui pointe vers 'id' de la table 'internaute'
        return $this->hasMany(Reservation::class, ['voyageur' => 'id']);
    }
     // Récupère les informations d'un internaute selon son pseudo.
    public static function getUserByIdentifiant($pseudo)
    {
        // self::findOne() est la méthode ActiveRecord pour trouver un enregistrement par ses attributs
         return self::findOne(['pseudo' => $pseudo]);
    }
}
