<?php
use yii\helpers\Html;
?>
<!-- Affiche les messages flash d’erreur -->
<?php if (Yii::$app->session->hasFlash('error')): ?>
    <div class="alert alert-danger">
        <?= Yii::$app->session->getFlash('error') ?>
    </div>
<?php endif; ?>

<!-- Affiche les messages flash de succès -->
<?php if (Yii::$app->session->hasFlash('success')): ?>
    <div class="alert alert-success">
        <?= Yii::$app->session->getFlash('success') ?>
    </div>
<?php endif; ?>

<!-- Titre de la page -->
<h3 class="mb-4">Mon profil</h3>

<!-- Grille principale -->
<div class="row">

    <!-- Voyages proposés -->
    <div class="col-md-6">
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-primary text-white">
                Mes voyages proposés
            </div>
            <div class="card-body">
                <!-- Récupération des voyages proposés -->
                <?php $voyages = $user->getVoyages()->all(); ?>

                <?php if (empty($voyages)): ?>
                    <p class="text-muted mb-0">Aucun voyage proposé</p>
                <?php else: ?>
                    <!-- Tableau des voyages proposés -->
                    <table class="table table-sm mb-0">
                        <thead>
                            <tr>
                                <th>Trajet</th>
                                <th>Places dispo</th>
                                <th>Complet</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($voyages as $v): ?>
                                <?php
                                // Calcule le nombre de places réservées
                                $placesReservees = array_sum(
                                    array_column($v->reservations, 'nbplaceresa')
                                );
                                // Détermine si le voyage est complet
                                $complet = ($placesReservees >= $v->nbplacedispo);
                                ?>
                                <tr>
                                    <td>
                                        <?= Html::encode($v->trajetInfo->depart) ?>
                                        →
                                        <?= Html::encode($v->trajetInfo->arrivee) ?>
                                    </td>
                                    <td><?= $v->nbplacedispo ?></td>
                                    <td><?= $complet ? 'Oui' : 'Non' ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Réservations -->
    <div class="col-md-6">
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-success text-white">
                Mes réservations
            </div>
            <div class="card-body">
                <!-- Récupération des réservations de l’utilisateur -->
                <?php $reservations = $user->getReservations()->all(); ?>

                <?php if (empty($reservations)): ?>
                    <p class="text-muted mb-0">Aucune réservation</p>
                <?php else: ?>
                    <!-- Tableau des réservations -->
                    <table class="table table-sm mb-0">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Voyage</th>
                                <th>Places réservées</th>
                                <th>ID voyage</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($reservations as $r): ?>
                                <tr>
                                    <td><?= $r->id ?></td>
                                    <td>
                                        <?= Html::encode($r->voyageInfo->trajetInfo->depart) ?>
                                        →
                                        <?= Html::encode($r->voyageInfo->trajetInfo->arrivee) ?>
                                    </td>
                                    <td><?= $r->nbplaceresa ?></td>
                                    <td><?= $r->voyage ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </div>

</div>
