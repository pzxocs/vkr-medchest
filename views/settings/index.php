<?php

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */

use yii\bootstrap\Tabs;
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use app\models\ChildList;

$this->title = 'Настройки - MedChest';
?>

<div style="padding: 15px;">
    <?php
    echo Tabs::widget([
        'items' => [
            [
                'label' => 'Общие настройки',
                'content' => $this->render('preferences', ['model' => new \app\models\PreferencesForm()]),
                'active' => true,
                'options' => ['id' => 'preferences'],
            ],
            [
                'label' => 'Семейные настройки',
                'content' => $this->render('family_preferences'),
                'options' => ['id' => 'family-pref'],
            ],
        ],
    ]);
    ?>
</div>
