<?php

namespace app\controllers;

use app\commands\NotifyController;
use app\models\Course;
use app\models\Graph;
use DateTime;
use Yii;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\web\Controller;


class GraphController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
        ];
    }

        /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionIndex()
    {
        $model = new Course();
        if ($model->load(Yii::$app->request->post()))
        {
            //проверим правильность
            if(!$model->validate())
            {
                return $this->render('/graph/add_graph', ['model' => $model]);
            }
            //заполняем полученными данными реальную таблицу
            $model->createNew();
        }
        return $this->render('/graph/index');
    }

    public function actionRemoveCourse($id)
    {
        //удалим сначала графы
        Graph::deleteAll(['course_id' => $id]);
        //сам ккурс
        $dbcourse = Course::find()->where(['course_id' => $id])->one();
        $dbcourse->delete();

        return $this->render('/graph/index');
    }

    private static $csrf;

    public function actionConfirmTake($id)
    {
        if (!Yii::$app->request->isAjax) {
            return $this->redirect('/graph/index');
        }

        //подтверждаем прием
        $dbgraph = Graph::find()->where(['graph_id' => $id])->one();
        if($dbgraph->fact_take_date != null)
        {
            return $this->render('/graph/graph_item',
                ['model' => $dbgraph]
            );
        }

        if($dbgraph->reduceOne())
        {
            $dbgraph->is_done = 1;
            $dbgraph->fact_take_date = date('Y-m-d H:i:s');
            $dbgraph->hours = 0;
            $dbgraph->minutes = 0;
            $dbgraph->save();
        }
        else
        {
            $dbgraph->message = "Ошибка: препарат закончился";
            return $this->render('/graph/graph_item',
                ['model' => $dbgraph]
            );
        }

        $course = Course::find()->where(['course_id' => $dbgraph->course_id])->one();

        //отмечаем, что у нас запасы уменьшились, проверяем нужно ли отослать уведомление об этом
        $res = $dbgraph->isNeedAlert();
        if($res > 0) {
            $controller = new NotifyController(Yii::$app->controller->id, Yii::$app);
            $controller->actionSendLowLevel($course->user_id, $res);
        }

        if (!Yii::$app->request->isAjax) {
            return $this->redirect('/graph/index');
        }

        return $this->render('/graph/graph_item',
            ['model' => $dbgraph]
        );
    }

    public function actionRemoveGraph($id)
    {
        if (!Yii::$app->request->isAjax) {
            return $this->redirect('/graph/index');
        }

        //удаление приемп
        $dbgraph = Graph::find()->where(['graph_id' => $id])->one();
        $course_id = $dbgraph->course_id;
        $dbgraph->delete();
        //определим, если удаление графа корректирует длительность курса - тоже его корректируем
        $minDate = DateTime::createFromFormat('Y-m-d H:i:s', Graph::find()
            ->where(['course_id' => $course_id])->orderBy('plan_take_date')->limit(1)->one()->plan_take_date)->format("Y-m-d");
        $maxDate = DateTime::createFromFormat('Y-m-d H:i:s', Graph::find()
            ->where(['course_id' => $course_id])->orderBy('plan_take_date DESC')->limit(1)->one()->plan_take_date)->format("Y-m-d");

        $dbcourse = Course::find()->where(['course_id' => $course_id])->one();
        if($dbcourse->begin_date != $minDate)
        {
            $dbcourse->begin_date = $minDate;
        }
        if($dbcourse->end_date != $maxDate)
        {
            $dbcourse->end_date = $maxDate;
        }
        $dbcourse->save();

        $dataProvider = new ActiveDataProvider([
            'query' => Graph::find()->where(['course_id' => $id]),
            'pagination' => [
                'pageSize' => 5,
            ],
        ]);

        return $this->render('/graph/list_graph',
            ['dataProvider' => $dataProvider]
        );
    }

    public function actionMissTake($id)
    {
        if (!Yii::$app->request->isAjax) {
            return $this->redirect('/graph/index');
        }

        //подтверждаем пропуск
        $dbgraph = Graph::find()->where(['graph_id' => $id])->one();
        $dbgraph->is_done = 0;
        $dbgraph->fact_take_date = date('Y-m-d H:i:s');
        $dbgraph->save();

        return $this->render('/graph/graph_item',
            ['model' => $dbgraph]
        );
    }

    public function actionGraphList($id)
    {
        if (!Yii::$app->request->isAjax) {
            return $this->redirect('/graph/index');
        }

        $dataProvider = new ActiveDataProvider([
            'query' => Graph::find()->where(['course_id' => $id]),
            'pagination' => [
                'pageSize' => 5,
            ],
        ]);

        return $this->render('/graph/list_graph',
            ['dataProvider' => $dataProvider]
        );
    }

    public function actionEditGraph($id)
    {
        if (!Yii::$app->request->isAjax) {
            return $this->redirect('/graph/index');
        }

        $model = new Graph();
        if ($model->load(Yii::$app->request->post()))
        {
            //проверим правильность
            if(!$model->validate())
            {
                return $this->render('/graph/edit_graph_item', ['model' => $model]);
            }
            //заполняем полученными данными реальную таблицу
            $model->editGraph();
        }


        $graph = Graph::find()->where(['graph_id' => $id])->one();
        $graph->hours = DateTime::createFromFormat('Y-m-d H:i:s', $graph->plan_take_date)->format('H');
        $graph->minutes = DateTime::createFromFormat('Y-m-d H:i:s', $graph->plan_take_date)->format('i');

        return $this->render('/graph/edit_graph_item',
            ['model' => $graph]
        );
    }
}
