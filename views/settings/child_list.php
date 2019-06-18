<?php

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */

use app\models\ChildList;
use app\models\Medicine;
use app\models\MedicineItem;
use yii\data\ActiveDataProvider;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use yii\helpers\Url;
use yii\widgets\Pjax;

$searchModel = new ChildList();
$dataProvider = $searchModel->search(Yii::$app->request->get());

?>


<div style="padding: 25px;">


<!-- Indicates a successful or positive action -->

<?php

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

     });
 ");

Pjax::begin(['id' => 'children']);
echo GridView::widget([
    'dataProvider' => $dataProvider,
    'filterModel' => $searchModel,
    'summary' => "Показано {begin} - {end} из {totalCount} позиций",
    'columns' => [
        // Обычные поля определенные данными содержащимися в $dataProvider.
        // Будут использованы данные из полей модели.
        ['attribute' => 'username', 'header' => 'Логин'],
        ['attribute' => 'name', 'header' => 'Имя'],
        ['attribute' => 'email', 'header' => 'Email'],
        ['attribute' => 'phone', 'header' => 'Телефон'],
        ['class' => 'yii\grid\ActionColumn',
            'template' => '{delete}',
            'buttons' => [
                'delete' => function ($url, $model) {
                    return Html::a('<span class="glyphicon glyphicon-trash" style="cursor: pointer;"></span>', false, [
                        'class' => 'pjax-delete-link',
                        'delete-url' => Url::to(["settings/delete-child", 'username' => $model->username], true),
                        'pjax-container' => 'children',
                        'title' => Yii::t('yii', 'Удалить')
                    ]);
                },
            ],
        ]
    ],
]);
Pjax::end();
?>

</div>
