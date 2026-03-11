<?php

namespace app\models;

use yii\db\ActiveRecord;

class TypeVehicule extends ActiveRecord
{

    public static function tableName()
    {
        return 'fredouil.typevehicule';
    }

    // --- RELATION INVERSE ---

    /**
     * Récupère les Voyages utilisant ce type de véhicule.
     */
    public function getVoyages()
    {
        // 'idtypev' est la FK dans voyage qui pointe vers 'id' de typevehicule
        return $this->hasMany(Voyage::class, ['idtypev' => 'id']);
    }
}