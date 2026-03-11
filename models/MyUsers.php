<?php

namespace app\models;

use yii\db\ActiveRecord;

class MyUsers extends ActiveRecord
{
    // Cette méthode indique à Yii quelle table correspond à ce modèle
    public static function tableName()
    {
        return 'fredouil.my_users';
    }
}

