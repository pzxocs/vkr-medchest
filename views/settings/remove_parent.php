<?php

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use yii\widgets\Pjax;

?>

<?php yii\widgets\Pjax::begin(['id' => 'set-parent-pjax']) ?>
<?php $form = ActiveForm::begin(['action' => Url::to(["settings/remove-parent"], true), 'options' => ['data-pjax' => true,
    'id' => 'remove-parent-form',
    'layout' => 'horizontal',
    'fieldConfig' => [
        'template' => "{label}\n<div class=\"col-lg-5\">{input}</div>\n<div class=\"col-lg-12\">{error}</div>",
        'labelOptions' => ['class' => 'col-lg-1'],
    ],
    ]
]); ?>

<span>Модель привязана к пользователю <?php echo $model->user_login.'('.$model->user_name.')' ?></span>

<div class="form-group">
    <div class="col-lg-12">
        <?= Html::submitButton('Отвязать', ['class' => 'btn btn-primary', 'name' => 'remove-parent-button']) ?>
    </div>
</div>

<?php ActiveForm::end(); ?>
<?php Pjax::end(); ?>
