<?php

namespace app\controllers;

use yii\web\Controller;
use app\models\Internaute;

class TestController extends Controller
{
    public function actionIndex($pseudo = 'Fourmi')
    {
        $user = Internaute::getUserByIdentifiant($pseudo);

        return $this->render('index', [
            'user' => $user
        ]);
    }
}
