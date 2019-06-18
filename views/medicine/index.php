<?php

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */

use app\models\MedicineItem;
use app\models\User;
use yii\data\ActiveDataProvider;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use yii\helpers\Url;
use yii\widgets\Pjax;

$this->title = 'Мои лекарства - MedChest';
?>


<div style="padding: 25px;">

<?php
$user = User::find()->where(['id' => Yii::$app->user->id])->one();
if( $user->parent_id == null)
{ ?>
    <!-- Indicates a successful or positive action -->
    <button type="button" class="btn btn-success" data-toggle="modal" data-target="#add_medicine_modal">Добавить</button>
<?php
}
else
{
?>
    <h4>Вы не можете добавлять и удалять лекарства, так как привязаны к семейному профилю.</h4>
<?php
}
?>


<?php

echo $this->render('add_medicine', [
    'model' => new MedicineItem(),
]);

Pjax::begin(['id' => 'medicines']);

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
             $('#add_medicine_modal').modal('show');
             $('#medicineitem-medicine_item_id').val(updateId);                          
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
                ['attribute' => 'medicine_name', 'label' => 'Наименование'],
                ['attribute' => 'medicine_form', 'label' => 'Форма'],
                ['attribute' => 'active_material', 'label' => 'Действ. вещество'],
                ['attribute' => 'storage_place', 'label' => 'Место хранения'],
                ['attribute' => 'expired', 'label' => 'Годен до', 'format' => ['date', 'dd.MM.yyyy']],
                //['attribute' => 'days_open', 'header' => 'От'],
                ['attribute' => 'dosage', 'label' => 'Дозировка'],
                ['attribute' => 'pcs_left', 'label' => 'Осталось'],
                ['attribute' => 'pcs', 'label' => 'Кол-во в пачке'],
                ['class' => 'yii\grid\ActionColumn',
                    'template' => '{delete}{update}',
                    'buttons' => [
                        'delete' => function ($url, $model) {
                            return Html::a('<span class="glyphicon glyphicon-trash" style="cursor: pointer;"></span>', false, [
                                'class' => 'pjax-delete-link',
                                'delete-url' => Url::to(["medicine/delete", 'id' => $model->medicine_item_id], true),
                                'pjax-container' => 'medicines',
                                'title' => Yii::t('yii', 'Удалить')
                            ]);
                        },
                        'update' => function ($url, $model) {
                            return Html::a('<span class="glyphicon glyphicon-pencil" style="cursor: pointer;"></span>', false, [
                                'class' => 'pjax-update-link',
                                'update-url' => Url::to(["medicine/update", 'id' => $model->medicine_item_id], true),
                                'update-id' => $model->medicine_item_id,
                                'pjax-container' => 'medicines',
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
                ['attribute' => 'medicine_name', 'label' => 'Наименование'],
                ['attribute' => 'medicine_form', 'label' => 'Форма'],
                ['attribute' => 'active_material', 'label' => 'Действ. вещество'],
                ['attribute' => 'storage_place', 'label' => 'Место хранения'],
                ['attribute' => 'expired', 'label' => 'Годен до', 'format' => ['date', 'dd.MM.yyyy']],
                ['attribute' => 'dosage', 'label' => 'Дозировка'],
                ['attribute' => 'pcs_left', 'label' => 'Осталось']
            ],
        ]);
    }
    Pjax::end();
?>

</div>
