<?php

namespace app\controllers;

use app\models\ChildList;
use app\models\PreferencesForm;
use app\models\SetParentForm;
use app\models\User;
use app\models\UserPreferences;
use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;


class SettingsController extends Controller
{

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
                    [
                        'actions' => ['get-firebase-token'], //делаем для гостей доступным только страницу логина
                        'allow' => true,
                        'roles' => ['?'],
                    ],
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
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
        return $this->render('/settings/index');
    }

    public function actionSavePreferences()
    {
        if(!Yii::$app->request->isPost)
            return $this->render('/settings/index');
        $model = new PreferencesForm();
        $model->flush();
        if(!$model->load(Yii::$app->request->post(), 'PreferencesForm'))
        {
            $this->goBack();
        }

        //заполним данные
        $dbuserpref = UserPreferences::find()
            ->where(['user_id' => Yii::$app->user->id])
            ->one();
        $dbuser = User::find()
            ->where(['id' => Yii::$app->user->id])
            ->one();

        if($dbuserpref == null)
        {
            $dbuserpref = new UserPreferences();
            $dbuserpref->user_id = $dbuser->id;
        }

        $dbuserpref->email_notify = $model->emailNotify;
        $dbuserpref->phone_notify = $model->phoneNotify;

        $dbuserpref->alert_days_to_expired = $model->alert_days_to_expired;
        $dbuserpref->alert_critical_pcs_left = $model->alert_critical_pcs_left;

        $dbuserpref->address = $model->address;

        $dbuser->email = $model->email;
        $dbuser->phone = $model->phone;

        $dbuserpref->save();
        $dbuser->save();

        return $this->render('index');
    }

    public function actionSetParent()
    {
        $model = new SetParentForm();

        if(!$model->load(Yii::$app->request->post(), 'SetParentForm'))
        {
            $this->goBack();
        }

        $dbuser = User::find()
            ->where(['id' => Yii::$app->user->id])
            ->one();

        //устанавливаем родителського пользователя
        //проверим, есть или во бще таккой пользователь
        $dbparent = User::find()
            ->where(['username' => $model->parent_id])
            ->one();

        $answer = null;
        if($dbparent != null)
        {
            $dbuser->parent_id = $dbparent->id;
            $dbuser->save();
            return $this->render('family_preferences');
        }
        else
        {
            $answer = 'Не найден пользователь '.$model->parent_id;
            return $this->render('family_preferences', compact('model', 'answer'));
        }
    }

    public function actionRemoveParent()
    {
        $model = new SetParentForm();

        if(!$model->load(Yii::$app->request->post(), 'SetParentForm'))
        {
            $this->goBack();
        }

        $dbuser = User::find()
            ->where(['id' => Yii::$app->user->id])
            ->one();

        //устанавливаем родителського пользователя

        $answer = null;
        $dbuser->parent_id = null;
        $dbuser->save();
        return $this->render('family_preferences');
    }

    public function actionDeleteChild($username)
    {
        //удаляем
        $dbuser = User::find()
            ->where(['username' => $username])
            ->one();
        $dbuser->parent_id = null;
        $dbuser->save();
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            return ['success' => true];
        }
        return $this->redirect(['settings/index']);
    }

    public function actionSetFirebaseToken()
    {
        $token = $_POST['token'];
        $dbuserpref = UserPreferences::find()
            ->where(['user_id' => Yii::$app->user->id])
            ->one();
        if($dbuserpref == null)
        {
            $dbuserpref = new UserPreferences();
            $dbuserpref->user_id = Yii::$app->user->id;
        }
        $dbuserpref->firebase_token = $token;
        $dbuserpref->save();
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            return ['success' => true];
        }
    }

    public function actionGetFirebaseToken()
    {
        $dbuserpref = UserPreferences::find()
            ->where(['user_id' => Yii::$app->user->id])
            ->one();

        if($dbuserpref == null)
        {
            if (Yii::$app->request->isAjax) {
                Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
                return ['success' => true];
            }
        }

        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            return ['success' => true, 'data' => $dbuserpref->firebase_token];
        }
    }
}
