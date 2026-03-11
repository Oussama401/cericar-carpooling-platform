<?php
use yii\helpers\Html;
?>

<!-- Titre de la page -->
<h3 class="mb-4">Inscription</h3>

<!-- Carte centrée contenant le formulaire d’inscription -->
<div class="card shadow-sm p-4" style="max-width: 700px; margin:auto;">
<!-- Début du formulaire Yii -->
<?= Html::beginForm(['auth/register'], 'post', [
    'class' => 'form-register'
]) ?>

    <!-- Ligne : pseudo / mot de passe / email -->
    <div class="row mb-3">
        <div class="col-md-4">
            <!-- Champ pseudo -->
            <?= Html::input('text', 'pseudo', null, [
                'class' => 'form-control',
                'placeholder' => 'Pseudo',
                'required' => true
            ]) ?>
        </div>

        <div class="col-md-4">
            <!-- Champ mot de passe -->
            <?= Html::input('password', 'pass', null, [
                'class' => 'form-control',
                'placeholder' => 'Mot de passe',
                'required' => true
            ]) ?>
        </div>

        <div class="col-md-4">
            <!-- Champ email -->
            <?= Html::input('email', 'mail', null, [
                'class' => 'form-control',
                'placeholder' => 'Email',
                'required' => true
            ]) ?>
        </div>
    </div>

    <!-- Ligne : nom / prénom -->
    <div class="row mb-3">
        <div class="col-md-6">
            <!-- Champ nom -->
            <?= Html::input('text', 'nom', null, [
                'class' => 'form-control',
                'placeholder' => 'Nom',
                'required' => true
            ]) ?>
        </div>

        <div class="col-md-6">
            <!-- Champ prénom -->
            <?= Html::input('text', 'prenom', null, [
                'class' => 'form-control',
                'placeholder' => 'Prénom',
                'required' => true
            ]) ?>
        </div>
    </div>

    <!-- Champ permis (optionnel) -->
    <div class="mb-3">
        <?= Html::input('text', 'permis', null, [
            'class' => 'form-control',
            'placeholder' => 'N° permis (optionnel)'
        ]) ?>
    </div>

    <!-- Bouton de soumission -->
    <?= Html::submitButton('Inscription', [
        'class' => 'btn btn-primary w-100'
    ]) ?>

<!-- Fin du formulaire -->
<?= Html::endForm() ?>
</div>
