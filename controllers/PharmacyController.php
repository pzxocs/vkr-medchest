<?php

namespace app\controllers;

use app\models\Pharmacy;
use app\models\UserPreferences;
use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use app\models\LoginForm;
use app\models\ContactForm;

class PharmacyController extends Controller
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
        $model = new Pharmacy();
        $dbuserpref = UserPreferences::find()
            ->where(['user_id' => Yii::$app->user->id])
            ->one();
        $model->address = $dbuserpref->address;
        return $this->render('/pharmacy/index', ['model' => $model]);
    }

}
