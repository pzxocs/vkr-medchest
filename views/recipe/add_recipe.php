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
            $(\'#add_recipe_modal\').on(\'shown.bs.modal\', function (e) {
                if($(\'#recipe-recipe_id\').val())//если редактирование
                {
                    $.pjax.reload({container: \'#new_recipe\', url: \'/index.php?r=recipe%2Fupdate&id=\' + $(\'#recipe-recipe_id\').val(), async: false });
                    //название кнопки
                    $(\'.add-recipe-btn\').html(\'Сохранить\');
                }                    
            });
    
            $("#new_recipe").on("pjax:end", function() {
                if(!$(\'#recipe-recipe_id\').val())
                {
                    $.pjax.reload({container:"#recipes"});  //Reload GridView
                    //закрываем модал
                    if(!$("#w0").find(".has-error").length)
                    {
                        $("#add_recipe_modal").modal("hide")
                    }
                }
            });
    });'
);

//для поиска по лекаствам
$medicinesList=\app\models\Medicine::find()
    ->select(['name as value', 'name as label'])
    ->asArray()
    ->all();
?>

<!-- Modal -->
<div class="modal fade" id="add_recipe_modal" tabindex="-1" role="dialog" aria-labelledby="addRecipeModal" data-backdrop="false" style="top: 50px;">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Закрыть"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="add_recipe_label">Добавление записи</h4>
            </div>
            <div class="modal-body">
                <div class="recipes-form">
                    <?php yii\widgets\Pjax::begin(['id' => 'new_recipe']) ?>
                    <?php $form = ActiveForm::begin(['options' => ['data-pjax' => true]]); ?>
                    <?php
                        $child = User::find()->where(['parent_id' => Yii::$app->user->id])->asArray()->all();
                        // формируем массив, с ключем равным полю 'id' и значением равным полю 'name'
                        $items = ArrayHelper::map($child,'id','name');
                        $params = [
                            'prompt' => 'Укажите учетную запись кому добавляется рецепт'
                        ];
                        echo $form->field($model, 'user_id')->dropDownList($items,$params)->label('Адресат (оставьте пустым для назначения себе)');
                    ?>
                    <?= $form->field($model, 'author')->textInput()->label('Автор') ?>

                    <?= $form->field($model, 'medicine_name')->widget(
                        AutoComplete::className(), [
                            'clientOptions' => [
                                'source' => $medicinesList,
                            ],
                            'options'=>[
                                'class'=>'form-control'
                            ]
                        ])->label('Препарат') ?>

                    <?= $form->field($model, 'issue_date_picker')->widget(DatePicker::classname(), [
                            'clientOptions' => ['dateFormat' => 'dd.MM.yyyy'],
                        'language' => 'ru',
                        'dateFormat' => 'dd.MM.yyyy',
                    ])->label('Выдан') ?>

                    <?= $form->field($model, 'valid_date_picker')->widget(DatePicker::classname(), [
                        'clientOptions' => ['dateFormat' => 'dd.MM.yyyy'],
                        'language' => 'ru',
                        'dateFormat' => 'dd.MM.yyyy',
                    ])->label('Действителен до') ?>
                    <?= $form->field($model, 'recipe_id')->hiddenInput()->label(false) ?>

                    <?= $form->field($model, 'comment')->textInput()->label('Комментарий') ?>

                    <div class="form-group">
                        <?= Html::submitButton(Yii::t('app', 'Добавить'), ['class' => 'btn btn-success add-recipe-btn']) ?>
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
