<?php

use app\models\User;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\jui\AutoComplete;
use yii\jui\DatePicker;
use yii\widgets\ActiveForm;
use yii\widgets\Pjax;


?>

<?php
$this->registerJs(
    '$("document").ready(function(){
            $("#new_graph").on("pjax:end", function() {
                $.pjax.reload({container:"#graph"});  //Reload GridView
                //закрываем модал
                if(!$("#w0").find(".has-error").length)
                {
                    $("#add_graph_modal").modal("hide");
                }                
            });
            
            $(".before-after-checkbox").on("change", function(){
                //если проставляем, то прежде снимаем со всех
                if($(this).is(":checked"))
                {
                    $(".before-after-checkbox").prop("checked", false);
                    $(this).prop("checked", true);
                }                
            });
    });'
);

//для поиска по лекаствам
$medicinesList=\app\models\Medicine::find()
    ->select(['name as value', 'name as label'])
    ->join('LEFT JOIN','medicine_item mi', 'mi.medicine_id = medicine.medicine_id')
    ->where(['mi.user_id' => Yii::$app->user->id ]) //только наши лекарства
    ->orWhere(['mi.user_id' => \app\models\User::find()->where(['parent_id' => Yii::$app->user->id])->select(['id'])->asArray()])
    ->andWhere(['>','mi.pcs_left','0'])
    ->distinct()
    ->asArray()
    ->all();
?>

<!-- Modal -->
<div class="modal fade" id="add_graph_modal" tabindex="-1" role="dialog" aria-labelledby="addGraphModal" data-backdrop="false" style="top: 50px;">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Закрыть"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="add_graph_label">Добавление записи</h4>
            </div>
            <div class="modal-body">
                <div class="graphs-form">
                    <?php yii\widgets\Pjax::begin(['id' => 'new_graph']) ?>
                    <?php $form = ActiveForm::begin(['options' => ['data-pjax' => true],
                        ]); ?>
                    <?php
                        $child = User::find()->where(['parent_id' => Yii::$app->user->id])->asArray()->all();
                        // формируем массив, с ключем равным полю 'id' и значением равным полю 'name'
                        $items = ArrayHelper::map($child,'id','name');
                        $params = [
                            'prompt' => 'Укажите учетную запись кому назначается курс'
                        ];
                        echo $form->field($model, 'user_id')->dropDownList($items,$params)->label('Адресат (оставьте пустым для назначения себе)');
                    ?>
                    <?= $form->field($model, 'medicine_name')->widget(
                        AutoComplete::className(), [
                            'clientOptions' => [
                                'source' => $medicinesList,
                            ],
                            'options'=>[
                                'class'=>'form-control'
                            ]
                        ])->label('Препарат') ?>

                    <?= $form->field($model, 'begin_date_picker')->widget(DatePicker::classname(), [
                            'clientOptions' => ['dateFormat' => 'dd.MM.yyyy'],
                        'language' => 'ru',
                        'dateFormat' => 'dd.MM.yyyy',
                    ])->label('Начало курса') ?>

                    <?= $form->field($model, 'end_date_picker')->widget(DatePicker::classname(), [
                        'clientOptions' => ['dateFormat' => 'dd.MM.yyyy'],
                        'language' => 'ru',
                        'dateFormat' => 'dd.MM.yyyy',
                    ])->label('Окончание курса') ?>

                    <?= $form->field($model, 'is_before_eat')->checkbox(['class'=> 'before-after-checkbox'], false)->label('До еды') ?>

                    <?= $form->field($model, 'is_after_eat')->checkbox(['class'=> 'before-after-checkbox'], false)->label('После еды') ?>

                    <?= $form->field($model, 'dosage')->textInput(['type'=>'number'])->label('Дозировка') ?>
                    <?= $form->field($model, 'takes')->textInput(['type'=>'number'])->label('Приемов в день') ?>

                    <div class="form-group">
                        <?= Html::submitButton(Yii::t('app', 'Добавить'), ['class' => 'btn btn-success']) ?>
                    </div>

                    <?php ActiveForm::end(); ?>
                    <?php Pjax::end(); ?>
                </div>
            </div>
            <div class="modal-footer">
            </div>
        </div>
    </div>
</div>
