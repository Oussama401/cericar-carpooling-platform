<?php
use yii\helpers\Html;
?>

<!-- Titre de la page -->
<h3 class="mb-4">Connexion</h3>

<!-- Carte centrée contenant le formulaire de connexion -->
<div class="card shadow-sm p-4" style="max-width: 500px; margin:auto;">

<!-- Début du formulaire Yii -->
<!-- Envoi POST vers actionLogin du controller Auth -->
<?= Html::beginForm(['auth/login'], 'post', [
    'class' => 'form-login' // utilisée par le JS AJAX
]) ?>

    <!-- Champ pseudo -->
    <div class="mb-3">
        <?= Html::input('text', 'pseudo', null, [
            'class' => 'form-control',
            'placeholder' => 'Pseudo',
            'required' => true
        ]) ?>
    </div>

    <!-- Champ mot de passe -->
    <div class="mb-3">
        <?= Html::input('password', 'pass', null, [
            'class' => 'form-control',
            'placeholder' => 'Mot de passe',
            'required' => true
        ]) ?>
    </div>

    <!-- Bouton de soumission -->
    <?= Html::submitButton('Connexion', [
        'class' => 'btn btn-primary w-100'
    ]) ?>

<!-- Fin du formulaire -->
<?= Html::endForm() ?>
</div>
