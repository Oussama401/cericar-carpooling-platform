<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use app\models\Internaute;

class AuthController extends Controller
{

    public function actionLogin()
    {
        // MODE AJAX
        if (Yii::$app->request->isAjax && Yii::$app->request->isPost) {
             // Récupération des données envoyées par le formulaire
            $pseudo = Yii::$app->request->post('pseudo');
            $pass   = Yii::$app->request->post('pass');
              // Recherche de l'utilisateur en base
            $user = Internaute::findOne([
                'pseudo' => $pseudo,
                'pass'   => sha1($pass)
            ]);
            // Si l'utilisateur existe
            if ($user) {
                // Stockage des infos essentielles en session
                Yii::$app->session->set('user', [
                    'id' => $user->id,
                    'pseudo' => $user->pseudo
                ]);
                // Réponse AJAX succès
                return $this->asJson([
                    'ok' => true,
                    'message' => 'Connexion réussie'
                ]);
            }
             // Réponse AJAX échec
            return $this->asJson([
                'ok' => false,
                'message' => 'Identifiants incorrects'
            ]);
        }

        // MODE NORMAL (affichage page)
        return $this->render('login');
    }

    

    public function actionLogout()
    {
        // Suppression des données utilisateur de la session
        Yii::$app->session->remove('user');

        // Si appel AJAX → réponse JSON
        if (Yii::$app->request->isAjax) {
            return $this->asJson([
                'ok' => true,
                'message' => 'Déconnexion réussie'
            ]);
        }

       // Fallback : déconnexion classique avec redirection
        return $this->redirect(['voyage/recherche']);
    }

    // REGISTER (INSCRIPTION)

    public function actionRegister()
    {
        // MODE AJAX
        if (Yii::$app->request->isAjax && Yii::$app->request->isPost) {
             // Création d’un nouvel utilisateur
            $user = new \app\models\Internaute();
             // Récupération des données du formulaire
            $user->pseudo = Yii::$app->request->post('pseudo');
            $user->pass   = sha1(Yii::$app->request->post('pass'));
            $user->nom    = Yii::$app->request->post('nom');
            $user->prenom = Yii::$app->request->post('prenom');
            $user->mail   = Yii::$app->request->post('mail');
            $user->permis = Yii::$app->request->post('permis') ?: null;

            // Tentative d'enregistrement en base
            if ($user->save()) {

                // Connexion auto
                Yii::$app->session->set('user', [
                    'id' => $user->id,
                    'pseudo' => $user->pseudo
                ]);

                // Réponse AJAX succès
                return $this->asJson([
                    'ok' => true,
                    'message' => 'Inscription réussie'
                ]);
            }

            // Réponse AJAX échec
            return $this->asJson([
                'ok' => false,
                'message' => 'Erreur lors de l’inscription'
            ]);
        }

        // MODE NORMAL (affichage page)
            return $this->render('register');
    }


    public function actionProfil()
    {
        // Vérifie que l’utilisateur est connecté
       if (!Yii::$app->session->has('user')) {
           // Sinon => redirection vers la page de connexion
           return $this->redirect(['auth/login']);
       }
       // Récupération de l’ID utilisateur depuis la session
       $userId = Yii::$app->session->get('user')['id'];
       // Récupération des données complètes de l’utilisateur en base
       $user = \app\models\Internaute::findOne($userId);

       
    // Affichage de la vue profil avec les données utilisateur
       return $this->render('profil', [
           'user' => $user
       ]);
    }


    
}
