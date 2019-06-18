<?php

namespace app\controllers;

use app\models\Medicine;
use app\models\MedicineForm;
use app\models\MedicineItem;
use app\models\MedItemSearch;
use app\models\User;
use Yii;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use app\models\LoginForm;
use app\models\ContactForm;

class MedicineController extends Controller
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
        $model = new MedicineItem();
        if ($model->load(Yii::$app->request->post()))
        {
            //проверим правильность
            if(!$model->validate())
            {
                return $this->render('/medicine/add_medicine', ['model' => $model]);
            }
            //заполняем полученными данными реальную таблицу
            $model->createNew();
        }

        $searchModel = new MedItemSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->get());

        return $this->render('/medicine/index', [
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
        ]);
    }

    public function actionUpdate($id)
    {
        if (!Yii::$app->request->isAjax) {
            return $this->redirect(['medicine/index']);
        }

        $model = new MedicineItem();
        if ($model->load(Yii::$app->request->post()))
        {
            //редактируем
            $model->edit();

            $model->medicine_item_id = null;
            return $this->render('/medicine/add_medicine',
                ['model' => $model]
            );
        }

        $meditem = MedicineItem::find()->where(['medicine_item_id' => $id])->one();
        //заполним сопутствующие поля

        $dbmedicine = Medicine::find()->where(['medicine_id' => $meditem->medicine_id])->one();
        $dbForm = MedicineForm::find()->where(['medicine_form_id' => $dbmedicine->medicine_form_id])->one();
        $dbuser = User::find()->where(['id' => $meditem->user_id])->one();

        $meditem->medicine_name = $dbmedicine->name;
        $meditem->medicine_form = $dbForm->name;
        $meditem->active_material = $dbmedicine->active_material;
        $meditem->storage_place = $dbmedicine->storage_place;
        $meditem->user_name = $dbuser->name;
        $meditem->pcs = $dbmedicine->pcs;

        $meditem->expired_picker = $meditem->expired;

        return $this->render('/medicine/add_medicine',
            ['model' => $meditem]
        );
    }

    public function actionDelete($id)
    {
        //удаляем
        $dbmedicine = MedicineItem::find()
            ->where(['medicine_item_id' => $id])
            ->one();
        $dbmedicine->delete();
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            return ['success' => true];
        }
        return $this->redirect(['medicine/index']);
    }
}
