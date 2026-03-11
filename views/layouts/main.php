<?php

use app\assets\AppAsset;
use yii\bootstrap5\Html;
use yii\bootstrap5\Nav;
use yii\bootstrap5\NavBar;

// Charge les assets globaux (CSS/JS) pour tout le layout.
AppAsset::register($this);

// Balises meta communes (CSRF, charset, viewport, SEO).
$this->registerCsrfMetaTags();
$this->registerMetaTag(['charset' => Yii::$app->charset], 'charset');
$this->registerMetaTag(['name' => 'viewport', 'content' => 'width=device-width, initial-scale=1']);
$this->registerMetaTag(['name' => 'description', 'content' => $this->params['meta_description'] ?? '']);
$this->registerMetaTag(['name' => 'keywords', 'content' => $this->params['meta_keywords'] ?? '']);
$this->registerLinkTag(['rel' => 'icon', 'href' => Yii::getAlias('@web/favicon.ico')]);

$this->beginPage();

// Récupération d’une notification stockée en session (flash)
$notification = Yii::$app->session->getFlash('notification');
?>

<?php
/* ===========================
 * MODE AJAX PARTIEL
 * ===========================
 * Si ?partial=1 :
 * → on renvoie uniquement le contenu
 * → sans layout HTML complet
 */
if (Yii::$app->request->get('partial') === '1'): ?>

    <?php if (!empty($notification['message'])): ?>
    <script>
        // Affichage du bandeau de notification en AJAX
        document.addEventListener('DOMContentLoaded', function () {
            const bar = document.getElementById('notification-bar');
            if (!bar) return;

            bar.textContent = <?= json_encode($notification['message']) ?>;
            bar.style.background = <?= json_encode(
                ($notification['type'] ?? '') === 'success' ? '#d1e7dd' :
                (($notification['type'] ?? '') === 'danger' ? '#f8d7da' : '#eef')
            ) ?>;

            bar.style.display = 'block';
            setTimeout(() => bar.style.display = 'none', 3000);
        });
    </script>
    <?php endif; ?>

    <!-- Contenu de la vue uniquement -->
    <?= $content ?>
    <!-- Fin du rendu partiel : on stoppe le layout complet -->
    <?php return; ?>
<?php endif; ?>


<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>" class="h-100">
<head>
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
</head>

<body class="d-flex flex-column h-100">

<!-- Bandeau global de notifications -->
<div id="notification-bar"
     style="width:100%; padding:10px; margin-top:60px;
            background:#eef; display:none;">
</div>

<?php
/* ===========================
 * NOTIFICATION MODE NORMAL
 * =========================== */
if (!empty($notification['message'])): ?>
<script>
    // Affichage du bandeau de notification en mode page complète
    document.addEventListener('DOMContentLoaded', function () {
        const bar = document.getElementById('notification-bar');
        if (!bar) return;

        bar.textContent = <?= json_encode($notification['message']) ?>;
        bar.style.background = <?= json_encode(
            ($notification['type'] ?? '') === 'success' ? '#d1e7dd' :
            (($notification['type'] ?? '') === 'danger' ? '#f8d7da' : '#eef')
        ) ?>;

        bar.style.display = 'block';
        setTimeout(() => bar.style.display = 'none', 3000);
    });
</script>
<?php endif; ?>


<?php $this->beginBody() ?>

<header id="header">
    <?php
    // Barre de navigation principale.
    NavBar::begin([
        'brandLabel' => 'CeriCar',
        'brandUrl'   => ['/voyage/recherche'],
        'options' => [
            'class' => 'navbar-expand-md navbar-light fixed-top',
            'style' => 'background:#e6f2ff;'
        ]
    ]);

    echo Nav::widget([
        'options' => ['class' => 'navbar-nav'],
        'items' => [
            // Liens visibles selon l'état de session utilisateur.
            [
                'label' => 'Rechercher un Voyage',
                'url' => ['/voyage/recherche'],
            ],
            [
                'label' => 'Proposer un voyage',
                'url' => ['/voyage/proposer'],
                'visible' => Yii::$app->session->has('user'),
            ],
            [
                'label' => 'Mon profil',
                'url' => ['/auth/profil'],
                'visible' => Yii::$app->session->has('user'),
            ],
            [
                'label' => 'Inscription',
                'url' => ['/auth/register'],
                'visible' => !Yii::$app->session->has('user'),
            ],
            [
                'label' => 'Connexion',
                'url' => ['/auth/login'],
                'visible' => !Yii::$app->session->has('user'),
            ],
            [
                'label' => 'Déconnexion',
                'url' => '#',
                'linkOptions' => [
                    'class' => 'btn-logout',
                    'data-url' => \yii\helpers\Url::to(['auth/logout']),
                ],
                'visible' => Yii::$app->session->has('user'),
            ],

        ]
    ]);

    NavBar::end();
    ?>
</header>

<main id="main" class="flex-shrink-0" role="main">
    <div class="container" id="main-content">
        <!-- Zone de rendu des vues -->
        <?= $content ?>
    </div>
</main>
<?php
// JS spécifique à la recherche (chargé avec dépendance jQuery).
$this->registerJsFile('@web/js/recherche.js', [
  'depends' => [\yii\web\JqueryAsset::class],
  'appendTimestamp' => true
]);
?>


<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
