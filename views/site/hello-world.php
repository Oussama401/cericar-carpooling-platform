<?php
use yii\helpers\Html;
use yii\helpers\ArrayHelper;

/** @var array $produits */ // les données envoyées depuis le contrôleur
?>

<h1>Liste des produits</h1>

<?= Html::dropDownList(
    'produit',                 // nom du champ
    null,                      // valeur sélectionnée par défaut
    ArrayHelper::map(          // conversion du tableau en format clé => valeur
        $produits,             // le tableau reçu du contrôleur
        'id',                  // clé
        'produit'              // valeur affichée
    ),
    ['prompt' => 'Choisissez un produit'] // option vide par défaut
) ?>
