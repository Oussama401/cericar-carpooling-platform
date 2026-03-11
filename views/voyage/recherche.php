<?php
/* @var $this yii\web.View */
/* @var $resultats array */
/* @var $rechercheLancee boolean */
/* @var $vDepart string */
/* @var $vArrivee string */
/* @var $nbVoyageurs int */

use yii\helpers\Html;

$this->title = 'Recherche de Covoiturage';

// Chargement du fichier CSS spécifique à cette vue
$this->registerCssFile('@web/css/recherche.css');

?>

<div class="container mt-5" style="max-width: 700px;">

    <div class="card shadow-lg p-4" style="border-radius: 14px;">
        
        <!-- Titre principal -->
        <h2 class="text-center mb-4" style="font-weight: 700;">
            Rechercher votre Covoiturage
        </h2>
        <!-- génère un formulaire -->
        <?= Html::beginForm(['voyage/recherche'], 'get', ['class' => 'qa']) ?>

            <!-- Champ de départ -->
            <div class="form-group mb-3">
                <?= Html::label('Départ', 'depart', ['class' => 'fw-bold']) ?>
                <?= Html::textInput(
                        'depart',
                        $vDepart,
                        [
                            'class' => 'form-control form-control-lg',
                            'placeholder' => 'Ex : Toulouse',
                            'required' => true
                        ]
                ) ?>
            </div>

            <!-- Champ d'arrivée -->
            <div class="form-group mb-3">
                <?= Html::label('Arrivée', 'arrivee', ['class' => 'fw-bold']) ?>
                <?= Html::textInput(
                        'arrivee',
                        $vArrivee,
                        [
                            'class' => 'form-control form-control-lg',
                            'placeholder' => 'Ex : Marseille',
                            'required' => true
                        ]
                ) ?>
            </div>

            <!-- Champ nombre de voyageurs -->
            <div class="form-group mb-4">
                <?= Html::label('Nombre de voyageurs', 'nb_pers', ['class' => 'fw-bold']) ?>
                <?= Html::input(
                        'number',
                        'nb_pers',
                        $nbVoyageurs,
                        [
                            'class' => 'form-control form-control-lg',
                            'min' => 1,
                            'placeholder' => 'Ex : 3',
                            'required' => true
                        ]
                ) ?>
            </div>

            <!-- Bouton de soumission -->
            <?= Html::submitButton(
                'Rechercher',
                [
                    'class' => 'btn btn-primary btn-lg w-100',
                    'style' => 'font-size: 18px; font-weight:600;'
                ]
            ) ?>

        <?= Html::endForm() ?>
    </div>

    <!-- Zone d'affichage des résultats -->
    <div class="mt-4" id="resultats-voyages">
        <!-- Rend la vue partielle des résultats -->
        <?= $this->render('_resultats', [
            'resultats' => $resultats,
            'correspondances' => $correspondances,
            'rechercheLancee' => $rechercheLancee,
            'vDepart' => $vDepart,
            'vArrivee' => $vArrivee,
            'nbVoyageurs' => $nbVoyageurs,
        ]) ?>
    </div>

</div>
