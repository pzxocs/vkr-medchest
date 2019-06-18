<?php

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use yii\widgets\Pjax;

$this->registerJs(
    '$("document").ready(function(){ 
		$("#set-parent-pjax").on("pjax:end", function() {
			$.pjax.reload({container:"#family_prefs"});  //Reload
		});
    });'
);

?>

<div>

    <?php yii\widgets\Pjax::begin(['id' => 'set-parent-pjax']) ?>
    <?php $form = ActiveForm::begin(['action' => Url::to(["settings/set-parent"], true), 'options' => ['data-pjax' => true,
        'id' => 'set-parent-form',
        'class' => 'form-horizontal',
        'fieldConfig' => [
            'template' => "{label}\n<div class=\"col-lg-5\">{input}</div>\n<div class=\"col-lg-12\">{error}</div>",
            'labelOptions' => ['class' => 'col-lg-2'],
        ],
    ]]); ?>

    <?= $form->field($model, 'parent_id')->textInput(['autofocus' => true])->label('Логин владельца семейного профиля') ?>

    <div class="form-group">
        <div class="col-lg-12">
            <?= Html::submitButton('Привязать', ['class' => 'btn btn-primary', 'name' => 'set-parent-button', 'id' => 'set_parent_button']) ?>
        </div>
    </div>

    <?php ActiveForm::end(); ?>
    <?php Pjax::end(); ?>
</div>