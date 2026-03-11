<?php

namespace app\controllers;
use yii\web\Controller;
use app\models\Trajet;
use app\models\Voyage;
use app\models\Internaute; 
use app\models\Reservation;
use Yii;


class VoyageController extends Controller
{   
    public function actionRecherche()
    { 
        //RÉCUPÉRATION DES PARAMÈTRES DE RECHERCHE
        $vDepart = Yii::$app->request->get('depart');
        $vArrivee = Yii::$app->request->get('arrivee');
        $nbVoyageurs = Yii::$app->request->get('nb_pers');

        // Recherche lancée 

        // Une recherche est valide SI les trois champs ont été fournis.
        $rechercheLancee = (
            !empty($vDepart) &&
            !empty($vArrivee) &&
            !empty($nbVoyageurs)
        );

        // Initialiser un tableau vide qui contiendra tous les voyages trouvés + infos calculées.
        $resultats = [];
        $correspondances = [];

        // Indique si le trajet existe ou non
        $trajetExiste = false;

         //  TRAITEMENT DE LA RECHERCHE (SI CRITÈRES VALIDES)
        if ($rechercheLancee) {

            // Recherche du trajet correspondant (départ → arrivée)
            $trajet = Trajet::getTrajet($vDepart, $vArrivee);

            // Si le trajet existe => on cherche les voyages
            if ($trajet) {
                $trajetExiste = true;

                // Récupération de TOUS les voyages associés à ce trajet
                $voyages = Voyage::getVoyagesByTrajetId($trajet->id);
                
                // traiter chaque voyage un par un
                foreach ($voyages as $voyage) {

                    // Si nbplacedispo < nbVoyageurs => on NE MONTRE PAS ce voyage
                    if ($voyage->nbplacedispo < $nbVoyageurs) {
                        continue;
                    }

                    //  CALCUL DES PLACES RÉSERVÉES DANS CE VOYAGE
                    $placesReservees = Reservation::find()
                        ->where(['voyage' => $voyage->id])
                        //  ??0 Si sum() retourne null ( réservation), on met 0
                        ->sum('nbplaceresa') ?? 0;

                    // CALCUL DES PLACES RESTANTES
                    $placesRestantes = $voyage->nbplacedispo - $placesReservees;

                    // DÉFINIT DES INDICATEURS DE DISPONIBILITÉ ET LE COÛT TOTAL
                    $estComplet = ($placesRestantes == 0);
                    $pasAssezPourDemande = ($placesRestantes < $nbVoyageurs);
                    $estDisponible = ($placesRestantes >= $nbVoyageurs);
                    $coutTotal = $trajet->distance * $voyage->tarif * $nbVoyageurs;
                    $heureArrivee = $trajet->formatHeureArrivee($voyage->heuredepart);

                    //  STOCKAGE DES DONNÉES POUR LA VUE
                    $resultats[] = [
                        'voyage' => $voyage,
                        'places_restantes' => $placesRestantes,
                        'pasAssezPourDemande' => $pasAssezPourDemande,
                        'est_complet' => $estComplet,
                        'est_disponible' => $estDisponible,
                        'cout_total' => $coutTotal,
                        'heure_arrivee' => $heureArrivee
                    ];
                }
            }

            // Si aucun trajet direct disponible, chercher une correspondance (A -> B -> C)
            if (empty($resultats)) {
                // RÉCUPÈRE TOUS LES TRAJETS POSSIBLES AU DÉPART
                $trajetsDepart = Trajet::find()
                    ->where(['depart' => $vDepart])
                    ->all();

                // Parcourt chaque premier segment possible (A -> B)
                foreach ($trajetsDepart as $trajet1) {
                    // Cherche les trajets de correspondance (B -> C)
                    $trajetsArrivee = Trajet::find()
                        ->where([
                            'depart' => $trajet1->arrivee, // // la ville B devient le nouveau départ
                            'arrivee' => $vArrivee // destination finale demandée
                        ])
                        ->all();

                     // Parcourt chaque deuxième segment (B -> C)
                    foreach ($trajetsArrivee as $trajet2) {

                        // Récupère les voyages associés au premier trajet
                        $voyages1 = Voyage::getVoyagesByTrajetId($trajet1->id);

                         // Récupère les voyages associés au second trajet
                        $voyages2 = Voyage::getVoyagesByTrajetId($trajet2->id);

                         // Parcourt chaque voyage du premier segment
                        foreach ($voyages1 as $voyage1) {

                            // Calcule le nombre de places déjà réservées sur le voyage 1
                            $placesReservees1 = Reservation::find()
                                ->where(['voyage' => $voyage1->id]) // // réservations liées à ce voyage
                                ->sum('nbplaceresa') ?? 0; // 0 si aucune réservation

                                // Calcule les places restantes sur le voyage 1
                            $placesRestantes1 = $voyage1->nbplacedispo - $placesReservees1;

                            // Si pas assez de places pour la demande ,  on ignore
                            if ($placesRestantes1 < $nbVoyageurs) {
                                continue;
                            }

                            foreach ($voyages2 as $voyage2) {
                                // Exige une correspondance après l'heure du segment 1
                                $arriveeMinutes1 = $trajet1->getArriveeMinutes($voyage1->heuredepart);
                                $departMinutes2 = $trajet2->getDepartMinutes($voyage2->heuredepart);
                                if ($arriveeMinutes1 >= $departMinutes2) {
                                    continue;
                                }
                                $placesReservees2 = Reservation::find()
                                    ->where(['voyage' => $voyage2->id])
                                    ->sum('nbplaceresa') ?? 0;
                                $placesRestantes2 = $voyage2->nbplacedispo - $placesReservees2;
                                if ($placesRestantes2 < $nbVoyageurs) {
                                    continue;
                                }
                                // Calcule le coût total des deux trajets pour le nombre de voyageurs
                                $coutTotal = ($trajet1->distance * $voyage1->tarif * $nbVoyageurs) +
                                    ($trajet2->distance * $voyage2->tarif * $nbVoyageurs);
                                $heureArrivee1 = $trajet1->formatHeureArrivee($voyage1->heuredepart);
                                $heureArrivee2 = $trajet2->formatHeureArrivee($voyage2->heuredepart);

                                $correspondances[] = [
                                    'trajet1' => $trajet1,
                                    'trajet2' => $trajet2,
                                    'voyage1' => $voyage1,
                                    'voyage2' => $voyage2,
                                    'places_restantes_1' => $placesRestantes1,
                                    'places_restantes_2' => $placesRestantes2,
                                    'cout_total' => $coutTotal,
                                    'heure_arrivee_1' => $heureArrivee1,
                                    'heure_arrivee_2' => $heureArrivee2
                                ];
                            }
                        }
                    }
                }

                // Recherche d'une double correspondance (A -> B -> C -> D)
                foreach ($trajetsDepart as $trajet1) {
                    // RÉCUPÈRE LES TRAJETS INTERMÉDIAIRES (B -> ?)
                    $trajetsIntermediaires = Trajet::find()
                        ->where(['depart' => $trajet1->arrivee])
                        ->all();

                    foreach ($trajetsIntermediaires as $trajet2) {
                        // RÉCUPÈRE LES DERNIERS SEGMENTS (? -> C)
                        $trajetsArrivee = Trajet::find()
                            ->where([
                                'depart' => $trajet2->arrivee,
                                'arrivee' => $vArrivee
                            ])
                            ->all();

                        foreach ($trajetsArrivee as $trajet3) {
                            // RÉCUPÈRE LES VOYAGES ASSOCIÉS À CHAQUE SEGMENT
                            $voyages1 = Voyage::getVoyagesByTrajetId($trajet1->id);
                            $voyages2 = Voyage::getVoyagesByTrajetId($trajet2->id);
                            $voyages3 = Voyage::getVoyagesByTrajetId($trajet3->id);

                            foreach ($voyages1 as $voyage1) {
                                // CALCULE LES PLACES DISPONIBLES SUR LE SEGMENT 1
                                $placesReservees1 = Reservation::find()
                                    ->where(['voyage' => $voyage1->id])
                                    ->sum('nbplaceresa') ?? 0;
                                $placesRestantes1 = $voyage1->nbplacedispo - $placesReservees1;

                                // SI PAS ASSEZ DE PLACES, ON IGNORE CE SEGMENT
                                if ($placesRestantes1 < $nbVoyageurs) {
                                    continue;
                                }

                                foreach ($voyages2 as $voyage2) {
                                    // Exige une correspondance après l'heure du segment 1
                                    $arriveeMinutes1 = $trajet1->getArriveeMinutes($voyage1->heuredepart);
                                    $departMinutes2 = $trajet2->getDepartMinutes($voyage2->heuredepart);
                                    if ($arriveeMinutes1 >= $departMinutes2) {
                                        continue;
                                    }
                                    // CALCULE LES PLACES DISPONIBLES SUR LE SEGMENT 2
                                    $placesReservees2 = Reservation::find()
                                        ->where(['voyage' => $voyage2->id])
                                        ->sum('nbplaceresa') ?? 0;
                                    $placesRestantes2 = $voyage2->nbplacedispo - $placesReservees2;

                                    // SI PAS ASSEZ DE PLACES, ON IGNORE CE SEGMENT
                                    if ($placesRestantes2 < $nbVoyageurs) {
                                        continue;
                                    }

                                    foreach ($voyages3 as $voyage3) {
                                        // Exige une correspondance après l'heure du segment 2
                                        $arriveeMinutes2 = $trajet2->getArriveeMinutes($voyage2->heuredepart);
                                        $departMinutes3 = $trajet3->getDepartMinutes($voyage3->heuredepart);
                                        if ($arriveeMinutes2 >= $departMinutes3) {
                                            continue;
                                        }
                                        // CALCULE LES PLACES DISPONIBLES SUR LE SEGMENT 3
                                        $placesReservees3 = Reservation::find()
                                            ->where(['voyage' => $voyage3->id])
                                            ->sum('nbplaceresa') ?? 0;
                                        $placesRestantes3 = $voyage3->nbplacedispo - $placesReservees3;

                                        // SI PAS ASSEZ DE PLACES, ON IGNORE CE SEGMENT
                                        if ($placesRestantes3 < $nbVoyageurs) {
                                            continue;
                                        }

                                        // CALCULE LE COÛT TOTAL DES TROIS SEGMENTS
                                        $coutTotal = ($trajet1->distance * $voyage1->tarif * $nbVoyageurs) +
                                            ($trajet2->distance * $voyage2->tarif * $nbVoyageurs) +
                                            ($trajet3->distance * $voyage3->tarif * $nbVoyageurs);
                                        $heureArrivee1 = $trajet1->formatHeureArrivee($voyage1->heuredepart);
                                        $heureArrivee2 = $trajet2->formatHeureArrivee($voyage2->heuredepart);
                                        $heureArrivee3 = $trajet3->formatHeureArrivee($voyage3->heuredepart);

                                        // STOCKE LA CORRESPONDANCE TROIS SEGMENTS POUR LA VUE
                                        $correspondances[] = [
                                            'trajet1' => $trajet1,
                                            'trajet2' => $trajet2,
                                            'trajet3' => $trajet3,
                                            'voyage1' => $voyage1,
                                            'voyage2' => $voyage2,
                                            'voyage3' => $voyage3,
                                            'places_restantes_1' => $placesRestantes1,
                                            'places_restantes_2' => $placesRestantes2,
                                            'places_restantes_3' => $placesRestantes3,
                                            'cout_total' => $coutTotal,
                                            'heure_arrivee_1' => $heureArrivee1,
                                            'heure_arrivee_2' => $heureArrivee2,
                                            'heure_arrivee_3' => $heureArrivee3
                                        ];
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        //MODE AJAX — RENVOI JSON (SANS LAYOUT, SANS PAGE COMPLÈTE)
        if (Yii::$app->request->isAjax) {
            // On génère uniquement la vue partielle "_resultats.php"
            $html = $this->renderPartial('_resultats', [
                'resultats' => $resultats,
                'correspondances' => $correspondances,
                'rechercheLancee' => $rechercheLancee,
                'vDepart' => $vDepart,
                'vArrivee' => $vArrivee,
                'nbVoyageurs' => $nbVoyageurs,
            ]);

            // Message à afficher dans le bandeau de notification du layout
            if (!$rechercheLancee) {
                $message = "Veuillez saisir vos critères";
            } elseif (empty($resultats) && !empty($correspondances)) {
                $message = "Correspondances possibles trouvées";
            } elseif (!$trajetExiste) {
                $message = "Ce trajet n’existe pas";
            } elseif (empty($resultats)) {
                $message = "Aucun voyage trouvé";
            } else {
                $message = "Recherche terminée";
            }

            // Réponse JSON envoyée à jQuery
            return $this->asJson([
                'html' => $html,
                'message' => $message,
            ]);
        }

        //MODE NORMAL AFFICHAGE PAGE COMPLÈTE (LAYOUT + VUE)
        return $this->render('recherche', [
            'resultats' => $resultats,
            'correspondances' => $correspondances,
            'rechercheLancee' => $rechercheLancee,
            'vDepart' => $vDepart,
            'vArrivee' => $vArrivee,
            'nbVoyageurs' => $nbVoyageurs,
        ]);
    }
    public function actionReserver()
    {
        // Réponse par défaut
        $response = [
            'ok' => false,
            'message' => '',
            'refresh' => false
        ];

        //  Vérifie si l’utilisateur est connecté
        if (!Yii::$app->session->has('user')) {
            $response['message'] = 'Veuillez vous connecter pour réserver';
            return $this->asJson($response);
        }

        // Récupération des données envoyées en AJAX
        $idVoyage = Yii::$app->request->post('idVoyage');
        $idVoyage1 = Yii::$app->request->post('idVoyage1');
        $idVoyage2 = Yii::$app->request->post('idVoyage2');
        $idVoyage3 = Yii::$app->request->post('idVoyage3');
        $nbPlaces = Yii::$app->request->post('nbPlaces', 1);
        $userId   = Yii::$app->session->get('user')['id']; // id utilisateur connecté 

        //  Réservation correspondance (3 segments)
        if (!empty($idVoyage1) && !empty($idVoyage2) && !empty($idVoyage3)) {
            // RÉCUPÈRE LES TROIS VOYAGES DE LA CORRESPONDANCE
            $voyage1 = Voyage::findOne($idVoyage1);
            $voyage2 = Voyage::findOne($idVoyage2);
            $voyage3 = Voyage::findOne($idVoyage3);

            // VÉRIFIE QUE LES TROIS VOYAGES EXISTENT
            if (!$voyage1 || !$voyage2 || !$voyage3) {
                $response['message'] = 'Voyage introuvable';
                return $this->asJson($response);
            }

            // CALCULE LES PLACES RÉSERVÉES ET RESTANTES POUR CHAQUE SEGMENT
            $placesReservees1 = Reservation::find()
                ->where(['voyage' => $idVoyage1])
                ->sum('nbplaceresa') ?? 0;
            $placesRestantes1 = $voyage1->nbplacedispo - $placesReservees1;
            $placesReservees2 = Reservation::find()
                ->where(['voyage' => $idVoyage2])
                ->sum('nbplaceresa') ?? 0;
            $placesRestantes2 = $voyage2->nbplacedispo - $placesReservees2;
            $placesReservees3 = Reservation::find()
                ->where(['voyage' => $idVoyage3])
                ->sum('nbplaceresa') ?? 0;
            $placesRestantes3 = $voyage3->nbplacedispo - $placesReservees3;

            // VÉRIFIE LA DISPONIBILITÉ SUR LES TROIS SEGMENTS
            if ($placesRestantes1 < $nbPlaces || $placesRestantes2 < $nbPlaces || $placesRestantes3 < $nbPlaces) {
                $response['message'] = 'Pas assez de places disponibles pour la correspondance';
                return $this->asJson($response);
            }

            // TRANSACTION : TOUTES LES RÉSERVATIONS OU AUCUNE
            $transaction = Yii::$app->db->beginTransaction();
            try {
                // CRÉE LA RÉSERVATION POUR LE SEGMENT 1
                $reservation1 = new Reservation();
                $reservation1->voyage = $idVoyage1;
                $reservation1->voyageur = $userId;
                $reservation1->nbplaceresa = $nbPlaces;
                if (!$reservation1->save()) {
                    throw new \RuntimeException('Erreur réservation segment 1');
                }

                // CRÉE LA RÉSERVATION POUR LE SEGMENT 2
                $reservation2 = new Reservation();
                $reservation2->voyage = $idVoyage2;
                $reservation2->voyageur = $userId;
                $reservation2->nbplaceresa = $nbPlaces;
                if (!$reservation2->save()) {
                    throw new \RuntimeException('Erreur réservation segment 2');
                }

                // CRÉE LA RÉSERVATION POUR LE SEGMENT 3
                $reservation3 = new Reservation();
                $reservation3->voyage = $idVoyage3;
                $reservation3->voyageur = $userId;
                $reservation3->nbplaceresa = $nbPlaces;
                if (!$reservation3->save()) {
                    throw new \RuntimeException('Erreur réservation segment 3');
                }

                // VALIDE LA TRANSACTION SI TOUT EST OK
                $transaction->commit();
                $response['ok'] = true;
                $response['message'] = 'Réservation effectuée avec succès (correspondance)';
            } catch (\Throwable $e) {
                // EN CAS D'ERREUR, ANNULER TOUTES LES RÉSERVATIONS
                $transaction->rollBack();
                $response['message'] = 'Erreur lors de la réservation de la correspondance';
            }

            return $this->asJson($response);
        }

        //  Réservation correspondance (2 segments)
        if (!empty($idVoyage1) && !empty($idVoyage2)) {

            // Récupère les deux voyages de la correspondance
            $voyage1 = Voyage::findOne($idVoyage1);
            $voyage2 = Voyage::findOne($idVoyage2);
             // Vérifie que les deux voyages existent
            if (!$voyage1 || !$voyage2) {
                $response['message'] = 'Voyage introuvable';
                return $this->asJson($response);
            }
            // Calcule les places réservées sur le premier segment
            $placesReservees1 = Reservation::find()
                ->where(['voyage' => $idVoyage1])
                ->sum('nbplaceresa') ?? 0;
                // Calcule les places restantes sur le premier segment
            $placesRestantes1 = $voyage1->nbplacedispo - $placesReservees1;
            // Calcule les places réservées sur le second segment
            $placesReservees2 = Reservation::find()
                ->where(['voyage' => $idVoyage2])
                ->sum('nbplaceresa') ?? 0;
                // Calcule les places restantes sur le second segment
            $placesRestantes2 = $voyage2->nbplacedispo - $placesReservees2;
             // Vérifie la disponibilité sur les deux segments
            if ($placesRestantes1 < $nbPlaces || $placesRestantes2 < $nbPlaces) {
                $response['message'] = 'Pas assez de places disponibles pour la correspondance';
                return $this->asJson($response);
            }
             // Démarre une transaction SQL pour une réservation en 2 segments. Objectif : tout ou rien.
            $transaction = Yii::$app->db->beginTransaction(); // démarre un bloc transactionnel
            try {
                // Crée la réservation pour le premier segment
                $reservation1 = new Reservation();
                $reservation1->voyage = $idVoyage1;
                $reservation1->voyageur = $userId;
                $reservation1->nbplaceresa = $nbPlaces;
                if (!$reservation1->save()) {
                    // Déclenche volontairement une exception pour interrompre la transaction
                    // et forcer un rollback si la réservation du premier segment échoue
                    throw new \RuntimeException('Erreur réservation segment 1');
                }
                // // Crée la réservation pour le second  segment
                $reservation2 = new Reservation();
                $reservation2->voyage = $idVoyage2;
                $reservation2->voyageur = $userId;
                $reservation2->nbplaceresa = $nbPlaces;
                if (!$reservation2->save()) {
                    throw new \RuntimeException('Erreur réservation segment 2');
                }
                // Valide les deux réservations
                $transaction->commit();
                // Réponse succès
                $response['ok'] = true;
                $response['message'] = 'Réservation effectuée avec succès (correspondance)';
            } catch (\Throwable $e) { // En cas d’erreur
                $transaction->rollBack(); // annule tout ce qui a été fait dans la transaction
                $response['message'] = 'Erreur lors de la réservation de la correspondance';
            }

            return $this->asJson($response);
        }

        // 4. Réservation directe
        $voyage = Voyage::findOne($idVoyage);
        if (!$voyage) {
            $response['message'] = 'Voyage introuvable';
            return $this->asJson($response);
        }

        $placesReservees = Reservation::find()
            ->where(['voyage' => $idVoyage])
            ->sum('nbplaceresa') ?? 0;

        // CALCULE LES PLACES RESTANTES SUR LE VOYAGE DIRECT
        $placesRestantes = $voyage->nbplacedispo - $placesReservees;

        // SI PLACES INSUFFISANTES, ON REFUSE LA RÉSERVATION
        if ($placesRestantes < $nbPlaces) {
            $response['message'] = 'Pas assez de places disponibles';
            return $this->asJson($response);
        }

        // CRÉE LA RÉSERVATION DIRECTE
        $reservation = new Reservation();
        $reservation->voyage     = $idVoyage;
        $reservation->voyageur   = $userId;
        $reservation->nbplaceresa = $nbPlaces;

        // SAUVEGARDE ET ENVOIE LA RÉPONSE DE SUCCÈS
        if ($reservation->save()) {
            Yii::$app->session->setFlash(
                'success',
                'Réservation enregistrée avec succès.'
            );

            $response['ok'] = true;
            $response['message'] = 'Réservation effectuée avec succès';
        }

        return $this->asJson($response);
}



    

    // PROPOSER UN VOYAGE
    public function actionProposer()
    {
        // Vérifie si l’utilisateur est connecté
        if (!Yii::$app->session->has('user')) {
            return $this->asJson([
                'ok' => false,
                'message' => 'Vous devez être connecté.'
            ]);
        }
         // Récupère l’utilisateur connecté depuis la base
        $user = Internaute::findOne(Yii::$app->session->get('user')['id']);
        // Vérifie si le conducteur a renseigné son permis
        if (empty($user->permis)) {
            // Si la requête est en AJAX
            if (Yii::$app->request->isAjax) {
                 // Réponse JSON d’erreur
            return $this->asJson([
                'ok' => false,
                'message' => 'Renseignez votre numéro de permis avant.'
            ]);
            }
            // Si navigation AJAX partielle, injecte un message directement dans le contenu
            if (Yii::$app->request->get('partial') === '1') {
                return $this->renderPartial('proposer', [
                    'inlineNotification' => 'Renseignez votre numéro de permis avant.'
                ]);
            }
            // Sinon (mode normal), message flash pour la vue
            Yii::$app->session->setFlash('notification', [
                'type' => 'danger',
                'message' => 'Renseignez votre numéro de permis avant.'
            ]);

            // Affiche la page proposer (formulaire)
            return $this->render('proposer');
            
        }

        // MODE AJAX
        if (Yii::$app->request->isAjax && Yii::$app->request->isPost) {
              // Création d’un nouvel objet Voyage
            $voyage = new Voyage();
             // Affectation du conducteur (utilisateur connecté)
            $voyage->conducteur   = $user->id;
            // RÉCUPÈRE LES DONNÉES DU FORMULAIRE
            $voyage->trajet       = Yii::$app->request->post('trajet');
            $voyage->heuredepart  = Yii::$app->request->post('heuredepart');
            $voyage->nbplacedispo = Yii::$app->request->post('nbplacedispo');
            $voyage->tarif        = Yii::$app->request->post('tarif');
            $voyage->nbbagage     = Yii::$app->request->post('nbbagage');
            $voyage->contraintes  = Yii::$app->request->post('contraintes');
            $voyage->idtypev      = Yii::$app->request->post('idtypev');
            $voyage->idmarquev    = Yii::$app->request->post('idmarquev');
             // Tentative de sauvegarde du voyage en base
            if ($voyage->save()) {
                 // Message flash (utile hors AJAX)
                Yii::$app->session->setFlash(
                    'success',
                    'Voyage proposé avec succès.'
                );
                // Réponse JSON de succès
                return $this->asJson([
                    'ok' => true,
                    'message' => 'Voyage proposé avec succès'
                ]);
            }
             // Réponse JSON en cas d’échec de sauvegarde
            return $this->asJson([
                'ok' => false,
                'message' => 'Erreur lors de la proposition du voyage'
            ]);
        }

        // Affichage du formulaire de proposition de voyage
        return $this->render('proposer');
}
}
