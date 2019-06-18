<?php

use app\models\User;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\jui\AutoComplete;
use yii\jui\DatePicker;
use yii\widgets\ActiveForm;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $model app\modules\notes\models\Notes */
/* @var $form yii\widgets\ActiveForm */
?>

<?php

$this->registerJs(
    '$("document").ready(function(){   
                $(\'#add_medicine_modal\').on(\'shown.bs.modal\', function (e) {
                    if($(\'#medicineitem-medicine_item_id\').val())//если редактирование
                    {
                        $.pjax.reload({container: \'#new_medicine\', url: \'/index.php?r=medicine%2Fupdate&id=\' + $(\'#medicineitem-medicine_item_id\').val(), async: false });
                        //название кнопки
                        $(\'.add-medicine-btn\').html(\'Сохранить\');
                    }                    
                });

                $("#new_medicine").on("pjax:end", function(e) {
                    if(!$(\'#medicineitem-medicine_item_id\').val())
                    {
                        $.pjax.reload({container:"#medicines"});  //Reload GridView
                        //закрываем модал если нет ошибок         
                        if(!$("#w0").find(".has-error").length)
                        {
                            $("#add_medicine_modal").modal("hide")
                        }    
                    }                                                                                                                        
                });
        });        
        '
);

//для поиска по лекаствам
$medicinesList=\app\models\Medicine::find()
    ->select(['name as value', 'name as label'])
    ->asArray()
    ->all();

//для поиска по формам
$medFormsList=\app\models\MedicineForm::find()
    ->select(['name as value', 'name as label'])
    ->asArray()
    ->all();

//для поиска по месту хранения
$stroragePlacesList=\app\models\Medicine::find()
    ->select(['storage_place as value', 'storage_place as label'])
    ->asArray()
    ->all();

//для поиска по действ веществу
$activeMaterialsList=\app\models\Medicine::find()
    ->select(['active_material as value', 'active_material as label'])
    ->asArray()
    ->all();

?>

<!-- Modal -->
<div class="modal fade" id="add_medicine_modal" tabindex="-1" role="dialog" aria-labelledby="addMedicineModal" data-backdrop="false" style="top: 50px;">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Закрыть"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="add_medicine_label">Добавление записи</h4>
            </div>
            <div class="modal-body">
                <div class="medicines-form">
                    <?php yii\widgets\Pjax::begin(['id' => 'new_medicine']) ?>

                    <?php $form = ActiveForm::begin(['options' => ['data-pjax' => true]]); ?>
                    <?php
                        $child = User::find()->where(['parent_id' => Yii::$app->user->id])->asArray()->all();
                        // формируем массив, с ключем равным полю 'id' и значением равным полю 'name'
                        $items = ArrayHelper::map($child,'id','name');
                        $params = [
                            'prompt' => 'Укажите учетную запись кому добавляется средство'
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
                        ])->label('Наименование') ?>

                    <?= $form->field($model, 'medicine_form')->widget(
                        AutoComplete::className(), [
                        'clientOptions' => [
                            'source' => $medFormsList,
                        ],
                        'options'=>[
                            'class'=>'form-control'
                        ]
                    ])->label('Форма') ?>

                    <?= $form->field($model, 'active_material')->widget(
                        AutoComplete::className(), [
                        'clientOptions' => [
                            'source' => $activeMaterialsList,
                        ],
                        'options'=>[
                            'class'=>'form-control'
                        ]
                    ])->label('Действ. вещество') ?>

                    <?= $form->field($model, 'storage_place')->widget(
                        AutoComplete::className(), [
                        'clientOptions' => [
                            'source' => $stroragePlacesList,
                        ],
                        'options'=>[
                            'class'=>'form-control'
                        ]
                    ])->label('Место хранения') ?>

                    <?= $form->field($model, 'expired_picker')->widget(DatePicker::classname(), [
                            'clientOptions' => ['dateFormat' => 'dd.MM.yyyy'],
                        'language' => 'ru',
                        'dateFormat' => 'dd.MM.yyyy',
                    ])->label('Годен до') ?>

                    <?= $form->field($model, 'medicine_item_id')->hiddenInput()->label(false) ?>

                    <?= $form->field($model, 'dosage')->textInput(['type'=>'number'])->label('Дозировка') ?>
                    <?= $form->field($model, 'pcs')->textInput(['type'=>'number'])->label('Всего в пачке') ?>
                    <?= $form->field($model, 'pcs_left')->textInput(['type'=>'number'])->label('Остаток') ?>

                    <div class="form-group">
                        <?= Html::submitButton(Yii::t('app', 'Добавить'), ['class' => 'btn btn-success add-medicine-btn']) ?>
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
