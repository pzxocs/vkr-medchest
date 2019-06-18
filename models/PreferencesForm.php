<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\db\ActiveRecord;

class PreferencesForm extends Model
{
    public $phoneNotify;
    public $emailNotify;
    public $phone;
    public $email;
    public $alert_days_to_expired;
    public $alert_critical_pcs_left;
    public $address;


    public function rules()
    {
        return [
            [['phoneNotify', 'emailNotify','phone','email','alert_days_to_expired','alert_critical_pcs_left', 'address'], 'safe'],
            ['email', 'email', 'message'=>'Введенное значнеие не является правильным адресом электронной почты'],
        ];
    }

    function __construct() {
        parent::__construct();
        //заполним данные
        $dbuserpref = UserPreferences::find()
            ->where(['user_id' => Yii::$app->user->id])
            ->one();
        $dbuser = User::find()
            ->where(['id' => Yii::$app->user->id])
            ->one();

        if($dbuserpref != null)
        {
            $this->emailNotify = $dbuserpref->email_notify;
            $this->phoneNotify = $dbuserpref->phone_notify;
            $this->alert_days_to_expired = $dbuserpref->alert_days_to_expired;
            $this->alert_critical_pcs_left = $dbuserpref->alert_critical_pcs_left;
            $this->address = $dbuserpref->address;
        }

        if($dbuser != null)
        {
            $this->email = $dbuser->email;
            $this->phone = $dbuser->phone;
        }
    }

    function flush()
    {
        $this->email = null;
        $this->phone = null;
        $this->emailNotify = null;
        $this->phoneNotify = null;
        $this->alert_days_to_expired = null;
        $this->alert_critical_pcs_left = null;
        $this->address = null;
    }
}


