<?php
use yii\helpers\Html;


?>
<?php if ($rechercheLancee): ?>
   <!--  affiche le contexte de la recherche effectuée-->
    <h3 class="result-title">
        Résultats pour <?= Html::encode($vDepart) ?> → <?= Html::encode($vArrivee) ?>
        (<?= Html::encode($nbVoyageurs) ?> pers.)
    </h3>
    
    <?php if (empty($resultats) && empty($correspondances)): ?>
         <!-- Aucun voyage trouvé -->
        <div class="voyage-card no-result">
            Aucun voyage direct trouvé pour ce trajet ou cette disponibilité.
        </div>

    <?php elseif (!empty($resultats)): ?>
         <!-- Boucle sur chaque voyage trouvé -->
        <?php foreach ($resultats as $data): ?>
            <!-- Raccourci pour accéder à l'objet voyage-->
            <?php $voyage = $data['voyage']; ?>

            <?php 
            // Détermine la classe CSS selon la disponibilité
            $cssClass = $data['est_complet']
                ? 'complet'
                : ($data['est_disponible'] ? 'disponible' : 'partiel');
            ?>

            <div class="voyage-card <?= $cssClass ?>">

                <!-- Titre du trajet -->
                <h4 class="voyage-title">
                    <?= Html::encode($voyage->trajetInfo->depart) ?>
                    
                    <?= Html::encode($voyage->trajetInfo->arrivee) ?>
                </h4>

                <!-- Infos principales du voyage -->
                <div class="voyage-info">
                    <p><strong>Voyage ID :</strong> <?= $voyage->id ?></p>
                    <p><strong>Heure de départ :</strong> <?= $voyage->heuredepart ?>h</p>
                    <p><strong>Heure d'arrivée :</strong> <?= Html::encode($data['heure_arrivee']) ?></p>
                    <p><strong>Places demandées :</strong> <?= Html::encode($nbVoyageurs) ?></p>
                    <p><strong>Places restantes :</strong> <b><?= $data['places_restantes'] ?></b></p>
                </div>

                <!-- État de disponibilité et actions -->
                <div class="voyage-status">
                    <?php if ($data['est_complet']): ?>

                        <p class="etat complet">COMPLET – Impossible de réserver</p>

                    <?php elseif ($data['pasAssezPourDemande']): ?>

                        <p class="etat manque">Pas assez de places – Impossible de réserver</p>

                    <?php elseif ($data['est_disponible']): ?>

                        <p class="etat disponible">
                            DISPONIBLE – Coût estimé :
                            <?= number_format($data['cout_total'], 2) ?> €
                        </p>

                        <!-- Bouton réserver si utilisateur connecté -->
                        <?php if (Yii::$app->session->has('user')): ?>


                            <button type="button"
                                    class="btn btn-success btn-sm btn-reserver"
                                    data-id="<?= $voyage->id ?>"
                                    data-nb="<?= $nbVoyageurs ?>">
                                Réserver
                            </button>
                            




                           
                        <?php else: ?>

                            <!-- Lien vers la connexion si non connecté -->
                            <?= Html::a(
                                'Connexion requise',
                                ['auth/login'],
                                ['class' => 'btn btn-warning btn-sm']
                            ) ?>

                        <?php endif; ?>

                    <?php endif; ?>
                </div>

                <!-- Détails du conducteur et du véhicule -->
                <details class="voyage-details">
                    <summary>Détails (Conducteur, Véhicule)</summary>
                    <div class="details-content">
                        <p><strong>Conducteur :</strong> <?= Html::encode($voyage->conducteurInfo->pseudo) ?></p>
                        <p><strong>Véhicule :</strong>
                            <?= Html::encode($voyage->marqueVehicule->marquev) ?>
                            <?= Html::encode($voyage->typeVehicule->typev) ?>
                        </p>
                        <p><strong>Contraintes :</strong>
                            <?= Html::encode($voyage->contraintes) ?: 'Aucune contrainte spécifique.' ?>
                        </p>
                    </div>
                </details>

            </div>

        <?php endforeach; ?>

    <?php else: ?>
        <!-- Résultats en correspondance -->
        <h4 class="result-title">Correspondances possibles</h4>
        <?php foreach ($correspondances as $data): ?>
            <?php
            // Récupération des segments de trajet et voyages associés
            $trajet1 = $data['trajet1'];
            $trajet2 = $data['trajet2'];
            $trajet3 = isset($data['trajet3']) ? $data['trajet3'] : null;
            $voyage1 = $data['voyage1'];
            $voyage2 = $data['voyage2'];
            $voyage3 = isset($data['voyage3']) ? $data['voyage3'] : null;
            ?>

            <div class="voyage-card disponible">
                <!-- Itinéraire complet de la correspondance -->
                <h4 class="voyage-title">
                    <?= Html::encode($trajet1->depart) ?>
                    → <?= Html::encode($trajet1->arrivee) ?>
                    → <?= Html::encode($trajet2->arrivee) ?>
                    <?php if ($trajet3): ?>
                        → <?= Html::encode($trajet3->arrivee) ?>
                    <?php endif; ?>
                </h4>

                <div class="voyage-info">
                    <p><strong>Segment 1 :</strong>
                        <?= Html::encode($trajet1->depart) ?> → <?= Html::encode($trajet1->arrivee) ?>
                        (ID <?= Html::encode($voyage1->id) ?>, départ <?= Html::encode($voyage1->heuredepart) ?>h, arrivée <?= Html::encode($data['heure_arrivee_1']) ?>)
                    </p>
                    <p><strong>Segment 2 :</strong>
                        <?= Html::encode($trajet2->depart) ?> → <?= Html::encode($trajet2->arrivee) ?>
                        (ID <?= Html::encode($voyage2->id) ?>, départ <?= Html::encode($voyage2->heuredepart) ?>h, arrivée <?= Html::encode($data['heure_arrivee_2']) ?>)
                    </p>
                    <?php if ($trajet3 && $voyage3): ?>
                        <p><strong>Segment 3 :</strong>
                            <?= Html::encode($trajet3->depart) ?> → <?= Html::encode($trajet3->arrivee) ?>
                            (ID <?= Html::encode($voyage3->id) ?>, départ <?= Html::encode($voyage3->heuredepart) ?>h, arrivée <?= Html::encode($data['heure_arrivee_3']) ?>)
                        </p>
                    <?php endif; ?>
                    <!-- Affiche les places restantes par segment -->
                    <p><strong>Places restantes :</strong>
                        S1: <b><?= Html::encode($data['places_restantes_1']) ?></b>,
                        S2: <b><?= Html::encode($data['places_restantes_2']) ?></b>
                        <?php if (isset($data['places_restantes_3'])): ?>
                            , S3: <b><?= Html::encode($data['places_restantes_3']) ?></b>
                        <?php endif; ?>
                    </p>
                </div>

                <div class="voyage-status">
                    <p class="etat disponible">
                        DISPONIBLE – Coût estimé :
                        <?= number_format($data['cout_total'], 2) ?> €
                    </p>

                    <!-- Bouton réserver si utilisateur connecté -->
                    <?php if (Yii::$app->session->has('user')): ?>
                        <button type="button"
                                class="btn btn-success btn-sm btn-reserver"
                                data-id1="<?= $voyage1->id ?>"
                                data-id2="<?= $voyage2->id ?>"
                                <?php if ($voyage3): ?>
                                    data-id3="<?= $voyage3->id ?>"
                                <?php endif; ?>
                                data-nb="<?= $nbVoyageurs ?>">
                            Réserver la correspondance
                        </button>
                    <?php else: ?>
                        <!-- Lien vers la connexion si non connecté -->
                        <?= Html::a(
                            'Connexion requise',
                            ['auth/login'],
                            ['class' => 'btn btn-warning btn-sm']
                        ) ?>
                    <?php endif; ?>
                </div>
            </div>
                
        <?php endforeach; ?>

    <?php endif; ?>

<?php else: ?>

    <!-- Message affiché avant toute recherche -->
    <p class="info-init">Veuillez saisir vos critères pour lancer la recherche.</p>

<?php endif; ?>
