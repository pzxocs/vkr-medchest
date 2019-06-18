<?php

namespace app\controllers;

use app\models\Medicine;
use app\models\Recipe;
use app\models\RecipeSearch;
use app\models\User;
use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use app\models\LoginForm;
use app\models\ContactForm;

class RecipeController extends Controller
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
        $model = new Recipe();
        if ($model->load(Yii::$app->request->post()))
        {
            //проверим правильность
            if(!$model->validate())
            {
                return $this->render('/recipe/add_recipe', ['model' => $model]);
            }
            //заполняем полученными данными реальную таблицу
            $model->createNew();
        }

        $searchModel = new RecipeSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->get());

        return $this->render('/recipe/index', [
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
        ]);
    }

    public function actionUpdate($id)
    {
        if (!Yii::$app->request->isAjax) {
            return $this->redirect(['recipe/index']);
        }

        $model = new Recipe();
        if ($model->load(Yii::$app->request->post()))
        {
            //редактируем
            $model->edit();

            $model->recipe_id = null;
            return $this->render('/recipe/add_recipe',
                ['model' => $model]
            );
        }

        $recipe = Recipe::find()->where(['recipe_id' => $id])->one();
        //заполним сопутствующие поля

        $dbmedicine = Medicine::find()->where(['medicine_id' => $recipe->medicine_id])->one();
        $dbuser = User::find()->where(['id' => $recipe->user_id])->one();

        $recipe->medicine_name = $dbmedicine->name;
        $recipe->user_name = $dbuser->name;

        $recipe->issue_date_picker = $recipe->issue_date;
        $recipe->valid_date_picker = $recipe->valid_date;

        return $this->render('/recipe/add_recipe',
            ['model' => $recipe]
        );
    }

    public function actionDelete($id)
    {
        //удаляем
        $dbrecipe = Recipe::find()
            ->where(['recipe_id' => $id])
            ->one();
        $dbrecipe->delete();
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            return ['success' => true];
        }
        return $this->redirect(['recipe/index']);
    }
}
