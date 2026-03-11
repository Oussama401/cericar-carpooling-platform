<?php

namespace app\models;

use yii\db\ActiveRecord;


class Reservation extends ActiveRecord
{
    public static function tableName()
    {
        return 'fredouil.reservation';
    }
     
    public function getVoyageInfo()
    {
        //Une réservation concerne un seul voyage.
        return $this->hasOne(Voyage::class, ['id' => 'voyage']);
    }

    public function getVoyageur()
    {
        //Une réservation est faite par un seul internaute (= le voyageur).
        return $this->hasOne(Internaute::class, ['id' => 'voyageur']);
    }

    public static function getReservationsByVoyageId($idVoyage)
    {
        return self::findAll(['voyage' => $idVoyage]);
    }
}
