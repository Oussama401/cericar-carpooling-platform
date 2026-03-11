<?php
use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use app\models\Trajet;
use app\models\TypeVehicule;
use app\models\MarqueVehicule;

/* Récupération de tous les trajets */
$trajets = Trajet::find()
    ->orderBy(['depart' => SORT_ASC, 'arrivee' => SORT_ASC])
    ->all();




/* Prépare la liste déroulante : id => "Départ → Arrivée" */
$listeTrajets = ArrayHelper::map(
    $trajets,
    'id',
    function ($t) {
        // Format d'affichage pour le select
        return $t->depart . ' → ' . $t->arrivee;
    }
);

/* Récupération types véhicule (SQL direct pour être sûr) */
$types = TypeVehicule::find()->orderBy('typev')->all();
$listeTypes = ArrayHelper::map($types, 'id', 'typev');

/* Récupération marques véhicule (SQL direct pour être sûr) */
$marques = MarqueVehicule::find()->orderBy('marquev')->all();
$listeMarques = ArrayHelper::map($marques, 'id', 'marquev');
?>
<?php if (!empty($inlineNotification)): ?>
    <div class="alert alert-danger mb-3">
        <?= Html::encode($inlineNotification) ?>
    </div>
<?php endif; ?>
<!-- Titre de la page -->
<h3 class="mb-4">Proposer un voyage</h3>
<!-- Carte contenant le formulaire -->
<div class="card shadow-sm p-4">
 <!-- Début du formulaire (POST vers actionProposer) -->
<?= Html::beginForm(['voyage/proposer'], 'post', [
    'class' => 'form-proposer'
]) ?>

    <!-- Sélection du trajet -->
    <div class="row mb-3">
        <div class="col-md-3">
            <!-- Liste des trajets disponibles -->
            <?= Html::dropDownList(
                'trajet',
                null,
                $listeTrajets,
                [
                    'class' => 'form-control',
                    'prompt' => 'Sélectionnez un trajet',
                    'required' => true
                ]
            ) ?>
        </div>

        <div class="col-md-3">
            <!-- Sélection de l'heure de départ -->
            <?= Html::dropDownList(
                'heuredepart',
                null,
                array_combine(
                    range(0, 23),
                    array_map(fn($h) => sprintf('%02dh', $h), range(0, 23))

                ),

                [
                  'class' => 'form-control', 
                  'prompt' => 'Heure de départ', 
                  'required' => true
                ]
            ) ?>


                
          
        </div>

        <div class="col-md-3">
            <!-- Nombre de places disponibles -->
            <?= Html::input('number', 'nbplacedispo', null, [
                'class' => 'form-control',
                'placeholder' => 'Nombre de places',
                'min' => 1,
                'required' => true
            ]) ?>
        </div>

        <div class="col-md-3">
            <!-- Tarif par km -->
            <?= Html::input('number', 'tarif', null, [
                'class' => 'form-control',
                'placeholder' => 'Tarif €/km',
                'step' => '0.1',
                'min' => 0,
                'required' => true
            ]) ?>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-md-3">
            <!-- Nombre de bagages acceptés -->
            <?= Html::input('number', 'nbbagage', null, [
                'class' => 'form-control',
                'placeholder' => 'Nombre de bagages',
                'min' => 0
            ]) ?>
        </div>

        <div class="col-md-3">
            <!-- Type de véhicule -->
            <?= Html::dropDownList(
                'idtypev',
                null,
                $listeTypes,
                [
                    'class' => 'form-control',
                    'prompt' => 'Type de véhicule',
                    'required' => true
                ]
            ) ?>
        </div>

        <div class="col-md-3">
            <!-- Marque du véhicule -->
            <?= Html::dropDownList(
                'idmarquev',
                null,
                $listeMarques,
                [
                    'class' => 'form-control',
                    'prompt' => 'Marque du véhicule',
                    'required' => true
                ]
            ) ?>
        </div>
    </div>

    <!-- Contraintes particulières -->
    <div class="mb-3">
        <?= Html::textarea('contraintes', null, [
            'class' => 'form-control',
            'placeholder' => 'Contraintes (animaux, fumeur, etc.)',
            'rows' => 3
        ]) ?>
    </div>

    <!-- Bouton de soumission -->
    <?= Html::submitButton('Proposer le voyage', [
        'class' => 'btn btn-primary w-100'
    ]) ?>

<?= Html::endForm() ?>
</div>
