<?php

use app\models\Graph;
use yii\data\ActiveDataProvider;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\jui\AutoComplete;
use yii\jui\DatePicker;
use yii\widgets\ActiveForm;
use yii\widgets\ListView;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $model app\modules\notes\models\Notes */
/* @var $form yii\widgets\ActiveForm */


$this->registerJs("
                    $('document').ready(function() {
                            $('#edit_graph_item_modal').on('shown.bs.modal', function (e) {
                            $.pjax.reload({container: '#edit-graph-item-pjax', 
                                url: '/index.php?r=graph%2Fedit-graph&id=' + $('#graph-graph_id').val(), async: false });                                                                             
                        });
                        
                        $(\"#edit-graph-item-pjax\").on(\"pjax:end\", function() {
                            //перезагрузим и листвью
                            $.pjax.reload({container: '#graph-list-pjax', 
                                url: '/index.php?r=graph%2Fgraph-list&id=' + $('#removecourseid').val(), async: false });                                                                                    
                        });                        
                    });                             
                ");

if($model->plan_take_date != null)
{
      $plan_date = DateTime::createFromFormat('Y-m-d H:i:s', $model->plan_take_date)->format("d.m.Y H:i");
}
else
{
    $plan_date = '';
}
?>

<!-- Modal -->
<div class="modal fade" id="edit_graph_item_modal" tabindex="-1" role="dialog" aria-labelledby="EditGraphItemModal" data-backdrop="false" style="top: 50px; z-index: 1070;">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Закрыть"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="edit_graph_item_label">Изменить прием</h4>
            </div>
            <div class="modal-body" style="padding-top: 0;">
                <?php Pjax::begin(['id' => 'edit-graph-item-pjax']) ?>
                    <?php $form = ActiveForm::begin(['options' => ['data-pjax' => true],]); ?>
                    <h3><?php
                        echo $plan_date;
                    ?></h3>
                    <?= $form->field($model, 'hours')->textInput(['type'=>'number', 'min' => '0', 'max' => '23'])->label('Час') ?>
                    <?= $form->field($model, 'minutes')->textInput(['type'=>'number', 'min' => '0', 'max' => '59'])->label('Минута') ?>
                    <?= $form->field($model, 'graph_id')->hiddenInput()->label(false) ?>
                    <?= $form->field($model, 'is_before_eat')->checkbox(['class'=> 'before-after-checkbox'], false)->label('До еды') ?>
                    <?= $form->field($model, 'is_after_eat')->checkbox(['class'=> 'before-after-checkbox'], false)->label('После еды') ?>

                    <div class="form-group">
                        <?= Html::submitButton(Yii::t('app', 'Сохранить'), ['class' => 'btn btn-success']) ?>
                    </div>

                    <?php ActiveForm::end(); ?>
                <?php Pjax::end(); ?>
            </div>
            <div class="modal-footer">
            </div>
        </div>
    </div>
</div>


