<?php

/* @var $this \yii\web\View */
/* @var $content string */

use app\widgets\Alert;
use yii\helpers\Html;
use yii\bootstrap\Nav;
use yii\bootstrap\NavBar;
use yii\helpers\Url;
use yii\widgets\Breadcrumbs;
use app\assets\AppAsset;

AppAsset::register($this);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script src="https://api-maps.yandex.ru/2.1/?lang=ru_RU&apikey=9c97eff4-b70b-4bc0-a6aa-aeb6cf5bfb04" type="text/javascript"></script>
    <script src="https://yandex.st/jquery/2.2.3/jquery.min.js" type="text/javascript"></script>
    <script type="text/javascript" src="//www.gstatic.com/firebasejs/3.6.8/firebase.js"></script>
    <?php $this->registerCsrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
</head>
<body>
<?php $this->beginBody() ?>

<div class="wrap">
    <?php
    NavBar::begin([
        'brandLabel' => '
               <div style="float: left;">
                <span class="glyphicon glyphicon-plus" style="margin-right: 5px; color: #e85f5f; font-size: 24pt; margin-top: 3px;"></span>
               </div>               
               <div style="float: right;">
                   <h3 style="margin-top: 0; margin-bottom: 0;">
                        MedChest
                   </h3>
                   <h5 style="margin-top: 0;">домашняя аптечка</h5>              
               </div>               
               ',
        'brandUrl' => Url::to(['graph/index'], true),
        'options' => [
            'class' => 'navbar-inverse navbar-fixed-top',

        ],
        'innerContainerOptions' => ['class' => 'container-full-width'],
    ]);
    echo Nav::widget([
        'options' => ['class' => 'navbar-nav navbar-right'],
        'items' => [
            ['label' => 'График приема', 'url' => ['/graph/index'], 'options'=>['class'=>'mobile-nav']],
            ['label' => 'Мои лекарства', 'url' => ['/medicine/index'], 'options'=>['class'=>'mobile-nav']],
            ['label' => 'Рецепты', 'url' => ['/recipe/index'],'options'=>['class'=>'mobile-nav']],
            ['label' => 'Аптеки рядом', 'url' => ['/pharmacy/index'],'options'=>['class'=>'mobile-nav']],
            ['label' => 'Настройки', 'url' => ['/settings/index'],'options'=>['class'=>'mobile-nav']],
            Yii::$app->user->isGuest ? (
                ['label' => 'Войти', 'url' => ['/site/login']]
            ) : (
                '<li style="padding-top: 5px;">'.
                '<button type="button" class="btn btn-default" id="subscribe"><i class="fa fa-bell"></i></button>'.
                '</li>'.
                '<li>'
                . Html::beginForm(['/site/logout'], 'post')
                . Html::submitButton(
                    'Выйти (' . Yii::$app->user->identity->name . ' - ' . Yii::$app->user->identity->username . ')',
                    ['class' => 'btn btn-link logout']
                )
                . Html::endForm()
                . '</li>'
            ),
            Yii::$app->user->isGuest ? (
                ['label' => 'Регистрация', 'url' => ['/account/register']]
            ) : (
                '<li>'
                .'</li>'
            )
        ],
    ]);
    NavBar::end();
    ?>
    <div class="container" style="width: 100%;">
        <div class="row" style="margin: 0;">
            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 bhoechie-tab-container">
                <?php
                echo Yii::$app->user->isGuest ? (
                ''
                ) :
                (
                '
                    <div class="col-lg-1 col-md-1 col-sm-1 col-xs-1 bhoechie-tab-menu">                    
                        <div class="list-group">
                            <a href="'.Url::to(["graph/index"], true).'" class="list-group-item '. ($this->context->id == 'graph' ? 'active' : '') .' text-center" style="-webkit-border-top-left-radius: 0;">
                                <h4><i class="fa fa-chart-line"></i></h4>График приема
                            </a>
                            <a href="'.Url::to(["medicine/index"], true).'" class="list-group-item '. ($this->context->id == 'medicine' ? 'active' : '') .' text-center">
                                <h4><i class="fa fa-pills"></i></h4>Мои лекарства
                            </a>
                            <a href="'.Url::to(["recipe/index"], true).'" class="list-group-item '. ($this->context->id == 'recipe' ? 'active' : '') .' text-center">
                                <h4><i class="fa fa-sticky-note"></i></h4>Рецепты
                            </a>
                            <a href="'.Url::to(["pharmacy/index"], true).'" class="list-group-item '. ($this->context->id == 'pharmacy' ? 'active' : '') .' text-center">
                                <h4><i class="fa fa-clinic-medical"></i></h4>Аптеки рядом
                            </a>
                            <a href="'.Url::to(["settings/index"], true).'" class="list-group-item '. ($this->context->id == 'settings' ? 'active' : '') .' text-center">
                                <h4><i class="fa fa-cogs"></i></h4>Настройки
                            </a>
                        </div>
                    </div>
                '
                )
                ?>
                <?php
                echo Yii::$app->user->isGuest ? (
                '
                    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 bhoechie-tab">                    
                '
                ) :
                (
                    '<div class="col-lg-9 col-md-9 col-sm-9 col-xs-9 bhoechie-tab">'
                )
                ?>
                    <?= Alert::widget() ?>
                    <?= $content ?>
                </div>
            </div>
        </div>
    </div>
</div>

<footer class="footer">
    <div class="container" style="width: 100%; padding-left: 20px;">
        <p class="pull-left">&copy; Батраханов Д.Х. <?= date('Y') ?></p>
    </div>
</footer>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
