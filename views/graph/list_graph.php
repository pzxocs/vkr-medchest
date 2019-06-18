<?php

use app\models\Graph;
use app\models\User;
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
                            $('#graph_list_modal').on('shown.bs.modal', function (e) {
                            $.pjax.reload({container: '#graph-list-pjax', 
                                url: '/index.php?r=graph%2Fgraph-list&id=' + $('#removecourseid').val(), async: false });
                        });
                    });
                    
                                       
                    $('.remove-course-btn').on('click', function(e) {
                         e.preventDefault();
                         var deleteUrl = '/index.php?r=graph%2Fremove-course&id=' + $('#removecourseid').val();
                         var pjaxContainer = 'graph';
                         var result = confirm('Действительно хотите удалить эту запись?');                                
                         if(result) {
                             $.ajax({
                                 url: deleteUrl,
                                 type: 'post',
                                 error: function(xhr, status, error) {
                                     alert('There was an error with your request.' + xhr.responseText);
                                 }
                             }).done(function(data) {
                                 $('#graph_list_modal').modal('hide');
                                 $.pjax.reload('#' + $.trim(pjaxContainer), {timeout: 3000});
                             });
                         }
                     });                    
                     
                ");

$user = User::find()->where(['id' => Yii::$app->user->id])->one();
?>


<!-- Modal -->
<div class="modal fade" id="graph_list_modal" tabindex="-1" role="dialog" aria-labelledby="GraphListModal" data-backdrop="false" style="top: 50px;">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Закрыть"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="graph_list_label">Список приемов</h4>
            </div>
            <div class="modal-body" style="padding-top: 0;">
                <div class="remove-course-form" style="padding-bottom: 45px;">
                    <?php $form = ActiveForm::begin(['action' => Url::to(["graph/remove-course"], true), 'options' => [
                        'id' => 'remove-course-form',
                        'layout' => 'horizontal',
                        'fieldConfig' => [
                            'template' => "{label}\n<div class=\"col-lg-5\">{input}</div>\n<div class=\"col-lg-12\">{error}</div>",
                            'labelOptions' => ['class' => 'col-lg-1'],
                        ],
                    ]
                    ]);
                    ?>
                    <input type="hidden" id="removecourseid" name="removecourseid">
                    <?php if( $user->parent_id == null)
                    { ?>

                        <div class="form-group">
                            <div class="col-lg-12" style="padding-left: 0;">
                                <?= Html::submitButton('Удалить курс', ['class' => 'btn btn-danger remove-course-btn', 'name' => 'remove-course-button']) ?>
                            </div>
                        </div>
                        <?php
                    }
                    ?>
                    <?php ActiveForm::end(); ?>
                </div>
                <?php Pjax::begin(['id' => 'graph-list-pjax']) ?>
                <div class="graph-list-form">
                    <?php
                        if($dataProvider != null)
                        {
                            echo ListView::widget([
                                'dataProvider' => $dataProvider,
                                'itemView' => 'graph_item',
                                'summary' => "Показано {begin} - {end} из {totalCount} позиций",
                            ]);
                        }
                    ?>
                </div>
                <?php Pjax::end(); ?>
            </div>
            <div class="modal-footer">
            </div>
        </div>
    </div>
</div>


