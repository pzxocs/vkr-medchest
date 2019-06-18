<?php

use app\models\User;
use yii\helpers\Html;
use yii\helpers\HtmlPurifier;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use yii\widgets\Pjax;

if($model->plan_take_date != null)
{
    $plan_date = DateTime::createFromFormat('Y-m-d H:i:s', $model->plan_take_date)->format("d.m.Y H:i");
}
if($model->fact_take_date != null)
{
    $fact_date = DateTime::createFromFormat('Y-m-d H:i:s', $model->fact_take_date)->format("d.m.Y H:i");
}

$this->registerJs("                                      
                    $('.remove-graph-btn-".$model->graph_id."').on('click', function(e) {
                         e.preventDefault();
                         var deleteUrl = '/index.php?r=graph%2Fremove-graph&id=' + ".$model->graph_id.";                         
                         var result = confirm('Действительно хотите удалить эту запись?');                                
                         if(result) {
                             $.ajax({
                                 url: deleteUrl,
                                 type: 'post',
                                 error: function(xhr, status, error) {
                                     alert('There was an error with your request.' + xhr.responseText);
                                 }
                             }).done(function(data) {                                 
                                 $.pjax.reload({container: '#graph-list-pjax', 
                                    url: '/index.php?r=graph%2Fgraph-list&id=' + $('#removecourseid').val(), async: false });
                             });
                         }
                     });
                ");


$this->registerJs('$(document).on("pjax:timeout", function(event) {
        // Prevent default timeout redirection behavior
        event.preventDefault()
    });');



$user = User::find()->where(['id' => Yii::$app->user->id])->one();

?>
<?php Pjax::begin(['id' => 'confirm-take-pjax-'.$model->graph_id, 'timeout' => 10000, 'enablePushState' => false, 'enableReplaceState' => false]); ?>

<div class="post graph-item">
    <?php
        if($model->message != null)
        {
            echo '<div class="row">
                <div class="col-xs-12">
                    <span style="color:red;">'.$model->message.'</span>    
                </div>
            </div>';
        }
    ?>

    <div class="row">
        <div class="col-xs-2">Дата и время: </div>
        <div class="col-xs-5">
            <span><?php
                    echo $plan_date;
                ?></span>
            <br/>
            <?php
            if($model->is_before_eat == 1)
            {
                echo '<span>До еды</span>';
            }
            if($model->is_after_eat == 1)
            {
                echo '<span>После еды</span>';
            }
            ?>
        </div>
            <?php
            if($model->fact_take_date == null)
            {
                echo '<div class="col-xs-1">';
                $form = ActiveForm::begin(['action' => Url::to(["graph/confirm-take", 'id'=> $model->graph_id], true), 'options' => ['data-pjax' => true,
                    'id' => 'confirm-take-form',
                    'layout' => 'horizontal',
                    'fieldConfig' => [
                        'template' => "{label}\n<div class=\"col-lg-5\">{input}</div>\n<div class=\"col-lg-12\">{error}</div>",
                        'labelOptions' => ['class' => 'col-lg-1'],
                    ],
                ]
                ]); ?>
                <div class="form-group">
                    <div class="col-lg-12">
                        <?= Html::submitButton('<i class="fas fa-check"></i>', ['class' => 'btn btn-default', 'name' => 'confirm-take-button']) ?>
                    </div>
                </div>

                <?php ActiveForm::end(); ?>
                <?php
            }
            else
            {
                echo '<div class="col-xs-5">';
                if($model->is_done == 1)
                {
                    echo 'Принято: '. $fact_date;
                }
                else
                {
                    echo 'Пропущено: '. $fact_date;
                }
            }
            ?>
        </div>
            <?php
            if($model->fact_take_date == null)
            {

            echo '<div class="col-xs-1">';
                $form = ActiveForm::begin(['action' => Url::to(["graph/miss-take", 'id'=> $model->graph_id], true), 'options' => ['data-pjax' => true,
                'id' => 'miss-take-form',
                'layout' => 'horizontal',
                'fieldConfig' => [
                'template' => "{label}\n<div class=\"col-lg-5\">{input}</div>\n<div class=\"col-lg-12\">{error}</div>",
                'labelOptions' => ['class' => 'col-lg-1'],
                ],
                ]
                ]); ?>
                <div class="form-group">
                    <div class="col-lg-12">
                        <?= Html::submitButton('<i class="fas fa-times"></i>', ['class' => 'btn btn-default', 'name' => 'confirm-take-button']) ?>
                    </div>
                </div>

                <?php ActiveForm::end(); ?>

                <?php
            }
            else
            {
                Html::encode('Принято: '.$model->fact_take_date);
            }
            ?>
        </div>
        <?php
            if( $user->parent_id == null)
            { ?>
                <div class="col-xs-1" style="padding-left: 30px; padding-top: 15px;">
                    <?php
                    if($model->fact_take_date == null)
                    {
                        ?>

                        <button type="button" class="btn btn-default" onclick="openEditGraphItem(<?php echo $model->graph_id; ?>)"><i class="fas fa-cog"></i></button>
                        <?php
                    }
                    else
                    {
                        Html::encode('Принято: '.$model->fact_take_date);
                    }
                    ?>
                </div>
                <div class="col-xs-1">
                    <?php
                    if($model->fact_take_date == null)
                    {
                        $form = ActiveForm::begin(['action' => Url::to(["graph/remove-graph"], true), 'options' => ['data-pjax' => true,
                            'id' => 'remove-graph-form',
                            'layout' => 'horizontal',
                            'fieldConfig' => [
                                'template' => "{label}\n<div class=\"col-lg-5\">{input}</div>\n<div class=\"col-lg-12\">{error}</div>",
                                'labelOptions' => ['class' => 'col-lg-1'],
                            ],
                        ]
                        ]); ?>
                        <div class="form-group">
                            <div class="col-lg-12">
                                <?= Html::submitButton('<i class="fas fa-trash"></i>', ['class' => 'btn btn-danger remove-graph-btn-'.$model->graph_id, 'name' => 'remove-graph-button']) ?>
                            </div>
                        </div>
                        <?php ActiveForm::end(); ?>
                        <?php
                    }
                    else
                    {
                        Html::encode('Принято: '.$model->fact_take_date);
                    }
                    ?>
                </div>

        <?php
        }
        ?>
    </div>
</div>
<?php Pjax::end(); ?>