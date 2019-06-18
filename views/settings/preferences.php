<?php

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

$this->registerJs("
                    $('document').ready(function() {
                        $('#preferencesform-alert_days_to_expired').on('input', function (e) {
                            if($('#preferencesform-alert_days_to_expired').val() < 0)
                            {
                                $('#preferencesform-alert_days_to_expired').val(0);
                            }                                                                                                      
                        });                        
                        $('#preferencesform-alert_critical_pcs_left').on('input', function (e) {
                            if($('#preferencesform-alert_critical_pcs_left').val() < 0)
                            {
                                $('#preferencesform-alert_critical_pcs_left').val(0);
                            }                                                          
                            if($('#preferencesform-alert_critical_pcs_left').val() > 100)
                            {
                                $('#preferencesform-alert_critical_pcs_left').val(100);
                            }                                                                                          
                        });
                    });                             
                ");

?>
<div>
    <h4>Общие параметры</h4>

    <?php $form = ActiveForm::begin([
        'id' => 'save-preferences-form',
        'layout' => 'horizontal',
        'action' => \yii\helpers\Url::to('site/index?r=settings/save-preferences'),
        'fieldConfig' => [
            'template' => "{label}\n<div class=\"col-lg-5\">{input}</div>\n<div class=\"col-lg-12\">{error}</div>",
            'labelOptions' => ['class' => 'col-lg-1'],
        ],
    ]); ?>

        <?= $form->field($model, 'phone')->textInput(['autofocus' => true])->label('Телефон') ?>

        <?= $form->field($model, 'email')->textInput(['autofocus' => true])->label('Email') ?>

        <?= $form->field($model, 'alert_days_to_expired')->textInput(['autofocus' => true,'type'=>'number', 'min' => '0'])->label('Уведомлять об окончании срока годности за') ?>

        <?= $form->field($model, 'alert_critical_pcs_left')->textInput(['autofocus' => true,'type'=>'number', 'min' => '0', 'max' => '100'])->label('Процент остатка медикамента для уведомления') ?>

        <?= $form->field($model, 'phoneNotify')->checkbox([
            'template' => "<div class=\"col-lg-12\">{input} {label}</div>\n<div class=\"col-lg-12\">{error}</div>",
        ])->label('Уведомления по телефону') ?>

        <?= $form->field($model, 'emailNotify')->checkbox([
            'template' => "<div class=\"col-lg-12\">{input} {label}</div>\n<div class=\"col-lg-12\">{error}</div>",
        ])->label('Уведомления по email') ?>

        <?= $form->field($model, 'address')->textInput(['autofocus' => true])
            ->hint('Данный адрес будет использоваться по молчанию для поиска ближайших аптек')->label('Адрес') ?>

        <div class="form-group">
            <div class="col-lg-12">
                <?= Html::submitButton('Сохранить', ['class' => 'btn btn-primary', 'name' => 'save-preferences-button']) ?>
            </div>
        </div>

    <?php ActiveForm::end(); ?>
</div>
