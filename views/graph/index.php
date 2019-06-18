<?php

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */

use app\models\Course;
use app\models\Graph;
use app\models\User;
use yii\data\ActiveDataProvider;
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;use yii\widgets\Pjax;

$this->title = 'График приема - MedChest';
?>

<h4>График приема препаратов</h4>

<div style="padding: 20px;">

    <?php
    $user = User::find()->where(['id' => Yii::$app->user->id])->one();
    if( $user->parent_id == null)
    { ?>
        <!-- Indicates a successful or positive action -->
        <button type="button" class="btn btn-success" data-toggle="modal" data-target="#add_graph_modal">Добавить</button>
        <?php
    }
    else
    {
        ?>
        <h4>Вы не можете добавлять и удалять курсы лечения, так как привязаны к семейному профилю.</h4>
        <?php
    }
    ?>    

    <?php

    echo $this->render('add_graph', [
        'model' => new Course(),
    ]);

    echo $this->render('edit_graph_item', [
        'model' => new Graph(),
    ]);

    $dataProvider = new ActiveDataProvider([
        'query' => Graph::find()->where(['course_id' => null]),
        'pagination' => [
            'pageSize' => 10,
        ],
    ]);

    echo $this->render('list_graph', ['dataProvider' => $dataProvider]);

    Pjax::begin(['id' => 'graph']);
        $dataprovider = Course::find()
            ->where(['u1.id' => Yii::$app->user->id])
            ->orWhere(['u1.id' => \app\models\User::find()->where(['parent_id' => Yii::$app->user->id])->select(['id'])->asArray()])
            ->join('LEFT JOIN', 'user u1', 'u1.id = course.user_id')
            ->join('LEFT JOIN', 'user u2', 'u1.id = u2.parent_id')
            ->join('LEFT JOIN', 'medicine', 'medicine.medicine_id = course.medicine_id')
            ->select(['course_id as course_id', 'begin_date as begin_date', 'end_date as end_date', 'medicine.name as medicine_name',
                'u1.name as user_name',
                '(select count(*) from graph g where g.course_id = course.course_id) as total_graphs',
                '(select count(*) from graph g where g.course_id = course.course_id and g.fact_take_date is not null) as done_graphs',
            ])->asArray()->all();

        $resstring = '';
        //наполняем массив

        foreach ($dataprovider as &$course) {
            $resstring .= '{
                            id: \''.$course["course_id"].'\',
                            name: \''.$course["user_name"].' - '.$course["medicine_name"].'\',
                            start: \''.$course["begin_date"].'\',
                            end: \''.$course["end_date"].'\',
                            progress: '.($course["total_graphs"] == null || $course["total_graphs"] == 0 ? 0 : (($course["done_graphs"] / $course["total_graphs"]) * 100)).',                            
                        },';
        }

        if($dataProvider != null && count($dataprovider) > 0)
        {
            echo'
                <script>
                    $("document").ready(function(){
                        //заполним наш массив данными о текущих графиках                    
                        var tasks = '.
                '[
                            '.
                $resstring
                .'
                        ];
                        initGantt(tasks);
                    });
                </script>
                
            ';
            echo '<svg id="gantt"></svg>';
        }
        else
        {
            if ( $user->parent_id == null)
            {
                echo '<h3>Добавьте курсы приема...</h3>';
            }
            else
            {
                echo '<h3>Нет текущих курсов лечения.</h3>';
            }

        }
    Pjax::end();
?>

</div>
