<?php

namespace app\models;

use yii\db\ActiveRecord;

class MarqueVehicule extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'fredouil.marquevehicule';
    }

    // --- RELATION INVERSE ---

    /**
     * Récupère les Voyages utilisant cette marque de véhicule.
     */
    public function getVoyages()
    {
        //Une marque de véhicule peut être utilisée dans plusieurs voyages.
        // 'idmarquev' est la FK dans voyage qui pointe vers 'id' de marquevehicule
        return $this->hasMany(Voyage::class, ['idmarquev' => 'id']);
    }
}