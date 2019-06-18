<?php

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */

use app\models\Recipe;
use app\models\User;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use yii\data\ActiveDataProvider;
use yii\helpers\Url;
use yii\widgets\Pjax;

$this->title = 'Рецепты - MedChest';
?>

<div style="padding: 25px;">

    <?php
    $user = User::find()->where(['id' => Yii::$app->user->id])->one();
    if( $user->parent_id == null)
    { ?>
        <!-- Indicates a successful or positive action -->
        <button type="button" class="btn btn-success" data-toggle="modal" data-target="#add_recipe_modal">Добавить</button>
        <?php
    }
    else
    {
        ?>
        <h4>Вы не можете добавлять и удалять рецепты, так как привязаны к семейному профилю.</h4>
        <?php
    }
    ?>



<?php

echo $this->render('add_recipe', [
    'model' => new Recipe(),
]);

Pjax::begin(['id' => 'recipes']);

$this->registerJs("
     $('document').ready(function() {                    
         $('.pjax-delete-link').on('click', function(e) {
             e.preventDefault();
             var deleteUrl = $(this).attr('delete-url');
             var pjaxContainer = $(this).attr('pjax-container');
             var result = confirm('Действительно хотите удалить эту запись?');                                
             if(result) {
                 $.ajax({
                     url: deleteUrl,
                     type: 'post',
                     error: function(xhr, status, error) {
                         alert('There was an error with your request.' + xhr.responseText);
                     }
                 }).done(function(data) {
                     $.pjax.reload('#' + $.trim(pjaxContainer), {timeout: 3000});
                 });
             }
         });
         
         $('.pjax-update-link').on('click', function(e) {
             e.preventDefault();
             var updateUrl = $(this).attr('update-url');
             var updateId = $(this).attr('update-id');
             var pjaxContainer = $(this).attr('pjax-container');
             //откроем всплывающее окно
             $('#add_recipe_modal').modal('show');
             $('#recipe-recipe_id').val(updateId);                          
         });

     });
 ");

    if( $user->parent_id == null)
    {
        echo GridView::widget([
            'dataProvider' => $dataProvider,
            'filterModel' => $searchModel,
            'summary' => "Показано {begin} - {end} из {totalCount} позиций",
            'columns' => [
                // Обычные поля определенные данными содержащимися в $dataProvider.
                // Будут использованы данные из полей модели.
                //'medicine_item_id',
                ['attribute' => 'user_name', 'label' => 'Пользователь'],
                ['attribute' => 'author', 'label' => 'Кем выдан'],
                ['attribute' => 'medicine_name', 'label' => 'Препарат'],
                ['attribute' => 'issue_date', 'label' => 'Дата получения', 'format' => ['date', 'dd.MM.yyyy']],
                ['attribute' => 'valid_date', 'label' => 'Действителен до', 'format' => ['date', 'dd.MM.yyyy']],
                [
                    'class' => 'yii\grid\CheckboxColumn',
                    'header' => 'Использован',
                    'checkboxOptions' => function($model) {
                        return ['checked' => $model->is_used == 1 ? true : false];
                    }
                ],
                ['attribute' => 'comment', 'label' => 'Комментарий'],
                ['class' => 'yii\grid\ActionColumn',
                    'template' => '{delete}{update}',
                    'buttons' => [
                        'delete' => function ($url, $model) {
                            return Html::a('<span class="glyphicon glyphicon-trash" style="cursor: pointer;"></span>', false, [
                                'class' => 'pjax-delete-link',
                                'delete-url' => Url::to(["recipe/delete", 'id' => $model->recipe_id], true),
                                'pjax-container' => 'recipes',
                                'title' => Yii::t('yii', 'Удалить')
                            ]);
                        },
                        'update' => function ($url, $model) {
                            return Html::a('<span class="glyphicon glyphicon-pencil" style="cursor: pointer;"></span>', false, [
                                'class' => 'pjax-update-link',
                                'update-url' => Url::to(["recipe/update", 'id' => $model->recipe_id], true),
                                'update-id' => $model->recipe_id,
                                'pjax-container' => 'recipes',
                                'title' => Yii::t('yii', 'Изменить')
                            ]);
                        },
                    ],
                ]
            ],
        ]);
    }
    else
    {
        echo GridView::widget([
            'dataProvider' => $dataProvider,
            'filterModel' => $searchModel,
            'summary' => "Показано {begin} - {end} из {totalCount} позиций",
            'columns' => [
                // Обычные поля определенные данными содержащимися в $dataProvider.
                // Будут использованы данные из полей модели.
                //'medicine_item_id',
                ['attribute' => 'user_name', 'label' => 'Пользователь'],
                ['attribute' => 'author', 'label' => 'Кем выдан'],
                ['attribute' => 'medicine_name', 'label' => 'Препарат'],
                ['attribute' => 'issue_date', 'label' => 'Дата получения', 'format' => ['date', 'dd.MM.yyyy']],
                ['attribute' => 'valid_date', 'label' => 'Действителен до', 'format' => ['date', 'dd.MM.yyyy']],
                [
                    'class' => 'yii\grid\CheckboxColumn',
                    'header' => 'Использован',
                    'checkboxOptions' => function($model) {
                        return ['checked' => $model->is_used == 1 ? true : false];
                    }
                ],
                ['attribute' => 'comment', 'label' => 'Комментарий'],
            ],
        ]);
    }


Pjax::end();
?>

</div>